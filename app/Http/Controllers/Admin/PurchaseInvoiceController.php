<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Branch;
use App\Models\Account;
use App\Models\Transection;
use App\Models\StockBatch;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductLog;
use Toastr;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use App\CPU\Helpers;
use function App\CPU\translate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentVoucher;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;
use Illuminate\Support\Facades\Schema;

class PurchaseInvoiceController extends Controller
{
    /**
     * عرض صفحة إنشاء فاتورة مشتريات.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        // جلب جميع الموردين والمنتجات من قاعدة البيانات
        $suppliers = Supplier::all();
        $products  = Product::where('product_type','product')->get();
        
        // التقاط تواريخ الفلترة (إن وُجدت)
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        // تمرير البيانات إلى الصفحة، ويمكنك أيضاً تمرير بيانات الفاتورة المحفوظة في session إن وُجدت
        $cachedInvoice = session()->get('purchase_invoice');
        $cart = session()->get('cart', []);
          $cost_centers = \App\Models\CostCenter::where('active',1)->doesntHave('children')->get();
$accounts = \App\Models\Account::where(function($query) {
    // نختار الحسابات الأصلية أو اللي رقمها 8 أو 14 أو اللي parent_id تبعهم أحد هذين الحسابين
    $query->whereIn('id', [8,14])
          ->orWhereIn('parent_id', [8,14]);
})->doesntHave('children') // نتأكد أنه ليس له أولاد
  ->orderBy('id')
  ->get();
        return view('admin-views.purchase_invoices.create', compact('suppliers', 'products', 'startDate', 'endDate', 'cachedInvoice', 'cart','accounts','cost_centers'));
    }
    
    /**
     * إضافة منتج إلى الفاتورة (حفظ بيانات المنتج في الـ session كعربة مشتريات).
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
public function addToCart(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity'   => 'required|numeric|min:1',
        'price'      => 'required|numeric|min:0',
        'unit'       => 'required|string',
        'tax'        => 'required|numeric|min:0',
    ]);
    
    $cart = session()->get('cart', []);
    $product_id = $request->input('product_id');
    $quantity   = $request->input('quantity');
    $price      = $request->input('price');
    $unit       = $request->input('unit');
    $tax        = $request->input('tax');
    $price_incl_tax = $price + $tax;
    
    $cart[$product_id] = [
        'product_id'     => $product_id,
        'quantity'       => $quantity,
        'price'          => $price,
        'unit'           => $unit,
        'tax'            => $tax,
        'price_incl_tax' => $price_incl_tax,
    ];
    
    session()->put('cart', $cart);
    return redirect()->route('admin.purchase_invoice.create')->with('success', 'تم إضافة المنتج إلى الفاتورة');
}

public function update(Request $request)
{
    $validated = $request->validate([
        'invoice.supplier_id' => 'required',
        'invoice.summary.subtotal' => 'required|numeric',
        'invoice.summary.totalTax' => 'required|numeric',
        'invoice.summary.grandTotal' => 'required|numeric',
    ]);
    
    $invoiceData = $validated['invoice'];
    session()->put('purchase_invoice', $invoiceData);
    
    return response()->json([
        'status' => 'success',
        'message' => 'تم تحديث الفاتورة وحفظها في الجلسة.',
    ]);
}

public function refreshInvoice()
{
    $invoice = session()->get('purchase_invoice', []);
    $cart = session()->get('cart', []);
    
    $subtotal = 0;
    $totalTax = 0;
    $grandTotal = 0;
    
    foreach ($cart as $item) {
        $quantity = $item['quantity'];
        $price = $item['price'];
        $tax = $item['tax'];
        $subtotal += $quantity * $price;
        $totalTax += $quantity * $tax;
        $grandTotal += $quantity * ($price + $tax);
    }
    
    $invoice['summary'] = [
        'subtotal' => number_format($subtotal, 2, '.', ''),
        'totalTax' => number_format($totalTax, 2, '.', ''),
        'grandTotal' => number_format($grandTotal, 2, '.', '')
    ];
    
    return response()->json([
        'invoice' => $invoice,
        'cart' => $cart,
    ]);
}

public function cancelInvoice(Request $request)
{
    session()->forget('cart');
    session()->forget('purchase_invoice');
    return response()->json([
        'status' => 'success',
        'message' => 'تم إلغاء الفاتورة ومسح بياناتها من الجلسة.'
    ]);
}
public function execute(Request $request)
{
    // ===== 1) Validation =====
    $request->validate([
        'supplier_id'           => 'required|exists:suppliers,id',
        'products'              => 'required|array|min:1',
        'products.*.id'         => 'required|exists:products,id',
        'products.*.quantity'   => 'required|numeric|min:1',
        'products.*.price'      => 'required|numeric|min:0',
        'products.*.unit'       => 'required|in:0,1',
        'products.*.tax'        => 'required|numeric|min:0',
        'cash'                  => 'required|in:1,2', // 1: كاش، 2: أجل
        'order_amount'          => 'required|numeric|min:0',
        'date'                  => 'required|date',

        // اختياري: دفعة مقدمة للأجل
        'advance_payment.amount'         => 'nullable|numeric|min:0',
        'advance_payment.account_id'     => 'nullable|exists:accounts,id',
        'advance_payment.cost_center_id' => 'nullable|exists:cost_centers,id',

        // اختياري: مصاريف إضافية الآن
        'other_expenses'                                 => 'nullable|numeric|min:0',
        'other_expenses_payment.pay_now'                 => 'nullable|in:0,1',
        'other_expenses_payment.amount'                  => 'nullable|numeric|min:0',
        'other_expenses_payment.to_supplier'             => 'nullable|in:0,1',
        'other_expenses_payment.creditor_account_id'     => 'nullable|exists:accounts,id', // حساب المصروف (المستفيد)
        'other_expenses_payment.pay_from_account_id'     => 'nullable|exists:accounts,id',
        'other_expenses_payment.creditor_cost_center_id' => 'nullable|exists:cost_centers,id',
        'other_expenses_payment.pay_from_cost_center_id' => 'nullable|exists:cost_centers,id',
        'other_expenses_payment.description'             => 'nullable|string|max:500',
        // للكاش
        'payment_info.account_id'        => 'nullable|exists:accounts,id',
        'payment_info.payment_amount'    => 'nullable|numeric|min:0',
        'payment_info.cost_center_id'    => 'nullable|exists:cost_centers,id',
    ]);

    DB::beginTransaction();
    try {
        $admin       = auth('admin')->user();
        $branch      = Branch::findOrFail($admin->branch_id);
        $supplier    = Supplier::findOrFail($request->supplier_id);
        $type        = 12; // مشتريات
        $note        = $request->note ?: null;

        // ===== 2) رقم الطلب =====
        $order_id = 100000 + Order::count() + 1;
        if (Order::find($order_id)) {
            $order_id = Order::orderBy('id', 'DESC')->value('id') + 1;
        }

        // ===== 3) صورة الفاتورة (img) =====
        $img = null;
        if ($request->hasFile('img')) {
            $img = $request->file('img')->store('shop', 'public');
        }

        // ===== 4) QR =====
        $qrcode_data  = url('real/invoicea2/' . $order_id);
        $qrcode       = new QrCode($qrcode_data);
        $writer       = new PngWriter();
        $qrcode_image = $writer->write($qrcode)->getString();
        $qrcode_path  = "qrcodes/order_$order_id.png";
        Storage::disk('public')->put($qrcode_path, $qrcode_image);

        // ===== 5) تفاصيل المنتجات + المخزون =====
        $product_price = 0;  // إجمالي أسعار الوحدات قبل الضريبة والخصم
        $product_tax   = 0;
        $product_disc  = 0;

        $order_details = [];
        $productlogs   = [];

        foreach ($request->products as $row) {
            /** @var Product $product */
            $product = Product::find($row['id']);
            if (!$product) continue;

            $price = (float) $row['price'];

            if ($row['unit'] == 1) { // كبري
                $adjustedPrice    = $price;
                $adjustedTax      = (float) $row['tax'];
                $adjustedDiscount = (float) $row['discount'];
                $quantityfinal    = (float) $row['quantity'];
            } else { // صغري
                $adjustedPrice    = $price * $product->unit_value;
                $adjustedTax      = ((float) $row['tax']) * $product->unit_value;
                $adjustedDiscount = ((float) $row['discount']) * $product->unit_value;
                $quantityfinal    = ((float) $row['quantity']) / $product->unit_value;
            }

            $product_price += $price * (float) $row['quantity'];
            $product_tax   += ((float) $row['tax']) * (float) $row['quantity'];
            $product_disc  += ((float) $row['discount']) * (float) $row['quantity'];

            $countBatches   = StockBatch::where('product_id', $row['id'])->count();
            $newProductCode = $product->product_code . '@' . ($countBatches + 1);

            $order_details[] = [
                'order_id'            => $order_id,
                'product_id'          => $product->id,
                'product_details'     => json_encode($product),
                'quantity'            => (float) $row['quantity'],
                'unit'                => (int) $row['unit'],
                'price'               => $price,
                'product_code'        => $newProductCode,
                'tax_amount'          => (float) $row['tax'],
                'discount_on_product' => 0,
                'discount_type'       => 'discount_on_product',
                'created_at'          => now(),
                'updated_at'          => now(),
            ];

            $productlogs[] = [
                'product_id' => $product->id,
                'quantity'   => $quantityfinal,
                'type'       => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // تحديث مخزون المنتج
            $branchColumn = 'branch_' . $admin->branch_id;
            if (isset($product->$branchColumn)) {
                $product->$branchColumn += $quantityfinal;
            } else {
                $product->quantity += $quantityfinal;
            }
            $product->purchase_count++;
            $product->purchase_price = $adjustedPrice + $adjustedTax - $adjustedDiscount;
            $product->save();

            // Batch
            $batch               = new StockBatch();
            $batch->product_id   = $product->id;
            $batch->quantity     = $quantityfinal;
            $batch->branch_id    = $admin->branch_id;
            $batch->price        = $adjustedPrice + $adjustedTax - $adjustedDiscount;
            $batch->product_code = $newProductCode;
            $batch->save();
        }

        $base_total     = $product_price + $product_tax - $product_disc; // بدون مصاريف إضافية
        $other_total    = (float) ($request->other_expenses ?? 0);
        $order_amountRq = (float) $request->order_amount; // من الواجهة (غالبًا base_total + other_total)

        // لسلامة البيانات، نخلي المبلغ النهائي اللي هيتسجل في الطلب = ما أرسلته الواجهة
        $final_order_amount = $order_amountRq;

        // ===== 6) إنشاء Order =====
        $order                         = new Order();
        $order->id                     = $order_id;
        $order->supplier_id            = $supplier->id;
        $order->type                   = $type;
        $order->total_tax              = $product_tax;
        $order->order_amount           = $final_order_amount; // النهائي (يشمل المصاريف حسب الواجهة)
        $order->extra_discount         = $product_disc;
        $order->coupon_discount_amount = (float) ($request->coupon_discount ?? 0);
        $order->cash                   = (int) $request->cash; // 1 كاش / 2 أجل
        $order->date                   = $request->date;
        $order->qrcode                 = $qrcode_path;
        $order->owner_id               = $admin->id;
        $order->branch_id              = $admin->branch_id;
        $order->img                    = $img;
        $order->note                   = $note; // ✅
        // حقول مرجعية للكاش من الواجهة (لو موجودة)
        $order->collected_cash         = $request->input('payment_info.payment_amount');
        $order->transaction_reference  = $request->input('payment_info.payment_amount');
        
        // ===== ZATCA Compliance Fields =====
        if (empty($order->uuid)) {
            $order->uuid = \App\Services\ZATCAService::generateUUID();
        }
        if (empty($order->currency_code)) {
            $order->currency_code = 'SAR';
        }
        if (empty($order->invoice_counter)) {
            $order->invoice_counter = \App\Services\ZATCAService::getNextInvoiceCounter($order->company_id);
        }
        $order->invoice_number = \App\Services\ZATCAService::generateInvoiceNumber($order->invoice_counter);
        $order->previous_invoice_hash = \App\Services\ZATCAService::getPreviousInvoiceHash($order->company_id);
        
        // Generate ZATCA QR code data
        $businessSettings = \App\Models\BusinessSetting::whereIn('key', ['shop_name', 'number_tax'])->pluck('value', 'key');
        $zatcaQrData = [
            'seller_name' => $businessSettings['shop_name'] ?? '',
            'vat_registration_number' => $businessSettings['number_tax'] ?? '',
            'invoice_date' => $order->date ?: $order->created_at?->format('Y-m-d H:i:s'),
            'invoice_total' => $final_order_amount,
            'vat_total' => $product_tax,
        ];
        $order->zatca_qr_code = \App\Services\ZATCAService::generateZATCAQRCode($zatcaQrData);
        
        $order->save();
        event(new \App\Events\InvoiceFinalized($order));

        OrderDetail::insert($order_details);
        ProductLog::insert($productlogs);

        // ===== 7) حسابات القيود =====
        $inventoryAccount = Account::find($branch->account_stock_Id); // مدين في قيد الشراء
        $supplierAccount  = Account::find($supplier->account_id);
        $cashAccount      = $request->cash == 1 ? Account::find($request->input('payment_info.account_id')) : null;

        // هل سيتم دفع مصاريف إضافية الآن؟
        $payOtherNow = (int) ($request->input('other_expenses_payment.pay_now') ?? 0) === 1;
        $otherToSupp = (int) ($request->input('other_expenses_payment.to_supplier') ?? 1) === 1;
        $otherNowAmt = (float) ($request->input('other_expenses_payment.amount') ?? 0);

        // مبلغ قيد الشراء = 
        // - دائمًا قيمة السلع (base_total)
        // - + المصاريف الإضافية إن لم تُدفع الآن إلى حساب آخر (أما إذا ستُدفع الآن للمورد، فهتبقى بعملية دفع منفصلة ولا نضيفها على القيد)
        $purchaseEntryAmount = $base_total;
        if (!$payOtherNow) {
            $purchaseEntryAmount += $other_total; // تُعالج كجزء من التزام المورد
        }

        // ===== 8) قيد الشراء (رأس + تفصيلين) =====
        if ($purchaseEntryAmount > 0) {
            $entry = new JournalEntry();
            $entry->entry_date  = $request->date;
            $entry->reference   = 'PO-' . $order_id;
            $entry->type        = ($request->cash == 1) ? 'purchase_cash' : 'purchase_credit';
            $entry->description = $note ?: ('Purchase Invoice #' . $order_id);
            $entry->created_by  = Auth::guard('admin')->id();
            $entry->branch_id   = $admin->branch_id;
            $entry->save();

            // مدين: مخزون
            $detailDebit = new JournalEntryDetail();
            $detailDebit->journal_entry_id = $entry->id;
            $detailDebit->account_id       = optional($inventoryAccount)->id;
            $detailDebit->debit            = $purchaseEntryAmount;
            $detailDebit->credit           = 0;
            $detailDebit->description      = 'قيد مشتريات';
            $detailDebit->attachment_path  = $img;
            $detailDebit->entry_date       = $request->date;
            $detailDebit->save();

            // دائن: مورد (أجل) أو نقدية/بنك (كاش)
            $creditAccId = ($request->cash == 2) ? optional($supplierAccount)->id : optional($cashAccount)->id;

            $detailCredit = new JournalEntryDetail();
            $detailCredit->journal_entry_id = $entry->id;
            $detailCredit->account_id       = $creditAccId;
            $detailCredit->debit            = 0;
            $detailCredit->credit           = $purchaseEntryAmount;
            $detailCredit->description      = 'قيد مشتريات';
            $detailCredit->attachment_path  = $img;
            $detailCredit->entry_date       = $request->date;
            $detailCredit->save();

            // ترانساكشن مزدوج
            // سطر مدين
            $tDeb = new Transection();
            $tDeb->tran_type               = $type;
            $tDeb->seller_id               = $admin->id;
            $tDeb->account_id              = optional($inventoryAccount)->id;
            $tDeb->account_id_to           = $creditAccId;
            $tDeb->debit                   = $purchaseEntryAmount;
            $tDeb->credit                  = 0;
            $tDeb->amount                  = $purchaseEntryAmount;
            $tDeb->tax                     = $product_tax;
            $tDeb->description             = 'قيد فاتورة مشتريات';
            $tDeb->date                    = $request->date;
            $tDeb->balance                 = $inventoryAccount ? ($inventoryAccount->balance + $purchaseEntryAmount) : null;
            $tDeb->branch_id               = $admin->branch_id;
            $tDeb->journal_entry_detail_id = $detailDebit->id;
            $tDeb->order_id                = $order_id;
            $tDeb->save();

            // سطر دائن
            $newCredBalance = null;
            if ($request->cash == 2 && $supplierAccount) {
                $newCredBalance = $supplierAccount->balance + $purchaseEntryAmount; // التزام يزيد
                $supplierAccount->balance  += $purchaseEntryAmount;
                $supplierAccount->total_in += $purchaseEntryAmount;
                $supplierAccount->save();
            } elseif ($request->cash == 1 && $cashAccount) {
                $newCredBalance = $cashAccount->balance - $purchaseEntryAmount; // نقدية تقل
                $cashAccount->balance   -= $purchaseEntryAmount;
                $cashAccount->total_out += $purchaseEntryAmount;
                $cashAccount->save();
            }

            $tCred = new Transection();
            $tCred->tran_type               = $type;
            $tCred->seller_id               = $admin->id;
            $tCred->account_id              = $creditAccId;
            $tCred->account_id_to           = optional($inventoryAccount)->id;
            $tCred->debit                   = 0;
            $tCred->credit                  = $purchaseEntryAmount;
            $tCred->amount                  = $purchaseEntryAmount;
            $tCred->tax                     = $product_tax;
            $tCred->description             = 'قيد فاتورة مشتريات';
            $tCred->date                    = $request->date;
            $tCred->balance                 = $newCredBalance;
            $tCred->branch_id               = $admin->branch_id;
            $tCred->journal_entry_detail_id = $detailCredit->id;
            $tCred->order_id                = $order_id;
            $tCred->save();

            // تحديث رصيد حساب المخزون
            if ($inventoryAccount) {
                $inventoryAccount->balance  += $purchaseEntryAmount;
                $inventoryAccount->total_in += $purchaseEntryAmount;
                $inventoryAccount->save();
            }
        }

        // ===== 9) دفعة مقدمة للأجل (اختياري) =====
        if ((int)$request->cash === 2) {
            $advAmount = (float) ($request->input('advance_payment.amount') ?? 0);
            $advAcc    = $request->input('advance_payment.account_id'); // حساب الدفع
            if ($advAmount > 0 && $advAcc) {
                $payAcc  = Account::findOrFail($advAcc);
                $needCC  = (int) ($payAcc->cost_center ?? 0) === 1;
                $advCCId = $request->input('advance_payment.cost_center_id');
                if ($needCC && !$advCCId) {
                    throw ValidationException::withMessages(['advance_payment.cost_center_id' => translate('cost_center_required_for_selected_account')]);
                }

                // قيد دفع: مدين المورد، دائن حساب الدفع
                $entry = new JournalEntry();
                $entry->entry_date  = $request->date;
                $entry->reference   = 'ADV-' . $order_id;
                $entry->type        = 'supplier_payment';
                $entry->description = 'دفعة مقدمة للمورد على فاتورة #' . $order_id;
                $entry->created_by  = $admin->id;
                $entry->branch_id   = $admin->branch_id;
                $entry->save();

                $d = new JournalEntryDetail();
                $d->journal_entry_id = $entry->id;
                $d->account_id       = optional($supplierAccount)->id;
                $d->debit            = $advAmount;
                $d->credit           = 0;
                $d->cost_center_id   = null;
                $d->description      = 'دفعة مقدمة';
                $d->entry_date       = $request->date;
                $d->save();

                $c = new JournalEntryDetail();
                $c->journal_entry_id = $entry->id;
                $c->account_id       = $payAcc->id;
                $c->debit            = 0;
                $c->credit           = $advAmount;
                $c->cost_center_id   = $advCCId;
                $c->description      = 'دفعة مقدمة';
                $c->entry_date       = $request->date;
                $c->save();

                // ترانساكشن مزدوج
                $tDeb = new Transection();
                $tDeb->tran_type               = $type;
                $tDeb->seller_id               = $admin->id;
                $tDeb->account_id              = optional($supplierAccount)->id;
                $tDeb->account_id_to           = $payAcc->id;
                $tDeb->debit                   = $advAmount;
                $tDeb->credit                  = 0;
                $tDeb->amount                  = $advAmount;
                $tDeb->description             = 'دفعة مقدمة';
                $tDeb->date                    = $request->date;
                $tDeb->balance                 = $supplierAccount ? ($supplierAccount->balance - $advAmount) : null;
                $tDeb->branch_id               = $admin->branch_id;
                $tDeb->journal_entry_detail_id = $d->id;
                $tDeb->order_id                = $order_id;
                $tDeb->save();

                $tCred = new Transection();
                $tCred->tran_type               = $type;
                $tCred->seller_id               = $admin->id;
                $tCred->account_id              = $payAcc->id;
                $tCred->account_id_to           = optional($supplierAccount)->id;
                $tCred->debit                   = 0;
                $tCred->credit                  = $advAmount;
                $tCred->amount                  = $advAmount;
                $tCred->description             = 'دفعة مقدمة';
                $tCred->date                    = $request->date;
                $tCred->balance                 = $payAcc->balance - $advAmount;
                $tCred->branch_id               = $admin->branch_id;
                $tCred->journal_entry_detail_id = $c->id;
                $tCred->order_id                = $order_id;
                $tCred->save();

                // تحديث الأرصدة
                if ($supplierAccount) {
                    $supplierAccount->balance  -= $advAmount; // التزام يقل
                    $supplierAccount->total_out += $advAmount;
                    $supplierAccount->save();
                }
                $payAcc->balance   -= $advAmount; // نقدية/بنك تقل
                $payAcc->total_out += $advAmount;
                $payAcc->save();
            }
        }

        // ===== 10) مصاريف إضافية الآن =====
        if ($payOtherNow && $otherNowAmt > 0) {
            // إما للمورد أو سند صرف لحساب مصروف (Expense Leaf)
            if ($otherToSupp) {
                // قيد دفع للمورد (مدين المورد / دائن حساب الدفع)
                $payAcc = Account::findOrFail($request->input('other_expenses_payment.pay_from_account_id'));
                $need   = (int) ($payAcc->cost_center ?? 0) === 1;
                $ccId   = $request->input('other_expenses_payment.pay_from_cost_center_id');
                if ($need && !$ccId) {
                    throw ValidationException::withMessages(['other_expenses_payment.pay_from_cost_center_id' => translate('cost_center_required_for_selected_account')]);
                }

                $desc = $request->input('other_expenses_payment.description') ?: 'سداد مصاريف إضافية للمورد';

                $entry = new JournalEntry();
                $entry->entry_date  = $request->date;
                $entry->reference   = 'OTH-SUP-' . $order_id;
                $entry->type        = 'supplier_payment';
                $entry->description = $desc;
                $entry->created_by  = $admin->id;
                $entry->branch_id   = $admin->branch_id;
                $entry->save();

                $d = new JournalEntryDetail();
                $d->journal_entry_id = $entry->id;
                $d->account_id       = optional($supplierAccount)->id;
                $d->debit            = $otherNowAmt;
                $d->credit           = 0;
                $d->description      = $desc;
                $d->entry_date       = $request->date;
                $d->save();

                $c = new JournalEntryDetail();
                $c->journal_entry_id = $entry->id;
                $c->account_id       = $payAcc->id;
                $c->debit            = 0;
                $c->credit           = $otherNowAmt;
                $c->cost_center_id   = $ccId;
                $c->description      = $desc;
                $c->entry_date       = $request->date;
                $c->save();

                // ترانساكشن مزدوج
                $tDeb = new Transection();
                $tDeb->tran_type               = $type;
                $tDeb->seller_id               = $admin->id;
                $tDeb->account_id              = optional($supplierAccount)->id;
                $tDeb->account_id_to           = $payAcc->id;
                $tDeb->debit                   = $otherNowAmt;
                $tDeb->credit                  = 0;
                $tDeb->amount                  = $otherNowAmt;
                $tDeb->description             = $desc;
                $tDeb->date                    = $request->date;
                $tDeb->balance                 = $supplierAccount ? ($supplierAccount->balance - $otherNowAmt) : null;
                $tDeb->branch_id               = $admin->branch_id;
                $tDeb->journal_entry_detail_id = $d->id;
                $tDeb->order_id                = $order_id;
                $tDeb->save();

                $tCred = new Transection();
                $tCred->tran_type               = $type;
                $tCred->seller_id               = $admin->id;
                $tCred->account_id              = $payAcc->id;
                $tCred->account_id_to           = optional($supplierAccount)->id;
                $tCred->debit                   = 0;
                $tCred->credit                  = $otherNowAmt;
                $tCred->amount                  = $otherNowAmt;
                $tCred->description             = $desc;
                $tCred->date                    = $request->date;
                $tCred->balance                 = $payAcc->balance - $otherNowAmt;
                $tCred->branch_id               = $admin->branch_id;
                $tCred->journal_entry_detail_id = $c->id;
                $tCred->order_id                = $order_id;
                $tCred->save();

                // تحديث أرصدة
                if ($supplierAccount) {
                    $supplierAccount->balance   -= $otherNowAmt;
                    $supplierAccount->total_out += $otherNowAmt;
                    $supplierAccount->save();
                }
                $payAcc->balance   -= $otherNowAmt;
                $payAcc->total_out += $otherNowAmt;
                $payAcc->save();

            } else {
                // ===== سند صرف لمصاريف (Expense Leaf) =====
                $creditor = Account::findOrFail($request->input('other_expenses_payment.creditor_account_id')); // حساب المصروف (المستفيد)
                $payAcc   = Account::findOrFail($request->input('other_expenses_payment.pay_from_account_id'));

                // التحقق: حساب المصروف يجب أن يكون expense وبلا أبناء
                $isExpense = strtolower($creditor->account_type ?? '') === 'expense';
                $hasChild  = Account::where('parent_id', $creditor->id)->exists();
                if (!$isExpense || $hasChild) {
                    throw ValidationException::withMessages(['other_expenses_payment.creditor_account_id' => translate('creditor_must_be_leaf_expense_account')]);
                }

                // تحقق مراكز التكلفة
                $needCredCC = (int) ($creditor->cost_center ?? 0) === 1;
                $needPayCC  = (int) ($payAcc->cost_center ?? 0) === 1;
                $credCCId   = $request->input('other_expenses_payment.creditor_cost_center_id');
                $payCCId    = $request->input('other_expenses_payment.pay_from_cost_center_id');
                if ($needCredCC && !$credCCId) {
                    throw ValidationException::withMessages(['other_expenses_payment.creditor_cost_center_id' => translate('cost_center_required_for_selected_account')]);
                }
                if ($needPayCC && !$payCCId) {
                    throw ValidationException::withMessages(['other_expenses_payment.pay_from_cost_center_id' => translate('cost_center_required_for_selected_account')]);
                }

                $desc = $request->input('other_expenses_payment.description') ?: 'سند صرف مصاريف إضافية';

                // (1) إنشاء سند صرف
                $rowVoucherNumber = 'PV-' . now()->format('YmdHis') . '-' . $order_id;
                $voucher = new PaymentVoucher();
                $voucher->voucher_number    = $rowVoucherNumber;
                $voucher->date              = $request->date;
                $voucher->payee_name        = $creditor->account ?? 'Expense';
                $voucher->debit_account_id  = $creditor->id;       // المدين (مصروف)
                $voucher->credit_account_id = $payAcc->id;         // الدائن (حساب الدفع)
                $voucher->amount            = $otherNowAmt;
                $voucher->branch_id         = $admin->branch_id;
                $voucher->payment_method    = 'cash';              // أو logic حسب نوع الحساب
                $voucher->cheque_number     = null;
                $voucher->description       = $desc;
                $voucher->attachment        = $img;
                $voucher->created_by        = $admin->id;
                if (Schema::hasColumn('payment_vouchers', 'cost_id')) {
                    $voucher->cost_id = $credCCId; // إن أردت ربط مركز تكلفة المدين
                }
                $voucher->save();

                // (2) قيد مستقل للسند
                $entry = new JournalEntry();
                $entry->entry_date         = $request->date;
                $entry->reference          = $rowVoucherNumber;
                $entry->type               = 'payment';
                $entry->description        = $desc;
                $entry->created_by         = $admin->id;
                $entry->payment_voucher_id = $voucher->id;
                $entry->branch_id          = $admin->branch_id;
                $entry->save();

                $voucher->journal_entry_id = $entry->id;
                $voucher->save();

                // (3) تفاصيل القيد
                $detailDebit = new JournalEntryDetail();
                $detailDebit->journal_entry_id = $entry->id;
                $detailDebit->account_id       = $creditor->id;
                $detailDebit->debit            = $otherNowAmt;
                $detailDebit->credit           = 0;
                $detailDebit->cost_center_id   = $credCCId;
                $detailDebit->description      = $desc;
                $detailDebit->attachment_path  = $img;
                $detailDebit->entry_date       = $request->date;
                $detailDebit->save();

                $detailCredit = new JournalEntryDetail();
                $detailCredit->journal_entry_id = $entry->id;
                $detailCredit->account_id       = $payAcc->id;
                $detailCredit->debit            = 0;
                $detailCredit->credit           = $otherNowAmt;
                $detailCredit->cost_center_id   = $payCCId;
                $detailCredit->description      = $desc;
                $detailCredit->attachment_path  = $img;
                $detailCredit->entry_date       = $request->date;
                $detailCredit->save();

                // (4) ترانساكشنين (كما طلبت: تنقسم لاتنين)
                // مدين
                $tDeb = new Transection();
                $tDeb->tran_type               = 100; // أو نوع خاص بالسند إن رغبت
                $tDeb->seller_id               = $admin->id;
                $tDeb->account_id              = $creditor->id;
                $tDeb->account_id_to           = $payAcc->id;
                $tDeb->debit                   = $otherNowAmt;
                $tDeb->credit                  = 0;
                $tDeb->amount                  = $otherNowAmt;
                $tDeb->tax                     = 0;
                $tDeb->description             = $desc;
                $tDeb->date                    = $request->date;
                $tDeb->balance                 = $creditor->balance + $otherNowAmt;
                $tDeb->branch_id               = $admin->branch_id;
                $tDeb->journal_entry_detail_id = $detailDebit->id;
                $tDeb->cost_id                 = $credCCId;
                $tDeb->save();

                // دائن
                $tCred = new Transection();
                $tCred->tran_type               = 100;
                $tCred->seller_id               = $admin->id;
                $tCred->account_id              = $payAcc->id;
                $tCred->account_id_to           = $creditor->id;
                $tCred->debit                   = 0;
                $tCred->credit                  = $otherNowAmt;
                $tCred->amount                  = $otherNowAmt;
                $tCred->tax                     = 0;
                $tCred->description             = $desc;
                $tCred->date                    = $request->date;
                $tCred->balance                 = $payAcc->balance - $otherNowAmt;
                $tCred->branch_id               = $admin->branch_id;
                $tCred->journal_entry_detail_id = $detailCredit->id;
                $tCred->cost_id                 = $payCCId;
                $tCred->save();

                // تحديث الأرصدة
                $creditor->balance  += $otherNowAmt;
                $creditor->total_in += $otherNowAmt;
                $creditor->save();

                $payAcc->balance   -= $otherNowAmt;
                $payAcc->total_out += $otherNowAmt;
                $payAcc->save();
            }
        }

        // ===== 11) تنظيف السلة =====
        session()->forget('cart');
        session()->forget('purchase_invoice');

        DB::commit();
        return response()->json(['message' => translate('تم تنفيذ فاتورة المشتريات بنجاح')]);

    } catch (\Throwable $e) {
        DB::rollBack();
        \Log::error("Error executing purchase invoice: ".$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json([
            'message' => translate('order_execution_failed'),
            'error'   => $e->getMessage(),
        ], 500);
    }
}



public function processReturn(Request $request)
{
    $invoice_number = $request->invoice_number;
    
    // البحث عن الفاتورة باستخدام رقم الفاتورة ونوع الفاتورة (4 أو 12)
    $order = Order::where('id', $invoice_number)
        ->where(function($query) {
            $query->where('type', 4)
                  ->orWhere('type', 12);
        })->first();

    if (!$order) {
        return redirect()->back()->withErrors('رقم الفاتورة غير موجود.');
    }

    // التحقق من أن فرع المشرف الحالي يطابق فرع الفاتورة
    if (auth('admin')->user()->branch_id != $order->branch_id) {
        return redirect()->back()->withErrors('فرعك الحالي غير الفرع الذي تم شراء الفاتورة فيه. يرجى التواصل مع المدير لتعديل بيانات الفرع.');
    }

    // جلب بيانات المنتجات من جدول order_details (تفاصيل الفاتورة)
    $orderProducts = OrderDetail::where('order_id', $order->id)->get();

    // التحقق من أن جميع المنتجات في الفاتورة تم إرجاعها بالفعل
    $allReturned = true;
    foreach ($orderProducts as $orderProduct) {
        // في حالة عدم وجود قيمة للكمية المرجعة، نفترض أنها 0
        $returned = $orderProduct->quantity_returned ?? 0;
        if ($orderProduct->quantity != $returned) {
            $allReturned = false;
            break;
        }
    }
    if ($allReturned) {
        return redirect()->back()->withErrors('كل المنتجات في هذه الفاتورة تم ارجاعها.');
    }

    // جلب دفعات المخزون (stock_batches) الخاصة بكل منتج في تفاصيل الفاتورة لنفس الفرع الحالي 
    $stockBatches = collect();
    foreach ($orderProducts as $orderProduct) {
        // نبحث عن دفعات المخزون التي تطابق product_code و product_id لنفس الفرع
        $batches = StockBatch::where('product_code', $orderProduct->product_code)
                    ->where('product_id', $orderProduct->product_id)
                    ->where('branch_id', auth('admin')->user()->branch_id)
                    ->get();
        $stockBatches = $stockBatches->merge($batches);
    }

    // تخزين بيانات الطلب والعميل مع تفاصيل الطلب ودفعات المخزون في السيشن
    session([
        'extra_discount' => $order->extra_discount,
        'total_tax'      => $order->total_tax,
        'order_amount'   => $order->order_amount,
        'name'           => $order->supplier->name ?? '',
        'mobile'         => $order->supplier->mobile ?? '',
        'credit'         => $order->supplier->credit ?? '',
        'c_history'      => $order->supplier->c_history ?? '',
        'tax_number'     => $order->supplier->tax_number ?? '',
        'seller'         => $order->seller->f_name ?? '',
        'created_at'     => $order->created_at,
        'branch'         => $order->branch->name,
        'orderDetails'   => [
            'order_id'       => $order->id,
            'order_products' => $orderProducts,
            'stock_batches'  => $stockBatches,
        ],
    ]);

    return redirect()->back();
}

public function processConfirmedReturn(Request $request)
{
    \DB::beginTransaction();
    try {
        /* -----------------------------------------------------------
         * 1) قراءة بيانات السيشن والطلب + تحقق أساسي
         * ----------------------------------------------------------- */
        $orderProducts    = session('orderDetails.order_products', []);      // بنود الفاتورة الأصلية
        $extraDiscount    = session('extra_discount') ?? 0;
        $totalTax         = session('total_tax') ?? 0;
        $baseOrderAmount  = session('order_amount') ?? 0;

        $returnQuantities = $request->input('return_quantities_hidden', []); // [product_id => qty]
        $returnUnits      = $request->input('return_unit_hidden', []);       // [product_id => 1/0]
        $returnType       = (int) $request->input('type', 24);               // 24 = Purchase Return

        // ملاحظة وصورة مرفقة (اختياري)
        $note = trim((string) $request->input('return_note', ''));
        $attachmentPath = null;
        if ($request->hasFile('return_attachment')) {
            $attachmentPath = $request->file('return_attachment')->store('returns', 'public');
        }

        // لا يوجد أي كمية مرتجع > 0
        $validReturnFound = false;
        foreach ($returnQuantities as $qty) {
            if ((float)$qty > 0) { $validReturnFound = true; break; }
        }
        if (!$validReturnFound) {
            \Toastr::error(translate("لا توجد منتجات للإرجاع."));
            return redirect()->back();
        }

        /* -----------------------------------------------------------
         * 2) حساب الإجماليات ونسبة الخصم الإضافي
         * ----------------------------------------------------------- */
        $totalProductDiscount = 0;
        foreach ($orderProducts as $p) {
            $totalProductDiscount += $p->discount_on_product * $p->quantity;
        }
        $orderAmount   = $baseOrderAmount + $extraDiscount + $totalProductDiscount - $totalTax;
        $orderAmount   = ($orderAmount > 0) ? $orderAmount : 1;
        $discountRatio = ($extraDiscount / $orderAmount) * 100;

        /* -----------------------------------------------------------
         * 3) جلب الفاتورة الأصلية + تحقق كميات المرتجع
         * ----------------------------------------------------------- */
        $oldOrderId = session('orderDetails.order_id');
        $oldOrder   = \App\Models\Order::with('details')->find($oldOrderId);
        if (!$oldOrder) {
            \DB::rollBack();
            \Toastr::error(translate("طلب قديم غير موجود"));
            return redirect()->back();
        }
        foreach ($orderProducts as $p) {
            $pid = $p->product_id;
            $newReturn = isset($returnQuantities[$pid]) ? (float)$returnQuantities[$pid] : 0;
            if ($newReturn <= 0) continue;

            $productDetails = json_decode($p->product_details);
            $unitValue = isset($productDetails->unit_value) ? $productDetails->unit_value : 1;
            $chosenUnit = isset($returnUnits[$pid]) ? $returnUnits[$pid] : 1;
            $newReturnConverted = ($chosenUnit == 0) ? ($newReturn / $unitValue) : $newReturn;

            $oldDetail = $oldOrder->details->where('product_id', $pid)->first();
            if ($oldDetail) {
                $alreadyReturned = $oldDetail->quantity_returned ?? 0;
                $purchasedQty    = $oldDetail->quantity;

                if (($alreadyReturned + $newReturnConverted) > $purchasedQty) {
                    \DB::rollBack();
                    $availableToReturn = $purchasedQty - $alreadyReturned;
                    \Toastr::error(translate("لقد قمت بإرجاع " . $alreadyReturned . " من المنتج " . $p->product->name . ". المتاح للإرجاع هو " . $availableToReturn));
                    return redirect()->back();
                }
            }
        }

        /* -----------------------------------------------------------
         * 4) معالجة كل صنف مرتجع + تحديث المخزون/الدفعات
         * ----------------------------------------------------------- */
        $productsReturnData        = [];
        $totalReturnPrice          = 0;
        $totalReturnDiscount       = 0;
        $totalReturnExtraDiscount  = 0;
        $totalReturnTax            = 0;
        $totalReturnOverall        = 0;

        // (للإيضاح فقط) إجمالي تكلفة المخزون على أساس دفعات المخزون المستخدمة
        $totalInventoryValueForReturn = 0;

        $adminId      = auth('admin')->user()->id;
        $branchId     = auth('admin')->user()->branch_id;
        $branchColumn = "branch_" . $branchId;

        foreach ($orderProducts as $product) {
            $pid            = $product->product_id;
            $productDetails = json_decode($product->product_details);
            $unitValue      = isset($productDetails->unit_value) ? $productDetails->unit_value : 1;
            $chosenUnit     = isset($returnUnits[$pid]) ? $returnUnits[$pid] : 1;

            // تسعير الوحدة بحسب الوحدة المختارة
            if ($chosenUnit == 0 && $product->unit == 1) {
                $adjustedPrice         = $product->price / $unitValue;
                $adjustedDiscount      = $product->discount_on_product / $unitValue;
                $adjustedExtraDiscount = (($discountRatio / 100) * $product->price) / $unitValue;
                $adjustedTax           = $product->tax_amount / $unitValue;
            } else {
                $adjustedPrice         = $product->price;
                $adjustedDiscount      = $product->discount_on_product;
                $adjustedExtraDiscount = ($discountRatio / 100) * $product->price;
                $adjustedTax           = $product->tax_amount;
            }

            $returnQuantity      = isset($returnQuantities[$pid]) ? (float)$returnQuantities[$pid] : 0;
            $effectiveFinalUnit  = $adjustedPrice - $adjustedDiscount - $adjustedExtraDiscount + $adjustedTax;

            // تجميع بيانات هذا الصنف
            $productsReturnData[] = [
                'product_id'      => $pid,
                'name'            => $product->product->name,
                'price'           => $adjustedPrice,
                'discount'        => $adjustedDiscount,
                'extra_discount'  => $adjustedExtraDiscount,
                'tax'             => $adjustedTax,
                'unit'            => $chosenUnit,
                'return_quantity' => $returnQuantity,
            ];

            // مجاميع المرتجع
            $totalReturnPrice          += $returnQuantity * $adjustedPrice;
            $totalReturnDiscount       += $returnQuantity * $adjustedDiscount;
            $totalReturnExtraDiscount  += $returnQuantity * $adjustedExtraDiscount;
            $totalReturnTax            += $returnQuantity * $adjustedTax;
            $totalReturnOverall        += $returnQuantity * $effectiveFinalUnit;

            // لو في مرتجع فعلي: أنزل المخزون + لوغ
            if ($returnQuantity > 0) {
                $finalQuantity = ($chosenUnit == 0) ? ($returnQuantity / $unitValue) : $returnQuantity;

                // متوسط تكلفة الدفعات المستخدمة (FIFO مبسط حسب created_at)
                $batchesCost = \App\Models\StockBatch::where('product_id', $pid)
                                ->where('branch_id', $branchId)
                                ->orderBy('created_at')->get();

                $weightedSumConsumed = 0;
                $totalConsumedQty    = 0;
                $remain              = $finalQuantity;

                foreach ($batchesCost as $batch) {
                    if ($remain <= 0) break;

                    $available = isset($batch->$branchColumn) ? $batch->$branchColumn : $batch->quantity;
                    if ($available <= 0) continue;

                    $use = min($available, $remain);
                    $weightedSumConsumed += ($batch->price * $use);
                    $totalConsumedQty    += $use;
                    $remain              -= $use;
                }
                if ($remain > 0) {
                    \DB::rollBack();
                    \Toastr::error(translate("كمية المنتج غير كافية في المخزن."));
                    return redirect()->back();
                }
                $weightedAvg = $totalConsumedQty > 0 ? $weightedSumConsumed / $totalConsumedQty : 0;
                $totalInventoryValueForReturn += $finalQuantity * $weightedAvg;

                // تسجيل لوغ خروج من المخزون (مرتجع مشتريات)
                $productLog = new \App\Models\ProductLog;
                $productLog->product_id = $pid;
                $productLog->quantity   = $finalQuantity;
                $productLog->type       = $returnType;  // 24
                $productLog->seller_id  = $adminId;
                $productLog->branch_id  = $branchId;
                $productLog->save();

                // تحديث مخزون المنتج
                $productModel = \App\Models\Product::find($pid);
                if (isset($productModel->$branchColumn)) {
                    $productModel->$branchColumn -= $finalQuantity;
                } else {
                    $productModel->quantity -= $finalQuantity;
                }
                $productModel->repurchase_count++;
                $productModel->save();

                // خصم الكميات من الدُفعات (بالكود ثم بالمنتج)
                $remainBatches = $finalQuantity;
                $stockBatches = \App\Models\StockBatch::where('product_code', $product->product_code)->get();
                if ($stockBatches->isEmpty()) {
                    $stockBatches = \App\Models\StockBatch::where('product_id', $pid)->where('branch_id', $branchId)->get();
                }
                if ($stockBatches->isEmpty()) {
                    \DB::rollBack();
                    \Toastr::error(translate("الفرع لا يحتوي على هذه الكمية لاستراجعها"));
                    return redirect()->back();
                }
                foreach ($stockBatches as $batch) {
                    if ($remainBatches <= 0) break;
                    $available = isset($batch->$branchColumn) ? $batch->$branchColumn : $batch->quantity;
                    if ($available <= 0) continue;

                    if ($available >= $remainBatches) {
                        if (isset($batch->$branchColumn)) {
                            $batch->$branchColumn -= $remainBatches;
                        } else {
                            $batch->quantity -= $remainBatches;
                        }
                        $batch->save();
                        $remainBatches = 0;
                        break;
                    } else {
                        if (isset($batch->$branchColumn)) {
                            $batch->$branchColumn = 0;
                        } else {
                            $batch->quantity = 0;
                        }
                        $remainBatches -= $available;
                        $batch->save();
                    }
                }
                if ($remainBatches > 0) {
                    \DB::rollBack();
                    \Toastr::error(translate("الفرع لا يحتوي على هذه الكمية لاستراجعها"));
                    return redirect()->back();
                }
            }
        }

        /* -----------------------------------------------------------
         * 5) إنشاء أمر جديد للمرتجع
         * ----------------------------------------------------------- */
        $newOrder = new \App\Models\Order;
        $newOrder->owner_id     = $adminId;
        $newOrder->supplier_id  = $oldOrder->supplier_id;
        $newOrder->parent_id    = $oldOrder->id;
        $newOrder->branch_id    = $branchId;
        $newOrder->cash         = 2; // أجل (مذكرة دائن لدى المورد)
        // نوع المرتجع حسب أصل الفاتورة
        if     ($oldOrder->type == 4)  $newOrder->type = 7;
        elseif ($oldOrder->type == 12) $newOrder->type = 24;
        else                            $newOrder->type = $returnType;

        $newOrder->total_tax              = $totalReturnTax;
        $newOrder->order_amount           = $totalReturnOverall;
        $newOrder->extra_discount         = $totalReturnExtraDiscount;
        $newOrder->coupon_discount_amount = 0;
        $newOrder->collected_cash         = 0;
        $newOrder->transaction_reference  = 0;
        $newOrder->date                   = $request->date ?: now()->toDateString();
        $newOrder->note                   = $note ?: null;
        if ($attachmentPath) {
            $newOrder->img = $attachmentPath;
        }
        $newOrder->save();

        /* -----------------------------------------------------------
         * 6) قيود اليومية (قيد واحد مزدوج) + تقسيم الترانزاكشن
         *    منطق مرتجع مشتريات: مدين (المورد/الدائنون)، دائن (المخزون)
         *    بالقيمة الصافية للمرتجع (totalReturnOverall)
         * ----------------------------------------------------------- */
        $branch     = \App\Models\Branch::findOrFail($branchId);
        $supplier   = \App\Models\Supplier::findOrFail($oldOrder->supplier_id);

        $inventoryAccount = \App\Models\Account::find($branch->account_stock_Id); // المخزون
        $supplierAccount  = \App\Models\Account::find($supplier->account_id);     // الدائنون/المورد

        $amountForJournal = (float) $totalReturnOverall;

        if ($amountForJournal > 0) {
            // رأس القيد
            $entry = new \App\Models\JournalEntry();
            $entry->entry_date  = $newOrder->date;
            $entry->reference   = 'PR-' . $newOrder->id;
            $entry->type        = 'purchase_return';
            $entry->description = $note ?: ('Purchase Return #' . $newOrder->id);
            $entry->created_by  = \Auth::guard('admin')->id();
            $entry->branch_id   = $branchId;
            $entry->save();

            // تفاصيل القيد
            // 1) مدين: المورد
            $detailDebit = new \App\Models\JournalEntryDetail();
            $detailDebit->journal_entry_id = $entry->id;
            $detailDebit->account_id       = $supplierAccount ? $supplierAccount->id : null;
            $detailDebit->debit            = $amountForJournal;
            $detailDebit->credit           = 0;
            $detailDebit->cost_center_id   = null;
            $detailDebit->description      = $note;
            $detailDebit->attachment_path  = $attachmentPath;
            $detailDebit->entry_date       = $newOrder->date;
            $detailDebit->save();

            // 2) دائن: المخزون
            $detailCredit = new \App\Models\JournalEntryDetail();
            $detailCredit->journal_entry_id = $entry->id;
            $detailCredit->account_id       = $inventoryAccount ? $inventoryAccount->id : null;
            $detailCredit->debit            = 0;
            $detailCredit->credit           = $amountForJournal;
            $detailCredit->cost_center_id   = null;
            $detailCredit->description      = $note;
            $detailCredit->attachment_path  = $attachmentPath;
            $detailCredit->entry_date       = $newOrder->date;
            $detailCredit->save();

            // تقسيم الترانزاكشن (سطرين)
            // سطر مدين (المورد)
            $tDeb = new \App\Models\Transection();
            $tDeb->tran_type               = $newOrder->type; // 24
            $tDeb->seller_id               = $adminId;
            $tDeb->account_id              = $supplierAccount ? $supplierAccount->id : null;
            $tDeb->account_id_to           = $inventoryAccount ? $inventoryAccount->id : null;
            $tDeb->debit                   = $amountForJournal;
            $tDeb->credit                  = 0;
            $tDeb->amount                  = $amountForJournal;
            $tDeb->tax                     = $totalReturnTax;
            $tDeb->description             = $note ?: 'مرتجع مشتريات';
            $tDeb->date                    = $newOrder->date;
            $tDeb->img                     = $attachmentPath;
            $tDeb->branch_id               = $branchId;
            $tDeb->journal_entry_detail_id = $detailDebit->id;
            $tDeb->cost_id                 = null;
            $tDeb->order_id                = $newOrder->id;
            // رصيد بعد الحركة (تقديري حسب الحقول عندك)
            if ($supplierAccount) {
                $tDeb->balance = $supplierAccount->balance - $amountForJournal;
            }
            $tDeb->save();

            // سطر دائن (المخزون)
            $tCred = new \App\Models\Transection();
            $tCred->tran_type               = $newOrder->type;
            $tCred->seller_id               = $adminId;
            $tCred->account_id              = $inventoryAccount ? $inventoryAccount->id : null;
            $tCred->account_id_to           = $supplierAccount ? $supplierAccount->id : null;
            $tCred->debit                   = 0;
            $tCred->credit                  = $amountForJournal;
            $tCred->amount                  = $amountForJournal;
            $tCred->tax                     = $totalReturnTax;
            $tCred->description             = $note ?: 'مرتجع مشتريات';
            $tCred->date                    = $newOrder->date;
            $tCred->img                     = $attachmentPath;
            $tCred->branch_id               = $branchId;
            $tCred->journal_entry_detail_id = $detailCredit->id;
            $tCred->cost_id                 = null;
            $tCred->order_id                = $newOrder->id;
            if ($inventoryAccount) {
                $tCred->balance = $inventoryAccount->balance - $amountForJournal;
            }
            $tCred->save();

            // تحديث أرصدة الحسابات (بنفس منطقك السابق: تخفيض المورد والمخزون)
            if ($inventoryAccount) {
                $inventoryAccount->balance   -= $amountForJournal;
                $inventoryAccount->total_out += $amountForJournal;
                $inventoryAccount->save();
            }
            if ($supplierAccount) {
                // تقليل التزامنا للمورد (نحن مدينون أقل)
                $supplierAccount->balance   -= $amountForJournal;
                $supplierAccount->total_out += $amountForJournal;
                $supplierAccount->save();
            }
        }

        /* -----------------------------------------------------------
         * 7) QR للفاتورة الجديدة
         * ----------------------------------------------------------- */
        $qrcodeData  = url('real/invoicea2/' . $newOrder->id);
        $qrCode      = new \Endroid\QrCode\QrCode($qrcodeData);
        $writer      = new \Endroid\QrCode\Writer\PngWriter();
        $qrcodeImage = $writer->write($qrCode)->getString();
        $qrcodePath  = "qrcodes/order_" . $newOrder->id . ".png";
        \Storage::disk('public')->put($qrcodePath, $qrcodeImage);
        $newOrder->qrcode = $qrcodePath;
        $newOrder->save();

        /* -----------------------------------------------------------
         * 8) إنشاء تفاصيل أمر المرتجع
         * ----------------------------------------------------------- */
        foreach ($productsReturnData as $productData) {
            $detail = new \App\Models\OrderDetail;
            $detail->order_id            = $newOrder->id;
            $detail->product_id          = $productData['product_id'];
            $detail->product_details     = json_encode($productData);
            $detail->quantity            = $productData['return_quantity'];
            $detail->unit                = $productData['unit'];
            $detail->price               = $productData['price'];
            $detail->tax_amount          = $productData['tax'];
            $detail->discount_on_product = $productData['discount'];
            $detail->discount_type       = 'discount_on_product';
            $detail->save();
        }

        /* -----------------------------------------------------------
         * 9) تحديث كميات المرتجع المجمعة في تفاصيل الفاتورة القديمة
         * ----------------------------------------------------------- */
        foreach ($orderProducts as $prod) {
            $pid = $prod->product_id;
            $newReturn = isset($returnQuantities[$pid]) ? (float)$returnQuantities[$pid] : 0;
            if ($newReturn <= 0) continue;

            $productDetails = json_decode($prod->product_details);
            $unitValue = isset($productDetails->unit_value) ? $productDetails->unit_value : 1;
            $chosenUnit = isset($returnUnits[$pid]) ? $returnUnits[$pid] : 1;
            $newReturnConverted = ($chosenUnit == 0) ? ($newReturn / $unitValue) : $newReturn;

            $oldDetail = $oldOrder->details->where('product_id', $pid)->first();
            if ($oldDetail) {
                $oldDetail->quantity_returned = ($oldDetail->quantity_returned ?? 0) + $newReturnConverted;
                $oldDetail->save();
            }
        }

        /* -----------------------------------------------------------
         * 10) إنهاء العملية + تنظيف السيشن
         * ----------------------------------------------------------- */
        \DB::commit();

        \Toastr::success(translate('تم تنفيذ المرتجع بنجاح') . ' - ' . translate('رقم الطلب') . ': ' . $newOrder->id);
        session()->forget([
            'extra_discount','total_tax','order_amount',
            'name','mobile','credit','c_history','tax_number',
            'seller','created_at','orderDetails'
        ]);

        return redirect()->back()->with('success', 'تم تنفيذ المرتجع بنجاح!');

    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error('[Purchase Return] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        \Toastr::error($e->getMessage());
        return redirect()->back()->with('error', $e->getMessage());
    }
}


}
