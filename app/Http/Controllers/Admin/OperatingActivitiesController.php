<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;

class OperatingActivitiesController extends Controller
{
    /**
     * عرض تقرير التدفقات النقدية (الأنشطة التشغيلية والتمويلية والاستثمارية).
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

        // الأنشطة التشغيلية:
        $currentAssets = $this->getAccountTreeById(1, $date);      // الموجودات المتداولة
        $currentLiabilities = $this->getAccountTreeById(24, $date);  // الالتزامات المتداولة
        $operatingExpenses = $this->getAccountTreeById(44, $date);   // المصروفات التشغيلية
        $operatingRevenues = $this->getOperatingRevenues($date);     // الإيرادات التشغيلية

        $totalRevenues = 0;
        foreach($operatingRevenues as $rev) {
            $totalRevenues += $rev->aggregated_balance;
        }
        
        // حساب صافي الأنشطة التشغيلية:
        // (إجمالي الإيرادات التشغيلية - إجمالي المصروفات التشغيلية) + (الالتزامات المتداولة - الموجودات المتداولة)
        $netOperating = ($totalRevenues - $operatingExpenses->aggregated_balance)
                        + ($currentLiabilities->aggregated_balance - $currentAssets->aggregated_balance);

        // الأنشطة التمويلية:
        $equityAccounts = $this->getEquityAccounts($date);  // حقوق الملكية
        $totalEquity = 0;
        foreach($equityAccounts as $eq) {
            $totalEquity += $eq->aggregated_balance;
        }
        $nonCurrentLiabilities = $this->getAccountTreeById(25, $date);  // الالتزامات غير المتداولة
        $netFinancing = $totalEquity + $nonCurrentLiabilities->aggregated_balance;

        // الأنشطة الاستثمارية:
        $investmentActivities = $this->getAccountTreeById(4, $date);    // الأصول غير المتداولة
        $netInvestment = $investmentActivities ? $investmentActivities->aggregated_balance : 0;

        return view('admin-views.reports.operating-report', compact(
            'date',
            'currentAssets',
            'currentLiabilities',
            'operatingExpenses',
            'operatingRevenues',
            'totalRevenues',
            'netOperating',
            'equityAccounts',
            'nonCurrentLiabilities',
            'netFinancing',
            'investmentActivities',
            'netInvestment'
        ));
    }

    /**
     * دالة لحساب الرصيد الختامي لحساب معين بناءً على أحدث معاملة مع فلترة التاريخ.
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
     * لكل حساب:
     * - direct_balance: الرصيد المباشر.
     * - aggregated_balance: مجموع الرصيد المباشر مع رصيد الحسابات الفرعية.
     * - children: قائمة الحسابات الفرعية.
     */
    private function buildAccountTree($account, $date = null)
    {
        $directBalance = $this->getAccountClosingBalance($account->id, $date);
        $aggregatedBalance = $directBalance;

        $childAccounts = DB::table('accounts')
            ->where('parent_id', $account->id)
            ->when($date, function ($query, $date) {
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
     * دالة لجلب حساب رئيسي بواسطة رقمه وبناء شجرته.
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

    /**
     * دالة لجلب الحسابات الإيرادية التشغيلية (من نوع "revenue") التي ليس لها حساب أب.
     */
    private function getOperatingRevenues($date = null)
    {
        $revenueAccounts = DB::table('accounts')
            ->where('account_type', 'revenue')
            ->whereNull('parent_id')
            ->when($date, function($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            })
            ->orderBy('id')
            ->get();
        foreach ($revenueAccounts as $key => $account) {
            $revenueAccounts[$key] = $this->buildAccountTree($account, $date);
        }
        return $revenueAccounts;
    }

    /**
     * دالة لجلب الحسابات الخاصة بحقوق الملكية (من نوع "equity") التي ليس لها حساب أب.
     */
    private function getEquityAccounts($date = null)
    {
        $equityAccounts = DB::table('accounts')
            ->where('account_type', 'equity')
            ->whereNull('parent_id')
            ->when($date, function($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            })
            ->orderBy('id')
            ->get();
        foreach ($equityAccounts as $key => $account) {
            $equityAccounts[$key] = $this->buildAccountTree($account, $date);
        }
        return $equityAccounts;
    }
  public function indexTrialBalance(Request $request)
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

    if (!in_array("mizan.report", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    // Retrieve all accounts from the 'accounts' table
    $accounts = DB::table('accounts')->get();
    
    $trialData = [];
    $totalDebit = 0;
    $totalCredit = 0;
    
    // Retrieve filter dates from the request
    $start_date = $request->start_date;
    $end_date = $request->end_date;
    
    // Process each account to calculate its debit and credit totals within the date range
    foreach ($accounts as $account) {
        // Build debit query for the current account
        $debitQuery = DB::table('transections')
            ->where('account_id', $account->id);
        
        // Build credit query for the current account
        $creditQuery = DB::table('transections')
            ->where('account_id_to', $account->id);
        
        // Apply the start date filter if provided
        if ($start_date) {
            $debitQuery->whereDate('created_at', '>=', $start_date);
            $creditQuery->whereDate('created_at', '>=', $start_date);
        }
        
        // Apply the end date filter if provided
        if ($end_date) {
            $debitQuery->whereDate('created_at', '<=', $end_date);
            $creditQuery->whereDate('created_at', '<=', $end_date);
        }
        
        // Calculate sums for debit and credit
        $debit = $debitQuery->sum('debit');
        $credit = $creditQuery->sum('credit_account');
        
        // Determine the account balance: positive for debit, negative for credit
        $balance = $debit - $credit;
        
        if ($balance >= 0) {
            $trialData[] = [
                'account' => $account->account,
                'debit'   => $balance,
                'credit'  => 0,
            ];
            $totalDebit += $balance;
        } else {
            $trialData[] = [
                'account' => $account->account,
                'debit'   => 0,
                'credit'  => abs($balance),
            ];
            $totalCredit += abs($balance);
        }
    }
    
    // Check if the trial balance is balanced
    $isBalanced = ($totalDebit == $totalCredit);
    
    return view('admin-views.reports.trial_balance', compact('trialData', 'totalDebit', 'totalCredit', 'isBalanced'));
}

}
