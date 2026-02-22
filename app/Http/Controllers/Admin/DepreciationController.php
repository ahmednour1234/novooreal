<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Branch;
use App\Models\CostCenter;
use App\Models\Transection;
use App\Models\Account;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\CPU\Helpers;

class DepreciationController extends Controller
{
    /**
     * عرض صفحة الاهلاك.
     */
      public function __construct(

        private Account $account,
                private CostCenter $costcenter,


    ){}
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

    if (!in_array("assets.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    try {
        $query = Asset::query();
        
        // تصفية حسب الفرع إذا تم تمرير branch_id
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        
        // تصفية حسب النطاق التاريخي
        // مصفوفة filter_dates تحتوي على أسماء الحقول التي سيتم التصفية عليها: purchase_date, commencement_date, created_at
        if ($request->filled('date_from') && $request->filled('date_to') && $request->has('filter_dates')) {
            $dateFrom = $request->date_from;
            $dateTo   = $request->date_to;
            $fields   = $request->filter_dates; // يُفترض أن تكون مصفوفة
            
            // يتم استخدام تجميع الشروط للتصفية بحيث يتم البحث في أي من الحقول المحددة
            $query->where(function($q) use ($fields, $dateFrom, $dateTo) {
                foreach ($fields as $field) {
                    // نتحقق من أن الحقل من الحقول المسموح بها
                    if (in_array($field, ['purchase_date', 'commencement_date', 'created_at'])) {
                        $q->orWhereBetween($field, [$dateFrom, $dateTo]);
                    }
                }
            });
        }
        
        // الترتيب حسب تاريخ الإنشاء والفلترة مع دعم pagination مع نقل معطيات الفلترة إلى روابط الترقيم
        $assets = $query->orderBy('created_at', 'desc')->paginate(Helpers::pagination_limit())
                        ->appends($request->query());
        $branches=Branch::where('active',1)->get();
        return view('admin-views.depreciation.index', compact('assets','branches'));
    } catch (\Exception $e) {
        \Toastr::error($e->getMessage());
        return redirect()->back();
    }
}
public function show($id)
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

    if (!in_array("asset.details", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    try {
        // جلب سجل الأصل بناءً على المعرف المُرسل
        $asset = Asset::findOrFail($id);

        // عرض صفحة التفاصيل وتمرير بيانات الأصل لها
        return view('admin-views.depreciation.show', compact('asset'));
    } catch (\Exception $e) {
        \Toastr::error($e->getMessage());
        return redirect()->back();
    }
}
public function assetTransactions(Request $request, $id)
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

    if (!in_array("asset.koyod", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    try {
        // جلب سجل الأصل للتأكد من وجوده
        $asset = Asset::findOrFail($id);

        // إعداد استعلام معاملات الأصل
        $query = Transection::where('asset_id', $id);
        
        // تصفية حسب الحساب (acc_id) إذا وُجد
        if ($request->filled('acc_id')) {
            $query->where('account_id', $request->acc_id);
        }
        
        // تصفية حسب الفرع (branch_id) إذا وُجد
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        
        // تصفية حسب نوع المعاملة (tran_type) إذا وُجد
        if ($request->filled('tran_type')) {
            $query->where('tran_type', $request->tran_type);
        }
        
        // تصفية بالنطاق التاريخي إذا كانت القيم موجودة
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('date', [$request->from, $request->to]);
        }
        
        // تصفية حسب مركز التكلفة (cost_id) إذا وُجد
        if ($request->filled('cost_id')) {
            $query->where('cost_id', $request->cost_id);
        }
        
        // ترتيب النتائج حسب التاريخ (تنازلي) وتطبيق pagination
        $transections = $query->orderBy('date', 'desc')
                              ->paginate(Helpers::pagination_limit())
                              ->appends($request->query());
                              
        // استرجاع باقي البيانات
        $costcenters = $this->costcenter
                          ->doesntHave('children')
                          ->orderBy('id', 'desc')
                          ->get();

        $accounts_to = $this->account
            ->where(function ($query) {
                $query->whereIn('parent_id', [4])
                      ->orWhereHas('parent', function ($q) {
                          $q->whereIn('parent_id', [4]);
                      });
            })
            ->doesntHave('childrenn')
            ->orderBy('id', 'desc')
            ->get();

        $accounts = $this->account
            ->orderBy('id', 'desc')
            ->get();

        $branches = Branch::where('active', 1)->get();

        // جلب معطيات الفلاتر لإرسالها للـ view
        $acc_id    = $request->input('acc_id');
        $branch_id = $request->input('branch_id');
        $tran_type = $request->input('tran_type');
        $from      = $request->input('from');
        $to        = $request->input('to');
        $cost_id   = $request->input('cost_id');

        // إعادة توجيه البيانات إلى الـ view
        return view('admin-views.depreciation.transactions', compact(
            'asset', 
            'transections',
            'accounts',
            'accounts_to',
            'costcenters',
            'acc_id',
            'tran_type',
            'from',
            'to',
            'branch_id',
            'branches',
            'cost_id'
        ));
    } catch (\Exception $e) {
        \Toastr::error($e->getMessage());
        return redirect()->back();
    }
}

    // في DepreciationController
public function getAssetDetails(Request $request)
{
    $request->validate([
        'asset_id' => 'required|exists:assets,id'
    ]);
    $asset = Asset::find($request->asset_id);
    return response()->json(['success' => true, 'asset' => $asset]);
}

    public function showDepreciationPage()
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

    if (!in_array("asset.ehlak", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        // جلب جميع الأصول الثابتة التي قد تُهلك (يمكنك تعديل الفلتر بحسب الحاجة)
$assets = Asset::orderBy('asset_name')
               ->whereNotIn('status', ['sold', 'closed'])
               ->get();
        $costcenters = $this->costcenter->doesntHave('children')->orderBy('id', 'desc')->get();

         $accounts_to = $this->account
        ->where(function ($query) {
            // هنا استخدمنا [4] فقط بدل [4, 4] لأنها نفس القيمة
            $query->whereIn('parent_id', [86])
                  ->orWhereHas('parent', function ($q) {
                      $q->whereIn('parent_id', [86]);
                  });
        })
        ->doesntHave('childrenn')
        ->orderBy('id', 'desc')
        ->get();

       $accounts = $this->account
        ->orWhere(function ($query) {
            $query->where('account_type', 'expense')
                  ->orWhereHas('parent', function ($q) {
                      $q->where('account_type','expense');
                  });
        })
        ->doesntHave('childrenn')
        ->orderBy('id', 'desc')
        ->get();

        return view('admin-views.depreciation.create', compact('assets','accounts','accounts_to','costcenters'));
    }

    /**
     * معالجة عملية الاهلاك للأصل المُحدد.
     */
public function depreciateAsset(Request $request)
{
    // التحقق من صحة المدخلات
    $validated = $request->validate([
        'asset_id'          => 'required|exists:assets,id',
        'date'              => 'required|date',
        'produced_units'    => 'nullable|numeric|min:0',

        // حسابات القيد
        'account_id'        => 'required|exists:accounts,id',                       // مدين: مصروف إهلاك
        'account_id_to'     => 'required|exists:accounts,id|different:account_id',  // دائن: مجمّع إهلاك

        // مراكز التكلفة (اختياري)
        'cost_id'           => 'nullable|exists:cost_centers,id',                   // عام
        'cost_id_debit'     => 'nullable|exists:cost_centers,id',                   // مدين
        'cost_id_credit'    => 'nullable|exists:cost_centers,id',                   // دائن

        'description'       => 'nullable|string|max:500',
        'img'               => 'nullable|image|max:2048',
    ]);

    try {
        DB::transaction(function () use ($request, $validated) {

            // 1) جلب الأصل
            /** @var \App\Models\Asset $asset */
            $asset = Asset::findOrFail($validated['asset_id']);

            // 2) حساب مصروف الاهلاك
            $depreciationAmount = 0.0;
            switch ($asset->depreciation_method) {
                case 'straight_line':
                    $totalDepreciable     = max(0, ($asset->total_cost ?? 0) - ($asset->salvage_value ?? 0));
                    $totalMonths          = max(1, ($asset->useful_life ?? 1) * 12);
                    $monthlyDepreciation  = $totalDepreciable / $totalMonths;
                    $depreciationAmount   = $monthlyDepreciation;
                    break;

                case 'declining_balance':
                    $annualRate = max(0, (float)($asset->depreciation_rate ?? 0)) / 100.0;
                    $depreciationAmount = ($asset->book_value ?? 0) * ($annualRate / 12.0);
                    break;

                case 'units_of_production':
                    if (!$request->filled('produced_units')) {
                        throw new \Exception("يجب توفير عدد الوحدات المنتجة لطريقة الإنتاج/الاستخدام.");
                    }
                    if (empty($asset->total_units) || (float)$asset->total_units == 0.0) {
                        throw new \Exception("لا يمكن حساب الاهلاك لطريقة الإنتاج/الاستخدام بسبب عدم توفر العدد الكلي للوحدات.");
                    }
                    $perUnit = (max(0, ($asset->total_cost ?? 0) - ($asset->salvage_value ?? 0)) / (float)$asset->total_units);
                    $depreciationAmount = $perUnit * (float)$request->produced_units;
                    break;

                default:
                    throw new \Exception("طريقة الاهلاك المحددة غير مدعومة.");
            }

            // سقف الاهلاك حتى لا ينزل عن القيمة المتبقية
            $maxAllowed = max(0, ($asset->book_value ?? 0) - ($asset->salvage_value ?? 0));
            if ($depreciationAmount > $maxAllowed) {
                $depreciationAmount = $maxAllowed;
            }
            if ($depreciationAmount <= 0) {
                throw new \Exception("لا يمكن تسجيل إهلاك: القيمة المحسوبة صفر.");
            }

            // 3) تحديث الأصل
            $asset->accumulated_depreciation = ($asset->accumulated_depreciation ?? 0) + $depreciationAmount;
            $asset->book_value               = max(($asset->salvage_value ?? 0), ($asset->total_cost ?? 0) - $asset->accumulated_depreciation);
            $asset->save();

            // 4) الحسابات والمدخلات الأخرى
            /** @var \App\Models\Account $debitAcc  (مصروف إهلاك) */
            $debitAcc  = Account::findOrFail($validated['account_id']);
            /** @var \App\Models\Account $creditAcc (مجمّع إهلاك) */
            $creditAcc = Account::findOrFail($validated['account_id_to']);

            $entryDate  = \Carbon\Carbon::parse($validated['date'])->toDateString();
            $admin      = Auth::guard('admin')->user();
            $branchId   = $admin->branch_id ?? null;
            $sellerId   = $admin->id ?? null;
            $img        = $request->hasFile('img') ? \App\CPU\Helpers::update('journal/', null, 'png', $request->file('img')) : null;

            $desc = $request->filled('description')
                ? $request->description
                : ("سند إهلاك شهري للأصل: " . ($asset->asset_name ?? ('#' . $asset->id)));

            // ✅ تحديد مركز تكلفة لكل طرف بالأولوية (مدين/دائن)
            $costIdCommon  = $request->input('cost_id');
            $costIdDebit   = $request->input('cost_id_debit')
                             ?? $costIdCommon
                             ?? ($debitAcc->default_cost_center_id ?? null);
            $costIdCredit  = $request->input('cost_id_credit')
                             ?? $costIdCommon
                             ?? ($creditAcc->default_cost_center_id ?? null);

            // مرجع/رقم قيد
            $reference = 'DEP-' . now()->format('Ymd') . '-' . str_pad((string)mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // 5) إنشاء قيد يومية (الرأس)
            $entry = new \App\Models\JournalEntry();
            $entry->entry_date   = $entryDate;
            $entry->reference    = $reference;
            $entry->description  = $desc;
            $entry->created_by   = $sellerId;
            $entry->branch_id    = $branchId;
            $entry->asset_id     = $asset->id;     // لو العمود موجود
            $entry->type         = 'depreciation'; // (اختياري) يفيد في الفلاتر
            $entry->save();

            // 6) تفاصيل اليومية — (أ) مدين: مصروف إهلاك
            $detailDebit = new \App\Models\JournalEntryDetail();
            $detailDebit->journal_entry_id = $entry->id;
            $detailDebit->account_id       = $debitAcc->id;
            $detailDebit->debit            = $depreciationAmount;
            $detailDebit->credit           = 0;
            $detailDebit->cost_center_id   = $costIdDebit;   // ✅
            $detailDebit->description      = $desc;
            $detailDebit->attachment_path  = $img;
            $detailDebit->entry_date       = $entryDate;
            $detailDebit->asset_id         = $asset->id;     // لو العمود موجود
            $detailDebit->save();

            // 6) تفاصيل اليومية — (ب) دائن: مجمّع إهلاك
            $detailCredit = new \App\Models\JournalEntryDetail();
            $detailCredit->journal_entry_id = $entry->id;
            $detailCredit->account_id       = $creditAcc->id;
            $detailCredit->debit            = 0;
            $detailCredit->credit           = $depreciationAmount;
            $detailCredit->cost_center_id   = $costIdCredit; // ✅ (إن رغبت بربطه)
            $detailCredit->description      = $desc;
            $detailCredit->attachment_path  = $img;
            $detailCredit->entry_date       = $entryDate;
            $detailCredit->asset_id         = $asset->id;    // لو العمود موجود
            $detailCredit->save();

            // 7) معاملات (transection) لكل طرف مع ربط cost_id المناسب
            $trxModelClass = \App\Models\Transection::class;

            // (أ) المدين
            /** @var \App\Models\Transection $trxDebit */
            $trxDebit = new $trxModelClass();
            $trxDebit->tran_type               = 'depreciation';
            $trxDebit->seller_id               = $sellerId;
            $trxDebit->branch_id               = $branchId;
            $trxDebit->date                    = $entryDate;
            $trxDebit->description             = $desc;
            $trxDebit->img                     = $img;
            $trxDebit->asset_id                = $asset->id;
            $trxDebit->cost_id                 = $costIdDebit;   // ✅

            $trxDebit->account_id              = $debitAcc->id;
            $trxDebit->account_id_to           = $creditAcc->id;
            $trxDebit->debit                   = $depreciationAmount;
            $trxDebit->credit                  = 0;
            $trxDebit->debit_account           = $depreciationAmount;
            $trxDebit->credit_account          = 0;
            $trxDebit->amount                  = $depreciationAmount;
            // حسب منطق الرصيد المستخدم عندك:
            $trxDebit->balance                 = ($debitAcc->balance ?? 0) - $depreciationAmount;

            $trxDebit->journal_entry_detail_id = $detailDebit->id;
            $trxDebit->save();

            // (ب) الدائن
            /** @var \App\Models\Transection $trxCredit */
            $trxCredit = new $trxModelClass();
            $trxCredit->tran_type               = 'depreciation';
            $trxCredit->seller_id               = $sellerId;
            $trxCredit->branch_id               = $branchId;
            $trxCredit->date                    = $entryDate;
            $trxCredit->description             = $desc;
            $trxCredit->img                     = $img;
            $trxCredit->asset_id                = $asset->id;
            $trxCredit->cost_id                 = $costIdCredit;  // ✅

            $trxCredit->account_id              = $creditAcc->id;
            $trxCredit->account_id_to           = $debitAcc->id;
            $trxCredit->debit                   = 0;
            $trxCredit->credit                  = $depreciationAmount;
            $trxCredit->debit_account           = 0;
            $trxCredit->credit_account          = $depreciationAmount;
            $trxCredit->amount                  = $depreciationAmount;
            $trxCredit->balance                 = ($creditAcc->balance ?? 0) + $depreciationAmount;

            $trxCredit->journal_entry_detail_id = $detailCredit->id;
            $trxCredit->save();

            // 8) تحديث أرصدة الحسابات
            $debitAcc->total_out = ($debitAcc->total_out ?? 0) + $depreciationAmount;
            $debitAcc->balance   = ($debitAcc->balance ?? 0) - $depreciationAmount;
            $debitAcc->save();

            $creditAcc->total_in = ($creditAcc->total_in ?? 0) + $depreciationAmount;
            $creditAcc->balance  = ($creditAcc->balance ?? 0) + $depreciationAmount;
            $creditAcc->save();
        });

        Toastr::success('تم تسجيل قيد إهلاك وتحديث بيانات الأصل بنجاح.');
        return back();

    } catch (\Exception $e) {
        Toastr::error($e->getMessage());
        return back()->with('error', $e->getMessage());
    }
}


public function reverseDepreciation(Request $request, $asset_id)
{
    try {
        DB::transaction(function () use ($asset_id) {
            // جلب سجل الأصل للتأكد من وجوده
                $transaction = Transection::where('id', $asset_id)
                ->where('tran_type', 'Depreciation')
                ->latest('date')
                ->first();
            $asset = Asset::findOrFail($transaction->asset_id);

            // إيجاد آخر قيد اهلاك مسجل لهذا الأصل
        

            if (!$transaction) {
                throw new \Exception("لا يوجد قيد اهلاك لهذا الأصل ليتم عكسه.");
            }
                   $transaction->is_reversal = 2;
            $transaction->save();      
            // مبلغ القيد الذي سيتم عكسه (نفترض عكس القيد كاملاً)
            $reversalAmount = $transaction->amount;

            // تحديث بيانات الأصل: عكس تأثير الاهلاك
            $asset->accumulated_depreciation = max(0, $asset->accumulated_depreciation - $reversalAmount);
            $asset->book_value = $asset->total_cost - $asset->accumulated_depreciation;
            if ($asset->book_value > $asset->total_cost) {
                $asset->book_value = $asset->total_cost;
            }
            $asset->save();

            // استرجاع الحسابات المستخدمة في القيد الأصلي
            $account = Account::findOrFail($transaction->account_id);
            $account_to = Account::findOrFail($transaction->account_id_to);

            // تحديث أرصدة الحسابات: عكس التأثير السابق
            $account->total_out += $reversalAmount;
            $account->balance -= $reversalAmount;
            $account->save();

            $account_to->total_out += $reversalAmount;
            $account_to->balance -= $reversalAmount;
            $account_to->save();

            // تسجيل قيد عكسي جديد
            $reversalTransaction = new Transection();
            $reversalTransaction->tran_type = 'Reversal Depreciation';
            $reversalTransaction->seller_id = $transaction->seller_id;
            $reversalTransaction->account_id = $transaction->account_id;
            $reversalTransaction->cost_id = $transaction->cost_id;
            $reversalTransaction->branch_id = $transaction->branch_id;
            $reversalTransaction->account_id_to = $transaction->account_id_to;
            $reversalTransaction->amount = -$reversalAmount; // قيمة سالبة لعكس العملية
            $reversalTransaction->description = "عكس سند صرف اهلاك للأصل: " . $asset->asset_name;
            $reversalTransaction->debit = -$reversalAmount;
            $reversalTransaction->credit = 0;
            $reversalTransaction->balance = $account->balance;  // الرصيد بعد التعديل
            $reversalTransaction->debit_account = 0;
            $reversalTransaction->credit_account = -$reversalAmount;
            $reversalTransaction->balance_account = $account_to->balance;
            $reversalTransaction->date = Carbon::now()->toDateString();
            $reversalTransaction->img = null;
            $reversalTransaction->asset_id = $asset->id;
                        $reversalTransaction->is_reversal = 1;
            $reversalTransaction->save();
        });
        
        Toastr::success('تم عكس قيد الاهلاك وإرجاع حالة الأصل إلى سابق عهدها.');
        return redirect()->back();
    } catch (\Exception $e) {
        Toastr::error($e->getMessage());
        return redirect()->back()->with('error', $e->getMessage());
    }
}
}