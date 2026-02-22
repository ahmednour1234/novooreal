<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Account;
use App\Models\Branch;
use App\Models\OrderDetail;
use App\Models\Seller;
use App\Models\Order;
use App\Models\Quotation;
use App\Models\ProductLog;
use App\Models\QuotationDetail;
use Barryvdh\DomPDF\Facade\Pdf;              // composer require barryvdh/laravel-dompdf
use Illuminate\Support\Facades\Mail;
use App\Mail\QuotationPdfMail;                // قم بإنشاء هذا الـ Mailable
use App\CPU\Helpers;
use function App\CPU\translate;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Brian2694\Toastr\Facades\Toastr;
use App\Services\WhatsAppWebService;
use App\Models\Guarantor;

class QuotationController extends Controller
{
        public function __construct(
        private Order $order,
                private Customer $customer,
        private OrderDetail $order_details,
                private Product $product,
                private ProductLog $product_logs,

    ){}
    /**
     * عرض صفحة إنشاء عرض السعر (Quotation).
     */
public function create(Request $request)
{
    $type = $request->input('type', 'product'); // القيمة الافتراضية "product" إن لم تُرسل

    $customers     = Customer::all();
    $products      = Product::where('product_type', $type)->get(); // تصفية حسب النوع
    $startDate     = $request->input('start_date');
    $endDate       = $request->input('end_date');
    $cachedInvoice = session()->get('Quotation');
    $cart          = session()->get('cart', []);
    $cost_centers  = \App\Models\CostCenter::where('active', 1)->doesntHave('children')->get();
    $accounts      = \App\Models\Account::where(function ($q) {
        $q->whereIn('id', [8, 14])->orWhereIn('parent_id', [8, 14]);
    })->doesntHave('children')->orderBy('id')->get();

    return view('admin-views.quotation.create', compact(
        'customers', 'products', 'startDate', 'endDate',
        'cachedInvoice', 'cart', 'accounts', 'cost_centers', 'type'
    ));
}

       public function create_type(Request $request)
    {
 

        return view('admin-views.quotation.create_type');
    }
public function store(Request $request)
{
    $validated = $request->validate([
        'customer_id'         => 'required|exists:customers,id',
        'products'            => 'required|array|min:1',
        'products.*.id'       => 'required|exists:products,id',
        'products.*.quantity' => 'required|numeric|min:1',
        'products.*.price'    => 'required|numeric|min:0',
        'products.*.tax'      => 'required|numeric|min:0',
        'products.*.discount' => 'nullable|numeric|min:0',
        'cash'                => 'required|in:1,2',
        'order_amount'        => 'required|numeric|min:0',
        'date'                => 'required|date',
        'type'                => 'required|in:0,8,12',
        'img'                 => 'nullable|image|max:2048',
    ]);

    DB::beginTransaction();

    try {
        $orderId = Quotation::max('id') + 1 ?: 100001;

        // رفع الصورة إن وُجدت
        $imgPath = null;
        if ($request->hasFile('img')) {
            $imgPath = $request->file('img')->store('quotations/images', 'public');
        }

        $totalPrice = 0;
        $totalTax   = 0;
        $totalDisc  = 0;
        $details    = [];

        // استخراج نوع العرض (من أول منتج)
        $firstProduct = Product::find($validated['products'][0]['id']);
        $quotationType = $firstProduct->product_type; // إما 'product' أو 'service'

        foreach ($validated['products'] as $index => $item) {
            $product = Product::find($item['id']);
            $isService = $product->product_type === 'service';

            $linePrice    = $item['price'] * $item['quantity'];
            $lineTax      = $item['tax']   * $item['quantity'];
            $lineDiscount = ($item['discount'] ?? 0) * $item['quantity'];

            $totalPrice += $linePrice;
            $totalTax   += $lineTax;
            $totalDisc  += $lineDiscount;

            $detail = [
                'order_id'            => $orderId,
                'product_id'          => $item['id'],
                'product_details'     => $product->toJson(),
                'quantity'            => $item['quantity'],
                'price'               => $item['price'],
                'tax_amount'          => $item['tax'],
                'discount_on_product' => $item['discount'] ?? 0,
                'discount_type'       => 'product_level',
                'created_at'          => now(),
                'updated_at'          => now(),
            ];

            // لا تُضف الوحدة إذا كانت خدمة
            if (!$isService) {
                $detail['unit'] = $item['unit'] ?? 0;
            }

            $details[] = $detail;
        }

        $grandTotal = $totalPrice + $totalTax - $totalDisc;

        // حفظ الاقتباس
        $quotation = new Quotation();
        $quotation->id             = $orderId;
        $quotation->user_id        = $validated['customer_id'];
        $quotation->type           = $validated['type'];
        $quotation->quotation_type = $quotationType; // هنا يتم التخزين الصحيح
        $quotation->total_tax      = $totalTax;
        $quotation->order_amount   = $grandTotal;
        $quotation->extra_discount = $totalDisc;
        $quotation->cash           = $validated['cash'];
        $quotation->date           = $validated['date'];
        $quotation->img            = $imgPath;
        $quotation->owner_id       = auth('admin')->id();
        $quotation->branch_id      = auth('admin')->user()->branch_id;
        $quotation->save();

        QuotationDetail::insert($details);

        $pdf = Pdf::loadView('admin-views.quotation.pdf', [
            'quotation' => $quotation,
            'details'   => $quotation->details,
        ])->setPaper('a4', 'portrait');

        $pdfPath = "quotations/pdf/quotation_{$orderId}.pdf";
        Storage::disk('public')->put($pdfPath, $pdf->output());

        if ((int)$validated['type'] === 8) {
            $customer = Customer::find($validated['customer_id']);
            $phone    = $customer->mobile;

            $wa = new WhatsAppWebService();
            $wa->sendDocument(
                $phone,
                storage_path("app/public/{$pdfPath}")
            );
        }

        DB::commit();
        Toastr::success(__('تم حفظ العرض بنجاح'));
        return back()->with('success', __('تم حفظ العرض بنجاح'));

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Quotation store error: '.$e->getMessage());
        Toastr::error(__('حدث خطأ أثناء معالجة العرض'));
        return back()->withErrors(['error' => __('حدث خطأ أثناء معالجة العرض')]);
    }
}


    /**
     * تنفيذ وإضافة عرض السعر (Quotation) مع تفاصيله.
     */
    public function execute(Request $request)
    {
        $request->validate([
            'customer_id'         => 'required|exists:customers,id',
            'products'            => 'required|array|min:1',
            'products.*.id'       => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.price'    => 'required|numeric|min:0',
            'products.*.unit'     => 'required|in:0,1',
            'products.*.tax'      => 'required|numeric|min:0',
            'cash'                => 'required|in:1,2',
            'order_amount'        => 'required|numeric|min:0',
            'date'                => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $customer_id     = $request->customer_id;
            $type            = 12; // نوع عرض السعر
            $order_id        = 100000 + Order::count() + 1;
            if (Quotation::find($order_id)) {
                $order_id = Quotation::orderBy('id', 'DESC')->first()->id + 1;
            }

            // رفع الصورة إن وُجدت
            $img = null;
            if ($request->hasFile('img')) {
                $img = $request->file('img')->store('shop', 'public');
            }

            // إنشاء QR Code
            $qrcode_data = url('real/invoicea2/' . $order_id);
            $qrCode      = new QrCode($qrcode_data);
            $writer      = new PngWriter();
            $qrcode_image= $writer->write($qrCode)->getString();
            $qrcode_path = "qrcodes/order_{$order_id}.png";
            Storage::disk('public')->put($qrcode_path, $qrcode_image);

            // تحضير المتغيرات
            $product_price    = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $order_details    = [];

            foreach ($request->products as $c) {
                $product = Product::find($c['id']);
                if (!$product) continue;

                $price         = $c['price'];
                $tax_calculated= Helpers::tax_calculate($product, $price);
                $discount_on_product = 0;

                // معالجة الوحدة والكمية (حسب منطقك)...
                $quantity = $c['quantity'];
                
                $product_price    += $price * $quantity;
                $product_tax      += $c['tax'] * $quantity;
                $product_discount += ($c['discount'] ?? 0) * $quantity;

                $order_details[] = [
                    'order_id'            => $order_id,
                    'product_id'          => $product->id,
                    'product_details'     => json_encode($product),
                    'quantity'            => $quantity,
                    'unit'                => $c['unit'],
                    'price'               => $price,
                    'tax_amount'          => $c['tax'],
                    'discount_on_product' => 0,
                    'discount_type'       => 'discount_on_product',
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];
            }

            $total_tax_amount     = $product_tax;
            $total_discount_amount= $product_discount;
            $grand_total         = $product_price + $total_tax_amount - $total_discount_amount;
            $coupon_discount     = 0; // إن وُجد كوبون

            // حفظ العرض
            $quotation = new Quotation;
            $quotation->id                     = $order_id;
            $quotation->customer_id            = $customer_id;
            $quotation->type                   = $type;
            $quotation->total_tax              = $total_tax_amount;
            $quotation->order_amount           = $grand_total;
            $quotation->coupon_discount_amount = $coupon_discount;
            $quotation->extra_discount         = $total_discount_amount;
            $quotation->cash                   = $request->cash;
            $quotation->date                   = $request->date;
            $quotation->qrcode                 = $qrcode_path;
            $quotation->owner_id               = Auth::id();
            $quotation->branch_id              = Auth::user()->branch_id;
            $quotation->img                    = $img;
            $quotation->created_at             = now();
            $quotation->updated_at             = now();
            $quotation->save();

            QuotationDetail::insert($order_details);

            DB::commit();
            return response()->json(['message' => translate('تم تنفيذ عرض السعر بنجاح')]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error executing quotation: " . $e->getMessage());
            return response()->json([
                'message' => translate('order_execution_failed'),
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * إرسال عرض السعر كملف PDF عبر البريد الإلكتروني أو SMS.
     */
    public function sendPdf(Request $request, $id)
    {
        $quotation = Quotation::with('details')->findOrFail($id);
        $pdf       = Pdf::loadView('admin-views.quotation.pdf', compact('quotation'));
        $path      = storage_path("app/public/quotations/quotation_{$id}.pdf");
        $pdf->save($path);

        if ($request->filled('email')) {
            Mail::to($request->input('email'))
                ->send(new QuotationPdfMail($quotation, $path));
        }

        if ($request->filled('mobile')) {
            $message = "عرض السعر رقم #{$id}: " . url("quotations/{$id}/download");
            Helpers::sendSms($request->input('mobile'), $message);
        }

        return response()->json(['message' => translate('تم إرسال عرض السعر بنجاح')]);
    }

    /**
     * استجابة العميل لعرض السعر (قبول أو رفض).
     */
    public function respond(Request $request, $id)
    {
        $request->validate(['response' => 'required|in:accepted,rejected']);
        $quotation = Quotation::findOrFail($id);
        $quotation->status       = $request->input('response');
        $quotation->responded_at = now();
        $quotation->save();

        return response()->json([
            'message' => $quotation->status === 'accepted'
                ? translate('تم قبول عرض السعر.')
                : translate('تم رفض عرض السعر.')
        ]);
    }

    /**
     * عرض جميع عروض الأسعار.
     */
    public function index()
    {
        $quotations = Quotation::with('customer')
            ->orderBy('date', 'desc')
            ->paginate(20);
        return view('admin-views.quotation.index', compact('quotations'));
    }

    /**
     * عرض تفاصيل عرض سعر واحد.
     */

        public function drafts()
    {
        $drafts = Quotation::where('type', 0)
                    ->with('customer')
                    ->orderBy('date','desc')
                    ->paginate(15);

        return view('admin-views.quotation.drafts', compact('drafts'));
    }

    // 2) عرض مسودة
    public function show($id)
    {
        $accounts = \App\Models\Account::where(function($query) {
    // نختار الحسابات الأصلية أو اللي رقمها 8 أو 14 أو اللي parent_id تبعهم أحد هذين الحسابين
    $query->whereIn('id', [8,14])
          ->orWhereIn('parent_id', [8,14]);
})->doesntHave('children') // نتأكد أنه ليس له أولاد
  ->orderBy('id')
  ->get();
  
$cost_centers = \App\Models\CostCenter::doesntHave('children')
    ->orderBy('id', 'desc')
    ->get();

        $quotation = Quotation::with(['customer','details.product'])
                        ->findOrFail($id);
$guarantors=Guarantor::all();

        $quotation = Quotation::with(['customer','details.product'])
                        ->findOrFail($id);

        return view('admin-views.quotation.show', compact('quotation','cost_centers','accounts','guarantors'));
    }

    // 3) نموذج التعديل
public function edit($id)
{
    $quotation = Quotation::with('details')->findOrFail($id);

    $products = Product::when($quotation->quotation_type, function ($query) use ($quotation) {
        return $query->where('product_type', $quotation->quotation_type);
    })->get();

    return view('admin-views.quotation.edit', compact('quotation', 'products'));
}


    // 4) حفظ التعديلات
public function update(Request $request, $id)
{
    $request->validate([
        'products'            => 'required|array|min:1',
        'products.*.id'       => 'required|exists:products,id',
        'products.*.quantity' => 'required|numeric|min:1',
        'products.*.price'    => 'required|numeric|min:0',
        'products.*.tax'      => 'required|numeric|min:0',
        'products.*.discount' => 'nullable|numeric|min:0',
        'order_amount'        => 'required|numeric|min:0',
        'cash'                => 'required|in:1,2',
        'date'                => 'required|date',
    ]);

    DB::beginTransaction();
    try {
        $quotation = Quotation::findOrFail($id);

        // حذف التفاصيل القديمة
        QuotationDetail::where('order_id', $id)->delete();

        // إعادة بناء التفاصيل
        $price = $tax = $disc = 0;
        $details = [];

        foreach ($request->products as $item) {
            $product = Product::findOrFail($item['id']);
            $isService = $product->product_type === 'service';

            $linePrice    = $item['price'] * $item['quantity'];
            $lineTax      = $item['tax']   * $item['quantity'];
            $lineDiscount = ($item['discount'] ?? 0) * $item['quantity'];

            $price += $linePrice;
            $tax   += $lineTax;
            $disc  += $lineDiscount;

            $detail = [
                'order_id'            => $id,
                'product_id'          => $item['id'],
                'product_details'     => $product->toJson(),
                'quantity'            => $item['quantity'],
                'price'               => $item['price'],
                'tax_amount'          => $item['tax'],
                'discount_on_product' => $item['discount'] ?? 0,
                'discount_type'       => 'product_level',
                'created_at'          => now(),
                'updated_at'          => now(),
            ];

            if (!$isService) {
                $detail['unit'] = $item['unit'] ?? 0;
            }

            $details[] = $detail;
        }

        $grand = $price + $tax - $disc;

        // تحديث الفاتورة
        $quotation->update([
            'total_tax'      => $tax,
            'extra_discount' => $disc,
            'order_amount'   => $grand,
            'cash'           => $request->cash,
            'date'           => $request->date,
        ]);

        QuotationDetail::insert($details);

        Toastr::success(__('تم تحديث العرض بنجاح'));

        DB::commit();
        return redirect()->route('admin.quotations.drafts')
                         ->with('success', 'تم تحديث المسودة بنجاح');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Quotation update error: ' . $e->getMessage());
        Toastr::error(__('حدث خطأ أثناء المعالجة'));
        return back()->withErrors(['error' => __('حدث خطأ أثناء التحديث')]);
    }
}

    public function destroy($id)
{
    DB::beginTransaction();

    try {
        // جلب العرض مع تفاصيله
        $quotation = Quotation::findOrFail($id);

        // حذف ملفات الصورة و QR code من التخزين إن وجدت
        if ($quotation->img) {
            Storage::disk('public')->delete($quotation->img);
        }
        if ($quotation->qrcode) {
            Storage::disk('public')->delete($quotation->qrcode);
        }

        // حذف التفاصيل المرتبطة
        QuotationDetail::where('order_id', $id)->delete();

        // حذف العرض نفسه
        $quotation->delete();

        DB::commit();

        return redirect()
            ->route('admin.quotations.drafts')
            ->with('success', __('تم حذف عرض الأسعار بنجاح'));
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Quotation destroy error: '.$e->getMessage());

        return back()
            ->withErrors(['error' => __('حدث خطأ أثناء حذف العرض')]);
    }
}
public function executequotaiton(Request $request, $quotation_id)
{
    DB::beginTransaction();

    try {
        // ========= إعدادات أساسية =========
        $quotation = Quotation::findOrFail($quotation_id);
        $details   = QuotationDetail::where('order_id', $quotation->id)->get();

        $admin     = auth('admin')->user();
        $sellerId  = $admin->id;
        $branchId  = $admin->branch_id;
        $branch    = \App\Models\Branch::find($branchId);

        // وضع السداد: 1 نقدي / 2 آجل (افتراضي آجل إذا لم يُرسل)
        $cashMode  = (int) $request->input('cash', 2);

        $date      = $quotation->date; // تاريخ عرض السعر
        $user_id   = $quotation->user_id;
        $customer  = $this->customer->find($user_id);
        $accCustomerId = $customer?->account_id;

        // ملاحظة
        $note = trim((string)$request->input('note'));
        if ($note === '') {
            $note = 'فاتورة مبيعات ' . ($cashMode === 1 ? 'نقدية' : 'آجل') . ' ناتجة عن عرض سعر #' . $quotation->id;
        }

        // أكواد الحسابات (عوِّض بحسب شجرتك)
        $accSalesCode  = 40;                                 // إيرادات/مبيعات (Credit عند الزيادة)
        $accVatCode    = 28;                                 // ضريبة مخرجات (التزام) (Credit عند الزيادة)
        $accCogsCode   = 47;                                 // تكلفة بضاعة مباعة (Expense) (Debit)
        $accStockCode  = $branch?->account_stock_Id;         // مخزون الفرع (Asset) (Credit عند الانخفاض)
        $accCashBankId = $request->payment_id ?: 92;         // نقدية/بنك

        // ========= رفع صورة (اختياري) =========
        $img = null;
        if ($request->hasFile('img')) {
            $img = $request->file('img')->store('shop', 'public');
        }

        // ========= إنشاء رقم طلب + QR =========
        $order_id = 100000 + $this->order->count() + 1;
        if ($this->order->find($order_id)) {
            $order_id = $this->order->orderBy('id', 'DESC')->first()->id + 1;
        }

        $qrcode_data = "https://demo.novoosystem.com/real/invoicea2/" . $order_id;
        $qrCode      = new \Endroid\QrCode\QrCode($qrcode_data);
        $writer      = new \Endroid\QrCode\Writer\PngWriter();
        $qrcode_path = "qrcodes/order_$order_id.png";
        Storage::disk('public')->put($qrcode_path, $writer->write($qrCode)->getString());

        // ========= إنشاء الطلب =========
        $order                       = $this->order;
        $order->id                   = $order_id;
        $order->user_id              = $user_id;
        $order->payment_id           = $request->payment_id;
        $order->type                 = 4;                     // 4 = بيع
        $order->cash                 = $cashMode;             // 1 نقدي / 2 آجل
        $order->date                 = $date;
        $order->qrcode               = $qrcode_path;
        $order->owner_id             = $sellerId;
        $order->branch_id            = $branchId;
        $order->transaction_reference= $request->input('transaction_reference', '');
        $order->img                  = $img;
        $order->note                 = $note;

        // ========= مجاميع + مخزون/خدمات =========
        $product_price        = 0;
        $product_discount     = 0;
        $product_tax          = 0;
        $totalCogsForProducts = 0;
        $hasAnyProduct        = false;

        $order_details = [];
        $productlogs   = [];

        foreach ($details as $row) {
            $product = $this->product->find($row['product_id']);
            if (!$product) continue;

            $isService = (string)($product->product_type ?? '') === 'service';

            $price   = (float)$row['price'];
            $qtySell = (int)$row['quantity'];
            $unit    = (int)($row['unit'] ?? 1); // للخدمة نعاملها كوحدة كبرى دائمًا

            // الكمية بوحدة الأساس
            if ($isService) {
                $qtyBase = $qtySell;
            } else {
                $hasAnyProduct = true;
                $unitValue = max(1, (int)($product->unit_value ?? 1));
                $qtyBase   = ($unit == 0) ? ($qtySell / $unitValue) : $qtySell;
            }

            // ===== المنتجات: FIFO + خصم مخزون + COGS =====
            $weightedAvg = 0;
            if (!$isService) {
                // قفل المنتج والدفعات
                $product = $this->product->where('id', $row['product_id'])->lockForUpdate()->first();

                $stockBatches = \App\Models\StockBatch::where('product_id', $row['product_id'])
                    ->where('branch_id', $branchId)
                    ->where('quantity', '>', 0)
                    ->orderBy('created_at')
                    ->lockForUpdate()
                    ->get();

                $remaining   = (float) $qtyBase;
                $consumed    = 0.0;
                $weightedSum = 0.0;

                foreach ($stockBatches as $b) {
                    if ($remaining <= 0) break;

                    $take = min((float)$b->quantity, $remaining);
                    if ($take <= 0) continue;

                    $weightedSum += ((float)$b->price) * $take;
                    $consumed    += $take;

                    $b->quantity = (float)$b->quantity - $take;
                    $b->saveQuietly();

                    $remaining -= $take;
                }

                if ($remaining > 0) {
                    DB::rollBack();
                    Toastr::error(translate('كمية المنتج غير كافية في المخزن.'));
                    return back();
                }

                $weightedAvg = $consumed > 0 ? round($weightedSum / $consumed, 6) : 0.0;
                $totalCogsForProducts += $weightedSum;

                // خصم من مخزون الفرع ثم العام
                $branchColumn     = 'branch_' . $branchId;
                $qtyBaseToDeduct  = (float) $qtyBase;
                $branchQtyCurrent = (float) ($product->$branchColumn ?? 0);
                $globalQtyCurrent = (float) ($product->quantity ?? 0);

                if ($branchQtyCurrent >= $qtyBaseToDeduct) {
                    $product->$branchColumn = $branchQtyCurrent - $qtyBaseToDeduct;
                } elseif ($globalQtyCurrent >= $qtyBaseToDeduct) {
                    $product->quantity = $globalQtyCurrent - $qtyBaseToDeduct;
                } else {
                    DB::rollBack();
                    Toastr::error(translate('لا توجد كمية كافية بالمخزن'));
                    return back();
                }

                $product->saveQuietly();

                // لوج حركة المنتج
                $productlogs[] = [
                    'product_id'     => $row['product_id'],
                    'quantity'       => $qtySell,
                    'base_quantity'  => $qtyBase,
                    'unit'           => $unit,
                    'purchase_price' => $weightedAvg,
                    'type'           => 4,
                    'seller_id'      => $sellerId,
                    'branch_id'      => $branchId,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }

            // تفاصيل الطلب
            $order_details[] = [
                'order_id'                 => $order_id,
                'product_id'               => $row['product_id'],
                'product_details'          => $product,
                'quantity'                 => $qtySell,
                'purchase_price'           => $isService ? 0 : $weightedAvg,
                'unit'                     => $isService ? 1 : $unit,
                'price'                    => $price,
                'extra_discount_on_product'=> (float)($row['extra_discount_on_product'] ?? 0),
                'tax_amount'               => (float)($row['tax_amount'] ?? 0),
                'discount_on_product'      => (float)($row['discount_on_product'] ?? 0),
                'discount_type'            => 'discount_type',
                'created_at'               => now(),
                'updated_at'               => now(),
            ];

            // مجاميع البيع
            $product_price    += $price * $qtySell;
            $product_discount += ((float)($row['discount_on_product'] ?? 0)) * $qtySell;
            $product_tax      += ((float)($row['tax_amount'] ?? 0)) * $qtySell;
        }

        // ===== إجماليات الفاتورة =====
        $net_sales     = $product_price - $product_discount;    // صافي بدون ضريبة
        $total_tax     = $product_tax;
        $invoice_total = $net_sales + $total_tax;

        // تثبيت قيم الطلب من عرض السعر
        $order->extra_discount  = $quotation->extra_discount ?? 0;
        $order->total_tax       = $quotation->total_tax;
        $order->order_amount    = $quotation->order_amount;     // معتمد من عرض السعر
        // لو نقدي، تجاهل collected_cash؛ لو آجل، خده إن وُجد
        $collectedNow = $cashMode === 1 ? 0.0 : (float)$request->input('collected_cash', 0);
        $order->collected_cash  = $collectedNow;
        $order->save();

        // حفظ تفاصيل ولوج المنتجات
        if (!empty($order_details)) $this->order_details->insert($order_details);
        if (!empty($productlogs))   $this->product_logs->insert($productlogs);

        // ========= قيد يومية =========
        $je = new \App\Models\JournalEntry();
        $je->entry_date = $date;
        $je->reference  = 'INV-' . $order_id . ($order->transaction_reference ? (' / REF: ' . $order->transaction_reference) : '');
        $je->description= $note;
        $je->created_by = $sellerId;
        $je->type       = 'sales';
        $je->branch_id  = $branchId;
        $je->save();

        if ($cashMode === 1) {
            // بيع نقدي: Dr Cash | Cr Sales + VAT
            $this->addJEDetailWithCostAuto($je->id, $accCashBankId, $order->order_amount, 0, $request->input('cost_id'), $note, $img, $date, $branchId);
            $this->addJEDetailWithCostAuto($je->id, $accSalesCode,     0, ($order->order_amount - $quotation->total_tax), $request->input('cost_id'), $note, $img, $date, $branchId);
            if ((float)$quotation->total_tax > 0) {
                $this->addJEDetailWithCostAuto($je->id, $accVatCode,   0, $quotation->total_tax, $request->input('cost_id'), 'ضريبة مخرجات فاتورة #'.$order_id, $img, $date, $branchId);
            }
        } else {
            // بيع آجل: Dr AR | Cr Sales + VAT
            $this->addJEDetailWithCostAuto($je->id, $accCustomerId, $order->order_amount, 0, $request->input('cost_id'), $note, $img, $date, $branchId);
            $this->addJEDetailWithCostAuto($je->id, $accSalesCode,     0, ($order->order_amount - $quotation->total_tax), $request->input('cost_id'), $note, $img, $date, $branchId);
            if ((float)$quotation->total_tax > 0) {
                $this->addJEDetailWithCostAuto($je->id, $accVatCode,   0, $quotation->total_tax, $request->input('cost_id'), 'ضريبة مخرجات فاتورة #'.$order_id, $img, $date, $branchId);
            }
            // تحصيل فوري على الآجل (إن وجد): Dr Cash | Cr AR
            if ($collectedNow > 0) {
                $this->addJEDetailWithCostAuto($je->id, $accCashBankId, $collectedNow, 0, $request->input('cost_id'), 'دفعة مُحصَّلة الآن من العميل', $img, $date, $branchId);
                $this->addJEDetailWithCostAuto($je->id, $accCustomerId, 0, $collectedNow, $request->input('cost_id'), 'دفعة مُحصَّلة الآن من العميل', $img, $date, $branchId);
            }
        }

        // COGS & Inventory (للمنتجات)
        if ($hasAnyProduct && $totalCogsForProducts > 0 && $accStockCode && $accCogsCode) {
            $this->addJEDetailWithCostAuto($je->id, $accCogsCode, $totalCogsForProducts, 0, $request->input('cost_id'), 'تكلفة بضاعة مباعة للفاتورة #'.$order_id, $img, $date, $branchId);
            $this->addJEDetailWithCostAuto($je->id, $accStockCode, 0, $totalCogsForProducts, $request->input('cost_id'), 'تخفيض مخزون للفاتورة #'.$order_id, $img, $date, $branchId);
        }

        // ========= Transactions (Log) =========
        if ($cashMode === 1) {
            // نقدي: Cash (Dr) ← Sales+VAT (Cr)
            $this->addTransectionWithCostAuto(4, $sellerId, $branchId, $accCashBankId, $accSalesCode, ($order->order_amount - $quotation->total_tax), 'بيع نقدي - مقابل إيرادات', $date, $user_id, $order_id, $img, $request->input('cost_id'));
            if ((float)$quotation->total_tax > 0) {
                $this->addTransectionWithCostAuto(4, $sellerId, $branchId, $accCashBankId, $accVatCode, $quotation->total_tax, 'بيع نقدي - ضريبة مخرجات', $date, $user_id, $order_id, $img, $request->input('cost_id'));
            }
        } else {
            // آجل: AR (Dr) ← Sales (Cr) + VAT (Cr)
            $this->addTransectionWithCostAuto(4, $sellerId, $branchId, $accCustomerId, $accSalesCode, ($order->order_amount - $quotation->total_tax), 'بيع آجل - مقابل إيرادات', $date, $user_id, $order_id, $img, $request->input('cost_id'));
            if ((float)$quotation->total_tax > 0) {
                $this->addTransectionWithCostAuto(4, $sellerId, $branchId, $accCustomerId, $accVatCode, $quotation->total_tax, 'بيع آجل - ضريبة مخرجات', $date, $user_id, $order_id, $img, $request->input('cost_id'));
            }
            if ($collectedNow > 0) {
                $this->addTransectionWithCostAuto(4, $sellerId, $branchId, $accCashBankId, $accCustomerId, $collectedNow, 'تحصيل فوري من عميل آجل', $date, $user_id, $order_id, $img, $request->input('cost_id'));
            }
        }
        // مخزون ↦ COGS (للمنتجات)
        if ($hasAnyProduct && $totalCogsForProducts > 0 && $accStockCode && $accCogsCode) {
            $this->addTransectionWithCostAuto(4, $sellerId, $branchId, $accCogsCode, $accStockCode, $totalCogsForProducts, 'تكلفة بضاعة مباعة', $date, $user_id, $order_id, $img, $request->input('cost_id'));
        }

        // تحديث ذمة العميل
        if ($customer) {
            if ($cashMode === 1) {
                // بيع نقدي: لا ذمة
                // لا تعديل
            } else {
                // آجل: ذمة = إجمالي - تحصيل فوري
                $customer->credit = ($customer->credit ?? 0) + $order->order_amount - $collectedNow;
                $customer->save();
            }
        }

        // تحديث حالة عرض السعر
        $quotation->status = 2;
        $quotation->save();

        // ========= إنشاء عقد أقساط (اختياري) =========
        $contract = null;
        if ($request->boolean('use_installments')) {
            $contract = new InstallmentContract();
            $contract->customer_id      = $quotation->user_id;
            $contract->total_amount     = (float)$request->total_paid_amount;
            $contract->start_date       = $request->start_date;
            $contract->duration_months  = (int)$request->duration_months;
            $contract->interest_percent = (float)$request->interest_percent;
            $contract->order_id         = $order_id;
            $contract->status           = $request->status ?? 'active';
            $contract->save();

            // المُكفِّل (اختياري)
            if ($request->filled('guarantor_id')) {
                $contract->guarantor_id = (int)$request->guarantor_id;
                $contract->save();
            } elseif ($request->filled('guarantor_name')) {
                $imagePaths = [];
                if ($request->hasFile('guarantor_images')) {
                    foreach ($request->file('guarantor_images') as $file) {
                        $imagePaths[] = $file->store('uploads/guarantors', 'public');
                    }
                }

                $guarantor = new Guarantor();
                $guarantor->contract_id       = $contract->id;
                $guarantor->name              = $request->guarantor_name;
                $guarantor->national_id       = $request->guarantor_national_id;
                $guarantor->phone             = $request->guarantor_phone;
                $guarantor->address           = $request->guarantor_address;
                $guarantor->job               = $request->guarantor_job;
                $guarantor->monthly_income    = $request->guarantor_monthly_income;
                $guarantor->relation          = $request->guarantor_relation;
                $guarantor->images            = json_encode($imagePaths);
                $guarantor->save();

                $contract->guarantor_id = $guarantor->id;
                $contract->save();
            }

            // جدول الأقساط
            $total  = (float) $request->total_paid_amount;
            $months = (int) $request->duration_months;
            $interestPercent = (float) $request->interest_percent;

            $totalWithInterest = $total * (1 + ($interestPercent / 100));
            $monthlyAmount     = round($totalWithInterest / max(1, $months), 2);

            $startDate = \Carbon\Carbon::parse($request->start_date);

            for ($i = 0; $i < $months; $i++) {
                $dueDate = $startDate->copy()->addMonths($i);

                $installment = new ScheduledInstallment();
                $installment->contract_id      = $contract->id;
                $installment->due_date         = $dueDate->toDateString();
                $installment->amount           = $request->filled('monthly_payment')
                                                    ? (float)$request->input('monthly_payment')
                                                    : $monthlyAmount;
                $installment->status           = 'pending';
                $installment->purchased_amount = 0;
                $installment->save();
            }
        }

        // ========= سداد أقساط تلقائي بمبلغ واحد (اختياري) =========
        $rawPayment = $request->input('installments_payment_amount', $request->input('payment_amount'));
        $autoPayAmount = (float)($rawPayment ?: 0);

        if ($autoPayAmount > 0) {
            // أولوية العقد: أوردر ثم أحدث Active للعميل
            if (!$contract) {
                $contract = InstallmentContract::where('order_id', $order_id)->first();
            }
            if (!$contract) {
                $contract = InstallmentContract::where('customer_id', $user_id)
                            ->where('status', 'active')
                            ->orderByDesc('id')
                            ->first();
            }
            if (!$contract) {
                throw new \Exception('لا يوجد عقد تقسيط نشط لتحصيل الأقساط عليه.');
            }

            $installments = ScheduledInstallment::where('contract_id', $contract->id)
                            ->whereIn('status', ['pending','partial'])
                            ->orderBy('due_date')->orderBy('id')
                            ->get();
            if ($installments->isEmpty()) {
                throw new \Exception('لا توجد أقساط مستحقة للسداد.');
            }

            $toPay = $autoPayAmount;
            foreach ($installments as $inst) {
                if ($toPay <= 0) break;

                $paidBefore   = (float)($inst->purchased_amount ?? 0);
                $remainForInst= max(0, (float)$inst->amount - $paidBefore);
                if ($remainForInst <= 0) {
                    $inst->status = 'paid';
                    $inst->save();
                    continue;
                }

                $chunk = min($toPay, $remainForInst);
                $inst->purchased_amount = $paidBefore + $chunk;
                $inst->status = ($inst->purchased_amount + 0.00001 >= (float)$inst->amount) ? 'paid' : 'partial';
                $inst->save();

                $toPay -= $chunk;
            }

            $actuallyPaid = $autoPayAmount - max(0, $toPay);
            if ($actuallyPaid <= 0) {
                throw new \Exception('المبلغ المُرسل لا يمكن تطبيقه على الأقساط.');
            }

            $payDate = now()->toDateString();

            // قيد واحد: Dr Cash | Cr AR
            $jePay = new \App\Models\JournalEntry();
            $jePay->entry_date = $payDate;
            $jePay->reference  = 'INST-AUTO-PAY-' . $contract->id;
            $jePay->description= 'تحصيل أقساط أوتوماتيكي لعقد #' . $contract->id;
            $jePay->created_by = $sellerId;
            $jePay->type       = 'installment_payment';
            $jePay->branch_id  = $branchId;
            $jePay->save();

            $this->addJEDetailWithCostAuto($jePay->id, $accCashBankId, $actuallyPaid, 0, $request->input('cost_id'), 'تحصيل أقساط', null, $payDate, $branchId);
            $this->addJEDetailWithCostAuto($jePay->id, $accCustomerId, 0, $actuallyPaid, $request->input('cost_id'), 'تحصيل أقساط', null, $payDate, $branchId);

            // Transection
            $this->addTransectionWithCostAuto(4, $sellerId, $branchId, $accCashBankId, $accCustomerId, $actuallyPaid, 'تحصيل أقساط أوتوماتيكي', $payDate, $user_id, $order_id, null, $request->input('cost_id'));

            // خفض ذمة العميل
            if ($customer) {
                $customer->credit = max(0, ($customer->credit ?? 0) - $actuallyPaid);
                $customer->save();
            }
        }

        DB::commit();

        Toastr::success(translate('تم تنفيذ الطلب بنجاح') . ' - رقم الطلب: ' . $order->id);
        return redirect()->back()->with('order_id', $order->id);

    } catch (\Exception $e) {
        DB::rollBack();
        Toastr::error(translate('order_failed_warning') . ' ' . $e->getMessage());
        return back();
    }
}

/* ===================== Helpers ===================== */

/**
 * يرجّع مركز التكلفة النهائي لهذا السطر:
 * 1) لو فيه costId مبعوت → نستخدمه
 * 2) غير كده → نقرأ default_cost_center_id من جدول accounts
 * 3) لو مفيش → NULL
 */
private function resolveCostCenter(?int $accountId, $explicitCostId = null): ?int
{
    if ($explicitCostId) {
        return (int) $explicitCostId;
    }
    if ($accountId) {
        $acc = \App\Models\Account::select('id','default_cost_center_id')->find($accountId);
        if ($acc && $acc->default_cost_center_id) {
            return (int) $acc->default_cost_center_id;
        }
    }
    return null;
}

/**
 * إضافة سطر تفصيلي لقيد اليومية مع منطق مركز التكلفة.
 * يُستخدم هنا بتوجيه صريح (Debit/Credit) وبالتالي الإيراد لا يظهر مدين إطلاقًا.
 */
private function addJEDetailWithCostAuto($jeId, $accountId, $debit, $credit, $explicitCostId, $desc, $attachment, $date, $branchId)
{
    $costId = $this->resolveCostCenter($accountId, $explicitCostId);

    $jed = new \App\Models\JournalEntryDetail();
    $jed->journal_entry_id = $jeId;
    $jed->account_id       = $accountId;
    $jed->debit            = (float)$debit;
    $jed->credit           = (float)$credit;
    $jed->cost_center_id   = $costId;   // قد تكون NULL
    $jed->description      = $desc;
    $jed->attachment_path  = $attachment;
    $jed->entry_date       = $date;
    $jed->save();

    return $jed;
}

/**
 * إنشاء سجل Transection كسطر "مدين ← دائن" بمبلغ واحد.
 * بنسجّل كلا الحقلين (debit و credit) بنفس القيمة لمنع أي تقارير تعتبر الإيرادات مدينة.
 */
private function addTransectionWithCostAuto($tranType, $sellerId, $branchId, $debitAcc, $creditAcc, $amount, $desc, $date, $customerId, $orderId, $img = null, $explicitCostId = null)
{
    // اختيار مركز التكلفة: المرسل صريحًا، وإلا من الحساب المدين، وإلا من الدائن.
    $costId = $this->resolveCostCenter($debitAcc, $explicitCostId)
           ?? $this->resolveCostCenter($creditAcc, null);

    $t = new \App\Models\Transection;
    $t->tran_type      = $tranType;
    $t->seller_id      = $sellerId;
    $t->branch_id      = $branchId;
    $t->cost_id        = $costId;                 // قد تكون NULL
    $t->account_id     = $debitAcc;               // الحساب المدين (من)
    $t->account_id_to  = $creditAcc;              // الحساب الدائن (إلى)
    $t->amount         = (float)$amount;
    $t->description    = $desc;
    $t->debit          = (float)$amount;          // ✅ تسجيل المدين
    $t->credit         = (float)$amount;          // ✅ وتسجيل الدائن
    $t->date           = $date;
    $t->customer_id    = $customerId;
    $t->order_id       = $orderId;
    $t->img            = $img;
    $t->save();

    return $t;
}


public function executequotation_service(Request $request,$quotation_id)
{
    // Retrieve cart session ID and determine user type (wc or sc)
$type=4;
$quotation=Quotation::where('id',$quotation_id)->first();

$quotationdetails=QuotationDetail::where('order_id',$quotation->id)->get();
$user_id=$quotation->user_id;
    $product_price = 0;
    $order_details = [];
    $product_discount = 0;
    $product_tax = 0;
    $ext_discount = 0;
    $coupon_discount = $cart['coupon_discount'] ?? 0;

    // Generate a unique order ID
    $order_id = 100000 + $this->order->all()->count() + 1;
    if ($this->order->find($order_id)) {
        $order_id = $this->order->orderBy('id', 'DESC')->first()->id + 1;
    }

    // Image upload (if provided)
    $img = null;
    if ($request->hasFile('img')) {
        $file = $request->file('img');
        $path = $file->store('shop', 'public'); // Stores in the 'shop' directory on public disk
        $img = $path;
    }
    // Generate QR code for the order
    $qrcode_data = "https://testnewpos.iqbrandx.com/real/invoicea2/" . $order_id;
    $qrCode = new QrCode($qrcode_data);
    $writer = new PngWriter();
    $qrcode_image = $writer->write($qrCode)->getString();
    $qrcode_path = "qrcodes/order_$order_id.png";
    Storage::disk('public')->put($qrcode_path, $qrcode_image);
    $admin = Seller::where('id', auth('admin')->user()->id)->first();

    // Create a new order instance and set its basic properties
    $order = $this->order;
    $order->id = $order_id;
    $order->user_id = $quotation->user_id;
    $order->payment_id = $request->payment_id;
    $order->type = 4;
    $order->order_type=$quotation->quotation_type;
    $order->cash = $request->cash;
    $order->date = $request->date;
                                            $order->order_type = 'service';

    $order->qrcode = $qrcode_path; // Save QR code path
    $order->owner_id = auth('admin')->user()->id;
    $order->branch_id = auth('admin')->user()->branch_id;
    $order->transaction_reference = $request->transaction_reference ?? 0;
    $order->created_at = now();
    $order->updated_at = now();
    $order->img = $img;

    // Initialize accumulator for total price from stock batches across all products
    $totalPriceAllProducts = 0;
    $productlogs = []; // to log product transactions

    // Process each cart item
    foreach ($quotationdetails as $c) {
    
        $product = $this->product->find($c['product_id']);
        if (!$product) {
            continue;
        }
        // Calculate tax and discount for this item
        $taxafter = Helpers::tax_calculate($product, $c['price']);
    
            $tax_amount = $taxafter;
            $discount_on_product = Helpers::discount_calculate($product, $taxafter);
        
        $price = $c['price'];
        // Recalculate discount based on original price
        $discount_on_product = Helpers::discount_calculate($product, $c['price']);
        $taxafter = $c['price'] - $discount_on_product;

            $quantityfinal = $c['quantity'];
        
        // المتغيرات لجمع البيانات من الدفعات التي ستُستهلك
        $weightedSumConsumed = 0;
        $totalConsumedQty = 0;
        $remainingQty = $quantityfinal;
       
        $weightedAvg = $totalConsumedQty > 0 ? $weightedSumConsumed / $totalConsumedQty : 0;
        // هذا المتوسط الوزني سيكون purchase_price في تفاصيل الطلب
        // *** نهاية حساب متوسط سعر الشراء للصفوف المستخدمة ***
                $product_discount += $c['discount_on_product'] * $c['quantity'];
       $subtotal = floatval($request->subtotal);

$discRatio = $subtotal > 0
    ? ($request->extra_discount / $subtotal) * 100
    : null;
            $extraDiscAmt = ($discRatio / 100) * $c['price'];
        // Build order details for this product using the calculated weighted average purchase price
        $or_d = [
            'order_id'            => $order->id,
            'product_id'          => $c['product_id'],
            'product_details'     => $product,
            'quantity'            => $c['quantity'],
            'purchase_price'      => $weightedAvg,
            'price'               => $c['price'],
          'extra_discount_on_product'=> $extraDiscAmt,
            'tax_amount'          => $c['tax_amount'],
            'discount_on_product' => $c['discount_on_product'],
            'discount_type'       => 'discount_type',
            'created_at'          => now(),
            'updated_at'          => now()
        ];
        $order_details[] = $or_d;
        $product_price += $price * $c['quantity'];
        $product_discount += $c['discount_on_product'] * $c['quantity'];
        $product_tax += $c['tax_amount'] * $c['quantity'];
$type==4;
$user_id=$quotation->user_id;
        // For sales transactions (type 4 or type 1), update customer price record
        if ($type == 4 || $type == 1) {
            $customerPrice = \App\Models\CustomerPrice::where('product_id', $c['product_id'])
                                ->where('customer_id', $quotation->user_id)
                                ->first();
            if ($customerPrice) {
        
                    $customerPrice->price = $c['price'] - $discount_on_product + $c['tax_amount'];
                
                $customerPrice->save();
            } else {
                $customerPrice = new \App\Models\CustomerPrice;
                $customerPrice->product_id = $c['product_id'];
                $customerPrice->customer_id = $quotation->user_id;
             
                    $customerPrice->price = $c['price'] - $discount_on_product + $c['tax_amount'];
                
                $customerPrice->save();
            }
        }

     

        $product->save();

        // Log this product transaction
     

     
    } // End foreach cart

    // Calculate overall totals
    $total_price = $product_price - $product_discount;

            $order->extra_discount = $quotation->extra_discount??0;

    $total_tax_amount = $product_tax;
    $grand_total = $total_price + $total_tax_amount - $ext_discount - $coupon_discount;

    try {
        
        // Transaction handling based on the order type
        if ($type == 4|| $type == 1) {
            // Handle sales (type 4 or 1) for both credit and cash
            if ($type == 4 || $type == 1) {
                if ($request->cash == 2) {
                    $branch = \App\Models\Branch::where('id', auth('admin')->user()->branch_id)->first();
                    $remaining_balance = $quotation->order_amount - $quotation->transaction_reference;
                    $customer = $this->customer->where('id', $quotation->user_id)->first();
                    $payable_account_to = \App\Models\Account::find($customer->account_id);
                    $payable_account = \App\Models\Account::find(40);
                    // First transaction
                    $payable_transaction = new \App\Models\Transection;
                    $payable_transaction->tran_type =4;
                    $payable_transaction->seller_id = auth('admin')->user()->id;
                    $payable_transaction->branch_id = auth('admin')->user()->branch_id;
                    $payable_transaction->cost_id = $request->cost_id;
                    $payable_transaction->account_id = 40;
                    $payable_transaction->account_id_to = $customer->account_id;
                    $payable_transaction->amount = $quotation->order_amount - $quotation->total_tax;
                    $payable_transaction->description = 'فاتورة مبيعات';
                    $payable_transaction->debit = $quotation->order_amount - $quotation->total_tax;
                    $payable_transaction->credit = 0;
                    $payable_transaction->balance = $payable_account->balance + ($quotation->order_amount - $quotation->total_tax);
                    $payable_transaction->debit_account = 0;
                    $payable_transaction->credit_account = $quotation->order_amount - $quotation->total_tax;
                    $payable_transaction->balance_account = $payable_account_to->balance + ($quotation->order_amount - $quotation->total_tax);
                    $payable_transaction->date = date("Y/m/d");
                    $payable_transaction->customer_id = $quotation->user_id;
                    $payable_transaction->order_id = $order_id;
                    $payable_transaction->img = $img;
                    $payable_transaction->save();

                    $payable_account->balance += ($quotation->order_amount - $quotation->total_tax);
                    $payable_account->total_in += ($quotation->order_amount - $quotation->total_tax);
                    $payable_account->save();
                    $payable_account_to->balance += ($quotation->order_amount - $quotation->total_tax);
                    $payable_account_to->total_in += ($quotation->order_amount - $quotation->total_tax);
                    $payable_account_to->save();

                    // Second transaction
                    $payable_account_2 = \App\Models\Account::find(28);
                    $payable_account_to_2 = \App\Models\Account::find($customer->account_id);
                    $payable_transaction_2 = new \App\Models\Transection;
                    $payable_transaction_2->tran_type =4;
                    $payable_transaction_2->seller_id = auth('admin')->user()->id;
                    $payable_transaction_2->branch_id = auth('admin')->user()->branch_id;
                    $payable_transaction_2->cost_id = $request->cost_id;
                    $payable_transaction_2->account_id = 28;
                    $payable_transaction_2->account_id_to = $customer->account_id;
                    $payable_transaction_2->amount = $quotation->total_tax;
                    $payable_transaction_2->description = 'ضرائب مستحقة لفاتورة المبيعات';
                    $payable_transaction_2->debit = $quotation->total_tax;
                    $payable_transaction_2->credit = 0;
                    $payable_transaction_2->balance = $payable_account_2->balance + $quotation->total_tax;
                    $payable_transaction_2->debit_account = 0;
                    $payable_transaction_2->credit_account = $quotation->total_tax;
                    $payable_transaction_2->balance_account = $payable_account_to_2->balance + $quotation->total_tax;
                    $payable_transaction_2->date = date("Y/m/d");
                    $payable_transaction_2->customer_id = $quotation->user_id;
                    $payable_transaction_2->order_id = $order_id;
                    $payable_transaction_2->img = $img;
                    $payable_transaction_2->save();

                    $payable_account_2->balance += $quotation->total_tax;
                    $payable_account_2->total_in += $quotation->total_tax;
                    $payable_account_2->save();
                    $payable_account_to_2->balance += $quotation->total_tax;
                    $payable_account_to_2->total_in += $quotation->total_tax;
                    $payable_account_to_2->save();

          
                    $customer->credit += $remaining_balance;
                    $customer->save();

                    $order->total_tax = $quotation->total_tax;
                    $order->order_amount = $quotation->order_amount;
                    $order->collected_cash = $request->collected_cash;
                                                            $order->order_type = 'service';

                    $order->transaction_reference = $request->collected_cash;
                    $order->type = 4 ;
                    $order->date = $request->date;
                    $order->save();
                    $this->order_details->insert($order_details);
                }elseif($request->cash == 1 && $type==4){
                    $branch = \App\Models\Branch::where('id', auth('admin')->user()->branch_id)->first();
                    $remaining_balance = $quotation->order_amount - $quotation->transaction_reference;
                    $customer = $this->customer->where('id', $user_id)->first();
                    $payable_account_to = \App\Models\Account::find($request->payment_id);
                    $payable_account = \App\Models\Account::find(40);
                    // First transaction
                    $payable_transaction = new \App\Models\Transection;
                    $payable_transaction->tran_type = 4 ;
                    $payable_transaction->seller_id = auth('admin')->user()->id;
                    $payable_transaction->branch_id = auth('admin')->user()->branch_id;
                    $payable_transaction->cost_id = $request->cost_id;
                    $payable_transaction->account_id = 40;
                    $payable_transaction->account_id_to = $request->payment_id;
                    $payable_transaction->amount = $quotation->order_amount - $quotation->total_tax;
                    $payable_transaction->description = 'فاتورة مبيعات';
                    $payable_transaction->debit = $quotation->order_amount - $quotation->total_tax;
                    $payable_transaction->credit = 0;
                    $payable_transaction->balance = $payable_account->balance + ($quotation->order_amount - $quotation->total_tax);
                    $payable_transaction->debit_account = 0;
                    $payable_transaction->credit_account = $quotation->order_amount - $quotation->total_tax;
                    $payable_transaction->balance_account = $payable_account_to->balance + ($quotation->order_amount - $quotation->total_tax);
                    $payable_transaction->date = date("Y/m/d");
                    $payable_transaction->customer_id = $user_id;
                    $payable_transaction->order_id = $order_id;
                    $payable_transaction->img = $img;
                    $payable_transaction->save();

                    $payable_account->balance += ($quotation->order_amount - $quotation->total_tax);
                    $payable_account->total_in += ($quotation->order_amount - $quotation->total_tax);
                    $payable_account->save();
                    $payable_account_to->balance += ($quotation->order_amount - $quotation->total_tax);
                    $payable_account_to->total_in += ($quotation->order_amount - $quotation->total_tax);
                    $payable_account_to->save();

                    // Second transaction
                    $payable_account_2 = \App\Models\Account::find(28);
                    $payable_account_to_2 = \App\Models\Account::find($request->payment_id);
                    $payable_transaction_2 = new \App\Models\Transection;
                    $payable_transaction_2->tran_type =  4 ;
                    $payable_transaction_2->seller_id = auth('admin')->user()->id;
                    $payable_transaction_2->branch_id = auth('admin')->user()->branch_id;
                    $payable_transaction_2->cost_id = $request->cost_id;
                    $payable_transaction_2->account_id = 28;
                    $payable_transaction_2->account_id_to = $request->payment_id;
                    $payable_transaction_2->amount = $quotation->total_tax;
                    $payable_transaction_2->description = 'ضرائب فاتورة المبيعات';
                    $payable_transaction_2->debit = $quotation->total_tax;
                    $payable_transaction_2->credit = 0;
                    $payable_transaction_2->balance = $payable_account_2->balance + $quotation->total_tax;
                    $payable_transaction_2->debit_account = 0;
                    $payable_transaction_2->credit_account = $quotation->total_tax;
                    $payable_transaction_2->balance_account = $payable_account_to_2->balance + $quotation->total_tax;
                    $payable_transaction_2->date = date("Y/m/d");
                    $payable_transaction_2->customer_id = $user_id;
                    $payable_transaction_2->order_id = $order_id;
                    $payable_transaction_2->img = $img;
                    $payable_transaction_2->save();

                    $payable_account_2->balance += $quotation->total_tax;
                    $payable_account_2->total_in += $quotation->total_tax;
                    $payable_account_2->save();
                    $payable_account_to_2->balance += $quotation->total_tax;
                    $payable_account_to_2->total_in += $quotation->total_tax;
                    $payable_account_to_2->save();

                    // Third transaction
                    $payable_account_3 = \App\Models\Account::find($branch->account_stock_Id);
                    $payable_account_to_3 = \App\Models\Account::find(47);
                    $payable_transaction_2 = new \App\Models\Transection;
                    $payable_transaction_2->tran_type = 4;
                    $payable_transaction_2->seller_id = auth('admin')->user()->id;
                    $payable_transaction_2->branch_id = auth('admin')->user()->branch_id;
                    $payable_transaction_2->cost_id = $request->cost_id;
                    $payable_transaction_2->account_id = $branch->account_stock_Id;
                    $payable_transaction_2->account_id_to = 47;
                    $payable_transaction_2->amount = $totalPriceAllProducts;
                    $payable_transaction_2->description = 'قيد المخزون الخاص  المبيعات';
                    $payable_transaction_2->debit = $totalPriceAllProducts;
                    $payable_transaction_2->credit = 0;
                    $payable_transaction_2->balance = $payable_account_3->balance - $totalPriceAllProducts;
                    $payable_transaction_2->debit_account = 0;
                    $payable_transaction_2->credit_account = $totalPriceAllProducts;
                    $payable_transaction_2->balance_account = $payable_account_to_3->balance + $totalPriceAllProducts;
                    $payable_transaction_2->date = date("Y/m/d");
                    $payable_transaction_2->customer_id = $quotation->user_id;
                    $payable_transaction_2->order_id = $order_id;
                    $payable_transaction_2->img = $img;
                    $payable_transaction_2->save();

                    $payable_account_3->balance -= $totalPriceAllProducts;
                    $payable_account_3->total_out += $totalPriceAllProducts;
                    $payable_account_3->save();
                    $payable_account_to_3->balance += $totalPriceAllProducts;
                    $payable_account_to_3->total_in += $totalPriceAllProducts;
                    $payable_account_to_3->save();
                    $customer->credit += $remaining_balance;
                    $customer->save();

                    $order->total_tax = $quotation->total_tax;
                    $order->order_amount = $quotation->order_amount;
                                        $order->order_type = 'service';
                    $order->coupon_discount_amount = $coupon_discount;
                    $order->collected_cash = $request->collected_cash;
                    $order->transaction_reference = $request->collected_cash;
                    $order->type =  4 ;
                    $order->date = $request->date;
                    $order->save();

                    $this->order_details->insert($order_details);

                }else{
                    $branch = \App\Models\Branch::where('id', auth('admin')->user()->branch_id)->first();
                    $remaining_balance = $quotation->order_amount - $request->transaction_reference;
                    $customer = $this->customer->where('id', $quotation->user_id)->first();
                    $payable_account_to = \App\Models\Account::find(92);
                    $payable_account = \App\Models\Account::find(40);
                    // First transaction
                    $payable_transaction = new \App\Models\Transection;
                    $payable_transaction->tran_type = 4;
                    $payable_transaction->seller_id = auth('admin')->user()->id;
                    $payable_transaction->branch_id = auth('admin')->user()->branch_id;
                    $payable_transaction->cost_id = $request->cost_id;
                    $payable_transaction->account_id = 40;
                    $payable_transaction->account_id_to = $payable_account_to->id;
                    $payable_transaction->amount = $quotation->order_amount - $quotation->total_tax;
                    $payable_transaction->description = 'فاتورة مبيعات';
                    $payable_transaction->debit = $quotation->order_amount - $quotation->total_tax;
                    $payable_transaction->credit = 0;
                    $payable_transaction->balance = $payable_account->balance + ($quotation->order_amount - $quotation->total_tax);
                    $payable_transaction->debit_account = 0;
                    $payable_transaction->credit_account = $quotation->order_amount - $quotation->total_tax;
                    $payable_transaction->balance_account = $payable_account_to->balance + ($quotation->order_amount - $quotation->total_tax);
                    $payable_transaction->date = date("Y/m/d");
                    $payable_transaction->customer_id = $quotation->user_id;
                    $payable_transaction->order_id = $order_id;
                    $payable_transaction->img = $img;
                    $payable_transaction->save();

                    $payable_account->balance += ($quotation->order_amount - $quotation->total_tax);
                    $payable_account->total_in += ($quotation->order_amount - $quotation->total_tax);
                    $payable_account->save();
                    $payable_account_to->balance += ($quotation->order_amount - $quotation->total_tax);
                    $payable_account_to->total_in += ($quotation->order_amount - $quotation->total_tax);
                    $payable_account_to->save();

                    // Second transaction
                    $payable_account_2 = \App\Models\Account::find(28);
                    $payable_account_to_2 = \App\Models\Account::find(92);
                    $payable_transaction_2 = new \App\Models\Transection;
                    $payable_transaction_2->tran_type = ($type == 1) ? 4 : $request->type;
                    $payable_transaction_2->seller_id = auth('admin')->user()->id;
                    $payable_transaction_2->branch_id = auth('admin')->user()->branch_id;
                    $payable_transaction_2->cost_id = $request->cost_id;
                    $payable_transaction_2->account_id = 28;
                    $payable_transaction_2->account_id_to = $payable_account_to_2->id;
                    $payable_transaction_2->amount = $quotation->total_tax;
                    $payable_transaction_2->description = 'ضرائب فاتورة المبيعات';
                    $payable_transaction_2->debit = $quotation->total_tax;
                    $payable_transaction_2->credit = 0;
                    $payable_transaction_2->balance = $payable_account_2->balance + $quotation->total_tax;
                    $payable_transaction_2->debit_account = 0;
                    $payable_transaction_2->credit_account = $quotation->total_tax;
                    $payable_transaction_2->balance_account = $payable_account_to_2->balance + $quotation->total_tax;
                    $payable_transaction_2->date = date("Y/m/d");
                    $payable_transaction_2->customer_id = $quotation->user_id;
                    $payable_transaction_2->order_id = $order_id;
                    $payable_transaction_2->img = $img;
                    $payable_transaction_2->save();

                    $payable_account_2->balance += $quotation->total_tax;
                    $payable_account_2->total_in += $quotation->total_tax;
                    $payable_account_2->save();
                    $payable_account_to_2->balance += $quotation->total_tax;
                    $payable_account_to_2->total_in += $quotation->total_tax;
                    $payable_account_to_2->save();

                    $customer->credit += $remaining_balance;
                    $customer->save();

                    $order->total_tax = $quotation->total_tax;
                    $order->order_amount = $quotation->order_amount;
                    $order->coupon_discount_amount = $coupon_discount;
                    $order->collected_cash = $quotation->order_amount;
                    $order->transaction_reference =$quotation->order_amount;
                    $order->type =  4;
                                                            $order->order_type = 'service';

                    $order->date = $request->date;
                    $order->save();
                    $this->order_details->insert($order_details);

                }
        }
}
$quotation->status=2;
$quotation->save();
        // Finalize order: clear cart and return response
        if ($type == 1) {
        session()->forget($cart_id);

            return response()->json([
                'success'  => true,
                'order_id' => $order->id
            ]);
        } else {
            Toastr::success(translate('تم تنفيذ الطلب بنجاح') . ' - رقم الطلب: ' . $order->id);
            return redirect()->back()->with('order_id', $order->id);
        }
    } catch (\Exception $e) {
        DB::rollBack();
        Toastr::error(translate('order_failed_warning' . $e->getMessage()));
        return back();
    }
}
}
