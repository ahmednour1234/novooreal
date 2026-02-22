<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder as QueryBuilder;
use App\CPU\Helpers;

class CostCenterReportsController extends Controller
{
    public function transactions(Request $r)
    {
        $q = $this->baseQuery();
        $q = $this->applyFilters($q, $r)
                 ->orderBy('e.entry_date')
                 ->orderBy('d.id');

        $rows = $q->paginate(50)->appends($r->query());

        return view('admin-views.costcenter.reports.transactions', [
            'rows'    => $rows,
            'totals'  => $this->calcTotals(clone $q),
            'filters' => $this->filtersMeta(),
        ]);
    }

    public function totals(Request $r)
    {
        $group = (string) $r->input('group_by', 'none'); // account / cost_center / branch / none

        $q = $this->applyFilters($this->baseQuery(), $r);

        // أعمدة التجميع والإجماليات
        $selects = [
            DB::raw('SUM(d.debit)  AS total_debit'),
            DB::raw('SUM(d.credit) AS total_credit'),
            DB::raw('SUM(d.debit - d.credit) AS net_amount'),
            DB::raw('COUNT(*) AS lines_count'),
        ];
        $groupBy = [];

        if ($group === 'account') {
            $selects[] = 'a.id AS account_id';
            $selects[] = 'a.account AS account_name';
            $groupBy   = ['a.id', 'a.name'];
        } elseif ($group === 'cost_center') {
            $selects[] = 'cc.id AS cost_center_id';
            $selects[] = 'cc.name AS cost_center_name';
            $groupBy   = ['cc.id', 'cc.name'];
        } elseif ($group === 'branch') {
            $selects[] = 'b.id AS branch_id';
            $selects[] = 'b.name AS branch_name';
            $groupBy   = ['b.id', 'b.name'];
        }

        $q->select($selects);
        if (!empty($groupBy)) {
            $q->groupBy($groupBy);
        }

        // رتب حسب أعلى صافي
        $q->orderByDesc(DB::raw('SUM(d.debit - d.credit)'));

        $rows  = $q->paginate(Helpers::pagination_limit())->appends($r->query());
        $grand = $this->calcTotals($this->applyFilters($this->baseQuery(), $r));

        return view('admin-views.costcenter.reports.totals', [
            'rows'    => $rows,
            'grand'   => $grand,
            'group'   => $group,
            'filters' => $this->filtersMeta(),
        ]);
    }

    /** ================= Helpers ================= */

    // الاستعلام الأساسي (Query Builder)
    private function baseQuery(): QueryBuilder
    {
        return DB::table('journal_entries_details AS d')
            ->join('journal_entries AS e', 'e.id', '=', 'd.journal_entry_id')
            ->leftJoin('accounts AS a', 'a.id', '=', 'd.account_id')
            ->leftJoin('cost_centers AS cc', 'cc.id', '=', 'd.cost_center_id')
            ->leftJoin('branches AS b', 'b.id', '=', 'e.branch_id')
            ->selectRaw("
                e.id AS entry_id,
                d.id,
                e.entry_date,
                e.reference,
                e.type,
                e.created_by,
                e.branch_id,
                b.name AS branch_name,
                d.account_id,
                a.account AS account_name,
                a.code AS account_code,
                d.cost_center_id,
                cc.name AS cost_center_name,
                d.debit,
                d.credit,
                (d.debit - d.credit) AS net_amount,
                d.description
            ");
    }

    // تطبيق الفلاتر المشتركة
    private function applyFilters(QueryBuilder $q, Request $r): QueryBuilder
    {
        // by account / writer / reference / description
        if ($r->filled('account_id')) {
            $q->where('d.account_id', (int) $r->input('account_id'));
        }
        if ($r->filled('writer_id')) {
            $q->where('e.created_by', (int) $r->input('writer_id'));
        }
        if ($r->filled('reference')) {
            $q->where('e.reference', 'like', '%' . trim($r->input('reference')) . '%');
        }
        if ($r->filled('desc_like')) {
            $q->where('d.description', 'like', '%' . trim($r->input('desc_like')) . '%');
        }

        // date range
        if ($r->filled('from_date')) {
            $q->whereDate('e.entry_date', '>=', $r->input('from_date'));
        }
        if ($r->filled('to_date')) {
            $q->whereDate('e.entry_date', '<=', $r->input('to_date'));
        }

        // branch + children
        if ($r->filled('branch_id')) {
            $branchIds = [$r->integer('branch_id')];
            if ($r->boolean('with_branch_children')) {
                $branchIds = $this->descendantsIds('branches', (int) $r->input('branch_id'));
            }
            $q->whereIn('e.branch_id', $branchIds);
        }

        // cost center + children
        if ($r->filled('cost_center_id')) {
            $ccIds = [$r->integer('cost_center_id')];
            if ($r->boolean('with_cc_children')) {
                $ccIds = $this->descendantsIds('cost_centers', (int) $r->input('cost_center_id'));
            }
            $q->whereIn('d.cost_center_id', $ccIds);
        }

        // include/exclude null cost center
        if (!$r->boolean('include_null_cost_center')) {
            $q->whereNotNull('d.cost_center_id');
        }

        return $q;
    }

    // إجماليات لنفس الاستعلام (بدون paginate)
    private function calcTotals(QueryBuilder $q): array
    {
        $tot = $q->selectRaw('
                SUM(d.debit)  AS total_debit,
                SUM(d.credit) AS total_credit,
                SUM(d.debit - d.credit) AS net_amount,
                COUNT(*) AS lines_count
            ')
            ->first();

        return [
            'total_debit'  => (float) ($tot->total_debit  ?? 0),
            'total_credit' => (float) ($tot->total_credit ?? 0),
            'net_amount'   => (float) ($tot->net_amount   ?? 0),
            'lines_count'  => (int)   ($tot->lines_count  ?? 0),
        ];
    }

    // جلب كل الأبناء لجدول هرمي (parent_id)
    private function descendantsIds(string $table, int $rootId): array
    {
        $ids   = [$rootId];
        $queue = [$rootId];

        while (!empty($queue)) {
            $current  = array_shift($queue);
            $children = DB::table($table)->where('parent_id', $current)->pluck('id')->all();
            foreach ($children as $cid) {
                if (!in_array($cid, $ids, true)) {
                    $ids[]   = $cid;
                    $queue[] = $cid;
                }
            }
        }
        return $ids;
    }

    // القوائم للفلاتر (accounts / cost_centers / branches / writers)
    private function filtersMeta(): array
    {
        return [
            'accounts'      => \App\Models\Account::orderBy('code')->get(['id','code','account']),
            'cost_centers'  => \App\Models\CostCenter::orderBy('code')->get(['id','code','name','parent_id']),
            'branches'      => \App\Models\Branch::orderBy('name')->get(['id','name']),
            'writers'       => \App\Models\Admin::orderBy('id')->get(['id','email']),
        ];
    }
}
