<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Transection;
use App\CPU\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;


class PayableController extends Controller
{
    public function __construct(
        private Transection $transection,
        private Account $account,
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
public function add(Request $request)
{
    $adminId = Auth::guard('admin')->id();
    $admin = DB::table('admins')->where('id', $adminId)->first();

    // تحقق من وجود المسؤول وصلاحياته
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

    if (!in_array("start.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    // استرجاع الحسابات التي ليست parent ولا تحتوي على children
    $accounts = $this->account
        ->whereNotNull('parent_id')
        ->doesntHave('childrenn')
        ->orderBy('id')
        ->get();

    // استرجاع معايير البحث من الطلب
    $search = $request['search'];
    $from = $request->from;
    $to = $request->to;

    // بناء الاستعلام بناءً على وجود بحث
    if ($request->has('search')) {
        $key = explode(' ', $request['search']);
        // استخدام Eloquent بدلاً من Query Builder
        $query = JournalEntry::where('type', 1) // 1 = رصيد افتتاحي
            ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('description', 'like', "%{$value}%");
                }
            });
        $query_param = ['search' => $request['search']];
    } else {
        $query = JournalEntry::where('type', 1) // رصيد افتتاحي
            ->when($from != null, function ($q) use ($request) {
                return $q->whereBetween('date', [$request['from'], $request['to']]);
            });
    }

    // استرجاع القيود اليومية مع التفاصيل المتعلقة بالحسابات و الكاتب، مع التصفح (pagination)
    $journalEntries = $query->with('details.account', 'seller') // تضمين التفاصيل المتعلقة بالحسابات والكاتب
        ->latest() // ترتيب القيود حسب التاريخ
        ->paginate(Helpers::pagination_limit()); // تحديد عدد النتائج في الصفحة باستخدام دالة pagination_limit

    // إرجاع العرض مع البيانات المطلوبة
    return view('admin-views.account-payable.add', compact('accounts', 'journalEntries', 'search', 'from', 'to'));
}



    /**
     * @param Request $request
     * @return RedirectResponse
     */

// use App\Models\BusinessSetting; // لو عندك موديل، وإلا هنستخدم جدول الإعدادات مباشرة



public function store(Request $request): RedirectResponse
{
    // ===== 1) صلاحيات أساسية (مُعطّلة حسب طلبك) =====
    $adminId = Auth::guard('admin')->id();

    // ===== 2) التحقق من المدخلات (حساب واحد + مدين/دائن) =====
    $request->validate([
        'account_id' => 'required|exists:accounts,id',
        'debit'      => 'nullable|numeric|min:0',
        'credit'     => 'nullable|numeric|min:0',
        // الوصف والمرجع سنتجاهلهما ونحددهم داخليًا
        'entry_date' => 'nullable|date',
    ]);

    $debit  = (float) ($request->input('debit')  ?? 0);
    $credit = (float) ($request->input('credit') ?? 0);

    // لازم واحد فقط من الاثنين > 0
    if (($debit > 0 && $credit > 0) || ($debit == 0 && $credit == 0)) {
        return back()->withErrors(['amount' => 'أدخل مبلغًا واحدًا فقط: إمّا مدين أو دائن.'])->withInput();
    }

    // تاريخ القيد: 01-01 من السنة الحالية لو مش مبعوت
    $entryDate = $request->input('entry_date') ?: Carbon::now()->format('Y') . '-01-01';

    // الوصف ثابت
    $description = 'رصيد افتتاحي';

    // رقم مرجعي تلقائي
    $reference = sprintf('OB-%s-%s',
        Carbon::now()->format('Ymd-His'),
        strtoupper(Str::random(4))
    );

    // ===== 3) جلب الحساب المختار =====
    /** @var \App\Models\Account|\Illuminate\Database\Eloquent\Model $account */
    $account = $this->account->find($request->account_id);
    if (!$account) {
        Toastr::warning('الحساب غير موجود!');
        return back();
    }

    // ===== 4) الحصول على حساب "الرصيد الافتتاحي" أو إنشاؤه =====
    $openingAccountId = DB::table('business_settings')
        ->where('key', 'opening_balance_account_id')
        ->value('value');

    $openingAccount = $openingAccountId ? $this->account->find($openingAccountId) : null;

    if (!$openingAccount) {
        $openingAccount = $this->account
            ->where('account', 'رصيد افتتاحي')
            ->orWhere('account', 'Opening Balance')
            ->first();
    }

    if (!$openingAccount) {
        $openingAccountId = DB::table('accounts')->insertGetId([
            'account'      => 'رصيد افتتاحي',
            'code'         => 'OB-' . Carbon::now()->format('Y'),
            'account_type' => 'equity',
            'parent_id'    => null,
            'description'  => 'حساب مقابل قيود الأرصدة الافتتاحية',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
        $openingAccount = $this->account->find($openingAccountId);

        DB::table('business_settings')->updateOrInsert(
            ['key' => 'opening_balance_account_id'],
            ['value' => $openingAccountId, 'updated_at' => now(), 'created_at' => now()]
        );
    }

    // ===== 5) إنشاء القيد + التفاصيل + الحركات وتحديث الأرصدة =====
    DB::beginTransaction();
    try {
        // القيد الرئيسي
        $journalId = DB::table('journal_entries')->insertGetId([
            'entry_date' => $entryDate,
            'reference'  => $reference,       // ✅ مرجع تلقائي
            'description'=> $description,      // ✅ وصف ثابت
            'type'       => 1,                 // رصيد افتتاحي
            'created_by' => $adminId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // تفاصيل القيد
        $firstDetail = [
            'journal_entry_id' => $journalId,
            'account_id'       => $account->id,
            'debit'            => $debit  > 0 ? $debit  : 0,
            'credit'           => $credit > 0 ? $credit : 0,
            'description'      => $description,  // ✅
            'created_at'       => now(),
            'updated_at'       => now(),
        ];

        $secondDetail = [
            'journal_entry_id' => $journalId,
            'account_id'       => $openingAccount->id,
            'debit'            => $credit > 0 ? $credit : 0, // عكس الطرف
            'credit'           => $debit  > 0 ? $debit  : 0,
            'description'      => $description,  // ✅
            'created_at'       => now(),
            'updated_at'       => now(),
        ];

        DB::table('journal_entries_details')->insert([$firstDetail, $secondDetail]);

        // ===== الحركات (transactions) =====
        $amount = max($debit, $credit);

        // حساب المستخدم
        $t1 = new $this->transection();
        $t1->tran_type       = 1;
        $t1->seller_id       = $adminId;
        $t1->account_id      = $account->id;
        $t1->amount          = $amount;
        $t1->description     = $description;   // ✅
        $t1->date            = $entryDate;
        $t1->debit           = $debit;
        $t1->credit          = $credit;
        $t1->debit_account   = $debit;
        $t1->credit_account  = $credit;
        $t1->balance         = $debit > 0
            ? ($account->total_in + $debit - $account->total_out)
            : ($account->total_in - ($account->total_out + $credit));
        $t1->balance_account = $account->balance; // قبل التحديث
        $t1->save();

        // حساب الرصيد الافتتاحي (عكس)
        $t2 = new $this->transection();
        $t2->tran_type       = 1;
        $t2->seller_id       = $adminId;
        $t2->account_id      = $openingAccount->id;
        $t2->amount          = $amount;
        $t2->description     = $description;   // ✅
        $t2->date            = $entryDate;
        $t2->debit           = $credit;
        $t2->credit          = $debit;
        $t2->debit_account   = $credit;
        $t2->credit_account  = $debit;
        $t2->balance         = $credit > 0
            ? ($openingAccount->total_in + $credit - $openingAccount->total_out)
            : ($openingAccount->total_in - ($openingAccount->total_out + $debit));
        $t2->balance_account = $openingAccount->balance; // قبل التحديث
        $t2->save();

        // ===== تحديث الأرصدة =====
        if ($debit > 0) {
            $account->total_in += $debit;
            $account->balance  += $debit;
        } else {
            $account->total_out += $credit;
            $account->balance   -= $credit;
        }
        $account->save();

        if ($credit > 0) {
            $openingAccount->total_in += $credit;
            $openingAccount->balance  += $credit;
        } else {
            $openingAccount->total_out += $debit;
            $openingAccount->balance   -= $debit;
        }
        $openingAccount->save();

        DB::commit();
        Toastr::success(translate('تمت إضافة الرصيد الافتتاحي بنجاح'));
        return back();

    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->withErrors(['error' => $e->getMessage()]);
    }
}



    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function transfer(Request $request): RedirectResponse
    {
        $payment_account = $this->account->find($request->payment_account_id);
        $remain_balance = $payment_account->balance - $request->amount;
        if($remain_balance < 0)
        {
            Toastr::warning(translate('Your payment account has not sufficent balance for this transaction'));
            return back();
        }
        $payable_account = $this->account->find($request->account_id);
        $payable_transection = $this->transection->find($request->transection_id);
        $balance = $payable_transection->amount - $request->amount;
        if($balance < 0){
            Toastr::warning(translate('You have not sufficient balance for this transaction'));
            return back();
        }
        $payable_transection->amount = $balance;
        $payable_transection->balance = $payable_transection->balance - $request->amount;
        $payable_transection->save();

        $payable_account->total_out = $payable_account->total_out + $request->amount;
        $payable_account->balance = $payable_account->balance - $request->amount;
        $payable_account->save();

        $transection = $this->transection;
        $transection->tran_type = 'Expense';
        $transection->account_id = $request->payment_account_id;
        $transection->amount = $request->amount;
        $transection->description = $request->description;
        $transection->debit = 1;
        $transection->credit = 0;
        $transection->balance =  $payment_account->balance - $request->amount;
        $transection->date = $request->date;
        $transection->save();

        $payment_account->total_out = $payment_account->total_out + $request->amount;
        $payment_account->balance = $payment_account->balance - $request->amount;
        $payment_account->save();

        Toastr::success(translate('Payable Balance pay successfully'));
        return back();
    }
   public function download(Request $request)
{
    // Ensure the user is authenticated as a seller
    $seller = Auth::guard('admin')->user();

    if (!$seller) {
        abort(403, 'Unauthorized');
    }

    // Fetch accounts and apply filters
    $payables = $this->transection->where('tran_type', 1)->orderBy('id')->get();

    $search = $request->input('search', '');
    $from = $request->input('from');
    $to = $request->input('to');

    $query = $this->transection->where('tran_type', 1);

    if ($search) {
        $key = explode(' ', $search);
        $query->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('description', 'like', "%{$value}%");
            }
        });
    }

    if ($from && $to) {
        $query->whereBetween('date', [$from, $to]);
    }

    $transactions = $query->get();

    // Render Blade view to generate HTML
    $html = view('admin-views.account-payable.pdf', compact('payables', 'search', 'seller', 'transactions'))->render();

    // Save the HTML content to a temporary file
    $fileName = 'account_report_' . now()->format('Y_m_d_H_i_s') . '.html';
    $filePath = storage_path('app/public/' . $fileName);
    file_put_contents($filePath, $html);

    // Return the file as a response for download
    return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
}


}
