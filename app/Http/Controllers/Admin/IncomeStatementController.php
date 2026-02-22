<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Transection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;

class IncomeStatementController extends Controller
{
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

    if (!in_array("kamtdakhl.report", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        // ضبط قيم افتراضية للتواريخ
        $startDate = $request->input('start_date') ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate   = $request->input('end_date') ?? Carbon::now()->toDateString();

        // -------------------------------
        // الإيرادات: حسابات "revenue"
        // -------------------------------
        $revenueAccounts = Account::where('account_type', 'revenue')->get();
        $revenuesData = [];
        $totalRevenues = 0;
        foreach ($revenueAccounts as $revenueAccount) {
            $descendantIds = $this->getDescendantIds($revenueAccount->id);
            $accountIds = array_merge([$revenueAccount->id], $descendantIds);
            
            $lastTransaction = Transection::where(function($query) use ($accountIds) {
                    $query->whereIn('account_id', $accountIds)
                          ->orWhereIn('account_id_to', $accountIds);
                })
                ->where('created_at', '<=', $endDate)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            
            $lastBalance = 0;
            if ($lastTransaction) {
                if (in_array($lastTransaction->account_id, $accountIds)) {
                    $lastBalance = $lastTransaction->balance;
                } elseif (in_array($lastTransaction->account_id_to, $accountIds)) {
                    $lastBalance = $lastTransaction->balance_account;
                }
            }
            
            $revenuesData[] = [
                'account'     => $revenueAccount,
                'lastBalance' => $lastBalance,
            ];
            $totalRevenues += $lastBalance;
        }
        
        // ------------------------------------
        // تكلفة البضاعة المباعة (COGS): الحساب رقم 47
        // ------------------------------------
        $cogsAccountId = 47;
        $cogsAccount = Account::find($cogsAccountId);
        $cogsDescendantIds = $this->getDescendantIds($cogsAccountId);
        $cogsAccountIds = array_merge([$cogsAccountId], $cogsDescendantIds);
        
        $cogsLastTransaction = Transection::where(function($query) use ($cogsAccountIds) {
                $query->whereIn('account_id', $cogsAccountIds)
                      ->orWhereIn('account_id_to', $cogsAccountIds);
            })
            ->where('created_at', '<=', $endDate)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();
        
        $cogsLastBalance = 0;
        if ($cogsLastTransaction) {
            if (in_array($cogsLastTransaction->account_id, $cogsAccountIds)) {
                $cogsLastBalance = $cogsLastTransaction->balance;
            } elseif (in_array($cogsLastTransaction->account_id_to, $cogsAccountIds)) {
                $cogsLastBalance = $cogsLastTransaction->balance_account;
            }
        }
        
        // -----------------------------------------
        // مجمل الربح = إجمالي الإيرادات - تكلفة البضاعة المباعة
        // -----------------------------------------
        $grossProfit = $totalRevenues - $cogsLastBalance;
        
        // ----------------------------------------------------
        // المصروفات التشغيلية (OPEX): حساب الأب (id=44) مع تجميع رصيده من شجرته
        // مع استبعاد حساب COGS وشجرته من التفاصيل
        // ----------------------------------------------------
        $opexRootId = 44;
        $opexAccount = Account::find($opexRootId);
        $opexDescendantIds = $this->getDescendantIds($opexRootId);
        $opexGroupIds = array_merge([$opexRootId], $opexDescendantIds);
        $opexGroupIds = array_diff($opexGroupIds, $cogsAccountIds);
        
        // حساب رصيد المصروفات التشغيلية بجمع آخر رصيد لكل حساب من المجموعة
        $totalOpex = 0;
        foreach ($opexGroupIds as $accountId) {
            $lastTransaction = Transection::where(function($query) use ($accountId) {
                    $query->where('account_id', $accountId)
                          ->orWhere('account_id_to', $accountId);
                })
                ->where('created_at', '<=', $endDate)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            if ($lastTransaction) {
                if ($lastTransaction->account_id == $accountId) {
                    $balance = $lastTransaction->balance;
                } else {
                    $balance = $lastTransaction->balance_account;
                }
                $totalOpex += $balance;
            }
        }
        
        // ----------------------------------------------------
        // المصروفات غير التشغيلية (Non-OPEX): حساب الأب (id=45) مع تجميع رصيده من شجرته
        // ----------------------------------------------------
        $nonOpExAccountId = 45;
        $nonOpExAccount = Account::find($nonOpExAccountId);
        $nonOpExDescendantIds = $this->getDescendantIds($nonOpExAccountId);
        $nonOpExGroupIds = array_merge([$nonOpExAccountId], $nonOpExDescendantIds);
        
        $nonOpExLastTransaction = Transection::where(function($query) use ($nonOpExGroupIds) {
            $query->whereIn('account_id', $nonOpExGroupIds)
                  ->orWhereIn('account_id_to', $nonOpExGroupIds);
        })
        ->where('created_at', '<=', $endDate)
        ->orderBy('created_at', 'desc')
        ->orderBy('id', 'desc')
        ->first();
        $totalNonOpEx = 0;
        if ($nonOpExLastTransaction) {
            if (in_array($nonOpExLastTransaction->account_id, $nonOpExGroupIds)) {
                $totalNonOpEx = $nonOpExLastTransaction->balance;
            } else {
                $totalNonOpEx = $nonOpExLastTransaction->balance_account;
            }
        }
        
        // ----------------------------------------
        // الدخل قبل المصروفات غير التشغيلية = مجمل الربح - المصروفات التشغيلية
        $incomeBeforeNonOpEx = $grossProfit - $totalOpex;
        // الدخل بعد المصروفات غير التشغيلية = الدخل قبل المصروفات غير التشغيلية - المصروفات غير التشغيلية
        $incomeAfterNonOpEx = $incomeBeforeNonOpEx - $totalNonOpEx;
        
        // ----------------------------------------------------
        // الضرائب: حساب الضرائب المستحقة (id=28) مع تجميع رصيده من شجرته
        // ----------------------------------------------------
        $taxAccountId = 28;
        $taxAccount = Account::find($taxAccountId);
        $taxDescendantIds = $this->getDescendantIds($taxAccountId);
        $taxGroupIds = array_merge([$taxAccountId], $taxDescendantIds);
        
        $taxLastTransaction = Transection::where(function($query) use ($taxGroupIds) {
            $query->whereIn('account_id', $taxGroupIds)
                  ->orWhereIn('account_id_to', $taxGroupIds);
        })
        ->where('created_at', '<=', $endDate)
        ->orderBy('created_at', 'desc')
        ->orderBy('id', 'desc')
        ->first();
        $taxDue = 0;
        if ($taxLastTransaction) {
            if (in_array($taxLastTransaction->account_id, $taxGroupIds)) {
                $taxDue = $taxLastTransaction->balance;
            } else {
                $taxDue = $taxLastTransaction->balance_account;
            }
        }
        
        // ----------------------------------------
        // صافي الربح النهائي = الدخل بعد المصروفات غير التشغيلية - الضرائب
        $netProfit = $incomeAfterNonOpEx - $taxDue;
        
        return view('admin-views.reports.income_statement', compact(
            'startDate',
            'endDate',
            'revenuesData',
            'totalRevenues',
            'cogsAccount',
            'cogsLastBalance',
            'grossProfit',
            'opexAccount',
            'totalOpex',
            'nonOpExAccount',
            'totalNonOpEx',
            'incomeBeforeNonOpEx',
            'incomeAfterNonOpEx',
            'taxAccount',
            'taxDue',
            'netProfit',
            'cogsAccountIds'
        ));
    }
    
    /**
     * دالة مساعدة للحصول على جميع الحسابات الفرعية (الأبناء والأحفاد)
     */
    private function getDescendantIds($accountId)
    {
        $descendants = Account::where('parent_id', $accountId)->get();
        $ids = [];
        foreach ($descendants as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child->id));
        }
        return $ids;
    }
}
