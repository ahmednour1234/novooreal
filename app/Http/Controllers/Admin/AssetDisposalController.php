<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\CostCenter;
use App\Models\Branch;
use App\Models\Account;
use App\Models\Transection; // افترض إنشاء نموذج AssetDisposal لتسجيل عمليات التخلص (يمكنك تخصيصه)
use Illuminate\Http\Request;
use DB;
use Toastr;
use Carbon\Carbon;
use function App\CPU\translate;
use Illuminate\Support\Facades\Auth;

class AssetDisposalController extends Controller
{
        public function __construct(
        private Transection $transection,
        private Account $account,
        private Branch $branch,
        private CostCenter $costcenter,
    ){}
    /**
     * عرض نموذج تسجيل بيع أصل.
     */
    public function createSale($asset_id)
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

    if (!in_array("asset.sale", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
            $accounts_to = $this->account
        ->where(function ($query) {
            // هنا استخدمنا [4] فقط بدل [4, 4] لأنها نفس القيمة
            $query->whereIn('parent_id', [4])
                  ->orWhereHas('parent', function ($q) {
                      $q->whereIn('parent_id', [4]);
                  });
        })
        ->doesntHave('childrenn')
        ->orderBy('id', 'desc')
        ->get();

    $accounts = $this->account
        ->whereIn('id', [8, 14])
        ->orWhere(function ($query) {
            $query->whereIn('parent_id', [8, 14])
                  ->orWhereHas('parent', function ($q) {
                      $q->whereIn('parent_id', [8, 14]);
                  });
        })
        ->doesntHave('childrenn')
        ->orderBy('id', 'desc')
        ->get();
        $costcenters = $this->costcenter->doesntHave('children')->orderBy('id', 'desc')->get();

        $asset = Asset::findOrFail($asset_id);
        return view('admin-views.assets_disposal.sale', compact('asset','accounts','accounts_to','costcenters'));
    }

    /**
     * تخزين عملية البيع.
     */
public function storeSale(Request $request, $asset_id)
{
    $validated = $request->validate([
        'sale_date'     => 'required|date',
        'sale_price'    => 'required|numeric|min:0',
        'description'   => 'nullable|string|max:1000',
        'cost_id'       => 'nullable|exists:cost_centers,id',
        'account_id_to' => 'required|exists:accounts,id', // البنك/النقدية (المستلم) = مدين
        'account_id'    => 'required|exists:accounts,id', // حساب الأصل = يُخرج
        'img'           => 'nullable|image|max:4096',
    ]);

    $asset = \App\Models\Asset::findOrFail($asset_id);
    if ($asset->status === 'sold') {
        return back()->withErrors(['status' => __('تم بيع هذا الأصل بالفعل.')]);
    }

    try {
        DB::transaction(function () use ($request, $validated, $asset) {

            // ✅ بدّل دي لـ true لو عايز "الربح مدين" بدل دائن
            $profitAsDebit = false;

            $saleDate   = \Carbon\Carbon::parse($validated['sale_date'])->toDateString();
            $salePrice  = (float)$validated['sale_price'];
            $desc       = $validated['description'] ?? ('بيع أصل: ' . ($asset->asset_name ?? ('#'.$asset->id)));
            $costId     = $validated['cost_id'] ?? null;

            $admin    = Auth::guard('admin')->user();
            $branchId = $admin->branch_id ?? null;
            $sellerId = $admin->id ?? null;

            $img = $request->hasFile('img')
                ? \App\CPU\Helpers::update('journal/', null, 'png', $request->file('img'))
                : null;

            // الحسابات
            $accountAsset  = \App\Models\Account::findOrFail($validated['account_id']);     // الأصل
            $accountBank   = \App\Models\Account::findOrFail($validated['account_id_to']);  // البنك/النقدية (مدين)
            $accountGain   = \App\Models\Account::findOrFail(82); // أرباح بيع أصل
            $accountLoss   = \App\Models\Account::findOrFail(83); // خسائر بيع أصل
            $accountTaswia = \App\Models\Account::findOrFail(84); // تسوية
            $accountEhalak = \App\Models\Account::findOrFail(86); // مجمّع الإهلاك

            // أرقام محاسبية
            $bookValue  = (float)($asset->book_value ?? 0);
            $totalCost  = (float)($asset->total_cost ?? 0);
            $accumDep   = max(0, $totalCost - $bookValue);
            $gainOrLoss = $salePrice - $bookValue;

            // مرجع
            $reference = 'SALE-' . now()->format('Ymd') . '-' . str_pad((string)mt_rand(1,9999), 4, '0', STR_PAD_LEFT);

            // رأس اليومية
            $entry = new \App\Models\JournalEntry();
            $entry->entry_date  = $saleDate;
            $entry->reference   = $reference;
            $entry->description = $desc;
            $entry->created_by  = $sellerId;
            $entry->branch_id   = $branchId;
            $entry->asset_id    = $asset->id;
            $entry->save();

            // Helper
            $addLine = function (
                \App\Models\Account $account,
                float $debit, float $credit,
                ?int $counterAccountId,
                string $lineDesc,
                ?int $lineCostCenterId = null
            ) use ($entry, $saleDate, $img, $asset, $branchId, $sellerId) {

                $detail = new \App\Models\JournalEntryDetail();
                $detail->journal_entry_id = $entry->id;
                $detail->account_id       = $account->id;
                $detail->debit            = $debit;
                $detail->credit           = $credit;
                $detail->cost_center_id   = $lineCostCenterId;
                $detail->description      = $lineDesc;
                $detail->attachment_path  = $img;
                $detail->entry_date       = $saleDate;
                $detail->asset_id         = $asset->id;
                $detail->save();

                $trx = new \App\Models\Transection();
                $trx->tran_type               = 'asset_sold';
                $trx->seller_id               = $sellerId;
                $trx->branch_id               = $branchId;
                $trx->date                    = $saleDate;
                $trx->description             = $lineDesc;
                $trx->img                     = $img;
                $trx->asset_id                = $asset->id;
                $trx->cost_id                 = $lineCostCenterId;
                $trx->account_id              = $account->id;
                $trx->account_id_to           = $counterAccountId;
                $trx->debit                   = $debit;
                $trx->credit                  = $credit;
                $trx->debit_account           = $debit;
                $trx->credit_account          = $credit;

                // رصيد الحساب = القديم + مدين - دائن
                $prevBalance  = (float)($account->balance ?? 0);
                $newBalance   = $prevBalance + $debit - $credit;
                $trx->balance = $newBalance;

                $trx->journal_entry_detail_id = $detail->id;
                $trx->save();

                // تحديث الحساب
                $account->balance    = $newBalance;
                $account->total_in   = ($account->total_in  ?? 0) + $debit;
                $account->total_out  = ($account->total_out ?? 0) + $credit;
                $account->save();
            };

            /** 1) استلام البيع: Dr بنك / Cr تسوية */
            $addLine($accountBank,   $salePrice, 0,             $accountTaswia->id, 'تسوية بيع الأصل - استلام قيمة البيع', null);
            $addLine($accountTaswia, 0,           $salePrice,   $accountBank->id,   'تسوية بيع الأصل - مقابل قيمة البيع',   $costId);

            /** 2) إخراج الأصل: Dr تسوية / Cr أصل (بقيمة التكلفة التاريخية) */
            $addLine($accountTaswia, $totalCost,  0,            $accountAsset->id,  'تسوية بيع الأصل - إثبات تكلفة الأصل',  $costId);
            $addLine($accountAsset,  0,           $totalCost,   $accountTaswia->id, 'تسوية بيع الأصل - إخراج الأصل',        $costId);

            /** 3) إلغاء مجمّع الإهلاك: Dr مجمّع / Cr تسوية */
            if ($accumDep > 0) {
                $addLine($accountEhalak, $accumDep, 0,          $accountTaswia->id, 'تسوية بيع الأصل - إلغاء الإهلاك المتراكم', $costId);
                $addLine($accountTaswia, 0,         $accumDep,  $accountEhalak->id, 'تسوية بيع الأصل - مقابل إلغاء الإهلاك',    $costId);
            }

            /** 4) ربح/خسارة */
            if ($gainOrLoss > 0) {
                // ✅ المعياري: الربح دائن
                if (!$profitAsDebit) {
                    // Dr تسوية / Cr أرباح
                    $addLine($accountTaswia, $gainOrLoss, 0,                $accountGain->id,   'تسوية بيع الأصل - ربح بيع أصل', $costId);
                    $addLine($accountGain,   0,            $gainOrLoss,     $accountTaswia->id, 'تسوية بيع الأصل - قيد مقابل الربح', $costId);
                } else {
                    // ❗️اختياري لو عايز الربح مدين: Dr أرباح / Cr تسوية
                    $addLine($accountGain,   $gainOrLoss, 0,                $accountTaswia->id, 'تسجيل ربح (مدين حسب إعدادك)', $costId);
                    $addLine($accountTaswia, 0,            $gainOrLoss,     $accountGain->id,   'مقابل ربح (تسوية)',            $costId);
                }
            } elseif ($gainOrLoss < 0) {
                $loss = abs($gainOrLoss);
                // الخسارة دائمًا مدين (معياري)
                // Dr خسائر / Cr تسوية
                $addLine($accountLoss,   $loss, 0,                  $accountTaswia->id, 'تسوية بيع الأصل - خسارة بيع أصل', $costId);
                $addLine($accountTaswia, 0,     $loss,              $accountLoss->id,   'تسوية بيع الأصل - قيد مقابل الخسارة', $costId);
            }

            // تحديث حالة الأصل
            $asset->status = 'sold';
            $asset->save();
        });

        Toastr::success('تم تسجيل بيع الأصل بنجاح.');
        return back();

    } catch (\Exception $e) {
        Toastr::error($e->getMessage());
        return back()->withInput();
    }
}




    /**
     * عرض نموذج تسجيل الإهلاك التام (إغلاق الأصل).
     */
    public function createCompleteDepreciation($asset_id)
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

    if (!in_array("asset.ehlaktam", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $asset = Asset::findOrFail($asset_id);
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
        return view('admin-views.assets_disposal.complete_depreciation', compact('asset','accounts_to','accounts','costcenters'));
    }

    /**
     * تخزين عملية الإهلاك التام.
     */
public function storeCompleteDepreciation(Request $request, $asset_id)
{
    // التحقق من صحة البيانات
    $validated = $request->validate([
        'closure_date' => 'required|date',
        'notes'        => 'nullable|string|max:1000',
        'account_id'   => 'required|exists:accounts,id',        // الدائن (مثلاً: أصل/مجمّع إهلاك)
        'account_id_to'=> 'required|exists:accounts,id|different:account_id', // المدين (مثلاً: مصروف/إغلاق)
        'cost_id'      => 'nullable|exists:cost_centers,id',
        'voucher_img'  => 'nullable|image|max:4096',
    ]);

    // احضر الأصل
    $asset = Asset::findOrFail($asset_id);
    if ($asset->status === 'closed') {
        return back()->withErrors(['status' => __('تم إهلاك هذا الأصل بالفعل.')]);
    }

    try {
        DB::transaction(function () use ($request, $validated, $asset) {

            // المتغيرات الأساسية
            $closureDate   = \Carbon\Carbon::parse($validated['closure_date'])->toDateString();
            $currentUserId = Auth::guard('admin')->id();
            $branchId      = Auth::guard('admin')->user()->branch_id ?? null;

            // الحسابات
            /** @var \App\Models\Account $creditAcc */
            $creditAcc = Account::findOrFail($validated['account_id']);    // دائن
            /** @var \App\Models\Account $debitAcc */
            $debitAcc  = Account::findOrFail($validated['account_id_to']); // مدين

            // صورة مرفق (اختياري)
            $voucherImg = null;
            if ($request->hasFile('voucher_img')) {
                // استخدم الهيلبر الخاص بك لو متوفر، وإلا storage:
                // $voucherImg = \App\CPU\Helpers::update('journal/', null, 'png', $request->file('voucher_img'));
                $voucherImg = $request->file('voucher_img')->store('voucher_imgs');
            }

            // الوصف
            $desc = $validated['notes'] ?? ('إغلاق أصل بالكامل: ' . ($asset->asset_name ?? ('#' . $asset->id)));

            // قيمة القيد (حسب طلبك: تكلفة الأصل كاملة)
            $amount = (float) ($asset->total_cost ?? 0);

            // لو عايز قيد محايد تمامًا وقت الإغلاق بدون أثر على الأرباح (أكثر دقة محاسبيًا):
            // يكون: مدين "مجمّع إهلاك" بمقدار accumulated، ودائن "الأصول" بمقدار total_cost.
            // لكنك طلبت جعل القيد بمبلغ total_cost للمدين والدائن المُدخلين، لذا أبقيت منطقك كما هو.

            // رقم مرجع للقيد
            $reference = 'CLOSE-' . now()->format('Ymd') . '-' . str_pad((string)mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            /*
             * 1) إنشاء رأس اليومية JournalEntry
             */
            $entry = new \App\Models\JournalEntry();
            $entry->entry_date   = $closureDate;
            $entry->reference    = $reference;
            $entry->description  = $desc;
            $entry->created_by   = $currentUserId;
            $entry->branch_id    = $branchId;
            $entry->asset_id     = $asset->id; // لو العمود موجود عندك
            $entry->save();

            /*
             * 2) تفاصيل اليومية:
             *   - مدين: account_id_to (مصروف/إغلاق)
             *   - دائن: account_id (أصل/مجمّع)
             */
            $detailDebit = new \App\Models\JournalEntryDetail();
            $detailDebit->journal_entry_id = $entry->id;
            $detailDebit->account_id       = $debitAcc->id;
            $detailDebit->debit            = $amount;
            $detailDebit->credit           = 0;
            $detailDebit->cost_center_id   = $validated['cost_id'] ?? null;
            $detailDebit->description      = $desc;
            $detailDebit->attachment_path  = $voucherImg;
            $detailDebit->entry_date       = $closureDate;
            $detailDebit->asset_id         = $asset->id; // إن وُجد
            $detailDebit->save();

            $detailCredit = new \App\Models\JournalEntryDetail();
            $detailCredit->journal_entry_id = $entry->id;
            $detailCredit->account_id       = $creditAcc->id;
            $detailCredit->debit            = 0;
            $detailCredit->credit           = $amount;
            $detailCredit->cost_center_id   = null;
            $detailCredit->description      = $desc;
            $detailCredit->attachment_path  = $voucherImg;
            $detailCredit->entry_date       = $closureDate;
            $detailCredit->asset_id         = $asset->id; // إن وُجد
            $detailCredit->save();

            /*
             * 3) إنشاء معاملتين (Transection) وربط كل واحدة بالسطر المناسب
             */
            $trxModelClass = \App\Models\Transection::class;

            // (أ) معاملة المدين
            /** @var \App\Models\Transection $trxDebit */
            $trxDebit = new $trxModelClass();
            $trxDebit->tran_type               = 'asset_closed';
            $trxDebit->seller_id               = $currentUserId;
            $trxDebit->branch_id               = $branchId;
            $trxDebit->date                    = $closureDate;
            $trxDebit->description             = $desc;
            $trxDebit->img                     = $voucherImg;
            $trxDebit->asset_id                = $asset->id;
            $trxDebit->cost_id                 = $validated['cost_id'] ?? null;

            $trxDebit->account_id              = $debitAcc->id;   // المدين
            $trxDebit->debit                   = $amount;
            $trxDebit->credit                  = 0;
            $trxDebit->debit_account           = $amount;
            $trxDebit->credit_account          = 0;
            $trxDebit->balance                 = ($debitAcc->balance ?? 0) - $amount;

            $trxDebit->journal_entry_detail_id = $detailDebit->id;
            $trxDebit->save();

            // (ب) معاملة الدائن
            /** @var \App\Models\Transection $trxCredit */
            $trxCredit = new $trxModelClass();
            $trxCredit->type                    = 'asset_closed';
            $trxCredit->tran_type               = 'asset_closed';
            $trxCredit->seller_id               = $currentUserId;
            $trxCredit->branch_id               = $branchId;
            $trxCredit->date                    = $closureDate;
            $trxCredit->description             = $desc;
            $trxCredit->img                     = $voucherImg;
            $trxCredit->asset_id                = $asset->id;
            $trxCredit->cost_id                 = $validated['cost_id'] ?? null;

            $trxCredit->account_id              = $creditAcc->id; // الدائن
            $trxCredit->debit                   = 0;
            $trxCredit->credit                  = $amount;
            $trxCredit->debit_account           = 0;
            $trxCredit->credit_account          = $amount;
            $trxCredit->balance                 = ($creditAcc->balance ?? 0) + $amount;

            $trxCredit->journal_entry_detail_id = $detailCredit->id;
            $trxCredit->save();

            /*
             * 4) تحديث أرصدة الحسابات
             * - المدين: يزيد رصيده/وارداته
             * - الدائن: يقل رصيده/يُسجّل عليه خروج
             */
            $debitAcc->total_out  = ($debitAcc->total_out ?? 0) + $amount;
            $debitAcc->balance   = ($debitAcc->balance ?? 0) - $amount;
            $debitAcc->save();

            $creditAcc->total_in = ($creditAcc->total_in ?? 0) + $amount;
            $creditAcc->balance   = ($creditAcc->balance ?? 0) + $amount;
            $creditAcc->save();

            /*
             * 5) إغلاق الأصل
             */
            $asset->status     = 'closed';
            $asset->book_value = $asset->salvage_value ?? 0; // بعد الإغلاق
            $asset->save();
        });

        Toastr::success('تم تسجيل قيد إغلاق الأصل وإنشاء قيود اليومية والمعاملات بنجاح.');
        return back();

    } catch (\Exception $e) {
        Toastr::error($e->getMessage());
        return back()->withInput();
    }
}


    /**
     * عرض نموذج تسجيل الإهداء/التخلص (نفايات) للأصل.
     */
    public function createDonation($asset_id)
    {
        $asset = Asset::findOrFail($asset_id);
        return view('admin-views.assets_disposal.donation', compact('asset'));
    }

    /**
     * تخزين عملية الإهداء/التخلص.
     */
    public function storeDonation(Request $request, $asset_id)
    {
        $validated = $request->validate([
            'disposal_date' => 'required|date',
            'notes'         => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $asset_id) {
                $asset = Asset::findOrFail($asset_id);
                      $account           = $this->account->find($request->account_id);       // حساب الأصل الثابت
            $account_to        = $this->account->find($request->account_id_to);    // حس
            $account_loss      = $this->account->find(83);                          // حساب خسائر بيع الأصل
            $account_taswia    = $this->account->find(84);                          // حساب تسوية بيع الأصل (مؤقت)
            $account_ehalak    = $this->account->find(86);                          // حساب إزالة الإهلاك
                           $totalCost = $asset->total_cost; // التكلفة التاريخية للأصل
            // نحسب الاستهلاك المتراكم: نفترض أنه مسجل بالفعل في النظام
            $accumDep = $asset->total_cost - $asset->book_value;
            // القيمة الدفترية = التكلفة التاريخية - الاستهلاك المتراكم
            $netBookValue = $asset->book_value; // في هذا المثال، لنحسب 10,000 - 7,000 = 3,000 جنيه
            $currentUserId = Auth::guard('admin')->id();
            $branchId = auth('admin')->user()->branch_id;
            $closureDate = $request->closure_date;
            
                // تسجيل قيد التخلص – مثال مبسط
          $transection = new Transection();
            $transection->tran_type = 'asset_disposed'; // على سبيل المثال "depreciation"
            $transection->seller_id = Auth::guard('admin')->id();
            $transection->account_id = $request->account_id;
            $transection->cost_id = $request->cost_id;
            $transection->branch_id = auth('admin')->user()->branch_id;
            $transection->account_id_to = $request->account_id_to;
            $transection->amount = $netBookValue;
            $transection->description = $request->notes;
            // هنا نفترض تسجيل المصروف (مدين) والاهلاك المتراكم (دائن)
            $transection->debit =  $netBookValue;
            $transection->credit = 0;
            // تحديث أرصدة الحسابات
            $transection->balance = $account->balance -  $netBookValue;
            $transection->debit_account = 0;
            $transection->credit_account = $netBookValue;
            $transection->balance_account = $account_to->balance +  $netBookValue;
            $transection->date =$request->disposal_date;
            $transection->img = $img;
            $transection->asset_id = $asset->id;
               $account->total_out += $lossValue;
                $account->balance   -= $lossValue;
                $account->save();

                $account_to->total_in += $lossValue;
                $account_to->balance  += $lossValue;
                $account_to->save();
//////////////////////////////////////////////////////////////////////////////////////
       $transection2 = new Transection();
            $transection2->tran_type = 'asset_disposed'; // على سبيل المثال "depreciation"
            $transection2->seller_id = Auth::guard('admin')->id();
            $transection2->account_id = $request->account_id;
            $transection2->cost_id = $request->cost_id;
            $transection2->branch_id = auth('admin')->user()->branch_id;
            $transection2->account_id_to = $account_ehalak->id;
            $transection2->amount = $accumDep;
            $transection2->description = $request->notes;
            // هنا نفترض تسجيل المصروف (مدين) والاهلاك المتراكم (دائن)
            $transection2->debit =  $accumDep;
            $transection2->credit = 0;
            // تحديث أرصدة الحسابات
            $transection2->balance = $account->balance -  $accumDep;
            $transection2->debit_account = 0;
            $transection2->credit_account = $accumDep;
            $transection2->balance_account = $account_ehalak->balance -  $accumDep;
            $transection2->date =$request->disposal_date;
            $transection2->img = $img;
            $transection2->asset_id = $asset->id;
               $account->total_out += $accumDep;
                $account->balance   -= $accumDep;
                $account->save();

                $account_ehalak->total_out += $accumDep;
                $account_ehalak->balance  -= $accumDep;
                $account_ehalak->save();
                // تحديث حالة الأصل، على سبيل المثال إلى "disposed"
                $asset->status = 'disposed';
                $asset->save();
            });

            Toastr::success('تم تسجيل عملية التخلص بنجاح.');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back()->withInput();
        }
    }
}
