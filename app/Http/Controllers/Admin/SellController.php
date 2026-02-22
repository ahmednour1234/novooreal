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
use App\Models\Guarantor;
use App\Models\ScheduledInstallment;
use App\Models\InstallmentContract;
use App\Models\Quotation;
use App\Models\ProductLog;
use App\Models\QuotationDetail;
use Barryvdh\DomPDF\Facade\Pdf;            
use Illuminate\Support\Facades\Mail;
use App\Mail\QuotationPdfMail;                
use App\CPU\Helpers;
use function App\CPU\translate;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Brian2694\Toastr\Facades\Toastr;
use App\Services\WhatsAppWebService;
use Carbon\Carbon;


class SellController extends Controller
{
        public function __construct(
        private Order $order,
                private Customer $customer,
                                private InstallmentContract $installmentcontract,
        private OrderDetail $order_details,
                private Product $product,
                private ProductLog $product_logs,

    ){}
    /**
     * عرض صفحة إنشاء عرض السعر (Quotation).
     */
public function create(Request $request)
{
    $adminId = Auth::guard('admin')->id();
    $admin   = DB::table('admins')->where('id', $adminId)->first();

    if (!$admin) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $roleId = $admin->role_id;
    $role   = DB::table('roles')->where('id', $roleId)->first();

    if (!$role) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $decodedData = json_decode($role->data, true);
    if (is_string($decodedData)) {
        $decodedData = json_decode($decodedData, true);
    }

    if (!is_array($decodedData) || !in_array("pos4.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    // جلب نوع الفاتورة (منتج أو خدمة)
    $orderType = $request->input('order_type', 'product'); // الافتراضي 'product'

    if (!in_array($orderType, ['product', 'service'])) {
        Toastr::error('نوع الفاتورة غير صحيح.');
        return redirect()->back();
    }

    $customers     = Customer::all();
    $products      = Product::where('product_type', $orderType)->get();
    $startDate     = $request->input('start_date');
    $endDate       = $request->input('end_date');
    $cachedInvoice = session()->get('Quotation');
    $cart          = session()->get('cart', []);

    $cost_centers = \App\Models\CostCenter::where('active', 1)
        ->doesntHave('children')
        ->get();

    $accounts = \App\Models\Account::where(function ($q) {
        $q->whereIn('id', [8, 14])->orWhereIn('parent_id', [8, 14]);
    })
        ->doesntHave('children')
        ->orderBy('id')
        ->get();

    return view('admin-views.sell.create', compact(
        'customers',
        'products',
        'startDate',
        'endDate',
        'cachedInvoice',
        'cart',
        'accounts',
        'cost_centers',
        'orderType'
    ));
}

       public function create_type(Request $request)
    {
 

        return view('admin-views.sell.create_type');
    }
public function store(Request $request)
{
    $validated = $request->validate([
        'customer_id'         => 'required|exists:customers,id',
        'products'            => 'required|array|min:1',
        'products.*.id'       => 'required|exists:products,id',
        'products.*.quantity' => 'required|numeric|min:1',
        'products.*.price'    => 'required|numeric|min:0',
        'products.*.unit'     => 'nullable|in:0,1',
        'products.*.tax'      => 'required|numeric|min:0',
        'products.*.discount' => 'nullable|numeric|min:0',
        'products.*.default_discount' => 'nullable|numeric|min:0',
        'products.*.extra_discount'   => 'nullable|numeric|min:0',
        'cash'                => 'required|in:1,2',
        'order_amount'        => 'required|numeric|min:0',
        'date'                => 'required|date',
        'type'                => 'required|in:0,8,12',
        'img'                 => 'nullable|image|max:2048',
    ]);

    $quotationType = $request->get('order_type', 'service'); // default to product if not provided

    DB::beginTransaction();

    try {
        $orderId = Quotation::max('id') + 1 ?: 100001;
        $imgPath = null;

        if ($request->hasFile('img')) {
            $imgPath = $request->file('img')->store('quotations/images', 'public');
        }

        $totalPrice = 0;
        $totalTax   = 0;
        $totalDisc  = 0;
        $details    = [];

        foreach ($validated['products'] as $item) {
            $product = Product::findOrFail($item['id']);

            $linePrice    = $item['price'] * $item['quantity'];
            $lineTax      = $item['tax']   * $item['quantity'];
            $lineDiscount = ($item['default_discount'] ?? 0) * $item['quantity'];

            $totalPrice += $linePrice;
            $totalTax   += $lineTax;
            $totalDisc  += $lineDiscount;

            $details[] = [
                'order_id'            => $orderId,
                'product_id'          => $item['id'],
                'product_details'     => $product->toJson(),
                'quantity'            => $item['quantity'],
                'unit'                => $request->order_type == 'service' ? 0 : $item['unit'],
                'price'               => $item['price'],
                'tax_amount'          => $item['tax'],
                'discount_on_product' => $item['default_discount'] ?? 0,
                'extra_discount_on_product' => $item['extra_discount'] ?? 0,
                'discount_type'       => 'product_level',
                'created_at'          => now(),
                'updated_at'          => now(),
            ];
        }

        $grandTotal = $totalPrice + $totalTax - $totalDisc;

        $quotation = new Quotation();
        $quotation->id             = $orderId;
        $quotation->user_id        = $validated['customer_id'];
        $quotation->type           = $validated['type'];
        $quotation->total_tax      = $totalTax;
        $quotation->order_amount   = $request->order_amount;
        $quotation->extra_discount = $request->extra_discount;
        $quotation->cash           = $validated['cash'];
        $quotation->date           = $validated['date'];
        $quotation->img            = $imgPath;
        $quotation->quotation_type	 = $quotationType; // ← حفظ نوع الفاتورة (خدمة/منتج)
        $quotation->owner_id       = auth('admin')->id();
        $quotation->branch_id      = auth('admin')->user()->branch_id;
        $quotation->save();

        QuotationDetail::insert($details);

        DB::commit();

        Toastr::success(__('تم حفظ الفاتورة كمسودة بنجاح'));
        return redirect()
            ->route('admin.sells.show', ['id' => $quotation->id])
            ->with('success', __('تم حفظ الفاتورة كمسودة بنجاح'));

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Quotation store error: ' . $e->getMessage());
Toastr::error(
    'خطأ: ' . $e->getMessage() . ' في الملف: ' . $e->getFile() . ' على السطر: ' . $e->getLine(),
    __('حدث خطأ أثناء المعالجة')
);
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
        return view('admin-views.sell.index', compact('quotations'));
    }

    /**
     * عرض تفاصيل عرض سعر واحد.
     */

        public function drafts()
    {
        $drafts = Quotation::where('type', 8)->where('status',1)->where('owner_id',auth('admin')->user()->id)
                    ->with('customer')
                    ->orderBy('date','desc')
                    ->paginate(15);

        return view('admin-views.sell.drafts', compact('drafts'));
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
        return view('admin-views.sell.show', compact('quotation','cost_centers','accounts','guarantors'));
    }

    // 3) نموذج التعديل
    public function edit($id)
    {
        $quotation = Quotation::with('details')->findOrFail($id);
        $products  = Product::all();
        $customers    = Customer::all();

        return view('admin-views.sell.edit', compact('quotation','products','customers'));
    }

    // 4) حفظ التعديلات
    public function update(Request $request, $id)
    {
        
        $request->validate([
            'products'            => 'required|array|min:1',
            'products.*.id'       => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.price'    => 'required|numeric|min:0',
            'products.*.unit'     => 'required|in:0,1',
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

            // إعادة بناء التفاصيل والجمعيات
            $price = $tax = $disc = 0;
            $details = [];
            foreach ($request->products as $item) {
                $price += $item['price'] * $item['quantity'];
                $tax   += $item['tax']   * $item['quantity'];
                $discount = $item['discount'] ?? 0;
                $disc  += $discount      * $item['quantity'];

                $details[] = [
                    'order_id'            => $id,
                    'product_id'          => $item['id'],
                    'product_details'     => Product::find($item['id'])->toJson(),
                    'quantity'            => $item['quantity'],
                    'unit'                => $item['unit'],
                    'price'               => $item['price'],
                    'tax_amount'          => $item['tax'],
                    'discount_on_product' => $discount,
                    'discount_type'       => 'product_level',
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];
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
                Toastr::success(translate(' تم تحديث العرض.'));

            DB::commit();
            return redirect()->route('admin.sell.drafts')
                             ->with('success','تم تحديث المسودة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
                                    Toastr::success(translate(' حدث خطأ أثناء المعالجة.'));

            dd($e);
            \Log::error($e->getMessage());
            return back()->withErrors(['error'=>'حدث خطأ أثناء التحديث']);
        }
    }
    public function destroy($id)
{
    DB::beginTransaction();

    try {
        // جلب العرض مع تفاصيله
        $quotation = Quotation::findOrFail($id);

        // حذف التفاصيل المرتبطة
        QuotationDetail::where('order_id', $id)->delete();

        // حذف العرض نفسه
        $quotation->delete();
     Toastr::success(translate(' تم تحديث العرض.'));

            DB::commit();
            return redirect()->route('admin.sell.drafts')
                             ->with('success','تم حذف المسودة بنجاح');
   
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
        $type      = 4; // بيع
        $quotation = Quotation::findOrFail($quotation_id);
        $details   = QuotationDetail::where('order_id', $quotation->id)->get();

        $admin     = auth('admin')->user();
        $sellerId  = $admin->id;
        $branchId  = $admin->branch_id;
        $branch    = \App\Models\Branch::find($branchId);

        $date      = $quotation->date; // التاريخ القادم من عرض السعر
        $user_id   = $quotation->user_id;
        $customer  = $this->customer->find($user_id);
        $accCustomerId = $customer?->account_id;

        // ملاحظة
        $note = trim((string)$request->input('note'));
        if ($note === '') {
            $note = 'فاتورة مبيعات آجل ناتجة عن عرض سعر #' . $quotation->id;
        }

        // أكواد الحسابات (عوِّض بحسب نظامك)
        $accSalesCode  = 40;                                 // إيرادات/مبيعات
        $accVatCode    = 28;                                 // ضريبة مخرجات
        $accCogsCode   = 47;                                 // تكلفة بضاعة مباعة
        $accStockCode  = $branch?->account_stock_Id;         // مخزون الفرع
        $accCashBankId = $request->payment_id ?: 92;         // نقدية/بنك (للتحصيل الفوري لو وُجد)

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
        $order->type                 = $type;                 // 4 = بيع
        $order->cash                 = 2;                     // آجل
        $order->date                 = $date;                 // تاريخ عرض السعر
        $order->qrcode               = $qrcode_path;
        $order->owner_id             = $sellerId;
        $order->branch_id            = $branchId;
        $order->transaction_reference= $request->input('transaction_reference', '');
        $order->img                  = $img;
        $order->note                 = $note;

        // ========= حساب المجاميع ومعالجة المخزون/الخدمة =========
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

            $isService = (string)($product->product_type ?? '') == 'service';

            $price   = (float)$row['price'];
            $qtySell = (int)$row['quantity'];
            $unit    = (int)($row['unit'] ?? 1); // للخدمة نتعامل ككبرى دائمًا

            if ($isService) {
                // خدمة: لا وحدات ولا مخزون ولا COGS
                $qtyBase     = $qtySell;
                $weightedAvg = 0;

            } else {
                $hasAnyProduct = true;

                // كمية بوحدة الأساس (FIFO لاحقًا)
                $unitValue = max(1, (int)($product->unit_value ?? 1));
                $qtyBase   = ($unit == 0) ? ($qtySell / $unitValue) : $qtySell;

                // ====== FIFO ======
                $stockBatches = \App\Models\StockBatch::where('product_id', $row['product_id'])
                                ->where('branch_id', $branchId)
                                ->where('quantity', '>', 0)
                                ->orderBy('created_at')
                                ->get();

                $remaining = $qtyBase;
                $consumed = 0;
                $weightedSum = 0;
          if (!$isService) {
    // 🔒 اقفل صف المنتج أثناء التحديث لتجنب السباقات
    $product = $this->product->where('id', $row['product_id'])->lockForUpdate()->first();

    // ====== FIFO مع قفل الدُفعات ======
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

        // نزّل الكمية من الدُفعة
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

    // ====== تحديث مخزون المنتج (فرع ثم عام) ======
    $branchColumn     = 'branch_' . $branchId;
    $qtyBaseToDeduct  = (float) $qtyBase;
    $branchQtyCurrent = (float) ($product->$branchColumn ?? 0);
    $globalQtyCurrent = (float) ($product->quantity ?? 0);

    if ($branchQtyCurrent >= $qtyBaseToDeduct) {
        // خصم من مخزون الفرع
        $product->$branchColumn = $branchQtyCurrent - $qtyBaseToDeduct;
    } elseif ($globalQtyCurrent >= $qtyBaseToDeduct) {
        // خصم من المخزون العام
        $product->quantity = $globalQtyCurrent - $qtyBaseToDeduct;
    } else {
        DB::rollBack();
        Toastr::error(translate('لا توجد كمية كافية بالمخزن'));
        return back();
    }

    $product->saveQuietly();

    // ====== لوج حركة المنتج ======
    $productlogs[] = [
        'product_id'     => $row['product_id'],
        'quantity'       => $qtySell,          // الكمية بوحدة البيع
        'base_quantity'  => $qtyBase,          // الكمية بوحدة الأساس (بعد التحويل)
        'unit'           => $unit,
        'purchase_price' => $weightedAvg,      // متوسط تكلفة البند
        'type'           => 4,                 // بيع
        'seller_id'      => $sellerId,
        'branch_id'      => $branchId,
        'created_at'     => now(),
        'updated_at'     => now(),
    ];
}
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
        $net_sales     = $product_price - $product_discount;
        $total_tax     = $product_tax;
        $invoice_total = $net_sales + $total_tax;

        // ضبط قيم الطلب النهائية من عرض السعر
        $order->extra_discount  = $quotation->extra_discount ?? 0;
        $order->total_tax       = $quotation->total_tax;
        $order->order_amount    = $quotation->order_amount; // معتمد من عرض السعر
        $order->collected_cash  = (float)$request->input('collected_cash', 0); // تحصيل فوري إن وجد
        $order->save();

        // حفظ تفاصيل ولوج المنتجات
        if (!empty($order_details)) $this->order_details->insert($order_details);
        if (!empty($productlogs))   $this->product_logs->insert($productlogs);

        $collectedNow = (float)$request->input('collected_cash', 0);

        // ========= قيد يومية واحد للفاتورة =========
        $je = new \App\Models\JournalEntry();
        $je->entry_date = $date;
        $je->reference  = 'INV-' . $order_id . ($order->transaction_reference ? (' / REF: ' . $order->transaction_reference) : '');
        $je->description= $note;
        $je->created_by = $sellerId;
        $je->type       = 'sales';
        $je->branch_id  = $branchId;
        $je->save();

        // (1) Dr العملاء (إجمالي الفاتورة)
        $this->addJEDetailWithCostAuto($je->id, $accCustomerId, $order->order_amount, 0, $request->input('cost_id'), $note, $img, $date, $branchId);

        // (2) Cr المبيعات (صافي)
        $this->addJEDetailWithCostAuto($je->id, $accSalesCode, 0, ($order->order_amount - $quotation->total_tax), $request->input('cost_id'), $note, $img, $date, $branchId);

        // (3) Cr الضريبة
        if ((float)$quotation->total_tax > 0) {
            $this->addJEDetailWithCostAuto($je->id, $accVatCode, 0, $quotation->total_tax, $request->input('cost_id'), 'ضريبة مخرجات فاتورة #'.$order_id, $img, $date, $branchId);
        }

        // (4) COGS والمخزون — للمنتجات فقط
        if ($hasAnyProduct && $totalCogsForProducts > 0 && $accStockCode && $accCogsCode) {
            $this->addJEDetailWithCostAuto($je->id, $accCogsCode, $totalCogsForProducts, 0, $request->input('cost_id'), 'تكلفة بضاعة مباعة للفاتورة #'.$order_id, $img, $date, $branchId);
            $this->addJEDetailWithCostAuto($je->id, $accStockCode, 0, $totalCogsForProducts, $request->input('cost_id'), 'تخفيض مخزون للفاتورة #'.$order_id, $img, $date, $branchId);
        }

        // (5) تحصيل فوري (اختياري)
        if ($collectedNow > 0) {
            $this->addJEDetailWithCostAuto($je->id, $accCashBankId, $collectedNow, 0, $request->input('cost_id'), 'دفعة مُحصَّلة الآن من العميل', $img, $date, $branchId);
            $this->addJEDetailWithCostAuto($je->id, $accCustomerId, 0, $collectedNow, $request->input('cost_id'), 'دفعة مُحصَّلة الآن من العميل', $img, $date, $branchId);
        }

        $order->journal_entry_id = $je->id;
        $order->save();
        event(new \App\Events\InvoiceFinalized($order));

        // ========= Transactions أساسية =========
        $this->addTransectionWithCostAuto(4, $sellerId, $branchId, $accSalesCode, $accCustomerId, ($order->order_amount - $quotation->total_tax), 'فاتورة مبيعات آجل', $date, $user_id, $order_id, $img, $request->input('cost_id'));
        if ((float)$quotation->total_tax > 0) {
            $this->addTransectionWithCostAuto(4, $sellerId, $branchId, $accVatCode, $accCustomerId, $quotation->total_tax, 'ضريبة مستحقة فاتورة مبيعات آجل', $date, $user_id, $order_id, $img, $request->input('cost_id'));
        }
        if ($hasAnyProduct && $totalCogsForProducts > 0 && $accStockCode && $accCogsCode) {
            $this->addTransectionWithCostAuto(4, $sellerId, $branchId, $accStockCode, $accCogsCode, $totalCogsForProducts, 'قيد المخزون مقابل تكلفة بضاعة مباعة', $date, $user_id, $order_id, $img, $request->input('cost_id'));
        }
        if ($collectedNow > 0) {
            $this->addTransectionWithCostAuto(4, $sellerId, $branchId, $accCashBankId, $accCustomerId, $collectedNow, 'دفعة محصلة الآن لفاتورة آجل', $date, $user_id, $order_id, $img, $request->input('cost_id'));
        }

        // تحديث ذمة العميل = إجمالي الفاتورة - التحصيل الفوري
        if ($customer) {
            $customer->credit = ($customer->credit ?? 0) + $order->order_amount - $collectedNow;
            $customer->save();
        }

        // تحديث حالة عرض السعر
        $quotation->status = 2; // تم تحويله إلى فاتورة
        $quotation->save();

        // ========= (اختياري) إنشاء عقد تقسيط + المُكفِّل + جدول الأقساط =========
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

            // إنشاء جدول الأقساط
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

        // ========= تحصيل أقساط أوتوماتيك بمبلغ مُرسل فقط =========
        $rawPayment = $request->input('installments_payment_amount', $request->input('payment_amount'));
        $autoPayAmount = (float)($rawPayment ?: 0);

        if ($autoPayAmount > 0) {
            // أولوية العثور على العقد: أوردر ثم أحدث Active لنفس العميل
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

            // سداد الأقساط بالترتيب
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

            // JE واحد: Dr نقدية/بنك — Cr العملاء (بمنطق مركز التكلفة الجديد)
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

            // Transection واحد
            $this->addTransectionWithCostAuto(4, $sellerId, $branchId, $accCashBankId, $accCustomerId, $actuallyPaid, 'تحصيل أقساط أوتوماتيكي', $payDate, $user_id, $order_id, null, $request->input('cost_id'));

            // خفّض ذمة العميل
            if ($customer) {
                $customer->credit = max(0, ($customer->credit ?? 0) - $actuallyPaid);
                $customer->save();
            }

            $msg = ($toPay <= 0)
                ? 'تم توزيع المبلغ على الأقساط وسداد المطلوب بالكامل قدر الإمكان.'
                : 'تم توزيع جزء من المبلغ على الأقساط، والمتبقي لم يُستخدم: ' . $toPay;
            session()->flash('success', $msg);
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
 * 2) غير كده → نقرأ default_cost_center_id من جدول accounts للحساب
 * 3) لو مفيش → نرجّع NULL
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
 * إضافة سطر تفصيلي لقيد اليومية مع تفعيل منطق مركز التكلفة التلقائي.
 */
private function addJEDetailWithCostAuto($jeId, $accountId, $debit, $credit, $explicitCostId, $desc, $attachment, $date, $branchId)
{
    $costId = $this->resolveCostCenter($accountId, $explicitCostId);

    $jed = new \App\Models\JournalEntryDetail();
    $jed->journal_entry_id = $jeId;
    $jed->account_id       = $accountId;
    $jed->debit            = (float)$debit;
    $jed->credit           = (float)$credit;
    $jed->cost_center_id   = $costId;   // ممكن تبقى NULL
    $jed->description      = $desc;
    $jed->attachment_path  = $attachment;
    $jed->entry_date       = $date;
    $jed->branch_id        = $branchId;
    $jed->save();

    return $jed;
}

/**
 * إنشاء سجل Transection مع تفعيل منطق مركز التكلفة التلقائي.
 */
private function addTransectionWithCostAuto($tranType, $sellerId, $branchId, $fromAcc, $toAcc, $amount, $desc, $date, $customerId, $orderId, $img = null, $explicitCostId = null)
{
    // مبدأ: الترانزكشن عندك بياخد cost_id واحد للسطر.
    // الأفضل نختار مركز تكلفة من "الحساب من" أولاً، ولو مفيش نجرّب "الحساب إلى"، وإلا NULL.
    $costId = $this->resolveCostCenter($fromAcc, $explicitCostId)
           ?? $this->resolveCostCenter($toAcc, null);

    $t = new \App\Models\Transection;
    $t->tran_type      = $tranType;
    $t->seller_id      = $sellerId;
    $t->branch_id      = $branchId;
    $t->cost_id        = $costId;   // ممكن تبقى NULL
    $t->account_id     = $fromAcc;  // من
    $t->account_id_to  = $toAcc;    // إلى
    $t->amount         = (float)$amount;
    $t->description    = $desc;
    $t->debit          = (float)$amount;
    $t->credit         = 0;
    $t->date           = $date;
    $t->customer_id    = $customerId;
    $t->order_id       = $orderId;
    $t->img            = $img;
    $t->save();

    return $t;
}


}
