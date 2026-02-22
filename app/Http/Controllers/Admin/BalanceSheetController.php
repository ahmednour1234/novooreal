<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;

class BalanceSheetController extends Controller
{
    /**
     * عرض تقرير الميزانية العمومية.
     */
    public function index(Request $request)
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

    if (!in_array("mizania.report", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $date = $request->input('date_to', now()->toDateString());
        // جلب شجرة الحسابات للميزانية العمومية (من الأنواع: asset, liability, equity)
        $balanceSheet = $this->getBalanceSheet($date);
        return view('admin-views.reports.balance-sheet', compact('balanceSheet', 'date'));
    }

    /**
     * عرض تقرير الأنشطة التشغيلية.
     * - الموجودات المتداولة: الحساب الرئيسي رقم 1 وجميع حساباته الفرعية.
     * - الالتزامات المتداولة: الحساب الرئيسي رقم 24 وجميع حساباته الفرعية.
     * - المصروفات التشغيلية: الحساب الرئيسي رقم 44 وجميع حساباته الفرعية.
     */
    public function indexOperating(Request $request)
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

    if (!in_array("tadfk.report", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $date = $request->input('date', now()->toDateString());
        $currentAssets = $this->getAccountTreeById(1, $date);
        $currentLiabilities = $this->getAccountTreeById(24, $date);
        $operatingExpenses = $this->getAccountTreeById(44, $date);

        return view('admin-views.reports.operating-report', compact(
            'date',
            'currentAssets',
            'currentLiabilities',
            'operatingExpenses'
        ));
    }

    /**
     * دالة لحساب الرصيد الختامي لحساب معين بناءً على أحدث معاملة
     * مع تطبيق فلتر التاريخ على حقل created_at.
     */
    private function getAccountClosingBalance($accountId, $date = null)
    {
        $query = DB::table('transections')
            ->where(function ($q) use ($accountId) {
                $q->where('account_id', $accountId)
                  ->orWhere('account_id_to', $accountId);
            });
        if ($date) {
            $query->whereDate('created_at', '<=', $date);
        }
        $tx = $query->orderByDesc('created_at')->orderByDesc('id')->first();
        if (!$tx) {
            return 0;
        }
        return $tx->account_id == $accountId ? $tx->balance : $tx->balance_account;
    }

    /**
     * دالة لبناء شجرة الحسابات بشكل تكراري مع حساب الرصيد المباشر والمجمّع.
     *
     * لكل حساب:
     * - direct_balance: الرصيد المباشر المستخلص من المعاملات.
     * - aggregated_balance: مجموع الرصيد المباشر مع رصيد جميع الحسابات الفرعية (أبناء، أحفاد، وهكذا).
     * - children: قائمة الحسابات الفرعية.
     *
     * يتم تطبيق فلتر التاريخ على الحسابات الفرعية (created_at) إذا تم تمرير تاريخ.
     */
    private function buildAccountTree($account, $date = null)
    {
        // حساب الرصيد المباشر
        $directBalance = $this->getAccountClosingBalance($account->id, $date);
        $aggregatedBalance = $directBalance;

        // استعلام الحسابات الفرعية (إن وُجدت)
        $childAccounts = DB::table('accounts')
            ->where('parent_id', $account->id)
            ->when($date, function($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            })
            ->orderBy('id')
            ->get();

        $account->children = [];
        if ($childAccounts->isNotEmpty()) {
            foreach ($childAccounts as $child) {
                $child = $this->buildAccountTree($child, $date);
                $account->children[] = $child;
                $aggregatedBalance += $child->aggregated_balance;
            }
        }

        $account->direct_balance = $directBalance;
        $account->aggregated_balance = $aggregatedBalance;

        return $account;
    }

    /**
     * دالة لجلب شجرة الحسابات للميزانية العمومية.
     * تستخرج الحسابات الرئيسية من شجرة الحسابات بناءً على account_type (asset, liability, equity)
     * التي ليس لها حساب أب.
     */
    private function getBalanceSheet($date = null)
    {
        $types = ['asset', 'liability', 'equity'];
        $mainAccounts = DB::table('accounts')
            ->whereIn('account_type', $types)
            ->whereNull('parent_id')
            ->when($date, function($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            })
            ->orderBy('id')
            ->get();

        foreach ($mainAccounts as $key => $account) {
            $mainAccounts[$key] = $this->buildAccountTree($account, $date);
        }

        return $mainAccounts;
    }

    /**
     * دالة لجلب شجرة الحسابات باستخدام رقم الحساب الرئيسي.
     * تُستخدم في تقارير الأنشطة التشغيلية لاسترجاع:
     * - الموجودات المتداولة (رقم 1)
     * - الالتزامات المتداولة (رقم 24)
     * - المصروفات التشغيلية (رقم 44)
     */
    private function getAccountTreeById($id, $date = null)
    {
        $account = DB::table('accounts')
            ->where('id', $id)
            ->when($date, function($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            })
            ->first();

        if ($account) {
            return $this->buildAccountTree($account, $date);
        }
        return null;
    }
}
