<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Admin;
use Illuminate\Http\Request;
use App\CPU\Helpers;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function index(Request $request)
{
    $query = JournalEntry::with(['details.account', 'branch', 'seller'])
        ->when($request->from_date, fn($q) => $q->whereDate('entry_date', '>=', $request->from_date))
        ->when($request->to_date, fn($q) => $q->whereDate('entry_date', '<=', $request->to_date))
        ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
        ->when($request->account_id, function($q) use ($request) {
            $q->whereHas('details', fn($sub) => $sub->where('account_id', $request->account_id));
        })
        ->when($request->seller_id, fn($q) => $q->where('created_by', $request->seller_id))
        ->when($request->reference, fn($q) => $q->where('reference', 'like', "%{$request->reference}%"))
         ->when($request->id, fn($q) => $q->where('id',$request->id))
        ->when($request->description, function($q) use ($request) {
            $q->whereHas('details', fn($sub) => 
                $sub->where('description', 'like', "%{$request->description}%")
            );
        })
        ->orderBy('entry_date', 'desc');

    if ($request->show_all) {
        $entries = $query->get(); // بدون pagination
    } else {
        $entries = $query->paginate(Helpers::pagination_limit());
    }

    $pageDebit = $entries->sum(fn($e) => $e->details->sum('debit'));
    $pageCredit = $entries->sum(fn($e) => $e->details->sum('credit'));

    return view('admin-views.journal_entries.index', [
        'entries'     => $entries,
        'pageDebit'   => $pageDebit,
        'pageCredit'  => $pageCredit,
        'branches'    => Branch::all(),
        'accounts'    => Account::all(),
        'sellers'     => Admin::all(),
    ]);
}


    public function show($id)
    {
        $entry = JournalEntry::with([
            'branch:id,name',
            'seller:id,f_name,email',
            'details' => function($q){
                $q->with('account:id,account,code');
            }
        ])->findOrFail($id);

        return view('admin-views.journal_entries.show', compact('entry'));
    }

    public function edit($id)
    {
        $entry = JournalEntry::with('details.account:id,account,code')->findOrFail($id);
        $branches = Branch::select('id','name')->orderBy('name')->get();
        return view('admin-views.journal_entries.edit', compact('entry','branches'));
    }

    public function update(Request $request, $id)
    {
        $entry = JournalEntry::findOrFail($id);

        $validated = $request->validate([
            'entry_date'  => 'required|date',
            'reference'   => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'branch_id'   => 'nullable|exists:branches,id',
        ]);

        $entry->entry_date  = $validated['entry_date'];
        $entry->reference   = $validated['reference'] ?? $entry->reference;
        $entry->description = $validated['description'] ?? $entry->description;
        $entry->branch_id   = $validated['branch_id'] ?? $entry->branch_id;
        $entry->save();

        // ملاحظة: تعديل تفاصيل القيد (deb/cred) يحتاج شاشة منفصلة لضمان الاتزان.
        // لو عايز نعملها، قولّي وأظبط لك فورم ديناميكي مع تحقق الاتزان قبل الحفظ.

        return redirect()->route('admin.journal-entries.show', $entry->id)
            ->with('success','تم تحديث بيانات القيد.');
    }

    public function reverseJournalEntry(Request $request, $id = null)
    {
        // خُد الـ id من أي مصدر متاح (URL أو BODY) عشان نتفادى مشاكل الفاليديشن
        $entryId = $request->input('journal_entry_id')
            ?? $request->input('id')
            ?? $request->input('entry_id')
            ?? $id;

        // فاليديشن واضح وصريح
        $request->merge(['journal_entry_id' => $entryId]);
        $request->validate([
            'journal_entry_id' => 'required|exists:journal_entries,id',
            'hard_recalc'      => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            /** @var \App\Models\JournalEntry $original */
            $original = \App\Models\JournalEntry::with([
                'details',
                'details.account',
                'branch',
                'seller',
            ])->lockForUpdate()->findOrFail($entryId);

            // لو هو أصلاً قيد عكسي، أو سبق واتعكس
            if ($original->reversal == 0 || $original->reversal_of_id || (int) $original->reversal === 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا القيد تم عكسه من قبل أو هو قيد عكسي بالفعل.',
                ], 409);
            }

            // 1) علّم الأصلي إنه "تم عكسه"
            $original->reversal = 2; // 0=عادي، 1=قابل للعكس/عكسي، 2=تم عكسه
            $original->save();

            // 2) أنشئ القيد العكسي
            $reverse                    = new \App\Models\JournalEntry();
            $reverse->type              = $original->type; // لو عايز "reversal" بدّلها
            $reverse->branch_id         = $original->branch_id;
            $reverse->created_by        = auth('admin')->id();
            $reverse->entry_date        = now();
            $reverse->head_date         = $reverse->entry_date;
            $reverse->reference         = 'REV-'.($original->reference ?? $original->head_ref ?? $original->id);
            $reverse->description       = 'عكس القيد #'.$original->id.' — '.$original->description;
            $reverse->reversal_of_id    = $original->id;
            $reverse->reversal          = 1; // ده قيد عكسي
            $reverse->save();

            // 3) فك ارتباط سندات/أصول مرتبطة بالقيد الأصلي (لو فيه)
            if (in_array($original->type, ['payment', 'receipt'])) {
                \App\Models\PaymentVoucher::where('journal_entry_id', $original->id)
                    ->update(['journal_entry_id' => null, 'reversed_at' => now()]);
            }
            if ($original->type === 'asset_solid') {
                \App\Models\AssetSold::where('journal_entry_id', $original->id)
                    ->update(['journal_entry_id' => null, 'reversed_at' => now()]);
            }

            // 4) إنشاء تفاصيل عكسية + معاملات عكسية
            $touchedAccountIds  = [];
            $touchedSupplierIds = [];
            $touchedCustomerIds = [];

            foreach ($original->details as $detail) {
                $revDetail                           = new \App\Models\JournalEntryDetail();
                $revDetail->journal_entry_id         = $reverse->id;
                $revDetail->account_id               = $detail->account_id;
                $revDetail->cost_center_id           = $detail->cost_center_id;
                $revDetail->description              = 'عكس: '.$detail->description;
                $revDetail->debit                    = $detail->credit;
                $revDetail->credit                   = $detail->debit;
                $revDetail->reversal_of_detail_id    = $detail->id;
                // timestamps تشتغل أوتوماتيك؛ سيب created_at/updated_at للـ ORM
                $revDetail->save();

                // اعكس كل الترانزاكشنز المرتبطة بالسطر
                $txs = \App\Models\Transection::where('journal_entry_detail_id', $detail->id)->get();

                foreach ($txs as $tx) {
                    $revTx                           = new \App\Models\Transection();
                    $revTx->journal_entry_detail_id  = $revDetail->id;

                    // اقلب الحسابين
                    $revTx->account_id               = $tx->account_id_to;
                    $revTx->account_id_to            = $tx->account_id;

                    // نفس البيانات
                    $revTx->tran_type                = $tx->tran_type;
                    $revTx->seller_id                = auth('admin')->id();
                    $revTx->branch_id                = auth('admin')->user()->branch_id ?? $tx->branch_id;
                    $revTx->amount                   = $tx->amount;

                    // اقلب المدين/الدائن
                    $revTx->debit                    = $tx->credit;
                    $revTx->credit                   = $tx->debit;
                    $revTx->debit_account            = $tx->credit_account;
                    $revTx->credit_account           = $tx->debit_account;

                    // وصف/ضرائب/تاريخ
                    $revTx->description              = 'عكس: '.$tx->description;
                    $revTx->tax                      = $tx->tax;
                    $revTx->tax_id                   = $tx->tax_id;
                    $revTx->tax_number               = $tx->tax_number;
                    $revTx->name                     = $tx->name;
                    $revTx->date                     = now();
                    $revTx->is_reversal              = 1;

                    $revTx->save();

                    // === تحديث أرصدة الحسابات فورًا (سريع وخفيف) ===
                    // الحساب المدين في العكس = account_id
                    $this->bumpAccountTotals($revTx->account_id,    +$revTx->amount, 'in');  // total_in/balance +
                    // الحساب الدائن في العكس = account_id_to
                    $this->bumpAccountTotals($revTx->account_id_to, +$revTx->amount, 'out'); // total_out/balance -

                    $touchedAccountIds[$revTx->account_id]    = true;
                    $touchedAccountIds[$revTx->account_id_to] = true;

                    // عكس أثر الأطراف (مورد/عميل) إن وُجدوا
                    $this->bumpPartyByAccounts($tx, $revTx, $touchedSupplierIds, $touchedCustomerIds);
                }
            }

            // 5) تعديل أثر القيد على أوامر العملاء/الموردين (لو عندك منطق مرتبط)
            $this->reverseOrdersForCustomersIfAny($original);
            $this->reverseOrdersForSuppliersIfAny($original);

            // 6) (اختياري) إعادة احتساب شاملة
            if ($request->boolean('hard_recalc')) {
                $this->hardRecalcAccounts(array_keys($touchedAccountIds));
                $this->hardRecalcSuppliers(array_keys($touchedSupplierIds));
                $this->hardRecalcCustomers(array_keys($touchedCustomerIds));
            }

            DB::commit();

            return response()->json([
                'success'           => true,
                'message'           => 'تم عكس القيد وتحديث الأرصدة بالكامل بنجاح.',
                'reversal_entry_id' => $reverse->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success'=>false,
                'message'=>$e->getMessage(),
            ], 500);
        }
    }

    /**
     * حدّث أرصدة الحساب طبقًا لمنطقك:
     * type = 'in'  => total_in += amount;  balance += amount
     * type = 'out' => total_out += amount; balance -= amount
     */
    private function bumpAccountTotals($accountId, $amount, $type)
    {
        if (!$accountId || $amount <= 0) return;

        /** @var \App\Models\Account|null $acc */
        $acc = \App\Models\Account::lockForUpdate()->find($accountId);
        if (!$acc) return;

        if ($type === 'in') {
            $acc->total_in = ($acc->total_in ?? 0) + $amount;
            $acc->balance  = ($acc->balance  ?? 0) + $amount;
        } else {
            $acc->total_out = ($acc->total_out ?? 0) + $amount;
            $acc->balance   = ($acc->balance   ?? 0) - $amount;
        }
        $acc->save();
    }

    /**
     * عكس أثر الأصلي على المورد/العميل.
     */
    private function bumpPartyByAccounts($origTx, $revTx, &$touchedSupplierIds, &$touchedCustomerIds)
    {
        // موردين محتملين للحسابين
        $supplier_to   = \App\Models\Supplier::where('account_id', $origTx->account_id_to)->first();
        $supplier_from = \App\Models\Supplier::where('account_id', $origTx->account_id)->first();

        // عملاء محتملين للحسابين
        $customer_to   = \App\Models\Customer::where('account_id', $origTx->account_id_to)->first();
        $customer_from = \App\Models\Customer::where('account_id', $origTx->account_id)->first();

        $amount = (float) $origTx->amount;

        // الموردين
        if ($supplier_to) {
            // الأصل: كان بيقلّل مستحق المورد (دفع) → العكس يعيده
            $supplier_to->due_amount = ($supplier_to->due_amount ?? 0) + $amount;
            $supplier_to->save();
            $touchedSupplierIds[$supplier_to->id] = true;
        }
        if ($supplier_from) {
            // الأصل: من حساب مورد → العكس يزيد عليه التزام/credit حسب منطقك
            $supplier_from->credit = ($supplier_from->credit ?? 0) + $amount;
            $supplier_from->save();
            $touchedSupplierIds[$supplier_from->id] = true;
        }

        // العملاء
        if ($customer_to) {
            // الأصل Receipt (قبض من العميل) يقلل ذمته → العكس يزيدها
            $customer_to->credit = ($customer_to->credit ?? 0) + $amount;
            $customer_to->save();
            $touchedCustomerIds[$customer_to->id] = true;
        }
        if ($customer_from) {
            // الأصل: من حساب عميل → العكس يعيد له رصيد/ذمة
            $customer_from->balance = ($customer_from->balance ?? 0) + $amount;
            $customer_from->save();
            $touchedCustomerIds[$customer_from->id] = true;
        }
    }

    /** تعديل أوامر العملاء (مثال تقريبي؛ عدّل للأقرب لمنطق مشروعك) */
    private function reverseOrdersForCustomersIfAny($original)
    {
        // ⚠️ هنا كان الخطأ: ما ينفعش where(...) بـ Collection → لازم whereIn(...)
        $accountIds = $original->details->pluck('account_id')->filter()->unique()->values()->all();

        $cust = \App\Models\Customer::whereIn('account_id', $accountIds)->first();
        if (!$cust) return;

        $orders = \App\Models\Order::where('type', 4) // مبيعات
            ->where('user_id', $cust->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $reversePayment = $original->details->sum(function ($d) {
            return max((float)$d->debit, (float)$d->credit);
        });

        foreach ($orders as $order) {
            if ($order->transaction_reference > 0) {
                $deduct = min($order->transaction_reference, $reversePayment);
                $order->transaction_reference -= $deduct;
                $reversePayment                -= $deduct;
                $order->save();
                if ($reversePayment <= 0) break;
            }
        }
    }

    /** تعديل أوامر الموردين */
    private function reverseOrdersForSuppliersIfAny($original)
    {
        $accountIds = $original->details->pluck('account_id')->filter()->unique()->values()->all();

        $supp = \App\Models\Supplier::whereIn('account_id', $accountIds)->first();
        if (!$supp) return;

        $orders = \App\Models\Order::where('type', 12) // مشتريات
            ->where('supplier_id', $supp->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $reversePayment = $original->details->sum(function ($d) {
            return max((float)$d->debit, (float)$d->credit);
        });

        foreach ($orders as $order) {
            if ($order->transaction_reference > 0) {
                $deduct = min($order->transaction_reference, $reversePayment);
                $order->transaction_reference -= $deduct;
                $reversePayment                -= $deduct;
                $order->save();
                if ($reversePayment <= 0) break;
            }
        }
    }

    /**
     * (اختياري) إعادة احتساب شاملة من الترانزاكشنز.
     */
    private function hardRecalcAccounts(array $accountIds)
    {
        if (empty($accountIds)) return;

        foreach ($accountIds as $id) {
            $in  = (float) \App\Models\Transection::where('account_id',    $id)->sum('amount');
            $out = (float) \App\Models\Transection::where('account_id_to', $id)->sum('amount');

            $acc = \App\Models\Account::find($id);
            if ($acc) {
                $acc->total_in  = $in;
                $acc->total_out = $out;
                $acc->balance   = $in - $out;
                $acc->save();
            }
        }
    }

    private function hardRecalcSuppliers(array $supplierIds)
    {
        // إن احتجت، اجمع منطقك من جدول المعاملات أو سندات الموردين
    }

    private function hardRecalcCustomers(array $customerIds)
    {
        // إن احتجت، اجمع منطقك من جدول المعاملات أو سندات العملاء
    }

}
