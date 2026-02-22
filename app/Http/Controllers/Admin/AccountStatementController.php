<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Branch;
use App\Models\CostCenter;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\CPU\Helpers;

class AccountStatementController extends Controller
{
public function statement(Request $request)
{
    // === الفلاتر ===
    $fromDate    = $request->date('from_date') ?: null;
    $toDate      = $request->date('to_date') ?: null;
    $branchId    = $request->integer('branch_id') ?: null;
    $costId      = $request->integer('cost_id') ?: null;
    $writerId    = $request->integer('writer_id') ?: null;
    $descLike    = $request->string('description')->toString() ?: null;

    // من حساب/إلى حساب (اختياري)
    $accountFrom = $request->integer('account_from') ?: null;
    $accountTo   = $request->integer('account_to') ?: null;

    // حساب محدد + خيار يشمل الأبناء
    $accountId    = $request->integer('account_id') ?: null;
    $withChildren = $request->boolean('with_children');

    // مصادر القوائم للفلتر
    $accounts    = Account::orderBy('account')->select('id','account','code','parent_id')->get();
    $branches    = Branch::orderBy('name')->get(['id','name']);
    $costCenters = CostCenter::orderBy('name')->get(['id','name','code']);
    $writers     = Admin::orderBy('f_name')->get(['id','f_name','email']);

    // نحدد مجموعة الحسابات المستهدفة
    $targetAccountIds = [];
    if ($accountId) {
        $targetAccountIds = [$accountId];
        if ($withChildren) {
            $targetAccountIds = array_values(array_unique(array_merge(
                $targetAccountIds,
                $this->getDescendantAccountIds($accountId)
            )));
        }
    } elseif ($accountFrom && $accountTo) {
        // (ممكن تغيّر للرنج بالكود حسب نظامك)
        $targetAccountIds = Account::whereBetween('id', [$accountFrom, $accountTo])
            ->pluck('id')->toArray();
    }

    // === الاستعلام الأساسي ===
    $q = JournalEntryDetail::query()
        // لو محتاج العلاقات لاحقًا، سيبها؛ لكن مش هنستخدمها للفرع/الكاتب بعد ما جبناهم بـ join
        ->with([
            'account:id,account,code',
            'costCenter:id,name,code',
        ])
        ->from('journal_entries_details as jed')
        ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
        // نجلب الفرع والكاتب مباشرة
        ->leftJoin('branches as br', 'br.id', '=', 'je.branch_id')
        ->leftJoin('admins as au', 'au.id', '=', 'je.created_by')
        ->select([
            'jed.*',
            'je.entry_date  as head_date',
            'je.reference   as head_ref',
            'je.description as head_desc',
            'je.created_by  as head_writer',
            'je.branch_id   as head_branch',
            // أعمدة جاهزة للاستخدام في الواجهة:
            'br.name        as branch_name',
            'au.email       as writer_email',
        ])
        ->orderBy('je.entry_date')
        ->orderBy('jed.id');

    // الفلاتر
    if (!empty($targetAccountIds)) {
        $q->whereIn('jed.account_id', $targetAccountIds);
    }
    if ($fromDate) { $q->whereDate('je.entry_date', '>=', $fromDate); }
    if ($toDate)   { $q->whereDate('je.entry_date', '<=', $toDate); }
    if ($branchId) { $q->where('je.branch_id', $branchId); }
    if ($costId)   { $q->where('jed.cost_center_id', $costId); }
    if ($writerId) { $q->where('je.created_by', $writerId); }
    if ($descLike) {
        $q->where(function($qq) use ($descLike){
            $qq->where('jed.description','like',"%{$descLike}%")
               ->orWhere('je.description','like',"%{$descLike}%")
               ->orWhere('je.reference','like',"%{$descLike}%");
        });
    }

    $rows = $q->paginate(Helpers::pagination_limit())->appends($request->query());

    // رصيد افتتاحي قبل fromDate
    $openingBalance = 0.0;
    if ($fromDate) {
        $openQ = JournalEntryDetail::query()
            ->from('journal_entries_details as jed')
            ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id');

        if (!empty($targetAccountIds)) $openQ->whereIn('jed.account_id', $targetAccountIds);
        $openQ->whereDate('je.entry_date','<',$fromDate);
        if ($branchId) $openQ->where('je.branch_id', $branchId);
        if ($costId)   $openQ->where('jed.cost_center_id', $costId);
        if ($writerId) $openQ->where('je.created_by', $writerId);

        $sumDebit  = (float) $openQ->clone()->sum('jed.debit');
        $sumCredit = (float) $openQ->clone()->sum('jed.credit');

        $openingBalance = $sumDebit - $sumCredit; // مدين (+) / دائن (-)
    }

    // إجماليات الصفحة
    $pageDebit  = (float) $rows->getCollection()->sum('debit');
    $pageCredit = (float) $rows->getCollection()->sum('credit');

    // بداية الرصيد الجاري
    $runningStart = $openingBalance;

    return view('admin-views.account.statement', compact(
        'rows','accounts','branches','costCenters','writers',
        'openingBalance','pageDebit','pageCredit','runningStart'
    ));
}

    /**
     * رجّع كل أحفاد الحساب (IDs) بشكل بسيط (loop) — بدّلها بـ nested set لو عندك.
     */
    private function getDescendantAccountIds(int $parentId): array
    {
        $all = Account::select('id','parent_id')->get()->groupBy('parent_id');
        $stack = [$parentId];
        $desc = [];

        while ($stack) {
            $pid = array_pop($stack);
            foreach (($all[$pid] ?? []) as $child) {
                $desc[] = $child->id;
                $stack[] = $child->id;
            }
        }
        return $desc;
    }
}
