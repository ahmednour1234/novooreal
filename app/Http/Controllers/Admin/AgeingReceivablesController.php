<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\CostCenter;
use App\Models\Transection;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;

class AgeingReceivablesController extends Controller
{
    // تقرير إعمار ديون العملاء (customers)
public function index(Request $request)
    {
        // ===== صلاحيات =====
 $adminId = Auth::guard('admin')->id();
    $admin = DB::table('admins')->where('id', $adminId)->first();

    if (!$admin) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $roleId = $admin->role_id;
    $role = DB::table('roles')->where('id', $roleId)->first();

    if (!$role) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $decodedData = json_decode($role->data, true);
    if (is_string($decodedData)) {
        $decodedData = json_decode($decodedData, true);
    }

    if (!is_array($decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    if (!in_array("yearscustomer.report", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        // ===== فلاتر التاريخ =====
        $today     = Carbon::now();
        $startDate = $request->filled('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : null;
        $endDate   = $request->filled('end_date')   ? Carbon::parse($request->input('end_date'))->endOfDay()   : null;

        // ===== العملاء (لازم يكون له account_id) =====
        $customers = DB::table('customers')
            ->select('id','name','account_id')
            ->whereNotNull('account_id')
            ->get();

        if ($customers->isEmpty()) {
            $report = [];
            $totals = ['0_30'=>0,'31_60'=>0,'61_90'=>0,'90_plus'=>0,'current'=>0];
            return view('admin-views.reports.ageing_receivables', compact('report','startDate','endDate','totals'));
        }

        $accountIds = $customers->pluck('account_id')->unique()->values()->all();

        // ===== تطبيع الحركات من الجدولين إلى شكل موحد: (account_id, debit, credit, created_at) =====
        $normalized = $this->buildNormalizedTransactions($accountIds, $startDate, $endDate);

        // group by account لسرعة الحساب
        $byAccount = $normalized->groupBy('account_id');

        $report = [];
        $totals = ['0_30'=>0.0,'31_60'=>0.0,'61_90'=>0.0,'90_plus'=>0.0,'current'=>0.0];

        foreach ($customers as $cust) {
            $aid  = $cust->account_id;
            $list = $byAccount->get($aid, collect());

            // الرصيد الحالي داخل نطاق الفلترة (إن وجد) = صافي (مدين - دائن)
            $current_balance = (float) $list->sum(fn($r) => (float)$r->debit - (float)$r->credit);

            // صافي الأعمار حسب created_at
            $b0_30    = $this->getAgeBucketSum($list, $today, 0, 30);
            $b31_60   = $this->getAgeBucketSum($list, $today, 31, 60);
            $b61_90   = $this->getAgeBucketSum($list, $today, 61, 90);
            $b90_plus = $this->getAgeBucketSum($list, $today, 91, null); // 90+

            $report[] = [
                'customer_id'     => $cust->id,
                'customer_name'   => $cust->name,
                'account_id'      => $aid,
                '0-30'            => $b0_30,
                '31-60'           => $b31_60,
                '61-90'           => $b61_90,
                '90+'             => $b90_plus,
                'current_balance' => $current_balance,
            ];

            $totals['0_30']    += $b0_30;
            $totals['31_60']   += $b31_60;
            $totals['61_90']   += $b61_90;
            $totals['90_plus'] += $b90_plus;
            $totals['current'] += $current_balance;
        }

        return view('admin-views.reports.ageing_receivables', compact('report','startDate','endDate','totals'));
    }

    /**
     * يبني Collection موحدة من:
     * - transections: صف للحساب المرسل (كما هو)، وصف للحساب المستقبل (بعكس المدين/الدائن)
     * - journal_entrie_details + journal_entries (entry_date كتاريخ)
     * كل صف: (account_id, debit, credit, created_at)
     */
    private function buildNormalizedTransactions(array $accountIds, ?Carbon $startDate, ?Carbon $endDate)
    {
        // 1) transections للحساب المرسل
        $trFrom = DB::table('transections')
            ->select([
                'account_id',
                DB::raw('COALESCE(debit,0)  as debit'),
                DB::raw('COALESCE(credit,0) as credit'),
                'created_at',
            ])
            ->whereIn('account_id', $accountIds)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->when($startDate && !$endDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when(!$startDate && $endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->get();

        // 2) transections للحساب المستقبل (بعكس المدين/الدائن)
        $trTo = DB::table('transections')
            ->select([
                DB::raw('account_id_to as account_id'),
                // لو التحويل من A إلى B: غالبًا A دائن و B مدين → نعكس
                DB::raw('COALESCE(credit,0) as debit'),
                DB::raw('COALESCE(debit,0)  as credit'),
                'created_at',
            ])
            ->whereIn('account_id_to', $accountIds)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->when($startDate && !$endDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when(!$startDate && $endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->get();

        // 3) تفاصيل القيود اليومية
        $je = DB::table('journal_entries_details as jd')
            ->join('journal_entries as je', 'je.id', '=', 'jd.journal_entry_id')
            ->select([
                'jd.account_id',
                DB::raw('COALESCE(jd.debit,0)  as debit'),
                DB::raw('COALESCE(jd.credit,0) as credit'),
                DB::raw('je.entry_date as created_at'),
            ])
            ->whereIn('jd.account_id', $accountIds)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('je.entry_date', [$startDate, $endDate]))
            ->when($startDate && !$endDate, fn($q) => $q->where('je.entry_date', '>=', $startDate))
            ->when(!$startDate && $endDate, fn($q) => $q->where('je.entry_date', '<=', $endDate))
            ->get();

        // دمج الكل
        return $trFrom->concat($trTo)->concat($je)->values();
    }

    /**
     * صافي مبلغ الباكِت = مجموع (مدين - دائن) للحركات التي عمرها داخل الفترة.
     * minDays/maxDays بالنسبة لليوم الحالي. لو maxDays = null → مفتوحة (90+).
     */
    private function getAgeBucketSum($rows, Carbon $today, int $minDays, ?int $maxDays): float
    {
        if (!$rows || $rows->isEmpty()) return 0.0;

        $sum = 0.0;
        foreach ($rows as $r) {
            $age = Carbon::parse($r->created_at)->diffInDays($today);
            if ($age < $minDays) continue;
            if (!is_null($maxDays) && $age > $maxDays) continue;
            $sum += (float)$r->debit - (float)$r->credit;
        }
        return $sum;
    }

    // تقرير إعمار ديون الموردين (suppliers)
public function suppliersIndex(Request $request)
{
    // ===== صلاحيات =====
   
          $adminId = Auth::guard('admin')->id();
    $admin = DB::table('admins')->where('id', $adminId)->first();

    if (!$admin) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $roleId = $admin->role_id;
    $role = DB::table('roles')->where('id', $roleId)->first();

    if (!$role) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $decodedData = json_decode($role->data, true);
    if (is_string($decodedData)) {
        $decodedData = json_decode($decodedData, true);
    }

    if (!is_array($decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    if (!in_array("yearssupplier.report", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   

    // ===== فلاتر التاريخ =====
    $today     = Carbon::now();
    $startDate = $request->filled('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : null;
    $endDate   = $request->filled('end_date')   ? Carbon::parse($request->input('end_date'))->endOfDay()   : null;

    // ===== الموردون =====
    $suppliers = DB::table('suppliers')
        ->select('id','name','account_id')
        ->whereNotNull('account_id')
        ->get();

    if ($suppliers->isEmpty()) {
        $report = [];
        $totals = ['0_30'=>0.0,'31_60'=>0.0,'61_90'=>0.0,'90_plus'=>0.0,'current'=>0.0];
        return view('admin-views.reports.ageing_receivables_suppliers', compact('report','startDate','endDate','totals'));
    }

    $accountIds = $suppliers->pluck('account_id')->unique()->values()->all();

    // ===== تطبيع الحركات: (account_id, debit, credit, created_at) — بدون account_id_to =====
    $normalized = $this->buildNormalizedSupplierTransactions($accountIds, $startDate, $endDate);

    // group by account
    $byAccount = $normalized->groupBy('account_id');

    $report = [];
    $totals = ['0_30'=>0.0,'31_60'=>0.0,'61_90'=>0.0,'90_plus'=>0.0,'current'=>0.0];

    foreach ($suppliers as $supplier) {
        $aid  = $supplier->account_id;
        $list = $byAccount->get($aid, collect());

        // الرصيد الحالي داخل نطاق الفلتر (إن وُجد) = مجموع (debit - credit)
        $current_balance = (float) $list->sum(fn($r) => (float)$r->debit - (float)$r->credit);

        // أعمار الديون (حسب created_at)
        $b0_30    = $this->getAgeBucketSumm($list, $today, 0, 30);
        $b31_60   = $this->getAgeBucketSumm($list, $today, 31, 60);
        $b61_90   = $this->getAgeBucketSumm($list, $today, 61, 90);
        $b90_plus = $this->getAgeBucketSumm($list, $today, 91, null);

        $report[] = [
            'supplier_id'     => $supplier->id,
            'supplier_name'   => $supplier->name,
            'account_id'      => $aid,
            '0-30'            => $b0_30,
            '31-60'           => $b31_60,
            '61-90'           => $b61_90,
            '90+'             => $b90_plus,
            'current_balance' => $current_balance,
        ];

        $totals['0_30']    += $b0_30;
        $totals['31_60']   += $b31_60;
        $totals['61_90']   += $b61_90;
        $totals['90_plus'] += $b90_plus;
        $totals['current'] += $current_balance;
    }

    return view('admin-views.reports.ageing_receivables_suppliers', compact('report', 'startDate', 'endDate', 'totals'));
}

/**
 * توحيد الحركات من جدولين:
 * - transections (فقط account_id) بقيم debit/credit كما هي
 * - journal_entrie_details + journal_entries (entry_date كتاريخ)
 * يرجع Collection لكل صف: (account_id, debit, credit, created_at)
 */
private function buildNormalizedSupplierTransactions(array $accountIds, ?Carbon $startDate, ?Carbon $endDate)
{
    // 1) transections — فقط account_id
    $tr = DB::table('transections')
        ->select([
            'account_id',
            DB::raw('COALESCE(debit,0)  as debit'),
            DB::raw('COALESCE(credit,0) as credit'),
            'created_at',
        ])
        ->whereIn('account_id', $accountIds)
        ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
        ->when($startDate && !$endDate, fn($q) => $q->where('created_at', '>=', $startDate))
        ->when(!$startDate && $endDate, fn($q) => $q->where('created_at', '<=', $endDate))
        ->get();

    // 2) journal entry details
    $je = DB::table('journal_entries_details as jd') // غيّر الاسم لو جدولك "journal_entry_details"
        ->join('journal_entries as je', 'je.id', '=', 'jd.journal_entry_id')
        ->select([
            'jd.account_id',
            DB::raw('COALESCE(jd.debit,0)  as debit'),
            DB::raw('COALESCE(jd.credit,0) as credit'),
            DB::raw('je.entry_date as created_at'),
        ])
        ->whereIn('jd.account_id', $accountIds)
        ->when($startDate && $endDate, fn($q) => $q->whereBetween('je.entry_date', [$startDate, $endDate]))
        ->when($startDate && !$endDate, fn($q) => $q->where('je.entry_date', '>=', $startDate))
        ->when(!$startDate && $endDate, fn($q) => $q->where('je.entry_date', '<=', $endDate))
        ->get();

    return $tr->concat($je)->values();
}

/**
 * صافي مبلغ الباكِت = مجموع (مدين - دائن) للحركات داخل الفترة (بالأيام من اليوم).
 * لو maxDays = null → فترة مفتوحة (90+).
 */
private function getAgeBucketSumm($rows, Carbon $today, int $minDays, ?int $maxDays): float
{
    if (!$rows || $rows->isEmpty()) return 0.0;

    $sum = 0.0;
    foreach ($rows as $r) {
        $age = Carbon::parse($r->created_at)->diffInDays($today);
        if ($age < $minDays) continue;
        if (!is_null($maxDays) && $age > $maxDays) continue;
        $sum += (float)$r->debit - (float)$r->credit;
    }
    return $sum;
}

   public function expenseCostCentersReport(Request $request)
{
    $adminId = Auth::guard('admin')->id();
    $admin = DB::table('admins')->where('id', $adminId)->first();

    if (!$admin) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $roleId = $admin->role_id;
    $role = DB::table('roles')->where('id', $roleId)->first();

    if (!$role) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $decodedData = json_decode($role->data, true);
    if (is_string($decodedData)) {
        $decodedData = json_decode($decodedData, true);
    }

    if (!is_array($decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    if (!in_array("costcenter.report", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    // التقاط تواريخ البداية والنهاية من الطلب، إن وُجدت
    $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
    $endDate   = $request->input('end_date')   ? Carbon::parse($request->input('end_date'))   : null;
    
    // جلب مراكز التكلفة الرئيسية (التي ليس لها أب)
    $mainCenters = DB::table('cost_centers')
                    ->whereNull('parent_id')
                    ->get();
                    
    $report = [];
    foreach ($mainCenters as $center) {
        $node = $this->buildCostCenterNode($center, $startDate, $endDate);
        $report[] = $node;
    }
    
    return view('admin-views.reports.expense_cost_centers', compact('report', 'startDate', 'endDate'));
}

/**
 * دالة تكرارية لبناء شجرة مراكز التكلفة مع حسابات كل مركز.
 * يتم احتساب إجمالي المصروفات مباشرةً عن طريق جمع عمود debit من جدول المعاملات.
 */
private function buildCostCenterNode($center, $startDate, $endDate)
{
    $node = [];
    // إضافة معرف مركز التكلفة
    $node['id'] = $center->id;
    $node['center_name'] = $center->name;
    
    // جلب المعاملات الخاصة بهذا مركز التكلفة من جدول transections
    $transactions = DB::table('transections')
                    ->where('cost_id', $center->id)
                    ->when($startDate && $endDate, function($query) use ($startDate, $endDate) {
                        return $query->whereBetween('created_at', [$startDate, $endDate]);
                    })
                    ->get();
                    
    // احتساب إجمالي المصروفات للمركز الحالي (من جدول المعاملات)
    $node['total_expense'] = $transactions->sum('debit');
    
    // استخراج قائمة الحسابات (account_id) من المعاملات
    // ثم جلب بيانات الحسابات (من accounts) التي تكون من نوع "expenses"
    $accountIds = $transactions->pluck('account_id')->unique();
    $accounts = DB::table('accounts')
                ->whereIn('id', $accountIds)
                ->where('account_type', 'expenses')
                ->get();
    
    // بناء شجرة الحسابات بناءً على علاقة parent_id (جميع الحسابات، سواء كانت رئيسية أو فرعية)
    $accountTree = $this->buildAccountTree($accounts);
    
    $node['accounts'] = [];
    foreach ($accountTree as $accountNode) {
        $expense = $this->getExpenseForAccountNode($accountNode, $transactions);
        $node['accounts'][] = [
            'account_name' => $accountNode['account_name'],
            'expense'      => $expense,
            'children'     => $accountNode['children'] // تفاصيل الحسابات الفرعية
        ];
    }
    
    // جلب مراكز التكلفة الفرعية (الأبناء) وتكرار العملية
    $children = DB::table('cost_centers')
                ->where('parent_id', $center->id)
                ->get();
    $node['children'] = [];
    foreach ($children as $child) {
        $childNode = $this->buildCostCenterNode($child, $startDate, $endDate);
        $node['children'][] = $childNode;
        // إضافة مصروفات الفروع إلى إجمالي مصروفات المركز الأب
        $node['total_expense'] += $childNode['total_expense'];
    }
    
    return $node;
}

/**
 * بناء شجرة الحسابات بناءً على الـ parent_id.
 * @param \Illuminate\Support\Collection $accounts
 * @param int|null $parentId
 * @return array
 */
private function buildAccountTree($accounts, $parentId = null)
{
    $tree = [];
    foreach ($accounts as $account) {
        if ($account->parent_id == $parentId) {
            $node = [];
            $node['id'] = $account->id;
            $node['account_name'] = $account->account; // اسم الحساب
            $node['children'] = $this->buildAccountTree($accounts, $account->id);
            $tree[] = $node;
        }
    }
    return $tree;
}

/**
 * حساب إجمالي المصروفات لحسابٍ معين (مع تجميع مصروفات الفروع) باستخدام بيانات المعاملات.
 * @param array $accountNode
 * @param \Illuminate\Support\Collection $transactions
 * @return float
 */
private function getExpenseForAccountNode($accountNode, $transactions)
{
    // جمع مبلغ debit للمعاملات التي تتعلق بهذا الحساب
    $expense = $transactions->where('account_id', $accountNode['id'])->sum('debit');
    if (isset($accountNode['children']) && count($accountNode['children']) > 0) {
        foreach ($accountNode['children'] as $child) {
            $expense += $this->getExpenseForAccountNode($child, $transactions);
        }
    }
    return $expense;
}
public function showCostCenterReport(Request $request, $cost_id)
{
    // جلب بيانات مركز التكلفة
    $costCenter = CostCenter::find($cost_id);

    // التقاط تواريخ البداية والنهاية من الطلب (إن وُجدت)
    $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
    $endDate   = $request->input('end_date')   ? Carbon::parse($request->input('end_date'))   : null;

    // جلب المعاملات الخاصة بمركز التكلفة المحدد مع تطبيق فلترة التاريخ (بناءً على created_at)
    $transactions = Transection::where('cost_Id', $cost_id)
                        ->when($startDate, function($query) use ($startDate) {
                            return $query->whereDate('created_at', '>=', $startDate);
                        })
                        ->when($endDate, function($query) use ($endDate) {
                            return $query->whereDate('created_at', '<=', $endDate);
                        })
                        ->get();

    // تجميع بيانات الحسابات مع جمع قيمة debit لكل حساب
    $accountsData = [];
    foreach ($transactions as $tran) {
        // افتراض أن عمود الحساب موجود باسم account_id
        $accountId = $tran->account_id;
        if (!isset($accountsData[$accountId])) {
            // جلب بيانات الحساب من جدول accounts
            $account = Account::find($accountId);
            if ($account) {
                $accountsData[$accountId] = [
                    'id'           => $account->id,
                    'account_name' => $account->account,      // تأكد من صحة اسم العمود لاسم الحساب
                    'parent_id'    => $account->parent_id,       // تأكد من صحة اسم العمود لمعرف الأب
                    'debit'        => 0,
                    'children'     => [],
                ];
            }
        }
        if (isset($accountsData[$accountId])) {
            $accountsData[$accountId]['debit'] += $tran->debit;
        }
    }

    // دالة لبناء شجرة الحسابات الهرمية
    $buildTree = function ($accounts) use (&$buildTree) {
        $tree = [];
        $lookup = [];

        // تجهيز مصفوفة البحث
        foreach ($accounts as $id => $account) {
            $account['children'] = [];
            $lookup[$id] = $account;
        }

        // بناء الشجرة باستخدام parent_id
        foreach ($lookup as $id => &$node) {
            if (isset($node['parent_id']) && $node['parent_id'] && isset($lookup[$node['parent_id']])) {
                $lookup[$node['parent_id']]['children'][] = &$node;
            } else {
                $tree[] = &$node;
            }
        }

        return $tree;
    };

    $accountsTree = $buildTree($accountsData);

    // حساب الإجمالي العام لقيم debit لجميع الحسابات
    $totalDebit = 0;
    foreach ($accountsData as $account) {
        $totalDebit += $account['debit'];
    }

    // تمرير البيانات إلى الـ view لعرض التقرير
    return view('admin-views.reports.cost_center_report', compact('costCenter', 'accountsTree', 'totalDebit', 'startDate', 'endDate'));
}

}
