<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstallmentContract;
use App\Models\Customer;
use App\Models\Admin;
use App\Models\Account;
use App\Models\ScheduledInstallment;
use App\Models\Transection;
use App\Models\CostCenter;
use App\Models\JournalEntryDetail;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use PDF;
use Illuminate\Support\Facades\DB; // ← هذا السطر
use Carbon\Carbon;         // ← هذا السطر
use Illuminate\Validation\Rule;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;
use Illuminate\Support\Facades\Auth;

class InstallmentContractController extends Controller
{
    public function index(Request $request)
    {
        $contracts = InstallmentContract::with(['customer'])
            ->when($request->customer_id, fn($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->date_from && $request->date_to, fn($q) => $q->whereBetween('start_date', [$request->date_from, $request->date_to]))
            ->orderBy('id', 'desc')
            ->paginate(15);

        $customers = Customer::all();

        return view('admin-views.installments.index', compact('contracts', 'customers'));
    }

public function downloadPDF($id)
{
    $contract = InstallmentContract::with(['customer', 'installments'])->findOrFail($id);

    $pdf = Pdf::loadView('admin-views.installments.pdf', compact('contract'));
    
    // تخصيص إعدادات الخط داخل كائن dompdf مباشرة
    $dompdf = $pdf->getDomPDF();
    $options = $dompdf->getOptions();
    $options->set('defaultFont', 'dejavu sans'); // استخدم خط يدعم العربية (موجود مع dompdf)
    $dompdf->setOptions($options);

    return $pdf->download("عقد_تقسيط_{$contract->id}.pdf");
}
public function show($id)
{
    $contract = InstallmentContract::with(['customer', 'scheduledInstallments', 'order.seller'])->findOrFail($id);
$customer=Customer::where('id',$contract->customer_id)->first();
$accounts=Account::where('id',$customer->account_id)->get();
    $accounts_to = Account::whereIn('id', [8, 14])
        ->orWhere(function ($query) {
            $query->whereIn('parent_id', [8, 14])
                  ->orWhereHas('parent', function ($q) {
                      $q->whereIn('parent_id', [8, 14]);
                  });
        })
        ->doesntHave('childrenn')
        ->orderBy('id', 'desc')
        ->get();
$cost_centers = CostCenter::doesntHave('children')->orderBy('id', 'desc')->get();

    return view('admin-views.installments.show', compact('contract','accounts','accounts_to','cost_centers'));
}

// use Illuminate\Support\Facades\Schema; // فقط لو هتتحقق من وجود عمود في runtime

public function payInstallment(Request $request, $contractId)
{
    $validated = $request->validate([
        'installment_id'   => 'required|exists:scheduled_installments,id',
        'account_id'       => 'required|exists:accounts,id',   // من حساب (يُسجَّل دائن)
        'account_id_to'    => 'required|exists:accounts,id',   // إلى حساب (يُسجَّل مدين)
        'description'      => 'required|string|max:255',
        'payment_date'     => 'required|date',
        'payment_amount'   => 'required|numeric|min:0.01',
        'receipt'          => 'nullable|image|max:2048',
        // ⬇️ مركز التكلفة: مطلوب فقط إذا كان account_id_to لديه cost_center=1
        'cost_center_id'   => [
            'nullable',
            'exists:cost_centers,id',
            Rule::requiredIf(function() use ($request) {
                $acc = Account::find($request->input('account_id_to'));
                return $acc && (int)($acc->cost_center ?? 0) === 1;
            }),
        ],
    ], [
        'cost_center_id.required' => 'يجب اختيار مركز تكلفة لهذا الحساب.',
    ]);

    // جلب القسط
    $installment = ScheduledInstallment::findOrFail($validated['installment_id']);

    // 1) بيانات مُهيّأة
    $paidBefore = $installment->purchased_amount ?? 0;
    $payment    = (float) $validated['payment_amount'];

    // 2) المتبقي
    $remaining = (float) $installment->amount - (float) $paidBefore;

    // 3) منع الدفع بأكثر من المتبقي
    if ($payment > $remaining + 0.00001) {
        $remainingFormatted = number_format($remaining, 2);
        return redirect()->back()->withInput()->withErrors([
            'payment_amount' => "لا يمكن دفع أكثر من المبلغ المتبقي ({$remainingFormatted})."
        ]);
    }

    // منع اختيار نفس الحسابين
    if ((int)$validated['account_id'] === (int)$validated['account_id_to']) {
        return redirect()->back()->withInput()->withErrors([
            'account_id' => 'لا يمكن أن يكون (من حساب) هو نفسه (إلى حساب).'
        ]);
    }

    DB::beginTransaction();
    try {
        // تحديث حالة القسط
        $installment->purchased_amount = ($installment->purchased_amount ?? 0) + $payment;
        $installment->status = ($installment->purchased_amount + 0.00001 >= (float) $installment->amount) ? 'paid' : 'partial';
        $installment->save();

        // الحسابات
        $fromAccount = Account::findOrFail($validated['account_id']);     // دائن
        $toAccount   = Account::findOrFail($validated['account_id_to']);  // مدين
        $amountPaid  = $payment;

        $fromBefore = (float) $fromAccount->balance;
        $toBefore   = (float) $toAccount->balance;

        // رفع الإيصال
        $storedReceiptPath = null;
        if ($request->hasFile('receipt')) {
            $storedReceiptPath = $request->file('receipt')->store('receipts/installments', 'public');
        }

        // قيد يومية (رأس)
        $entry = new JournalEntry();
        $entry->entry_date         = \Carbon\Carbon::parse($validated['payment_date'])->toDateString();
        $entry->reference          = 'INST-'.$installment->id.'-PAY-'.now()->format('YmdHis');
        $entry->type               = 'payment';
        $entry->description        = $validated['description'];
        $entry->created_by         = Auth::guard('admin')->id();
        $entry->payment_voucher_id = null;
        $entry->branch_id          = auth('admin')->user()->branch_id ?? null;
        $entry->save();

        $ccId = $validated['cost_center_id'] ?? null;

        // تفاصيل — مدين (إلى حساب)
        $detailDebit = new JournalEntryDetail();
        $detailDebit->journal_entry_id = $entry->id;
        $detailDebit->account_id       = $toAccount->id;
        $detailDebit->debit            = $amountPaid;
        $detailDebit->credit           = 0;
        $detailDebit->cost_center_id   = $ccId;
        $detailDebit->description      = $validated['description'];
        $detailDebit->attachment_path  = $storedReceiptPath;
        $detailDebit->entry_date       = $entry->entry_date;
        $detailDebit->save();

        // تفاصيل — دائن (من حساب)
        $detailCredit = new JournalEntryDetail();
        $detailCredit->journal_entry_id = $entry->id;
        $detailCredit->account_id       = $fromAccount->id;
        $detailCredit->debit            = 0;
        $detailCredit->credit           = $amountPaid;
        $detailCredit->cost_center_id   = $ccId;
        $detailCredit->description      = $validated['description'];
        $detailCredit->attachment_path  = $storedReceiptPath;
        $detailCredit->entry_date       = $entry->entry_date;
        $detailCredit->save();

        // حركتان في Transection (مدين/دائن)
        // مدين (إلى حساب)
        $tranDebit = new Transection();
        $tranDebit->account_id       = $toAccount->id;
        $tranDebit->account_id_to    = $fromAccount->id;
        $tranDebit->description      = $validated['description'];
        $tranDebit->tran_type        = 200;
        $tranDebit->amount           = $amountPaid;
        $tranDebit->debit            = $amountPaid;
        $tranDebit->credit           = 0;
        $tranDebit->balance          = $toBefore + $amountPaid;
        $tranDebit->debit_account    = $amountPaid;
        $tranDebit->credit_account   = 0;
        $tranDebit->balance_account  = $toBefore + $amountPaid;
        $tranDebit->seller_id        = auth('admin')->id();
        $tranDebit->branch_id        = auth('admin')->user()->branch_id ?? null;
        $tranDebit->order_id         = $installment->contract->order_id ?? null;
        $tranDebit->date             = \Carbon\Carbon::parse($validated['payment_date'])->toDateString();
        if ($storedReceiptPath) $tranDebit->img = $storedReceiptPath;
        // لو جدول transections به عمود cost_center_id فكّ الكومنت التالي:
        // $tranDebit->cost_center_id = $ccId;
        $tranDebit->save();

        // دائن (من حساب)
        $tranCredit = new Transection();
        $tranCredit->account_id       = $fromAccount->id;
        $tranCredit->account_id_to    = $toAccount->id;
        $tranCredit->description      = $validated['description'];
        $tranCredit->tran_type        = 200;
        $tranCredit->amount           = $amountPaid;
        $tranCredit->debit            = 0;
        $tranCredit->credit           = $amountPaid;
        $tranCredit->balance          = $fromBefore - $amountPaid;
        $tranCredit->debit_account    = 0;
        $tranCredit->credit_account   = $amountPaid;
        $tranCredit->balance_account  = $fromBefore - $amountPaid;
        $tranCredit->seller_id        = auth('admin')->id();
        $tranCredit->branch_id        = auth('admin')->user()->branch_id ?? null;
        $tranCredit->order_id         = $installment->contract->order_id ?? null;
        $tranCredit->date             = \Carbon\Carbon::parse($validated['payment_date'])->toDateString();
        if ($storedReceiptPath) $tranCredit->img = $storedReceiptPath;
        // $tranCredit->cost_center_id = $ccId; // ← فك لو العمود موجود
        $tranCredit->save();

        // تحديث أرصدة الحسابات
        $fromAccount->balance   = $fromBefore - $amountPaid;
        $fromAccount->total_out = ($fromAccount->total_out ?? 0) + $amountPaid;
        $fromAccount->save();

        $toAccount->balance     = $toBefore + $amountPaid;
        $toAccount->total_in    = ($toAccount->total_in ?? 0) + $amountPaid;
        $toAccount->save();

        // تحديث transaction_reference في Order
        $order = $installment->contract->order;
        if (! $order) {
            throw new \RuntimeException('Order not found for this contract.');
        }
        $order->transaction_reference = ($order->transaction_reference ?? 0) + $amountPaid;
        $order->save();

        DB::commit();

        $msg = $installment->status === 'paid'
            ? 'تم دفع القسط كاملاً وتسجيل القيد والمعاملات بنجاح.'
            : 'تم دفع جزء من القسط ('.number_format($amountPaid,2).') وتسجيل القيد والمعاملات بنجاح.';
        return redirect()->back()->with('success', $msg);

    } catch (\Throwable $e) {
        DB::rollBack();
        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => 'فشل تسجيل الدفع: '.$e->getMessage()]);
    }
}



}
