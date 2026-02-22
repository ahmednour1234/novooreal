<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\CPU\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{


public function trialBalance(Request $request)
{
    // ===== 1) فلاتر =====
    $from = $request->filled('from_date')
        ? Carbon::parse($request->input('from_date'))->startOfDay()
        : Carbon::create(1900, 1, 1);
    $to = $request->filled('to_date')
        ? Carbon::parse($request->input('to_date'))->endOfDay()
        : now()->endOfDay();

    $viewMode    = $request->input('view_mode', 'summary'); // summary | details
    $levelSel    = max(1, (int)$request->input('level', 2));
    $showZero    = $request->boolean('show_zero', false);
    $showCode    = $request->boolean('show_code', false); // افتراضي مخفي
    $withOpening = $request->boolean('with_opening', true);

    $fromStr = $from->toDateString();
    $toStr   = $to->toDateString();

    // ===== 2) تجميع self لكل حساب =====
    $agg = DB::table('transections as t')
        ->select('t.account_id')
        ->selectRaw('SUM(IF(t.date < ?,  t.debit,  0)) as debit_before',  [$fromStr])
        ->selectRaw('SUM(IF(t.date < ?,  t.credit, 0)) as credit_before', [$fromStr])
        ->selectRaw('SUM(IF(t.date BETWEEN ? AND ?, t.debit,  0)) as debit_period',  [$fromStr, $toStr])
        ->selectRaw('SUM(IF(t.date BETWEEN ? AND ?, t.credit, 0)) as credit_period', [$fromStr, $toStr])
        ->groupBy('t.account_id');

    // ===== 3) جلب الحسابات + النوع (استبعاد account_type = other نهائيًا) =====
    $raw = DB::table('accounts as a')
        ->leftJoinSub($agg, 'x', 'x.account_id', '=', 'a.id')
        ->select(
            'a.id', 'a.parent_id', 'a.code',
            DB::raw('a.account as account_name'),
            'a.account_type',
            DB::raw('COALESCE(x.debit_before,  0) as debit_before'),
            DB::raw('COALESCE(x.credit_before, 0) as credit_before'),
            DB::raw('COALESCE(x.debit_period,  0) as debit_period'),
            DB::raw('COALESCE(x.credit_period, 0) as credit_period')
        )
        ->where(function($q){
            // استبعاد other (مع اختلاف حالة الأحرف والمسافات)
            $q->whereNull('a.account_type')
              ->orWhereRaw("LOWER(TRIM(a.account_type)) <> 'other'");
        })
        ->orderBy('a.code', 'asc')
        ->get();

    if ($raw->isEmpty()) {
        return view('admin-views.reports.trial_balance', [
            'rows' => [],
            'totals' => [
                'opening_debit' => 0, 'opening_credit' => 0,
                'period_debit' => 0,  'period_credit'  => 0,
                'ending_debit'  => 0, 'ending_credit'  => 0,
            ],
            'diff' => ['opening'=>0,'period'=>0,'ending'=>0],
            'filters' => [
                'from_date' => $request->input('from_date'),
                'to_date'   => $request->input('to_date'),
                'view_mode' => $viewMode,
                'level'     => $levelSel,
                'show_code' => $showCode,
                'with_opening' => $withOpening,
                'show_zero' => $showZero,
            ],
            'maxLevel' => 1,
        ]);
    }

    // ===== 4) بناء شجرة (لـ details فقط) =====
    $nodes = [];
    $children = [];
    foreach ($raw as $r) {
        $nodes[$r->id] = [
            'id' => $r->id,
            'parent_id' => $r->parent_id,
            'code' => $r->code,
            'account_name' => $r->account_name,
            'account_type' => $r->account_type,
            'debit_before'  => (float)$r->debit_before,
            'credit_before' => (float)$r->credit_before,
            'debit_period'  => (float)$r->debit_period,
            'credit_period' => (float)$r->credit_period,
            'level' => null,
        ];
        $pid = $r->parent_id ?: 0;
        $children[$pid] = $children[$pid] ?? [];
        $children[$pid][] = $r->id;
    }

    // مستويات
    $levelCache = [];
    $calcLevel = function($id) use (&$calcLevel, &$nodes, &$levelCache) {
        if (isset($levelCache[$id])) return $levelCache[$id];
        $p = $nodes[$id]['parent_id'] ?? null;
        $lv = ($p && isset($nodes[$p])) ? ($calcLevel($p) + 1) : 1;
        return $levelCache[$id] = $lv;
    };
    $maxLevel = 1;
    foreach ($nodes as $id => &$n) {
        $n['level'] = $calcLevel($id);
        if ($n['level'] > $maxLevel) $maxLevel = $n['level'];
    }
    unset($n);
    $levelSel = min($levelSel, $maxLevel);

    // ===== 5) تحويل self إلى (opening/period/ending) =====
    $makeVals = function(array $n) use ($withOpening) {
        $db = $withOpening ? $n['debit_before']  : 0.0;
        $cb = $withOpening ? $n['credit_before'] : 0.0;

        $openingNet = $db - $cb;
        $opening_debit  = $openingNet > 0 ? $openingNet : 0.0;
        $opening_credit = $openingNet < 0 ? -$openingNet : 0.0;

        $period_debit  = $n['debit_period'];
        $period_credit = $n['credit_period'];

        $endingNet = ($withOpening ? $openingNet : 0.0) + ($period_debit - $period_credit);
        $ending_debit  = $endingNet > 0 ? $endingNet : 0.0;
        $ending_credit = $endingNet < 0 ? -$endingNet : 0.0;

        return compact('opening_debit','opening_credit','period_debit','period_credit','ending_debit','ending_credit');
    };

    // دوال مساعدة لترتيب/تطبيع النوع
    $typeOrder = [
        'asset' => 1, 'assets' => 1, 'current_asset' => 1, 'non_current_asset' => 1, 'fixed_asset' => 1,
        'liability' => 2, 'liabilities' => 2, 'current_liability' => 2, 'non_current_liability' => 2,
        'equity' => 3, 'capital' => 3, 'retained_earnings' => 3,
        'revenue' => 4, 'income' => 4, 'sales' => 4, 'other_income' => 4,
        'cogs' => 5, 'cost_of_goods_sold' => 5,
        'expense' => 6, 'expenses' => 6, 'operating_expense' => 6, 'other_expense' => 6,
        '—' => 99, 'uncategorized' => 99, '' => 99,
    ];
    $normalizeType = function($t) {
        $k = strtolower(trim((string)$t));
        return str_replace([' ', '-'], '_', $k) ?: '—';
    };

    // ===== 6) لو summary: جمّع حسب account_type (مع تجاهل other احتياطيًا) =====
    $rows = [];
    if ($viewMode === 'summary') {
        $byType = []; // type => sums
        foreach ($nodes as $n) {
            $type = $normalizeType($n['account_type'] ?? '—');

            // تأكيد تجاهل other حتى لو وصل من الداتا لأي سبب
            if ($type === 'other') {
                continue;
            }

            $vals = $makeVals($n); // self-based
            if (!isset($byType[$type])) {
                $byType[$type] = [
                    'opening_debit' => 0, 'opening_credit' => 0,
                    'period_debit'  => 0, 'period_credit'  => 0,
                    'ending_debit'  => 0, 'ending_credit'  => 0,
                ];
            }
            foreach ($byType[$type] as $k => $v) {
                $byType[$type][$k] += $vals[$k];
            }
        }

        foreach ($byType as $type => $vals) {
            $isZero = round(array_sum($vals), 2) == 0.0;
            if (!$showZero && $isZero) continue;

            $rows[] = (object) array_merge([
                'level' => 1,
                'code'  => null,
                'account_type' => $type,
                'account_name' => $type,
            ], $vals);
        }

        usort($rows, function($a, $b) use ($typeOrder){
            $wa = $typeOrder[$a->account_type] ?? 50;
            $wb = $typeOrder[$b->account_type] ?? 50;
            if ($wa === $wb) return strcmp((string)$a->account_type, (string)$b->account_type);
            return $wa <=> $wb;
        });
    }
    else {
        // ===== 7) details: عرض حتى المستوى المختار (self فقط) =====
        foreach ($nodes as $id => $n) {
            if ($n['level'] <= $levelSel) {
                $vals = $makeVals($n);
                $row = (object) array_merge([
                    'id' => $id,
                    'level' => $n['level'],
                    'code' => $n['code'],
                    'account_name' => $n['account_name'],
                ], $vals);

                $isZero = round(
                    $row->opening_debit + $row->opening_credit +
                    $row->period_debit  + $row->period_credit  +
                    $row->ending_debit  + $row->ending_credit, 2
                ) == 0.0;

                if (!$showZero && $isZero) continue;

                $rows[] = $row;
            }
        }

        usort($rows, fn($a,$b)=>strcmp((string)$a->code, (string)$b->code));
    }

    // ===== 8) الإجماليات والفرق =====
    $totals = [
        'opening_debit'  => collect($rows)->sum('opening_debit'),
        'opening_credit' => collect($rows)->sum('opening_credit'),
        'period_debit'   => collect($rows)->sum('period_debit'),
        'period_credit'  => collect($rows)->sum('period_credit'),
        'ending_debit'   => collect($rows)->sum('ending_debit'),
        'ending_credit'  => collect($rows)->sum('ending_credit'),
    ];

    $diff = [
        'opening' => $totals['opening_debit'] - $totals['opening_credit'],
        'period'  => $totals['period_debit']  - $totals['period_credit'],
        'ending'  => $totals['ending_debit']  - $totals['ending_credit'],
    ];

    return view('admin-views.reports.trial_balance', [
        'rows'     => $rows,
        'totals'   => $totals,
        'diff'     => $diff,
        'filters'  => [
            'from_date'    => $request->input('from_date'),
            'to_date'      => $request->input('to_date'),
            'view_mode'    => $viewMode,
            'level'        => $levelSel,
            'show_code'    => $showCode,
            'with_opening' => $withOpening,
            'show_zero'    => $showZero,
        ],
        'maxLevel' => $maxLevel,
    ]);
}


}
