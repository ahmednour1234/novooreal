<?php

namespace App\Http\Controllers\Api\V1;

use App\CPU\Helpers;
use App\Models\CurrentOrder;
use App\Models\Order;
use App\Models\OrderNotification;
use App\Models\Account;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Customer;
use App\Models\OrderDetail;
use App\Models\OrderDetailNotification;
use App\Models\Installment;
use App\Models\HistoryInstallment;
use App\Models\HistoryTransection;
use App\Models\Transection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use function App\CPU\translate;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductsResource;
use App\Http\Resources\StocksResource;
use App\Models\ReserveProduct;
use App\Models\CurrentReserveProduct;
use App\Models\Stock;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Rap2hpoutre\FastExcel\FastExcel;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;


class PosController extends Controller
{
    public function __construct(
        private CurrentOrder $current_order,
        private Order $order,
        private OrderDetail $order_details,
        private OrderNotification $order_notification,
        private OrderDetailNotification $order_details_notification,
        private Installment $installment,
        private HistoryInstallment $history_installment,
        private Account $account,
        private Product $product,
        private Stock $stock,
        private Customer $customer,
        private OrderDetail $order_detail,
        private ReserveProduct $reserveProduct,
        private CurrentReserveProduct $current_reserve_products,
        private HistoryTransection $history_transection,
        private Transection $transection,
        private BusinessSetting $business_setting,
    ){}
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getProductIndex(Request $request): JsonResponse
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        $product = $this->product->latest()->paginate($limit, ['*'], 'page', $offset);
        $products = ProductsResource::collection($product);
        $data = [
            'total' => $products->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $products->items(),
        ];
        return response()->json($data, 200);
    }
    public function getProductCode(Request $request): JsonResponse
{
    $limit = $request->input('limit', 10);
    $offset = $request->input('offset', 1);
    $productCode = $request->input('product_code');

    // Check if product_code is provided to fetch a specific product
    if ($productCode) {
        $product = Product::where('product_code', $productCode)->first();
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        return response()->json(['product' => new ProductsResource($product)], 200);
    }

    // Fetch paginated products if product_code is not provided
    $products = Product::latest()->paginate($limit, ['*'], 'page', $offset);
    $productsCollection = ProductsResource::collection($products);

    $data = [
        'total' => $productsCollection->total(),
        'limit' => $limit,
        'offset' => $offset,
        'products' => $productsCollection->items(),
    ];

    return response()->json($data, 200);
}
public function getSellerProducts(Request $request)
{
    $seller = \App\Models\Seller::find(Auth::user()->id);

    // Collect category IDs associated with the seller
    $categoryIds = [];
    foreach ($seller->cats as $category) {
        $categoryIds[] = $category->cat->id;
    }

    // Fetch products ordered by name
    $products = $this->product->whereIn('category_id', $categoryIds)->orderBy('name', 'asc')->get();

    // Loop through products to check seller prices
    foreach ($products as $product) {
        // Check if there is a seller price
        $seller_price = \App\Models\SellerPrice::where(['seller_id' => Auth::user()->id, 'product_id' => $product->id])->first();
        
        if ($seller_price) {
            // Replace selling_price with the seller's price
            $product->selling_price = $seller_price->price; // Assuming `price` is the field in SellerPrice
        } else {
            // If no seller price exists, use the product's selling price
            $product->price = $product->selling_price; // Assuming `selling_price` is the field in Product
        }
    }

    // Transform the collection using the resource
    $productsResource = ProductsResource::collection($products);
    
    return response()->json($productsResource, 200);
}

    /**
     * @param Request $request
     * @return JsonResponse
     */
  public function orderList(Request $request): JsonResponse
{
    $orders = $this->current_order
        ->where('type', $request->type)
        ->where('owner_id', auth()->user()->id)
        ->with('account')
        ->get();

    $type = $request->type == 4 ? "orders" : "refnd";

    $data = [
        'total' => $orders->count(),
        $type => $orders,
    ];

    return response()->json($data, 200);
}

     public function orderListnotinstall(Request $request): JsonResponse
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        $orders = $this->order->where('type', $request->type)->where('owner_id', auth()->user()->id)->whereColumn('order_amount', '!=', 'transaction_reference')->with('account')->latest()->paginate($limit, ['*'], 'page', $offset);
        $type = $request->type == 4 ? "orders" : "refnd";
        $data = [
            'total' => $orders->total(),
            'limit' => $limit,
            'offset' => $offset,
            $type => $orders->items(),
        ];
        return response()->json($data, 200);
    }
        public function orderListinstall(Request $request): JsonResponse
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        $orders = $this->order->where('type', $request->type)->where('owner_id', auth()->user()->id)->whereColumn('order_amount', '=', 'transaction_reference')->with('account')->latest()->paginate($limit, ['*'], 'page', $offset);
        $type = $request->type == 4 ? "orders" : "refnd";
        $data = [
            'total' => $orders->total(),
            'limit' => $limit,
            'offset' => $offset,
            $type => $orders->items(),
        ];
        return response()->json($data, 200);
    }



  public function installmentList(Request $request): JsonResponse
{
    if (Auth::user()->role == 'admin') {
        $installments = $this->installment->with('seller', 'customer')->get();
    } else {
        $installments = $this->installment
            ->where('seller_id', Auth::user()->id)
            ->with('seller', 'customer')
            ->latest()
            ->get();
    }

    $data = [];
    foreach ($installments as $i => $install) {
        $data[$i]['id'] = $install->id;
        $data[$i]['seller']['name'] = trim(($install->seller->f_name ?? '') . ' ' . ($install->seller->l_name ?? ''));
        $data[$i]['customer']['name'] = $install->customer->name ?? '';
        $data[$i]['price'] = $install->total_price;
        $data[$i]['date'] = $install->created_at;
    }

    return response()->json([
        'total' => $installments->count(),
        'installments' => $data,
    ], 200);
}


    /**
     * @param Request $request
     * @return JsonResponse
     */
public function invoiceGenerate(Request $request): JsonResponse
{
    // التحقق من وجود معامل order_id وإرجاع خطأ إذا لم يكن موجودًا
    if (!$request->has('order_id') || empty($request->input('order_id'))) {
        return response()->json(['errors' => ['order_id' => ['Order id is required.']]], 403);
    }

    // استخراج قيمة order_id من الطلب
    $order_id = $request->input('order_id');

    // البحث عن الفاتورة في جدول الطلبات مع العلاقات المطلوبة
    $invoice = $this->order->with(['details', 'details.product', 'account', 'seller', 'customer'])
                           ->where('id', $order_id)
                           ->first();

    // طباعة الفاتورة للتأكد (يمكن إزالة print_r بعد مرحلة التصحيح)

    // في حال عدم وجود فاتورة في جدول الطلبات، يتم البحث في جدول order_notifications (أو جدول آخر حسب الحاجة)
    if (!$invoice) {
        $invoice = $this->order->with(['details', 'details.product', 'account', 'seller', 'user'])
                               ->where('id', $order_id)
                               ->first();

        if (!$invoice) {
            return response()->json(['message' => 'Order not found'], 404);
        }
    }

    // تقريب قيمة order_amount (يمكنك تعديل إذا كنت تريد عملية تقريب محددة)
    $invoice->order_amount = $invoice->order_amount;
    
    // تعديل vehicle_code للبائع باستخدام دالة vehicleCode

    return response()->json([
        'success' => true,
        'invoice' => $invoice,
    ], 200);
}

    
    public function installmentInvoiceGenerate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'installment_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        
        $invoice = $this->installment->with(['customer','seller'])->where(['id' => $request['installment_id']])->first();
        // $invoice['seller']->vehicle_code = $this->vehicleCode($invoice->seller->vehicle_code);
        return response()->json([
            'success' => true,
            'invoice' => $invoice,
        ], 200);
    }
    
public function reserveProduct(Request $request)
{
    // Validate request data
    if ($request['data']) {
        if (count($request['data']) < 1) {
            return response()->json(['message' => 'Data empty'], 403);
        }
    } else {
        return response()->json(['message' => 'Data empty'], 403);
    }

    $reserveProduct = new \App\Models\ReserveProduct();
            $current_reserve_products = $this->current_reserve_products;

    $data = [];
    
    foreach ($request->data as $i => $item) {
        $product_id = $item['product_id'];

        // Retrieve the product
        $product = \App\Models\Product::find($product_id);
        if (!$product) {
            return response()->json(['message' => "Product not found for ID: $product_id"], 404);
        }

        // First check for customer price
        $customerPrice = \App\Models\CustomerPrice::where('product_id', $product_id)
            ->where('customer_id', $request->customer_id)
            ->first();

        // If customer price is not found, check for seller price
        $seller_price = null;
        if (!$customerPrice) {
            $seller_price = \App\Models\SellerPrice::where(['seller_id' => Auth::user()->id, 'product_id' => $product_id])->first();
        }

        // Determine the final price
        $price = $customerPrice ? $customerPrice->price : ($seller_price ? $seller_price->price : $product->selling_price);
         if ($request->type == '7') {

                if (  $item['balance'] < $item['stock']) {
                    return response()->json([
                    'message' => 'هذا المنتج لا توجد منه كمية كافية في المخزون للاسترجاع: ' . $product->name
                    ], 403);
                }
            }

        // Prepare data for the reservation
        $data[$i] = [
            'product_name' => $item['product_name'],
            'product_id' => $product_id,
            'stock' => $item['stock'],
            'balance' => $item['balance'],
            'price' => $price, // Use the determined price
        ];
    }

    // Generate order ID
    $order_id = 20000000 + $reserveProduct->count() + 1;
    if ($reserveProduct->find($order_id)) {
        $order_id = $reserveProduct->orderBy('id', 'DESC')->first()->id + 1;
    }

    // Save the reservation
    $reserveProduct->id = $order_id;
    $reserveProduct->data = json_encode($data);
    $reserveProduct->seller_id = Auth::user()->id;
    $reserveProduct->date = now()->format('Y-m-d'); // Use now() for current date
    $reserveProduct->customer_id = $request->customer_id;
    $reserveProduct->type = $request->type;
    $reserveProduct->active = 1; // Set the active column to 1
    $reserveProduct->save();
     $current_reserve_products->id = $order_id;
        $current_reserve_products->data = json_encode($data);
        $current_reserve_products->seller_id = Auth::user()->id;
        $current_reserve_products->date = date('Y-m-d');
        $current_reserve_products->customer_id = $request->customer_id;
        $current_reserve_products->type = $request->type;
        $current_reserve_products->save();
    return response()->json([
        'message' => 'Reservation successful',
        'id' => $reserveProduct->id
    ], 200);
}




public function reservations(Request $request, $type)
{
    $reserveProducts =CurrentReserveProduct::
        where('seller_id', Auth::id())
        ->where('type', $type)
        ->with(['customer', 'seller']) // جلب البيانات بالعلاقات لتقليل الاستعلامات
        ->get();
$seller=Seller::where('id', Auth::id())->first();
    $reserveProducts->transform(function ($item) {
        $item->data = json_decode($item->data);
        
        // تحسين عرض بيانات البائع
    

        // إضافة product_code لكل منتج في البيانات
        foreach ($item->data as $value) {
            $product = Product::find($value->product_id);
            if ($product) {
                $value->product_code = $product->product_code;
            }
        }

        return $item;
    });

    return response()->json([
        'total' => $reserveProducts->count(),
        'reservations' => $reserveProducts,
        'seller'=>$seller
    ], 200);
}

public function placeOrder(Request $request): JsonResponse
{
    // 1. Check if the cart is empty
    if (empty($request->cart)) {
        return response()->json(['message' => 'Cart is empty'], 403);
    }

    // 2. Preprocess & decode the cart string into a PHP array
    $cartJson = preg_replace(
        '/([{,])(\s*)([a-zA-Z0-9_]+)(\s*:\s*)/',
        '$1$2"$3"$4',
        $request->cart
    );
    $cart = json_decode($cartJson, true);

    if (!$cart || !is_array($cart) || count($cart) < 1) {
        return response()->json(['message' => 'Cart is empty or invalid'], 403);
    }

    // 3. Retrieve the customer
    $customer = \App\Models\Customer::find($request->user_id);
    if (!$customer) {
        return response()->json(['message' => 'Customer not found'], 404);
    }

    // 4. Determine a new order ID
    $order_id        = $this->order->max('id') + 1;
    $coupon_discount = $request->coupon_discount ?? 0;

    // 5. Handle optional image upload
    $imgPath = null;
    if ($request->hasFile('img')) {
        $imgPath = $request->file('img')->store('shop', 'public');
    }

    // 6. Generate & store a QR code
    $qrcodeData  = "https://testnewpos.iqbrandx.com/real/invoicea2/{$order_id}";
    $qrCode      = new \Endroid\QrCode\QrCode($qrcodeData);
    $writer      = new \Endroid\QrCode\Writer\PngWriter();
    $qrcodeImage = $writer->write($qrCode)->getString();
    $qrcodePath  = "qrcodes/order_{$order_id}.png";
    \Storage::disk('public')->put($qrcodePath, $qrcodeImage);

    // 7. Prepare base order data
    $orderData = [
        'id'                     => $order_id,
        'owner_id'               => auth()->id(),
        'user_id'                => $customer->id,
        'coupon_code'            => $request->coupon_code,
        'coupon_discount_title'  => $request->coupon_title,
        'payment_id'             => $request->payment_id,
        'cash'                   => $request->cash,
        'img'                    => $imgPath,
        'total_tax'              => $request->total_tax,
        'order_amount'           => $request->order_amount,
        'extra_discount'         => $request->extra_discount,
        'coupon_discount_amount' => $coupon_discount,
        'collected_cash'         => $request->collected_cash,
        'qrcode'                 => $qrcodePath,
        'transaction_reference'  => $request->collected_cash,
        'type'                   => $request->order_type,
        'created_at'             => now(),
        'updated_at'             => now(),
    ];

    $order   = $this->order->newInstance($orderData);
    $c_order = $this->current_order->newInstance($orderData);

    // 8. Initialize summary totals and detail buffer
    $product_price         = 0;
    $product_discount      = 0;
    $product_tax           = 0;
    $order_details         = [];
    $totalPriceAllProducts = 0;

    try {
        \DB::beginTransaction();

        // 9. Validate stock availability
        foreach ($cart as $item) {
            $product       = \App\Models\Product::find($item['id']);
            $finalQuantity = $item['unit'] == 0
                ? ($item['quantity'] / $product->unit_value)
                : $item['quantity'];

            $stock = \App\Models\Stock::where('seller_id', auth()->id())
                                      ->where('product_id', $item['id'])
                                      ->first();

            if (in_array($request->order_type, [4, 12, 24]) &&
                $stock && $stock->stock < $finalQuantity) {
                \DB::rollBack();
                return response()->json([
                    'message' => "Not enough stock for {$product->name}. Requested: {$finalQuantity}"
                ], 422);
            }
        }

        // 10. Process each cart item
        foreach ($cart as $item) {
            $product = $this->product->find($item['id']);
            if (!$product) {
                continue;
            }

            // Determine quantities
            if ($item['unit'] == 0) {
                $quantity      = $item['quantity'] * $product->unit_value;
                $finalQuantity = $item['quantity'] / $product->unit_value;
            } else {
                $quantity      = $item['quantity'];
                $finalQuantity = $item['quantity'];
            }

            // Load or create stock record
            $stock = \App\Models\Stock::where('seller_id', auth()->id())
                                      ->where('product_id', $item['id'])
                                      ->first();

            // —— SPECIAL HANDLING for order_type = 4: consume JSON‐tiered stock.prices
       if ($request->order_type == 4 && $stock) {
    $remaining = $finalQuantity;
    $tiers     = json_decode($stock->price, true);

    $totalPriceAllProducts = 0;
    $usedQuantity = 0;

    foreach ($tiers as $idx => $tier) {
        if ($remaining <= 0) break;

        $use = min($remaining, $tier['quantity']);
        $tiers[$idx]['quantity'] -= $use;
        $remaining -= $use;

        // accumulate total cost
        $totalPriceAllProducts += $use * $tier['price'];
        $usedQuantity += $use;
    }

    // Calculate average price (guard against division by zero)
    $avgPrice = $usedQuantity > 0 ? $totalPriceAllProducts / $usedQuantity : 0;

    // Save back
    $stock->price = json_encode($tiers);
    $stock->stock -= $finalQuantity;
    $stock->save();
}

            // —— RESTOCK for returns (order_type = 7)
            elseif (!$stock && $request->order_type == 7) {
                $store = \App\Models\Store::where('seller_id', auth()->id())->first();
                if (!$store) {
                    \DB::rollBack();
                    return response()->json(['error' => 'No store found for seller'], 404);
                }
                $stock = \App\Models\Stock::create([
                    'seller_id' => auth()->id(),
                    'store_id'  => $store->id,
                    'product_id'=> $item['id'],
                    'main_stock'=> $finalQuantity,
                    'stock'     => $finalQuantity,
                    'price'     => json_encode([
                        ['quantity' => $finalQuantity, 'price' => $product->purchase_price]
                    ])
                ]);
            }
            // —— INCREMENT if restocking
            elseif ($stock && $request->order_type == 7) {
                $stock->stock += $finalQuantity;
                $stock->save();
            }
            // —— STANDARD stock decrement
            elseif ($stock) {
                $stock->stock -= $finalQuantity;
                $stock->save();
            }

            // 11. Calculate discounts, extra discount & tax
            $discProd   = Helpers::discountCalculatePrice($product, $item['price'], $item['unit']);
            $discRatio  = ($request->extra_discount
                          / $request->subtotal) * 100;
            $extraDiscAmt = ($discRatio / 100) * $item['price'];
            $netPrice     = $item['price'] - $discProd - $extraDiscAmt;
            $taxRate =  ($product->taxe->amount ?? 0);

$taxAmt = $netPrice * ($taxRate / 100);


            // Build this item’s detail record
            $detail = [
                'product_id'               => $item['id'],
                'product_details'          => $product,
                'quantity'                 => $item['quantity'],
                'price'                    => $item['price'],
                'unit'                     => $item['unit'],
                'extra_discount_on_product'=> $extraDiscAmt,
                'tax_amount'               => $item['tax'],
                'discount_on_product'      => $discProd,
                'discount_type'            => 'discount_on_product',
                'purchase_price'=>$avgPrice,
                'created_at'               => now(),
                'updated_at'               => now(),
            ];

            $order_details[] = $detail;

            // Update summary totals
            $product_price    += $item['price'] * $item['quantity'];
            $product_discount += $item['discount'] * $item['quantity'];
            $product_tax      += $item['tax'] * $item['quantity'];

            // Bump product’s order count
            $product->increment('order_count');
        }

        // 12. Compute final totals
        $total_price    = $product_price - $product_discount;
        $extra_discount = $request->extra_discount_type === 'percent'
                          ? ($total_price * $request->extra_discount) / 100
                          : $request->extra_discount;
        $grand_total    = $total_price + $request->total_tax
                          - $extra_discount - $coupon_discount;

        // 13. Persist the order
        $order->fill([
            'collected_cash' => $request->collected_cash ?? $grand_total,
            'order_amount'   => $request->order_amount,
        ]);
        $c_order->fill($order->getAttributes());

        $order->save();
        $c_order->save();

        // Bulk‐insert the order details
        $this->order_detail->insert(
            array_map(fn($d) => array_merge($d, ['order_id' => $order_id]), $order_details)
        );

     if ($request->cash == 2) {
         $user_id=$request->user_id;
         $type=$request->order_type;
                    $seller = \App\Models\Seller::where('id', auth()->user()->id)->first();
                    $remaining_balance = $request->order_amount - $request->transaction_reference;
                    $customer = $this->customer->where('id', $user_id)->first();
                    $payable_account_to = \App\Models\Account::find($customer->account_id);
                    $payable_account = \App\Models\Account::find(40);
                    // First transaction
                    $payable_transaction = new \App\Models\Transection;
                    $payable_transaction->tran_type = ($type == 1) ? 4 : $request->order_type;
                    $payable_transaction->seller_id = auth()->user()->id;
                    $payable_transaction->branch_id = auth()->user()->branch_id;
                    $payable_transaction->cost_id = $request->cost_id;
                    $payable_transaction->account_id = 40;
                    $payable_transaction->account_id_to = $customer->account_id;
                    $payable_transaction->amount = $request->order_amount - $request->total_tax;
                    $payable_transaction->description = 'فاتورة مبيعات';
                    $payable_transaction->debit = $request->order_amount - $request->total_tax;
                    $payable_transaction->credit = 0;
                    $payable_transaction->balance = $payable_account->balance + ($request->order_amount - $request->total_tax);
                    $payable_transaction->debit_account = 0;
                    $payable_transaction->credit_account = $request->order_amount - $request->total_tax;
                    $payable_transaction->balance_account = $payable_account_to->balance + ($request->order_amount - $request->total_tax);
                    $payable_transaction->date = date("Y/m/d");
                    $payable_transaction->customer_id = $user_id;
                    $payable_transaction->order_id = $order_id;
                    $payable_transaction->img = $imgPath;
                    $payable_transaction->save();

                    $payable_account->balance += ($request->order_amount - $request->total_tax);
                    $payable_account->total_in += ($request->order_amount - $request->total_tax);
                    $payable_account->save();
                    $payable_account_to->balance += ($request->order_amount - $request->total_tax);
                    $payable_account_to->total_in += ($request->order_amount - $request->total_tax);
                    $payable_account_to->save();

                    // Second transaction
                    $payable_account_2 = \App\Models\Account::find(28);
                    $payable_account_to_2 = \App\Models\Account::find($customer->account_id);
                    $payable_transaction_2 = new \App\Models\Transection;
                    $payable_transaction_2->tran_type = ($type == 1) ? 4 : $request->order_type;
                    $payable_transaction_2->seller_id = auth()->user()->id;
                    $payable_transaction_2->branch_id = auth()->user()->branch_id;
                    $payable_transaction_2->cost_id = $request->cost_id;
                    $payable_transaction_2->account_id = 28;
                    $payable_transaction_2->account_id_to = $customer->account_id;
                    $payable_transaction_2->amount = $request->total_tax;
                    $payable_transaction_2->description = 'فاتورة مبيعات';
                    $payable_transaction_2->debit = $request->total_tax;
                    $payable_transaction_2->credit = 0;
                    $payable_transaction_2->balance = $payable_account_2->balance + $request->total_tax;
                    $payable_transaction_2->debit_account = 0;
                    $payable_transaction_2->credit_account = $request->total_tax;
                    $payable_transaction_2->balance_account = $payable_account_to_2->balance + $request->total_tax;
                    $payable_transaction_2->date = date("Y/m/d");
                    $payable_transaction_2->customer_id = $user_id;
                    $payable_transaction_2->order_id = $order_id;
                    $payable_transaction_2->img = $imgPath;
                    $payable_transaction_2->save();

                    $payable_account_2->balance += $request->total_tax;
                    $payable_account_2->total_in += $request->total_tax;
                    $payable_account_2->save();
                    $payable_account_to_2->balance += $request->total_tax;
                    $payable_account_to_2->total_in += $request->total_tax;
                    $payable_account_to_2->save();

                    // Third transaction
                    $payable_account_3 = \App\Models\Account::find($seller->account_id);
                    $payable_account_to_3 = \App\Models\Account::find(47);
                    $payable_transaction_2 = new \App\Models\Transection;
                    $payable_transaction_2->tran_type = ($type == 1) ? 4 : $request->order_type;
                    $payable_transaction_2->seller_id = auth()->user()->id;
                    $payable_transaction_2->branch_id = auth()->user()->branch_id;
                    $payable_transaction_2->cost_id = $request->cost_id;
                    $payable_transaction_2->account_id = $seller->account_id;
                    $payable_transaction_2->account_id_to = 47;
                    $payable_transaction_2->amount = $totalPriceAllProducts;
                    $payable_transaction_2->description = 'فاتورة مبيعات';
                    $payable_transaction_2->debit = $totalPriceAllProducts;
                    $payable_transaction_2->credit = 0;
                    $payable_transaction_2->balance = $payable_account_3->balance - $totalPriceAllProducts;
                    $payable_transaction_2->debit_account = 0;
                    $payable_transaction_2->credit_account = $totalPriceAllProducts;
                    $payable_transaction_2->balance_account = $payable_account_to_3->balance + $totalPriceAllProducts;
                    $payable_transaction_2->date = date("Y/m/d");
                    $payable_transaction_2->customer_id = $user_id;
                    $payable_transaction_2->order_id = $order_id;
                    $payable_transaction_2->img = $imgPath;
                    $payable_transaction_2->save();

                    $payable_account_3->balance -= $totalPriceAllProducts;
                    $payable_account_3->total_out += $totalPriceAllProducts;
                    $payable_account_3->save();
                    $payable_account_to_3->balance += $totalPriceAllProducts;
                    $payable_account_to_3->total_in += $totalPriceAllProducts;
                    $payable_account_to_3->save();
                    $customer->credit += $remaining_balance;
                    $customer->save();

                 
                }else{
                     $user_id=$request->user_id;
         $type=$request->order_type;
                    $seller = \App\Models\Seller::where('id', auth()->user()->id)->first();
                    $remaining_balance = $request->order_amount - $request->transaction_reference;
                    $customer = $this->customer->where('id', $user_id)->first();
                    $payable_account_to = \App\Models\Account::find($seller->account_id);
                    $payable_account = \App\Models\Account::find(40);
                    // First transaction
                    $payable_transaction = new \App\Models\Transection;
                    $payable_transaction->tran_type = ($type == 1) ? 4 : $request->order_type;
                    $payable_transaction->seller_id = auth()->user()->id;
                    $payable_transaction->branch_id = auth()->user()->branch_id;
                    $payable_transaction->cost_id = $request->cost_id;
                    $payable_transaction->account_id = 40;
                    $payable_transaction->account_id_to = $seller->account_id;
                    $payable_transaction->amount = $request->order_amount - $request->total_tax;
                    $payable_transaction->description = 'فاتورة مبيعات';
                    $payable_transaction->debit = $request->order_amount - $request->total_tax;
                    $payable_transaction->credit = 0;
                    $payable_transaction->balance = $payable_account->balance + ($request->order_amount - $request->total_tax);
                    $payable_transaction->debit_account = 0;
                    $payable_transaction->credit_account = $request->order_amount - $request->total_tax;
                    $payable_transaction->balance_account = $payable_account_to->balance + ($request->order_amount - $request->total_tax);
                    $payable_transaction->date = date("Y/m/d");
                    $payable_transaction->customer_id = $user_id;
                    $payable_transaction->order_id = $order_id;
                    $payable_transaction->img = $imgPath;
                    $payable_transaction->save();

                    $payable_account->balance += ($request->order_amount - $request->total_tax);
                    $payable_account->total_in += ($request->order_amount - $request->total_tax);
                    $payable_account->save();
                    $payable_account_to->balance += ($request->order_amount - $request->total_tax);
                    $payable_account_to->total_in += ($request->order_amount - $request->total_tax);
                    $payable_account_to->save();

                    // Second transaction
                    $payable_account_2 = \App\Models\Account::find(28);
                    $payable_account_to_2 = \App\Models\Account::find($seller->account_id);
                    $payable_transaction_2 = new \App\Models\Transection;
                    $payable_transaction_2->tran_type = ($type == 1) ? 4 : $request->order_type;
                    $payable_transaction_2->seller_id = auth()->user()->id;
                    $payable_transaction_2->branch_id = auth()->user()->branch_id;
                    $payable_transaction_2->cost_id = $request->cost_id;
                    $payable_transaction_2->account_id = 28;
                    $payable_transaction_2->account_id_to = $seller->account_id;
                    $payable_transaction_2->amount = $request->total_tax;
                    $payable_transaction_2->description = 'فاتورة مبيعات';
                    $payable_transaction_2->debit = $request->total_tax;
                    $payable_transaction_2->credit = 0;
                    $payable_transaction_2->balance = $payable_account_2->balance + $request->total_tax;
                    $payable_transaction_2->debit_account = 0;
                    $payable_transaction_2->credit_account = $request->total_tax;
                    $payable_transaction_2->balance_account = $payable_account_to_2->balance + $request->total_tax;
                    $payable_transaction_2->date = date("Y/m/d");
                    $payable_transaction_2->customer_id = $user_id;
                    $payable_transaction_2->order_id = $order_id;
                    $payable_transaction_2->img = $imgPath;
                    $payable_transaction_2->save();

                    $payable_account_2->balance += $request->total_tax;
                    $payable_account_2->total_in += $request->total_tax;
                    $payable_account_2->save();
                    $payable_account_to_2->balance += $request->total_tax;
                    $payable_account_to_2->total_in += $request->total_tax;
                    $payable_account_to_2->save();

                    // Third transaction
                    $payable_account_3 = \App\Models\Account::find($seller->account_id);
                    $payable_account_to_3 = \App\Models\Account::find(47);
                    $payable_transaction_2 = new \App\Models\Transection;
                    $payable_transaction_2->tran_type = ($type == 1) ? 4 : $request->order_type;
                    $payable_transaction_2->seller_id = auth()->user()->id;
                    $payable_transaction_2->branch_id = auth()->user()->branch_id;
                    $payable_transaction_2->cost_id = $request->cost_id;
                    $payable_transaction_2->account_id = $seller->account_id;
                    $payable_transaction_2->account_id_to = 47;
                    $payable_transaction_2->amount = $totalPriceAllProducts;
                    $payable_transaction_2->description = 'فاتورة مبيعات';
                    $payable_transaction_2->debit = $totalPriceAllProducts;
                    $payable_transaction_2->credit = 0;
                    $payable_transaction_2->balance = $payable_account_3->balance - $totalPriceAllProducts;
                    $payable_transaction_2->debit_account = 0;
                    $payable_transaction_2->credit_account = $totalPriceAllProducts;
                    $payable_transaction_2->balance_account = $payable_account_to_3->balance + $totalPriceAllProducts;
                    $payable_transaction_2->date = date("Y/m/d");
                    $payable_transaction_2->customer_id = $user_id;
                    $payable_transaction_2->order_id = $order_id;
                    $payable_transaction_2->img = $imgPath;
                    $payable_transaction_2->save();

                    $payable_account_3->balance -= $totalPriceAllProducts;
                    $payable_account_3->total_out += $totalPriceAllProducts;
                    $payable_account_3->save();
                    $payable_account_to_3->balance += $totalPriceAllProducts;
                    $payable_account_to_3->total_in += $totalPriceAllProducts;
                    $payable_account_to_3->save();
                    $seller->credit += $remaining_balance;
                    $seller->save();
                }
        \DB::commit();

        return response()->json([
            'message'  => 'Order placed successfully',
            'order_id' => $order_id
        ], 200);

    } catch (\Exception $e) {
        \DB::rollBack();
        return response()->json([
            'message' => 'Failed to place order',
            'error'   => $e->getMessage()
        ], 400);
    }
}







public function placeInstallment(Request $request): JsonResponse
{
    // Validate the incoming request
    $request->validate([
        'user_id' => 'required|exists:customers,id',
        'order_id' => 'nullable|exists:orders,id',
        'price' => 'required|numeric|min:1',
        'note' => 'nullable|string',
        'img' => 'nullable|image' // Validate image file
    ]);

    $img = null;

    if ($request->hasFile('img')) {
        $img = $request->file('img')->store('shop', 'public'); // Store image in 'shop' folder
    }

    try {
        // Find the customer
        $customer = Customer::findOrFail($request->user_id);
       $seller = Seller::where('id', Auth::id())->first();

        // // Check if customer's credit is sufficient
        // if ($customer->credit < $request->price) {
        //     return response()->json([
        //         'message' => 'المبلغ المحصل أكبر من مديونية العميل'
        //     ], 400);
        // }

        // Initialize the order variable
        $order = null;
        $status = null;

        if (!is_null($request->order_id)) {
            // Check the existence of the order
            $order = Order::findOrFail($request->order_id);

            // Update the transaction reference
            $order->transaction_reference += $request->price;

            if ($order->transaction_reference == $order->order_amount) {
                $status = 'تم تحصيل كامل المبلغ';
            } elseif ($order->transaction_reference < $order->order_amount) {
                $remaining = $order->order_amount - $order->transaction_reference;
                $status = "جزء من المبلغ تم تحصيله. الباقي: $remaining";
            } else {
                return response()->json([
                    'message' => 'هذه الفاتورة تم تحصيلها بالكامل',
                    'error' => 'Payment exceeds order amount'
                ], 400);
            }
            $order->save();
        }

        // Create the installment
        $installment = new Installment();
        $installment->seller_id = Auth::id();
        $installment->customer_id = $request->user_id;
        $installment->total_price = $request->price;
        $installment->note = $request->note??'';
        $installment->img = $img;
        $installment->save();

        // Create a history installment entry
        $historyInstallment = new HistoryInstallment();
        $historyInstallment->seller_id = Auth::id();
        $historyInstallment->customer_id = $request->user_id;
        $historyInstallment->total_price = $request->price;
        $historyInstallment->note = $request->note??'';
        $historyInstallment->img = $img;
        $historyInstallment->save();

        // Create a transaction
      

        // Update the seller's commission and credit
 
        $seller = Seller::findOrFail(Auth::id());
        $seller->commission += $request->price;
        $seller->credit += $request->price;
        $account=Account::where('id',$customer->account_id)->first();
        $account_to=Account::where('id',$seller->account_id)->first();
          $transaction = new Transection();
        $transaction->tran_type = 26;
        $transaction->amount = 0;
        $transaction->account_id=$account->id;
        $transaction->account_id_to=$account_to->id;
        $transaction->amount= $request->price;
        $transaction->description = $request->note;
        $transaction->debit = $request->price;
        $transaction->credit = 0;
        $transaction->balance =$account->balance- $request->price;
        $transaction->debit_account = 0;
        $transaction->credit_account = $request->price;
        $transaction->balance_account =$account->balance+ $request->price;
        $transaction->cash = $request->cash;
        $transaction->date = now();
        $transaction->customer_id = $request->user_id;
        $transaction->seller_id = Auth::id();
                $transaction->branch_id = auth()->user()->branch_id;;
        $transaction->img = $img;
     
        $seller->save();
        $account->total_out+=$request->price;
        $account->balance-=$request->price;
        $account->save();
           $account_to->total_in+=$request->price;
        $account_to->balance+=$request->price;
        $account_to->save();
                $transaction->save();


        // Deduct the price from the customer's credit
        $customer->balance += $request->price;
        $customer->save();

        return response()->json([
            'message' => 'تم التحصيل بنجاح',
            'installment_id' => $installment->id,
            'status' => $status
        ], 200);

    } catch (\Exception $e) {
        // Log the error message
        \Log::error($e->getMessage());

        return response()->json([
            'message' => 'Failed to place installment'.$e->getMessage(),
            'error' => $e->getMessage()
        ], 400);
    }
}





    /**
     * @param Request $request
     * @return JsonResponse
     */
public function getSearch(Request $request): JsonResponse
{
    $limit = $request->input('limit', 10); // Default limit to 10
    $offset = $request->input('offset', 1); // Default offset to 1 (current page)
    $search = $request->input('name');
    $type = $request->input('type');
    $products = [];

    if (!empty($search)) {
        // Get product IDs based on search criteria
        $product_ids = $this->product
            ->where('product_code', 'LIKE', "%$search%")
            ->orWhere('name', 'LIKE', "%$search%")
            ->pluck('id');

        // Fetch stocks for the seller
        $result = $this->stock
            ->whereIn('product_id', $product_ids)
            ->where('seller_id', Auth::id())
            ->get();

        // Transform the result into a resource collection
        $products = StocksResource::collection($result);

        if ($type != 4) {
            // If type is not 4, fetch products based on category
            $cat_ids = \App\Models\Seller::find(auth()->user()->id)->cats->pluck('cat_id');
            $result = $this->product
                ->whereIn('category_id', $cat_ids)
                ->where(function ($query) use ($search) {
                    $query->where('product_code', 'LIKE', "%$search%")
                          ->orWhere('name', 'LIKE', "%$search%");
                })
                ->get();

            // Loop through products to check seller prices
            foreach ($result as $product) {
                // Check if there is a seller price
                $seller_price = \App\Models\SellerPrice::where(['seller_id' => Auth::user()->id, 'product_id' => $product->id])->first();
                
                // Set the price based on seller price or default to selling price
                $product->selling_price = $seller_price ? $seller_price->price : $product->selling_price;
            }

            // Transform the result into a resource collection
            $products = ProductsResource::collection($result);
        }
    }

    // Calculate pagination data
    $total = count($products);
    $last_page = ceil($total / $limit); // Last page is the total products divided by limit, rounded up
    $current_page = $offset; // Current page is the offset provided in the request

    // Prepare the response data
    $data = [
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset,
        'current_page' => $current_page,
        'last_page' => $last_page,
        'products' => $products,
    ];

    return response()->json($data, 200);
}

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $product = $this->product->findOrFail($request->id);
            $image_path = public_path('/storage/app/public/product/') . $product->image;
            if (!is_null($image_path)) {
                $product->delete();
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            return response()->json([
                'success' => true,
                'message' => translate('Product deleted successfully'),
            ], 200);
        } catch (\Throwable $th) {
            throw $th;
            return redirect()->back()->with('success', 'Product not deleted!');
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function orderGetSearch(Request $request): JsonResponse
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        $search = $request->name;
        if (!empty($search)) {
            $result = $this->order->where('id', 'like', '%' . $search . '%')->latest()->paginate($limit, ['*'], 'page', $offset);
            $data = [
                'total' => $result->total(),
                'limit' => $limit,
                'orders' => $result->items(),
            ];
        } else {
            $data = [
                'total' => 0,
                'limit' => $limit,
                'offset' => $offset,
                'orders' => [],
            ];
        }
        return response()->json($data, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function customerOrders(Request $request): JsonResponse
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;

        $orders = $this->order->with('account')->where('user_id', $request->customer_id)->latest()->paginate($limit, ['*'], 'page', $offset);
        $data = [
            'total' => $orders->total(),
            'limit' => $limit,
            'offset' => $offset,
            'orders' => $orders->items(),
        ];
        return response()->json($data, 200);
    }
    public function reservationsInvoice(Request $request, $id)
{
    $reserveProducts =CurrentReserveProduct::
        where('seller_id', Auth::id())->where('id',$id)
        ->with(['customer', 'seller']) // جلب البيانات بالعلاقات لتقليل الاستعلامات
        ->get();
$seller=Seller::where('id', Auth::id())->first();
    $reserveProducts->transform(function ($item) {
        $item->data = json_decode($item->data);
        
        // تحسين عرض بيانات البائع
    

        // إضافة product_code لكل منتج في البيانات
        foreach ($item->data as $value) {
            $product = Product::find($value->product_id);
            if ($product) {
                $value->product_code = $product->product_code??'';
            }
        }

        return $item;
    });

    return response()->json([
        'total' => $reserveProducts->count(),
        'reservations' => $reserveProducts,
        'seller'=>$seller
    ], 200);
}
public function processConfirmedReturn(Request $request)
{
    // Start database transaction
    \DB::beginTransaction();

    try {
        // 1. Validate inputs
        $request->validate([
            'order_id'                  => 'required|integer|exists:orders,id',
            'return_quantities_hidden'  => 'required|array',
            'return_unit_hidden'        => 'required|array',
            'date'                      => 'required|date',
            'type'                      => 'nullable|integer',
        ]);

        // 2. Retrieve old order and its details
        $orderId       = $request->input('order_id');
        $oldOrder      = \App\Models\Order::with('details.product')
                              ->findOrFail($orderId);
    // only sales invoices (type = 4) can be returned
if ($oldOrder->type == 7 || $oldOrder->type==12 || $oldOrder->type==24 ) {
    return response()->json([
        'error'   => true,
        'message' => 'لا يمكنك سوى عمل مرتجع لفواتير البيع فقط',
    ], 422);
}
                   
                              
        $orderProducts = $oldOrder->details;

        // 3. Retrieve financial parameters from the original order
        $extraDiscount     = $oldOrder->extra_discount ?? 0;
        $totalTax          = $oldOrder->total_tax ?? 0;
        $baseOrderAmount   = $oldOrder->order_amount ?? 0;

        // 4. Compute total discount on products
        $totalProductDiscount = $orderProducts->sum(function($item) {
            return ($item->discount_on_product * $item->quantity);
        });

        // 5. Compute overall order amount and discount ratio
        $orderAmount   = $baseOrderAmount + $extraDiscount + $totalProductDiscount - $totalTax;
        $orderAmount   = max($orderAmount, 1);  // avoid division by zero
        $discountRatio = ($extraDiscount / $orderAmount) * 100;

        // 6. Retrieve return requests
        $returnQuantities = $request->input('return_quantities_hidden', []);
        $returnUnits      = $request->input('return_unit_hidden', []);
        $returnType       = $request->input('type', 7);

        // 7. Validation: ensure we don't return more than ordered
        foreach ($orderProducts as $product) {
    $pid       = $product->product_id;
    $newReturn = isset($returnQuantities[$pid]) 
                    ? (float)$returnQuantities[$pid] 
                    : 0;

    if ($newReturn > 0) {
        // Decode product details to obtain unit_value
        $details   = json_decode($product->product_details);
        $unitValue = $details->unit_value ?? 1;

        // Which unit did the user choose for this return?
        $chosenUnit = $returnUnits[$pid] ?? 1;  // 1 = large, 0 = small

        // Grab the old order detail record
        $oldDetail = $oldOrder->details
                        ->where('product_id', $pid)
                        ->first();

        // Compute how much was already returned, in the "base" (large) unit:
        if ($oldDetail) {
            if ($oldDetail->unit === 0) {
                // old detail was recorded in small units → convert back to large
                $alreadyReturned = ($oldDetail->quantity_returned ?? 0) * $unitValue;
            } else {
                // old detail was recorded in large units → use as is
                $alreadyReturned = $oldDetail->quantity_returned ?? 0;
            }
        } else {
            $alreadyReturned = 0;
        }

        // Figure out how many units are actually available on the original order
        if ($chosenUnit === 0) {
            // returning in small units → total stock = original qty × unitValue
            $availableQty       = $oldDetail->quantity ;
            $newReturnConverted = $newReturn;  // user already gave it in small units
        } else {
            // returning in large units → no conversion
            $availableQty       = $oldDetail->quantity;
            $newReturnConverted = $newReturn;
        }

        // Final check: can't return more than what's available
        if (($alreadyReturned + $newReturnConverted) > $availableQty) {
            \DB::rollBack();

            $availableToReturn = $availableQty - $alreadyReturned;
              return response()->json([
                    'error'               => true,
                    'message'             => "لقد قمت بإرجاع {$alreadyReturned} من المنتج {$oldDetail->product->name}. المتاح للإرجاع هو {$availableToReturn}",
                    'available_to_return' => $availableToReturn
                ], 422);
        }
    }
}


        // 8. Prepare aggregation variables
        $productsReturnData       = [];
        $totalReturnPrice         = 0;
        $totalReturnDiscount      = 0;
        $totalReturnExtraDiscount = 0;
        $totalReturnTax           = 0;
        $totalReturnOverall       = 0;
        $totalPriceAllProducts    = 0;

        // 9. Process each detail for return
        foreach ($orderProducts as $detail) {
            $pid            = $detail->product_id;
            $productInfo    = json_decode($detail->product_details);
            $unitValue      = $productInfo->unit_value ?? 1;
            $chosenUnit     = $returnUnits[$pid] ?? 1;
            $returnQuantity = (float) ($returnQuantities[$pid] ?? 0);
            if ($returnQuantity <= 0) continue;

            // 9a. Adjust price components by unit
            if ($chosenUnit === 0 && $detail->unit === 1) {
                $price            = $detail->price / $unitValue;
                $discount         = $detail->discount_on_product / $unitValue;
                $extraDiscPerUnit = (($discountRatio / 100) * $detail->price) / $unitValue;
                $tax              = $detail->tax_amount / $unitValue;
            } else {
                $price            = $detail->price;
                $discount         = $detail->discount_on_product;
                $extraDiscPerUnit = ($discountRatio / 100) * $detail->price;
                $tax              = $detail->tax_amount;
            }

            $finalUnitPrice = $price - $discount - $extraDiscPerUnit + $tax;
            $productsReturnData[] = compact(
                'pid', 'price', 'discount', 'extraDiscPerUnit', 'tax', 'chosenUnit', 'returnQuantity'
            );

            // 9b. Update aggregates
            $totalReturnPrice         += $returnQuantity * $price;
            $totalReturnDiscount      += $returnQuantity * $discount;
            $totalReturnExtraDiscount += $returnQuantity * $extraDiscPerUnit;
            $totalReturnTax           += $returnQuantity * $tax;
            $totalReturnOverall       += $returnQuantity * $finalUnitPrice;

            // 9c. Handle stock and product logs
            $baseQty = ($chosenUnit === 0)
                     ? ($returnQuantity / $unitValue)
                     : $returnQuantity;
            $totalPriceAllProducts += $baseQty * $detail->purchase_price;

            // Log inventory return
            $log = new \App\Models\ProductLog();
            $log->product_id = $pid;
            $log->quantity   = $baseQty;
            $log->type       = $returnType;
            $log->seller_id  = auth()->id();
            $log->branch_id  = auth()->user()->branch_id;
            $log->save();
                    $stock = Stock::where('seller_id', Auth::id())
                  ->where('product_id', $pid)
                  ->first();

            if (!$stock ) {
    // Retrieve the store associated with the seller
    $store = Store::where('seller_id', Auth::id())->first();

    if ($store) {
        // Create a new stock entry for the product
        $stock = Stock::create([
            'seller_id' => Auth::id(),
            'store_id' => $store->id,
            'product_id' => $pid,
            'main_stock' => $baseQty,
            'stock' => $baseQty,
            ['quantity' => $baseQty, 'price' => $detail->purchase_price]

        ]);
    } else {
        // Return error response if no store is found for the seller
        return response()->json(['error' => 'No store found for the seller'], 404);
    }
}elseif ($stock) {
    // تحديث كمية المخزون
   
    $stock->stock += $baseQty;

    // التعامل مع الحقل price كمصفوفة
    $currentPrices = is_array($stock->price) ? $stock->price : json_decode($stock->price, true);

    if (!is_array($currentPrices)) {
        $currentPrices = [];
    }

    // إضافة السعر الجديد إلى المصفوفة
    $currentPrices[] = [
        'quantity' => $baseQty,
        'price' => $detail->purchase_price,
    ];

    // إعادة تخزين المصفوفة
    $stock->price = json_encode($currentPrices, JSON_UNESCAPED_UNICODE);
    $stock->save();
}

            
        }
        

        // 10. Create new Order for return
        $newOrder = new \App\Models\Order();
        $newOrder->owner_id             = auth()->id();
        $newOrder->user_id              = $oldOrder->user_id;
        $newOrder->parent_id            = $oldOrder->id;
        $newOrder->branch_id            = auth()->user()->branch_id;
        $newOrder->cash                 = 2;
        $newOrder->type                 = $returnType;
        $newOrder->total_tax            = $totalReturnTax;
        $newOrder->order_amount         = $totalReturnOverall;
        $newOrder->extra_discount       = $totalReturnExtraDiscount;
        $newOrder->coupon_discount_amount = 0;
        $newOrder->collected_cash       = 0;
        $newOrder->transaction_reference = 0;
        $newOrder->date                 = $request->date;
        $newOrder->save();

        // 11. Create CurrentOrder record
        $newCurrentOrder = new \App\Models\CurrentOrder();
        $newCurrentOrder->owner_id             = auth()->id();
        $newCurrentOrder->user_id              = $oldOrder->user_id;
        $newCurrentOrder->cash                 = 2;
        $newCurrentOrder->type                 = $returnType;
        $newCurrentOrder->total_tax            = $totalReturnTax;
        $newCurrentOrder->order_amount         = $totalReturnOverall;
        $newCurrentOrder->extra_discount       = $totalReturnExtraDiscount;
        $newCurrentOrder->coupon_discount_amount = 0;
        $newCurrentOrder->collected_cash       = 0;
        $newCurrentOrder->transaction_reference = 0;
        $newCurrentOrder->save();

        // 12. Accounting: customer payable
        $customer       = \App\Models\Customer::findOrFail($oldOrder->user_id);
        $payableAccount = \App\Models\Account::findOrFail($customer->account_id);
        $payableAccountTo = \App\Models\Account::findOrFail(40);

        $payTrans = new \App\Models\Transection();
        $payTrans->tran_type      = $newOrder->type;
        $payTrans->seller_id      = auth()->user()->id;
        $payTrans->branch_id      = auth()->user()->branch_id;
        $payTrans->account_id     = $customer->account_id;
        $payTrans->account_id_to  = 40;
        $payTrans->amount         = $totalReturnOverall - $totalReturnTax;
        $payTrans->description    = 'فاتورة مرتجع بيع';
        $payTrans->debit          = $totalReturnOverall - $totalReturnTax;
        $payTrans->credit         = 0;
        $payTrans->balance        = $payableAccount->balance - ($totalReturnOverall - $totalReturnTax);
        $payTrans->debit_account  = 0;
        $payTrans->credit_account = $totalReturnOverall - $totalReturnTax;
        $payTrans->balance_account= $payableAccountTo->balance - ($totalReturnOverall - $totalReturnTax);
        $payTrans->date           = now()->format('Y/m/d');
        $payTrans->customer_id    = $oldOrder->user_id;
        $payTrans->order_id       = $newOrder->id;
        $payTrans->save();

        $payableAccount->balance   -= ($totalReturnOverall - $totalReturnTax);
        $payableAccount->total_out += ($totalReturnOverall - $totalReturnTax);
        $payableAccount->save();

        $payableAccountTo->balance   -= ($totalReturnOverall - $totalReturnTax);
        $payableAccountTo->total_out += ($totalReturnOverall - $totalReturnTax);
        $payableAccountTo->save();

        // 13. Accounting: tax
        $taxAccountTo = \App\Models\Account::findOrFail(28);
        $taxAccount   = \App\Models\Account::findOrFail($customer->account_id);

        $taxTrans = new \App\Models\Transection();
        $taxTrans->tran_type      = $newOrder->type;
        $taxTrans->seller_id      = auth()->user()->id;
        $taxTrans->branch_id      = auth()->user()->branch_id;
        $taxTrans->account_id     = $customer->account_id;
        $taxTrans->account_id_to  = 28;
        $taxTrans->amount         = $totalReturnTax;
        $taxTrans->description    = 'فاتورة مرتجع بيع';
        $taxTrans->debit          = $totalReturnTax;
        $taxTrans->credit         = 0;
        $taxTrans->balance        = $taxAccount->balance - $totalReturnTax;
        $taxTrans->debit_account  = 0;
        $taxTrans->credit_account = $totalReturnTax;
        $taxTrans->balance_account= $taxAccountTo->balance - $totalReturnTax;
        $taxTrans->date           = now()->format('Y/m/d');
        $taxTrans->customer_id    = $oldOrder->user_id;
        $taxTrans->order_id       = $newOrder->id;
        $taxTrans->save();

        $taxAccount->balance   -= $totalReturnTax;
        $taxAccount->total_out -= $totalReturnTax;
        $taxAccount->save();

        $taxAccountTo->balance   -= $totalReturnTax;
        $taxAccountTo->total_out -= $totalReturnTax;
        $taxAccountTo->save();

        // 14. Accounting: stock
        $seller           = \App\Models\Seller::findOrFail(auth()->user()->id);
        $stockAccount     = \App\Models\Account::findOrFail($seller->account_id);
        $stockAccountTo   = \App\Models\Account::findOrFail(47);

        $stockTrans = new \App\Models\Transection();
        $stockTrans->tran_type       = $newOrder->type;
        $stockTrans->seller_id       = auth()->user()->id;
        $stockTrans->branch_id       = auth()->user()->branch_id;
        $stockTrans->cost_id         = $request->cost_id;
        $stockTrans->account_id      = 47;
        $stockTrans->account_id_to   = $seller->account_id;
        $stockTrans->amount          = $totalPriceAllProducts;
        $stockTrans->description     = 'فاتورة مرتجع مبيعات';
        $stockTrans->debit           = $totalPriceAllProducts;
        $stockTrans->credit          = 0;
        $stockTrans->balance         = $stockAccount->balance - $totalPriceAllProducts;
        $stockTrans->debit_account   = 0;
        $stockTrans->credit_account  = $totalPriceAllProducts;
        $stockTrans->balance_account = $stockAccountTo->balance + $totalPriceAllProducts;
        $stockTrans->date            = now()->format('Y/m/d');
        $stockTrans->customer_id     = $oldOrder->user_id;
        $stockTrans->order_id        = $newOrder->id;
        $stockTrans->save();

        $stockAccount->balance   -= $totalPriceAllProducts;
        $stockAccount->total_out += $totalPriceAllProducts;
        $stockAccount->save();

        $stockAccountTo->balance  += $totalPriceAllProducts;
        $stockAccountTo->total_in += $totalPriceAllProducts;
        $stockAccountTo->save();

        // 15. Update customer balance
        $customer->balance += $totalReturnOverall;
        $customer->save();

        // 16. Generate and save QR code
        $qrcodeData = url("invoice/{$newOrder->id}");
        $qrCode     = new \Endroid\QrCode\QrCode($qrcodeData);
        $writer     = new \Endroid\QrCode\Writer\PngWriter();
        $qrcodePath = "qrcodes/order_{$newOrder->id}.png";
        \Storage::disk('public')->put($qrcodePath, $writer->write($qrCode)->getString());

        // Assign QR code and save
        $newOrder->qrcode = $qrcodePath;
        $newOrder->save();

        // Commit transaction
        \DB::commit();

        // 17. Save return order details
        foreach ($productsReturnData as $pd) {
            $detail        = new \App\Models\OrderDetail();
            $detail->order_id            = $newOrder->id;
            $detail->product_id          = $pd['pid'];
            $detail->product_details     = json_encode($pd);
            $detail->quantity            = $pd['returnQuantity'];
            $detail->unit                = $pd['chosenUnit'];
            $detail->price               = $pd['price'];
            $detail->tax_amount          = $pd['tax'];
            $detail->discount_on_product = $pd['discount'];
            $detail->discount_type       = 'discount_on_product';
            $detail->save();
        }

        // 18. Update original details' returned_quantity
        foreach ($productsReturnData as $pd) {
            $pid         = $pd['pid'];
            $returnQty   = $pd['returnQuantity'];
            $unit        = $pd['chosenUnit'];

            $oldDetail = $oldOrder->details->firstWhere('product_id', $pid);
            if ($oldDetail) {
                $info      = json_decode($oldDetail->product_details);
                $unitValue = $info->unit_value ?? 1;
                $returnInBase = ($unit === 0) ? ($returnQty / $unitValue) : $returnQty;

                $oldDetail->quantity_returned = ($oldDetail->quantity_returned ?? 0) + $returnInBase;
                $oldDetail->save();
            }
        }

        // 19. Return success JSON
        return response()->json([
            'success'      => true,
            'message'      => 'تم تنفيذ المرتجع بنجاح',
            'return_order' => $newOrder->load('details'),
            'totals'       => [
                'price'         => $totalReturnPrice,
                'discount'      => $totalReturnDiscount,
                'extraDiscount' => $totalReturnExtraDiscount,
                'tax'           => $totalReturnTax,
                'overall'       => $totalReturnOverall,
            ],
        ], 200);

    } catch (\Exception $e) {
        \DB::rollBack();
        return response()->json([
            'error'   => true,
            'message' => $e->getMessage(),
        ], 500);
    }
}


}


