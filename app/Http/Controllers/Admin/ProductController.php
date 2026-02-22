<?php

namespace App\Http\Controllers\Admin;

use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use PDF;
use App\CPU\Helpers;
use App\Models\Unit;
use App\Models\Brand;
use App\Models\StockBatch;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\ProductExpire;
use App\Models\ReserveProduct;
use App\Models\OrderDetail;
use App\Models\Customer;
use App\Models\Stock;
use App\Models\ProductLog;
use App\Models\Branch;
use App\Models\Account;
use App\Models\Transection;
use App\Models\Order;
use App\Models\StockHistory;
use App\Models\Store;
use App\Models\Region;
use App\Models\Taxe;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function App\CPU\translate;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Console\Input\Input;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminSeller;
use App\Models\Seller;
use Illuminate\Support\Facades\Schema;
use App\Models\PaymentVoucher;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;

class ProductController extends Controller
{
    public function __construct(
        private Unit $unit,
        private Brand $brand,
        private Product $product,
        private ProductExpire $productexpire,
        private Category $category,
        private Supplier $supplier,
        private Taxe $taxe,
        private Store $store
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
public function getreportProducts(Request $request)
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

    if (!in_array("report.productsales", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    // Validate input
    $validated = $request->validate([
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'product_name' => 'nullable|string',
        'product_code' => 'nullable|string',
        'seller_id' => 'nullable|exists:admins,id',
        'region_id' => 'nullable|exists:regions,id',
        'order_type' => 'nullable|integer',
        'payment_status' => 'nullable|in:paid,unpaid',
        'supplier_id' => 'nullable|exists:suppliers,id', // Add supplier_id filter
    ]);

    $adminId = Auth::guard('admin')->id();
    $sellers = Seller::join('admin_sellers', 'admins.id', '=', 'admin_sellers.seller_id')
        ->where('admin_sellers.admin_id', $adminId)
        ->select('admins.*')
        ->get();

    $regions = Region::all();

    $start_date = isset($validated['start_date']) ? Carbon::parse($validated['start_date'])->startOfDay() : null;
    $end_date = isset($validated['end_date']) ? Carbon::parse($validated['end_date'])->endOfDay() : null;

    $query = OrderDetail::query();

    if (!empty($validated['product_name'])) {
        $query->whereJsonContains('product_details->name', $validated['product_name']);
    }

    if (!empty($validated['product_code'])) {
        $query->whereJsonContains('product_details->product_code', $validated['product_code']);
    }

    if ($start_date && $end_date) {
        $query->whereBetween('created_at', [$start_date, $end_date]);
    }

    if (!empty($validated['seller_id'])) {
        $query->whereHas('order', function ($q) use ($validated) {
            $q->where('owner_id', $validated['seller_id']);
        });
    }

    if (!empty($validated['order_type'])) {
        $query->whereHas('order', function ($q) use ($validated) {
            $q->where('type', $validated['order_type']);
        });
    }

    if (!empty($validated['region_id'])) {
        $query->whereHas('order.customer', function ($q) use ($validated) {
            $q->where('region_id', $validated['region_id']);
        });
    }

    if (!empty($validated['payment_status'])) {
        $query->whereHas('order', function ($q) use ($validated) {
            if ($validated['payment_status'] === 'paid') {
                $q->whereColumn('order_amount', 'transaction_reference');
            } elseif ($validated['payment_status'] === 'unpaid') {
                $q->whereColumn('order_amount', '>', 'transaction_reference');
            }
        });
    }

    if (!empty($validated['supplier_id'])) {
        $query->whereHas('order.supplier', function ($q) use ($validated) {
            $q->where('id', $validated['supplier_id']);
        });
    }

    // Paginate order details with 10 items per page
    $orderDetails = $query->paginate(10);

    // Calculate grouped data with unique products
    $products = $orderDetails->getCollection()->groupBy('product_details->id')->map(function ($details) {
        return $details->map(function ($detail) use ($details) {
            $productDetails = json_decode($detail->product_details);
            $order = $detail->order;

            // Fetch the last price of the product from the latest detail based on created_at
            $lastPrice = $details->sortByDesc('created_at')->first()->price;

            return [
                'product_id' => $productDetails->id??'',
                'product_name' => app()->getLocale() == 'ar' ? $productDetails->name_ar : $productDetails->name,
                'product_code' => $productDetails->product_code??'',
                'unit_value' => $productDetails->unit_value??'',
                'selling_price' => $lastPrice, // Get the last price of the product
                'quantity' => $detail->quantity,
                'order_type' => $order->type ?? '',
                'order_id' => $order->id ?? '',
                'transaction_reference' => $order->transaction_reference ?? '',
                'created_at' => $order->created_at ?? '',
                'total_selling_price' => $lastPrice * $detail->quantity, // Calculate total selling price
                'seller' => $order->seller->email ?? '',
                'customer' => $order->customer->name ?? $order->supplier->name ?? '',
                'unit' => $detail->unit == 0 ? (
                    // If unit is 0, display the subUnit name
                    $detail->product->unit->subUnits->first()?->name ?? ''
                ) : (
                    // If unit is 1, display the unit_type
                    $detail->product->unit->unit_type ?? ''
                ),
                'region' => $order->customer->regions->name ?? '',
            ];
        });
    });

    // Calculate totals
    $productCount = $orderDetails->groupBy('product_details->id')->count();
    $quantitySum = $orderDetails->sum('quantity');

    return view('admin-views.product.indexreport', compact('products', 'sellers', 'regions', 'productCount', 'quantitySum', 'orderDetails'));
}
public function getreportProductsPurchase(Request $request)
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

    if (!in_array("report.productpurchases", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    // Validate input
    $validated = $request->validate([
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'product_name' => 'nullable|string',
        'product_code' => 'nullable|string',
        'seller_id' => 'nullable|exists:admins,id',
        'region_id' => 'nullable|exists:regions,id',
        'payment_status' => 'nullable|in:paid,unpaid',
        'supplier_id' => 'nullable|exists:suppliers,id',
    ]);

    $adminId = Auth::guard('admin')->id();
    $sellers = Seller::join('admin_sellers', 'admins.id', '=', 'admin_sellers.seller_id')
        ->where('admin_sellers.admin_id', $adminId)
        ->select('admins.*')
        ->get();

    $regions = Region::all();

    $start_date = isset($validated['start_date']) ? Carbon::parse($validated['start_date'])->startOfDay() : null;
    $end_date = isset($validated['end_date']) ? Carbon::parse($validated['end_date'])->endOfDay() : null;

    $query = OrderDetail::query();

    if (!empty($validated['product_name'])) {
        $query->whereJsonContains('product_details->name', $validated['product_name']);
    }

    if (!empty($validated['product_code'])) {
        $query->whereJsonContains('product_details->product_code', $validated['product_code']);
    }

    if ($start_date && $end_date) {
        $query->whereBetween('created_at', [$start_date, $end_date]);
    }

    if (!empty($validated['seller_id'])) {
        $query->whereHas('order', function ($q) use ($validated) {
            $q->where('owner_id', $validated['seller_id']);
        });
    }

    // Force order_type to always be 12
    $query->whereHas('order', function ($q) {
        $q->where('type', 12);
    });

    if (!empty($validated['supplier_id'])) {
        $query->whereHas('order.supplier', function ($q) use ($validated) {
            $q->where('supplier_id', $validated['supplier_id']);
        });
    }

    // Get order details
    $orderDetails = $query->get();

    // Process all order details and group by product
    $products = $orderDetails->groupBy(function ($detail) {
        // Group by product id to aggregate product details
        $productDetails = json_decode($detail->product_details, true);
        return $productDetails['id']; // Group by 'id' from decoded product details
    })->map(function ($details) {
        $productDetails = json_decode($details->first()->product_details, true);
        $totalQuantity = 0;
        $lastPrice = 0;
        $unit = '';
        $discount = 0;

        // Loop through each order detail for the same product
        foreach ($details as $detail) {
            if ($detail->unit == 0) {
                // If unit = 0, use unit_value to adjust quantity
                $adjustedQuantity = $detail->quantity / $productDetails['unit_value'];
            } else {
                // If unit = 1, just add the quantity as is
                $adjustedQuantity = $detail->quantity;
            }

            // Sum quantities for the same product
            $totalQuantity += $adjustedQuantity;

            // Get the last price from the most recent order detail
            $lastPrice = $detail->price;
                        $taxamount = $detail->tax_amount;
            $unit = $detail->unit == 0 ? $detail->product->unit->subUnits->first()?->name ?? '' : $detail->product->unit->unit_type;
            
            // Calculate the discount based on the last order
            $order = $detail->order;
            if ($order && $order->extra_discount && $order->order_amount) {
                // Calculate the discount for this product
                $extraDiscountPercentage = $order->extra_discount / $order->order_amount * 100;
                $discount += ($lastPrice * $extraDiscountPercentage / 100);
            }
        }

        // Adjust the quantity based on whether it is a decimal or an integer
        if (is_float($totalQuantity)) {
            $totalQuantity = $totalQuantity * $productDetails['unit_value']; // Convert to smallest unit if decimal
        }
        
        // Return the final result with the discount applied
        return [
            'product_id' => $productDetails['id'],
            'product_name' => app()->getLocale() == 'ar' ? $productDetails['name_ar'] : $productDetails['name'],
            'product_code' => $productDetails['product_code'],
            'unit_value' => $productDetails['unit_value'],
            'selling_price' => $lastPrice , // Apply discount on the price
            'quantity' => $totalQuantity,
            'unit' => $unit,
            'tax_amount'=>$taxamount,
            'total_selling_price' => ($lastPrice - $discount) * $totalQuantity, // Apply discount on total selling price
            'discount' => $discount, // Store the discount for reference
        ];
    });

    // Calculate totals
    $productCount = $products->count();
    $quantitySum = $products->sum(function ($product) {
        return $product['quantity'];
    });
    $suppliers = Supplier::all();

    // Return the results to the view
    return view('admin-views.product.indexreportpurchase', compact('products', 'sellers', 'regions', 'productCount', 'quantitySum', 'orderDetails', 'suppliers'));
}
public function getreportProductsSales(Request $request,$type)
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

    if (!in_array("report.gain", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    // Validate input
    $validated = $request->validate([
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'product_name' => 'nullable|string',
        'product_code' => 'nullable|string',
        'seller_id' => 'nullable|exists:admins,id',
        'region_id' => 'nullable|exists:regions,id',
        'payment_status' => 'nullable|in:paid,unpaid',
    ]);

    // Get the authenticated admin's ID
    $adminId = Auth::guard('admin')->id();

    // Fetch sellers for the admin
    $sellers = Seller::join('admin_sellers', 'admins.id', '=', 'admin_sellers.seller_id')
        ->where('admin_sellers.admin_id', $adminId)
        ->select('admins.*')
        ->get();

    // Parse start and end dates, if provided
    $start_date = isset($validated['start_date']) ? Carbon::parse($validated['start_date'])->startOfDay() : null;
    $end_date = isset($validated['end_date']) ? Carbon::parse($validated['end_date'])->endOfDay() : null;

    // Query for orders of type 4 (sales)
    $queryType4 = OrderDetail::query();
    $queryType4->whereHas('order', function ($q) use ($validated) {
        $q->where('type', 4); // Sales type
        if (!empty($validated['seller_id'])) {
            $q->where('owner_id', $validated['seller_id']); // Apply seller_id filter for type 4
        }
    });

    // Filter by product name if provided
    if (!empty($validated['product_name'])) {
        $queryType4->whereJsonContains('product_details->name', $validated['product_name']);
    }

    // Filter by product code if provided
    if (!empty($validated['product_code'])) {
        $queryType4->whereJsonContains('product_details->product_code', $validated['product_code']);
    }

    // Filter by date range if provided
    if ($start_date && $end_date) {
        $queryType4->whereBetween('created_at', [$start_date, $end_date]);
    }

    // Query for orders of type 12 (purchase), no seller_id filter applied here
    $queryType12 = OrderDetail::query();
    $queryType12->whereHas('order', function ($q) {
    $q->where('type', 12); // Purchase type
});

  if ($start_date && $end_date) {
        // If there are orders in the given range, filter by them, else fetch nearest order before the range
        $ordersInRange = $queryType12->whereBetween('created_at', [$start_date, $end_date])->get();

        if ($ordersInRange->isEmpty()) {
            // Get the order with the closest created_at before start_date
            $queryType12->where('created_at', '<', $start_date)
                        ->orderBy('created_at', 'desc')
                        ->limit(1); // Get the closest order before the start_date
        }
    } else {
        // If no dates are provided, get all orders of type 12
        $queryType12->orderBy('created_at', 'desc');
    }



    // Get results for both sales and purchases
    $orderDetailsType4 = $queryType4->get();
    $orderDetailsType12 = $queryType12->get();

    // Group purchase prices by product ID
// Group purchase prices by product ID
$purchasePrices = $orderDetailsType12->groupBy(function ($detail) {
    $productDetails = json_decode($detail->product_details, true);
    return $productDetails['id'];
})->map(function ($details) use ($orderDetailsType12) {
    // Initialize variables
    $totalQuantity = 0;
    $lastPrice = 0;
    $lastQuantity = 0;
    $taxpurchase=0;
    $productDetails = json_decode($details->first()->product_details, true);
    $productId = $productDetails['id'];
    $unitValue = $productDetails['unit_value'];

    // Iterate through all details for the given product to adjust the quantity and calculate price
    foreach ($details as $detail) {
        // Adjust quantity based on unit value
        $adjustedQuantity = $detail->unit == 0 ? $detail->quantity / $unitValue : $detail->quantity;
        $totalQuantity += $adjustedQuantity;
        $taxpurchase = $detail->tax_amount;
        // Calculate selling price after applying discount
        $order = $detail->order;
        // Calculate the base amount (order total net of tax plus any extra discount)
$finalOrderAmount = $order->order_amount - $order->total_tax + $order->extra_discount;

// Guard against zero before computing the percentage
if ($finalOrderAmount != 0) {
    $discountPercentage = ($order->extra_discount / $finalOrderAmount) * 100;
} else {
    // If there’s nothing to divide into, assume no discount percentage
    $discountPercentage = 0;
}

// Finally apply that percentage to each detail’s price
$lastPrice = $detail->price * (1 - ($discountPercentage / 100));

        // Update last quantity (adjusted)
        $lastQuantity = $adjustedQuantity;

        // Determine unit type
        $unit = $detail->unit == 0
            ? ($detail->product->unit->subUnits->first()?->name ?? '')
            : $detail->product->unit->unit_type;
    }

    // // Get the last order with type = 12 to apply discount
    // $lastOrder = $details->last()->order;

    // if ($lastOrder) {
    //     // Calculate final price after discount using order data (order_amount, total_tax, extra_discount)
    //     $finalOrderAmount = $lastOrder->order_amount - $lastOrder->total_tax + $lastOrder->extra_discount;

    //     if ($finalOrderAmount > 0) {
    //         $discountPercentage = ($lastOrder->extra_discount / $finalOrderAmount) * 100;
    //         $lastPrice -= ($lastPrice * ($discountPercentage / 100));
    //     }
    // }

    return [
        'product_id' => $productId,
        'purchase_price' => $lastPrice,
        'total_quantity' => $totalQuantity,
        'last_quantity' => $lastQuantity,
        'taxpurchase'=>$taxpurchase ?? 0,
        'unit' => $unit
    ];
});


    // Process sales prices for orderDetailsType4
    $products = $orderDetailsType4->groupBy(function ($detail) {
        $productDetails = json_decode($detail->product_details, true);
        return $productDetails['id'];
    })->map(function ($details) use ($purchasePrices) {
        $productDetails = json_decode($details->first()->product_details, true);
        $totalQuantity = 0;
        $lastPrice = 0;
        $unit = '';

$totalQuantity = 0;
$totalLastPrice = 0; // إجمالي سعر المنتج بعد الحسابات
$totalQuantity = 0;
$totalLastPrice = 0; // إجمالي سعر المنتج بعد الحسابات

foreach ($details as $detail) {
    $productId = $productDetails['id'];
    $unitValue = $productDetails['unit_value'];

    // **تحويل الكمية للوحدة الصغرى**
    $adjustedQuantity = ($detail->unit == 1) ? $detail->quantity * $unitValue : $detail->quantity;
    $totalQuantity += $adjustedQuantity;

    // **حساب السعر بعد الخصم والضرائب**
    $order = $detail->order;
   // 1. احسب صافي المبلغ (بعد الضريبة) زائد الخصم الإضافي
$netAmount         = $order->order_amount - $order->total_tax;
$finalOrderAmount  = $netAmount + $order->extra_discount;

// 2. إذا كان $finalOrderAmount > 0 احسب النسبة، وإلا اعتبرها صفر
if ($finalOrderAmount > 0) {
    $discountPercentage = ($order->extra_discount / $finalOrderAmount) * 100;
} else {
    $discountPercentage = 0;
}

// 3. احسب السعر النهائي للعنصر
$lastPrice = $detail->price * (1 - $discountPercentage / 100);


    // **تحويل السعر للوحدة الصغرى**
$adjustedPrice = ((int)$detail->unit === 1)
    ? (float)$lastPrice / ( (is_numeric($unitValue) && (float)$unitValue > 0) ? (float)$unitValue : 1 )
    : (float)$lastPrice;
$detailtax=$detail->tax;
    // **جمع إجمالي الأسعار لكل الفواتير**
    $totalLastPrice += $adjustedPrice * $adjustedQuantity;
    $totaldetailtax=0;
    $totaldetailtax+=$detailtax*$adjustedQuantity;
}

// **حساب الكمية النهائية بعد التحويل**
$finalQuantity = $totalQuantity;

// **حساب السعر النهائي بعد جميع العمليات**
$finalLastPrice = $totalLastPrice;

// ✅ الآن `$finalQuantity` فيه إجمالي الكمية بعد التحويل للوحدة الصغرى  
// ✅ و `$finalLastPrice` فيه إجمالي السعر بعد الخصومات والضرائب لكل الفواتير بالوحدة الصغرى  


// ✅ الآن لديك `finalLastPrice` يحتوي على إجمالي السعر بعد الحسابات عبر كل الفواتير

        // Fetch purchase price from purchase details (order type 12)
$purchasePrice = isset($purchasePrices->get($productDetails['id'])['purchase_price']) 
    ? $purchasePrices->get($productDetails['id'])['purchase_price'] 
    : 0;
if ($purchasePrices->has($productDetails['id'])) {
    $taxpurchase = $purchasePrices->get($productDetails['id'])['taxpurchase'];
} else {
    $taxpurchase = 0; // Default value
}
if (!$purchasePrice) {
    // Fetch the product from the products table
    $product = \App\Models\Product::find($productDetails['id'] ?? $product_id);

    // Check if the product exists and retrieve its purchase price
    $purchasePrice = $product ? $product->purchase_price : 0;

    // Adjust for unit value if applicable
    if ($purchasePrice && isset($productDetails['unit_value'])) {
        $purchasePrice /= $productDetails['unit_value'];
    }
}

        // Adjust the final quantity if it's a decimal
$taxSetting = \App\Models\BusinessSetting::where('key', 'tax')->first();
$taxRate = $taxSetting ? $taxSetting->value : 0; // Default to 0 if not found
        // Return the product information
        $qtyF       = (float) ($detail->quantity ?? 0);
$lineNetF   = (float) ($lastPrice ?? 0);      // صافي قبل الضريبة لسطر التفصيل
$lineTaxF   = (float) ($detailtax ?? 0);      // ضريبة سطر التفصيل
$lineTotal  = $lineNetF + $lineTaxF;          // إجمالي السطر بعد الضريبة

        return [
            'product_id' => $productDetails['id'],
            'product_name' => app()->getLocale() == 'ar' ? $productDetails['name_ar'] : $productDetails['name'],
            'product_code' => $productDetails['product_code'],
    'selling_price'       => $qtyF > 0 ? round($lineTotal / $qtyF, 2) : 0.0, // سعر الوحدة
    'total_selling_price' => round($lineTotal, 2),
            'purchase_price' => $purchasePrice+$taxpurchase, // Purchase price from order type 12
            'total_purchase_price' => $purchasePrice,
            'last_quantity' => $finalQuantity, // Only calculating for sales type (4)
            'unit' => $unit,
            'taxpurchase'=>$taxpurchase,
            'unit_value'=>$productDetails['unit_value'],
            'profit' => ($finalLastPrice - $purchasePrice) * $finalQuantity, // Profit calculation
        ];
    });

    // Calculate totals
    $productCount = $products->count();
    $quantitySum = $products->sum(function ($product) {
        return $product['last_quantity'];
    });

    // Return the results to the view
    return view('admin-views.product.indexreportsales', compact('products', 'sellers', 'productCount', 'quantitySum', 'orderDetailsType4','type'));
}
  public function product_type()
    {
        return view('admin-views.product.product_type');
    }
public function getReportProductsMainStock(Request $request)
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

    if (!in_array("report.mainstock", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    // Validate the input
    $validated = $request->validate([
        'start_date'   => 'nullable|date',
        'end_date'     => 'nullable|date|after_or_equal:start_date',
        'product_name' => 'nullable|string',
        'product_code' => 'nullable|string',
        'branch_id'    => 'nullable|integer',
    ]);

    $adminId = Auth::guard('admin')->id();

    // Fetch related sellers for the admin
    $sellers = Seller::join('admin_sellers', 'admins.id', '=', 'admin_sellers.seller_id')
        ->where('admin_sellers.admin_id', $adminId)
        ->select('admins.*')
        ->get();

    // Set date range filters
$start_date = !empty($validated['start_date'])
    ? Carbon::parse($validated['start_date'])->startOfDay()
    : Carbon::create(2024, 10, 22, 0, 0, 0);

$end_date = !empty($validated['end_date'])
    ? Carbon::parse($validated['end_date'])->endOfDay()
    : Carbon::create(2080, 10, 22, 23, 59, 59);
    // Query unique product IDs from product_logs
    $productLogsQuery = ProductLog::query();

    // Filter by date range if provided
    if ($start_date && $end_date) {
        $productLogsQuery->whereBetween('created_at', [$start_date, $end_date]);
    }

    // Add filter by branch_id if provided
    if (!empty($validated['branch_id'])) {
        $productLogsQuery->where('branch_id', $validated['branch_id']);
    }

    // Filter by product name or code if provided
    if (!empty($validated['product_name'])) {
        $productLogsQuery->whereHas('product', function ($query) use ($validated) {
            $query->where('name', 'LIKE', '%' . $validated['product_name'] . '%');
        });
    }
    if (!empty($validated['product_code'])) {
        $productLogsQuery->whereHas('product', function ($query) use ($validated) {
            $query->where('product_code', 'LIKE', '%' . $validated['product_code'] . '%');
        });
    }

    // Group by product_id and calculate the totals for each type
    $productLogs = $productLogsQuery->get()
        ->groupBy('product_id')
        ->map(function ($logs) {
            $summary = [
                'sold'             => 0, 
                'sale_returned'    => 0, 
                'purchased'        => 0, 
                'purchase_returned'=> 0, 
                'issued'           => 0, 
                'delegate_returned'=> 0, 
                'damaged'          => 0, 
                'initial'          => 0, 
            ];
// جولة على السجلات
foreach ($logs as $log) {
    // نحول الكمية إلى عدد صحيح (أو عشري إذا احتجت)
    $qty  = is_numeric($log->quantity) ? (int) $log->quantity : 0;
    $type = (int) $log->type;

    switch ($type) {
        case 4:
            $summary['sold'] += $qty;
            break;

        case 7:
            $summary['sale_returned'] += $qty;
            break;

        case 12:
            $summary['purchased'] += $qty;
            break;

        case 24:
            $summary['purchase_returned'] += $qty;
            break;

        case 100:
            $summary['issued'] += $qty;
            break;

        case 200:
            $summary['delegate_returned'] += $qty;
            break;

        case 0:
            $summary['damaged'] += $qty;
            break;

        case 1:
            $summary['initial'] += $qty;
            break;

        // يمكنك إضافة default إذا أردت تجاهل أنواع أخرى
    }
}

            return $summary;
        });

    // Prepare final data with product details
    $finalProducts = collect();
    foreach ($productLogs as $productId => $summary) {
        $product = Product::find($productId);

        // Calculate "start" based on logs before the start_date
        $startLogsQuery = ProductLog::where('product_id', $productId);
        if ($start_date) {
            $startLogsQuery->where('created_at', '<', $start_date);
        }
        // Apply branch filter for start logs as well
        if (!empty($validated['branch_id'])) {
            $startLogsQuery->where('branch_id', $validated['branch_id']);
        }
        $startLogs = $startLogsQuery->get();

        $startSummary = [
            'sold'             => 0,
            'sale_returned'    => 0,
            'purchased'        => 0,
            'purchase_returned'=> 0,
            'issued'           => 0,
            'delegate_returned'=> 0,
            'damaged'          => 0,
            'initial'          => 0,
        ];
foreach ($startLogs as $log) {
    // تحويل النوع والكمية لعدد صحيح (أو float إذا أردت كسور)
    $type = (int) $log->type;
    $qty  = is_numeric($log->quantity)
          ? (int) $log->quantity
          : 0;

    switch ($type) {
        case 4:
            $startSummary['sold'] += $qty;
            break;

        case 7:
            $startSummary['sale_returned'] += $qty;
            break;

        case 12:
            $startSummary['purchased'] += $qty;
            break;

        case 24:
            $startSummary['purchase_returned'] += $qty;
            break;

        case 100:
            $startSummary['issued'] += $qty;
            break;

        case 200:
            $startSummary['delegate_returned'] += $qty;
            break;

        case 0:
            $startSummary['damaged'] += $qty;
            break;

        case 1:
            $startSummary['initial'] += $qty;
            break;

        default:
            // تجاهل الأنواع غير المعرفة أو أضف معالجة أخرى إذا لزم
            break;
    }
}
        $startQuantity = $startSummary['purchased'] 
            + $startSummary['sale_returned'] 
            + $startSummary['delegate_returned'] 
            + $startSummary['initial']
            - $startSummary['damaged'] 
            - $startSummary['issued'] 
            - $startSummary['purchase_returned'] 
            - $startSummary['sold'];

        // Find the purchase price from order details or fallback to product's purchase price
        $orderDetails = OrderDetail::where('product_id', $productId)
            ->join('orders', 'orders.id', '=', 'order_details.order_id') // Join orders table
            ->where('orders.type', 12) // Filter by type in the orders table
            ->when($start_date, function ($query) use ($start_date) {
                return $query->where('order_details.created_at', '<', $start_date);
            })
            ->latest('order_details.created_at')
            ->first();

        // Apply discount to purchase price
        $purchasePrice = $orderDetails ? $orderDetails->price : $product->purchase_price;
        $tax_purchase = $orderDetails 
            ? $orderDetails->tax_amount 
            : $product->purchase_price * (($product->taxe->amount ?? 0) / 100); 
        $unitPrice = $orderDetails ? $orderDetails->unit : 1;

        // If order detail is found, adjust purchase price with discount
        if ($orderDetails) {
            $order = $orderDetails->order;
            $finalorder = $order->order_amount - $order->total_tax + $order->extra_discount;
            $discountPercentage = ($order->extra_discount / $finalorder) * 100;
            $purchasePrice -= ($purchasePrice * ($discountPercentage / 100));
        }

        $finalProducts->push([
            'product_id'       => $productId,
            'product_name'     => $product->name ?? 'N/A',
            'product_code'     => $product->product_code ?? 'N/A',
            'start'            => $startQuantity,
            'sold'             => $summary['sold'],
            'sale_returned'    => $summary['sale_returned'],
            'purchased'        => $summary['purchased'],
            'purchase_returned'=> $summary['purchase_returned'],
            'issued'           => $summary['issued'],
            'delegate_returned'=> $summary['delegate_returned'],
            'damaged'          => $summary['damaged'],
            'initial'          => $summary['initial'],
            'unit_value'       => $product->unit_value,
            'subunit'          => $product->unit?->subUnits?->first()?->name ?? '',
            'unit'             => $product->unit?->unit_type,
            'tax_purchase'     => $tax_purchase,
            'now'              => $startQuantity 
                                  + $summary['purchased'] 
                                  + $summary['sale_returned'] 
                                  + $summary['delegate_returned'] 
                                  + $summary['initial']
                                  - $summary['damaged'] 
                                  - $summary['issued'] 
                                  - $summary['purchase_returned'] 
                                  - $summary['sold'],
            'purchase_price'   => $purchasePrice,  // Include purchase price
            'unitPrice'        => $unitPrice,
        ]);
    }

    // Group by product unit if needed (assuming 'unit' is a field in the products table)
    $productsByUnit = $finalProducts->groupBy('unit');
$branches=Branch::all();
    return view('admin-views.product.indexreportmainstock', compact('finalProducts', 'productsByUnit','branches'));
}

public function getReportProductsAllStock(Request $request)
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

    if (!in_array("report.allstock", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    // Validate input
    $validated = $request->validate([
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'product_name' => 'nullable|string',
        'product_code' => 'nullable|string',
        'seller_id' => 'nullable|exists:admins,id',
    ]);

    $adminId = Auth::guard('admin')->id();

    // Fetch related sellers for the admin
    $sellers = Seller::join('admin_sellers', 'admins.id', '=', 'admin_sellers.seller_id')
        ->where('admin_sellers.admin_id', $adminId)
        ->select('admins.*')
        ->get();

    // Set date ranges
    $start_date = isset($validated['start_date']) ? Carbon::parse($validated['start_date'])->startOfDay() : null;
    $end_date = isset($validated['end_date']) ? Carbon::parse($validated['end_date'])->endOfDay() : Carbon::now()->endOfDay();
    $previous_start_date = $start_date ? $start_date->copy()->subDay() : null;
    $previous_end_date = $start_date ? $start_date->copy()->subDay()->endOfDay() : null;

    $seller_id = $validated['seller_id'] ?? null;

    // Fetch the last stock record from stockhistory before start_date
    $lastStockHistoryQuery = StockHistory::where('seller_id', $seller_id);

    if ($start_date) {
        $lastStockHistoryQuery->where('created_at', '<', $start_date);
    }

    $lastStockHistory = $lastStockHistoryQuery->latest()->first();

    // Set previous start date to the last stock history record or use the start date if no history found
    $previous_start_dateew = $lastStockHistory ? $lastStockHistory->created_at : Carbon::parse($start_date);

    // Ensure the format is consistent for later use
    $previous_start_dateewFormatted = $previous_start_dateew->toDateTimeString();

    // If no stock history exists, use 0 as the initial stock
    $initialStock = $lastStockHistory ? $lastStockHistory->stock : 0;
  $previous_start_date = $lastStockHistory ? $lastStockHistory->created_at : Carbon::parse($start_date);

    // Fetch the first reserve product after the last stock history
    $firstReserveProduct = ReserveProduct::where('seller_id', $seller_id)
        ->where('created_at', '>=', $previous_start_date)
        ->orderBy('created_at', 'asc')
        ->first();
    // Fetch reserved products for the given period (after stockhistory)
    $reservedProducts = $this->fetchReservedProductsFromStart($start_date, $seller_id, $initialStock);
    $reservedProductsPreviousDay = $this->fetchReservedProductsFromPreviousDay($previous_start_dateewFormatted, $previous_end_date, $seller_id, $initialStock);

    // Fetch order details for current and previous day
    $orderDetails = $this->fetchOrderDetails([4, 7], $start_date, $end_date, $seller_id);
    $orderDetailsPreviousDay = $this->fetchOrderDetails([4, 7], $previous_start_dateewFormatted, $previous_end_date, $seller_id);

    // Process and calculate stock data
    $productsFromOrders = $this->processOrderDetails($orderDetails, $reservedProducts, $orderDetailsPreviousDay, $reservedProductsPreviousDay);

    // Include unprocessed reserved products
    $allReservedProducts = collect($reservedProducts)->keys();
    $processedProducts = $productsFromOrders->keys();
    $unprocessedProducts = $allReservedProducts->diff($processedProducts);

    // Adding unprocessed reserved products data
    foreach ($unprocessedProducts as $productId) {
        $reservedStock = $reservedProducts[$productId] ?? 0;
        $previousStock = $reservedProductsPreviousDay[$productId] ?? 0;

        $productsFromOrders[$productId] = [
            'product_id' => $productId,
            'total_quantity' => $reservedStock,
            'reserved_stock' => $reservedStock,
            'sold_quantity' => 0,
            'returned_quantity' => 0,
            'previous_stock' => $previousStock,
            'final_stock' => $reservedStock,
        ];
    }

    // Return the view with the data
    return view('admin-views.product.indexreportallstock', compact('productsFromOrders', 'sellers'));
}

private function fetchReservedProductsFromStart($start_date, $seller_id, $initialStock)
{
    $reserveProductsQuery = ReserveProduct::where('seller_id', $seller_id);

    if ($start_date) {
        $reserveProductsQuery->where('created_at', '>=', $start_date);
    }

    return $reserveProductsQuery->get()
        ->flatMap(fn($reserveProduct) => json_decode($reserveProduct->data, true))
        ->mapWithKeys(fn($product) => [$product['product_id'] => $product['stock']])
        ->toArray();
}

private function fetchReservedProductsFromPreviousDay($previous_start_date, $previous_end_date, $seller_id, $initialStock)
{
    return ReserveProduct::where('seller_id', $seller_id)
        ->whereBetween('created_at', [$previous_start_date, $previous_end_date])
        ->get()
        ->flatMap(fn($reserveProduct) => json_decode($reserveProduct->data, true))
        ->mapWithKeys(fn($product) => [$product['product_id'] => $product['stock']])
        ->toArray();
}

private function fetchOrderDetails($statuses, $start_date, $end_date, $seller_id)
{
    return Order::whereIn('type', $statuses) 
        ->whereBetween('created_at', [$start_date, $end_date])
        ->where('owner_id', $seller_id)
        ->get()
        ->flatMap(function($order) {
            // Filter the order details based on the order type
            if (in_array($order->type, [4, 7])) {
                return $order->details;
            }
            return collect();
        });
}

private function processOrderDetails($orderDetails, $reservedProducts, $orderDetailsPreviousDay, $reservedProductsPreviousDay)
{
    $products = collect();

    $soldQuantityCurrentPeriod = [];
    $soldQuantityPreviousPeriod = [];
    $totalSoldQuantity = []; // To store total sold quantities for each product
    $returnedQuantityPreviousPeriod = []; // To store returned quantities for previous period

    // Process order details for the current period
    foreach ($orderDetails as $orderDetail) {
        $productId = $orderDetail->product_id;

        $reservedStock = $reservedProducts[$productId] ?? 0;
        $previousReservedStock = $reservedProductsPreviousDay[$productId] ?? 0;

        // Initialize sold quantity for the current period
        $soldQuantityCurrentPeriod[$productId] = 0;
        $returnedQuantity = 0;

        // Get product's unit value
        $product = Product::find($productId);
        $productUnitValue = $product ? $product->unit_value : 1; // Use unit_value or default to 1 if not found

        // Calculate sold or returned quantities based on order type
        if ($orderDetail->order->type == 4) { // Sale
            // If unit_value is 0, handle division properly
            if ($orderDetail->unit == 0) {
                $soldQuantityCurrentPeriod[$productId] += $orderDetail->quantity / $productUnitValue;
            } else {
                $soldQuantityCurrentPeriod[$productId] += $orderDetail->quantity;
            }
        } elseif ($orderDetail->order->type == 7) { // Return
            $returnedQuantity = $orderDetail->quantity;
        }

        // Add the sold quantity to the total
        if (!isset($totalSoldQuantity[$productId])) {
            $totalSoldQuantity[$productId] = 0;
        }
        $totalSoldQuantity[$productId] += $soldQuantityCurrentPeriod[$productId];

        // Calculate initial stock for the period
        $initialStock = $reservedStock - $totalSoldQuantity[$productId] + $returnedQuantity;

        // Update previous reserved stock for the final stock calculation
        $previousReservedStock = $previousReservedStock - ($soldQuantityPreviousPeriod[$productId] ?? 0) + ($returnedQuantityPreviousPeriod[$productId] ?? 0);

        // Final stock calculation
        $finalStock = $previousReservedStock + $reservedStock - $totalSoldQuantity[$productId] + $returnedQuantity;

        // Store product data
        $products[$productId] = [
            'product_id' => $productId,
            'total_quantity' => $reservedStock,
            'reserved_stock' => $reservedStock + $previousReservedStock + $returnedQuantity,
            'sold_quantity' => $totalSoldQuantity[$productId], // Use total sold quantity
            'returned_quantity' => $returnedQuantity,
            'previous_stock' => $previousReservedStock,
            'initial_stock' => $initialStock,
            'final_stock' => $finalStock,
        ];
    }

    // Process order details for the previous period
    foreach ($orderDetailsPreviousDay as $orderDetailPreviousDay) {
        $productId = $orderDetailPreviousDay->product_id;

        if ($orderDetailPreviousDay->order->type == 4) { // Sale
            $soldQuantityPreviousPeriod[$productId] = $soldQuantityPreviousPeriod[$productId] ?? 0;
            $soldQuantityPreviousPeriod[$productId] += $orderDetailPreviousDay->quantity;
        } elseif ($orderDetailPreviousDay->order->type == 7) { // Return
            $returnedQuantityPreviousPeriod[$productId] = $returnedQuantityPreviousPeriod[$productId] ?? 0;
            $returnedQuantityPreviousPeriod[$productId] += $orderDetailPreviousDay->quantity;
        }
    }

    return $products;
}










public function getReportProductsStocks(Request $request)
{
    // Validate the input
    $validated = $request->validate([
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'product_name' => 'nullable|string',
        'product_code' => 'nullable|string',
    ]);

    $adminId = Auth::guard('admin')->id();

    // Fetch related sellers for the admin
    $sellers = Seller::join('admin_sellers', 'admins.id', '=', 'admin_sellers.seller_id')
        ->where('admin_sellers.admin_id', $adminId)
        ->select('admins.*')
        ->get();

$start_date = isset($validated['start_date']) ? Carbon::parse($validated['start_date'])->startOfDay() : null;
$end_date = isset($validated['end_date']) ? Carbon::parse($validated['end_date'])->endOfDay() : null;

    // Query order details
    $query = OrderDetail::query();

    if (!empty($validated['product_name'])) {
        $query->whereJsonContains('product_details->name', $validated['product_name']);
    }

    if (!empty($validated['product_code'])) {
        $query->whereJsonContains('product_details->product_code', $validated['product_code']);
    }

    if ($start_date && $end_date) {
        $query->whereBetween('updated_at', [$start_date, $end_date]);
    }

    $query->whereHas('order', fn($q) => $q->where('type', 12));

    // Fetch and group order details
    $orderDetails = $query->get();

    $productsFromOrders = $orderDetails->groupBy(fn($detail) => json_decode($detail->product_details, true)['id'])
        ->map(function ($details) {
            $productDetails = json_decode($details->first()->product_details, true);
            $totalQuantity = 0;
            $unit = '';

            foreach ($details as $detail) {
                $product = $detail->product;

                if ($detail->unit == 0) {
                    $adjustedQuantity = $detail->quantity / $product->unit_value;
                    $unit = $product->unit->subUnits->first()?->name ?? '';
                } else {
                    $adjustedQuantity = $detail->quantity;
                    $unit = $product->unit->unit_type;
                }

                $totalQuantity += $adjustedQuantity;
            }

            return [
                'product_id' => $productDetails['id'],
                'price' => $details->first()->price,
                'last_price' => $productDetails['selling_price'],
                'product_name' => app()->getLocale() == 'ar' ? $productDetails['name_ar'] : $productDetails['name'],
                'product_code' => $productDetails['product_code'],
                'quantity' => $totalQuantity,
                'unit' => $unit,
                'unit_value' => $product->unit_value,
                'first' => 0,
            ];
        });

    // Query stock data
    $stocks = StockHistory::query();
    $stocksData = Stock::query();
    $ProductExpire = ProductExpire::query();

    if ($start_date) {
        $stocks->where('updated_at', '>=', $start_date);
        $stocksData->where('updated_at', '>=', $start_date);
        $ProductExpire->where('updated_at', '>=', $start_date);
    }

    $stocks = $stocks->get();
    $stocksData = $stocksData->get();
    $ProductExpire = $ProductExpire->get();

    $combinedStocks = $stocks->merge($stocksData)->merge($ProductExpire)->groupBy('product_id')
        ->map(function ($stockGroup) {
            $firstStock = $stockGroup->first();
            $totalMainStock = $stockGroup->sum('main_stock');
            $totalQuantityExport = $stockGroup->sum('quantity');
            $totalMainStock += $totalQuantityExport;

            $unit_expire = '';
            $product = Product::find($firstStock->product_id);

            if ($product) {
                $unit = Unit::find($product->unit_type);

                if ($unit) {
                    $unit_expire = $unit->subUnits->first()?->name ?? '';
                }
            }

            return [
                'product_id' => $firstStock->product_id,
                'quantity_export' => $totalMainStock,
                'unit_expire' => $unit_expire,
                'total_main_stock' => $totalMainStock,
                'first' => 0,
            ];
        });

    // Merge products with stock data
    $finalProducts = $productsFromOrders->map(function ($product) use ($combinedStocks) {
        $stockData = $combinedStocks->firstWhere('product_id', $product['product_id']);

        $product['quantity_export'] = $stockData['quantity_export'] ?? 0;
        $product['unit_expire'] = $stockData['unit_expire'] ?? '';
        $product['last'] = $product['quantity'] + $product['first'] - $product['quantity_export'];

        // Fetch last price from recent orders
        $orderDetailIds = OrderDetail::where('product_id', $product['product_id'])->pluck('order_id');
        $orders = Order::whereIn('id', $orderDetailIds)->where('type', 12)->pluck('id');
        $latestOrderDetail = OrderDetail::whereIn('order_id', $orders)
            ->where('product_id', $product['product_id'])
            ->orderBy('created_at', 'desc')
            ->first();
$order = Order::where('id', $latestOrderDetail->order_id)->first();

if ($order) {
    $discountPercentage = $order->extra_discount / $order->order_amount * 100;
    $product['last_price'] =  $latestOrderDetail->price-($latestOrderDetail->price * $discountPercentage/100) ?? $product['first_price'];
}


        return $product;
    });

    $combinedStocks->each(function ($stock) use ($finalProducts) {
        if (!$finalProducts->pluck('product_id')->contains($stock['product_id'])) {
            
       // Fetch last price from recent orders
$orderDetailIds = OrderDetail::where('product_id', $stock['product_id'])->pluck('order_id');
$orders = Order::whereIn('id', $orderDetailIds)->where('type', 12)->pluck('id');
$latestOrderDetail = OrderDetail::whereIn('order_id', $orders)
    ->where('product_id', $stock['product_id'])
    ->orderBy('created_at', 'desc')
    ->first();
$productprice=Product::where('id',$stock['product_id'])->first();
if ($latestOrderDetail) {
    $order = Order::where('id', $latestOrderDetail->order_id)->first();

    if ($order) {
        $discountPercentage = $order->extra_discount / $order->order_amount * 100;
        $product['last_price'] = $latestOrderDetail->price - ($latestOrderDetail->price * $discountPercentage / 100);
    } else {
        $product['last_price'] = $stock['first_price'] ??$productprice->selling_price;
    }
} else {
    $product['last_price'] = $stock['first_price'] ?? $productprice->selling_price;
}

            $finalProducts->push([
                'product_id' => $stock['product_id'],
                'product_name' => Product::find($stock['product_id'])->name ?? 'N/A',
                'product_code' => Product::find($stock['product_id'])->product_code ?? 'N/A',
                'quantity' => 0,
                'unit' => $stock['unit_expire'],
                'unit_value' => 0,
                'quantity_export' => $stock['quantity_export'],
                'first' => 0,
                'last' => 0,
                'last_price' =>  $product['last_price'],
            ]);
        }
    });

    $finalProducts = $finalProducts->values();
    $productsByUnit = $finalProducts->groupBy('unit');

    return view('admin-views.product.indexreportmainstock', compact('finalProducts', 'productsByUnit'));
}























public function list(Request $request)
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

    if (!in_array("product.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    $query_param = [];
    $search = $request['search'];
    $sort_orderQty = $request['sort_orderQty'];
    $search_quantity = $request['search_quantity'];
 $product_type    = $request->get('product_type', 'product');


    $query = $this->product->with('productexpire')        ->where('product_type', $product_type)

        ->when($request->has('search'), function ($q) use ($search) {
            $key = explode(' ', $search);
            $q->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%")
                        ->orWhere('product_code', 'like', "%{$value}%");
                }
            });
        })
        ->when($search_quantity, function ($q) use ($search_quantity) {
            $q->whereHas('productexpire', function ($subQuery) use ($search_quantity) {
                $subQuery->where('quantity', '=', $search_quantity);
            });
        })
        ->when($sort_orderQty == 'quantity_expire_asc', function ($q) {
            return $q->join('product_expires', 'products.id', '=', 'product_expires.product_id')
                ->select('products.*')
                ->groupBy('products.id')
                ->orderByRaw('SUM(product_expires.quantity) ASC');
        })
        ->when($sort_orderQty == 'quantity_expire_desc', function ($q) {
            return $q->join('product_expires', 'products.id', '=', 'product_expires.product_id')
                ->select('products.*')
                ->groupBy('products.id')
                ->orderByRaw('SUM(product_expires.quantity) DESC');
        })
        ->when($sort_orderQty == 'quantity_asc', function ($q) {
            return $q->orderBy('quantity', 'asc');
        })
        ->when($sort_orderQty == 'quantity_desc', function ($q) {
            return $q->orderBy('quantity', 'desc');
        })
        ->when($sort_orderQty == 'order_asc', function ($q) {
            return $q->orderBy('order_count', 'asc');
        })
        ->when($sort_orderQty == 'order_desc', function ($q) {
            return $q->orderBy('order_count', 'desc');
        })
        ->when($sort_orderQty == 'default', function ($q) {
            return $q->orderBy('id');
        });

    $products = $query->latest()->paginate(Helpers::pagination_limit())
                    ->appends([
                        'search' => $search,
                        'sort_orderQty' => $sort_orderQty,
                        'search_quantity' => $search_quantity,
                    ]);

    return view('admin-views.product.list', compact('products', 'search', 'sort_orderQty', 'search_quantity'));
}
public function listProductsByOrderType(Request $request)
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

    if (!in_array("product.show", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    // Initialize the query with necessary relationships
    $query = OrderDetail::with(['product', 'order.customer', 'order.supplier'])
        ->when($request->filled('product_id'), function($q) use ($request) {
            // Filter by product_id if provided
            $q->where('product_id', $request->product_id);
        })
        ->when($request->filled('customer_id'), function($q) use ($request) {
            // Filter by customer_id if provided
            $q->whereHas('order.customer', function ($subQuery) use ($request) {
                $subQuery->where('id', $request->customer_id);
            });
        })
        ->when($request->filled('order_type'), function($q) use ($request) {
            // Filter by order_type if provided
            $q->whereHas('order', function ($subQuery) use ($request) {
                $subQuery->where('type', $request->order_type);
            });
        })
        ->when($request->filled('date_from') && $request->filled('date_to'), function ($q) use ($request) {
            // Filter by date range if both date_from and date_to are provided
            $q->whereBetween('created_at', [$request->date_from, $request->date_to]);
        });

    // If no filters are provided, return the most recent 10 records by default
    if (!$request->hasAny(['product_id', 'customer_id', 'order_type', 'date_from', 'date_to'])) {
        $query->latest(); // Apply default sorting if no filters are present
    }

    // Fetch paginated products
    $products = $query->paginate(10)->appends($request->all());

    // Clone query to use for additional calculations
    $queryForCalculations = clone $query;

    // Grouped totals for each order type
    $sales = $queryForCalculations->whereHas('order', function ($q) {
        $q->where('type', 4); // مبيعات
    })->get();

    $purchaseReturns = $queryForCalculations->whereHas('order', function ($q) {
        $q->where('type', 7); // مرتجع مبيعات
    })->get();

    $purchases = $queryForCalculations->whereHas('order', function ($q) {
        $q->where('type', 12); // مشتريات
    })->get();

    $salesReturns = $queryForCalculations->whereHas('order', function ($q) {
        $q->where('type', 24); // مرتجع مشتريات
    })->get();

    // Additional calculations
    $last_sale_price = optional($sales->last())->price;
    $last_purchase_price = optional($purchases->last())->price;

    $max_purchase_price = $purchases->max('price');
    $min_purchase_price = $purchases->min('price');

    $min_sale_quantity = $sales->min('quantity');
    $min_purchase_quantity = $purchases->min('quantity');
    $total_stock_quantity = Product::where('id', $request->product_id)->sum('quantity'); // Adjust the field name as needed

    $customers = Customer::all();

    return view('admin-views.product.trackproduct', compact(
        'products', 'customers', 'sales', 'purchaseReturns', 'purchases', 'salesReturns',
        'last_sale_price', 'last_purchase_price', 'max_purchase_price', 'min_purchase_price',
        'min_sale_quantity', 'min_purchase_quantity', 'total_stock_quantity'
    ));
}





public function listreportexpire(Request $request)
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

    if (!in_array("report.expire", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    $query_param = [];
    $search = $request['search'];
    $sort_orderQty = $request['sort_orderQty'];
    $search_quantity = $request['search_quantity'];

    $query = $this->product->with('productexpire')->where('product_type','product')
        ->when($request->has('search'), function ($q) use ($search) {
            $key = explode(' ', $search);
            $q->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%")
                        ->orWhere('product_code', 'like', "%{$value}%");
                }
            });
        })
        ->when($search_quantity, function ($q) use ($search_quantity) {
            $q->whereHas('productexpire', function ($subQuery) use ($search_quantity) {
                $subQuery->where('quantity', '=', $search_quantity);
            });
        })
        // Sorting by product expire quantity in ascending order
        ->when($sort_orderQty == 'quantity_expire_asc', function ($q) {
            return $q->join('product_expires', 'products.id', '=', 'product_expires.product_id')
                ->select('products.*')
                ->groupBy('products.id')
                ->orderByRaw('SUM(product_expires.quantity) ASC');
        })
        // Sorting by product expire quantity in descending order
        ->when($sort_orderQty == 'quantity_expire_desc', function ($q) {
            return $q->join('product_expires', 'products.id', '=', 'product_expires.product_id')
                ->select('products.*')
                ->groupBy('products.id')
                ->orderByRaw('SUM(product_expires.quantity) DESC');
        })
        // Sorting by total quantity
        ->when($sort_orderQty == 'quantity_asc', function ($q) {
            return $q->orderBy('quantity', 'asc');
        })
        ->when($sort_orderQty == 'quantity_desc', function ($q) {
            return $q->orderBy('quantity', 'desc');
        })
        // Sorting by order count
        ->when($sort_orderQty == 'order_asc', function ($q) {
            return $q->orderBy('order_count', 'asc');
        })
        ->when($sort_orderQty == 'order_desc', function ($q) {
            return $q->orderBy('order_count', 'desc');
        })
        // Sorting by name (alphabetically)
        ->when($sort_orderQty == 'name_asc', function ($q) {
            return $q->orderBy('name', 'asc');
        })
        ->when($sort_orderQty == 'name_desc', function ($q) {
            return $q->orderBy('name', 'desc');
        })
        // Sorting by selling price
        ->when($sort_orderQty == 'price_asc', function ($q) {
            return $q->orderBy('selling_price', 'asc');
        })
        ->when($sort_orderQty == 'price_desc', function ($q) {
            return $q->orderBy('selling_price', 'desc');
        })
        // Sorting by expire date
        ->when($sort_orderQty == 'expire_date_asc', function ($q) {
            return $q->orderBy('expiry_date', 'asc');
        })
        ->when($sort_orderQty == 'expire_date_desc', function ($q) {
            return $q->orderBy('expiry_date', 'desc');
        })
        // Default sorting
        ->when($sort_orderQty == 'default', function ($q) {
            return $q->orderBy('id');
        });

    $products = $query->latest()->paginate(Helpers::pagination_limit())
                    ->appends([
                        'search' => $search,
                        'sort_orderQty' => $sort_orderQty,
                        'search_quantity' => $search_quantity,
                    ]);

    return view('admin-views.product.listreportexpire', compact('products', 'search', 'sort_orderQty', 'search_quantity'));
}



    /**
     * @return Application|Factory|View
     */
    public function index($type)
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

    if (!in_array("product.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $categories = $this->category->where(['position' => 0])->where('status',1)->get();
        $brands = $this->brand->get();
        $suppliers = $this->supplier->get();
        $stores = $this->store->get();
        $units = $this->unit->get();
        $taxes = $this->taxe->get();
        if($type=='product'){

        return view('admin-views.product.add', compact('categories','taxes','brands','suppliers','units','stores'));
        }else{
                 return view('admin-views.product.add_service', compact('categories','taxes','brands','suppliers','units','stores'));
   
        }
    }
     public function indexexpire()
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

    if (!in_array("product.expire", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $categories = $this->category->where(['position' => 0])->where('status',1)->get();
        $brands = $this->brand->get();
        $suppliers = $this->supplier->get();
        $units = $this->unit->get();
        $products = $this->product->where('product_type','product')->get();
        return view('admin-views.product.addexpire', compact('categories','brands','suppliers','units','products'));
    }
    public function listexpire(Request $request)
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

    if (!in_array("product.expire.show", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $productsexpire = $this->productexpire->get();

    return view('admin-views.product.listexpire', compact('productsexpires'));
}
public function listexpireinvoice(Request $request)
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

    if (!in_array("product.expire.show", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    // Fetch sellers for the dropdown
    $sellers = Seller::all();

    // Build the query with optional filters
    $query = $this->productexpire->query();

    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
    }

    if ($request->filled('seller_id')) {
        $query->where('seller_id', $request->seller_id);
    }

    // Get the filtered results
    $productsexpires = $query->get();

    return view('admin-views.product.listexpireinvoice', compact('productsexpires', 'sellers'));
}


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get_categories(Request $request): JsonResponse
    {
        $cat = $this->category->where(['parent_id' => $request->parent_id])->get();
        $res = '<option value="' . 0 . '" disabled selected>---'.translate('Select').'---</option>';
        foreach ($cat as $row) {
            if ($row->id == $request->sub_category) {
                $res .= '<option value="' . $row->id . '" selected >' . $row->name . '</option>';
            } else {
                $res .= '<option value="' . $row->id . '">' . $row->name . '</option>';
            }
        }
        return response()->json([
            'options' => $res,
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */

public function store(Request $request): RedirectResponse
{
    $request->validate([
        'name' => 'required|unique:products',
        'product_code' => 'required|unique:products',
        'category_id' => 'required',
        'unit_type' => 'required',
        'unit_value' => 'required|numeric|min:0',
        'quantity' => 'required',
        'selling_price' => 'required|numeric|min:1',
        'purchase_price' => 'required|numeric|min:1',
        'expiry_date' => 'nullable|date',
        'type' => 'required|string',
    ], [
        'name.required' => translate('Product arabic name is required'),
        'category_id.required' => translate('Category is required'),
    ]);

    // حساب الخصم
    $dis = ($request['discount_type'] == 'percent') ? 
        ($request['selling_price'] / 100) * $request['discount'] 
        : $request['discount'];

    if ($request['selling_price'] <= $dis) {
        Toastr::warning(translate('Discount cannot be more than Selling price'));
        return back();
    }

    // إنشاء المنتج
    $products = new Product();
    $products->name = $request->name;
    $products->name_en = $request->name_en;
    $products->product_code = $request->product_code;
    $products->category_id = $request->category_id;
    $products->unit_type = $request->unit_type;
    $products->unit_value = $request->unit_value;
    $products->brand = $request->brand_id;
    $products->discount_type = $request->discount_type;
    $products->discount = $request->discount ?? 0;
            $products->product_type = 'prodcut' ;

    $products->tax = $request->tax ?? 0;
    $products->order_count = 0;
    $products->selling_price = $request->selling_price;
    $products->purchase_price = $request->purchase_price;
    $products->expiry_date = $request->expiry_date;
        $products->tax_id = $request->tax_id;

    $products->type = $request->type;
    $products->image = Helpers::upload('product/', 'png', $request->file('image'));
    $products->supplier_id = $request->supplier_id;

    // الحصول على `branch_id` الخاص بالمستخدم
    $branch_id = auth('admin')->user()->branch_id ?? null;

  
if ($branch_id !=1)  {
        // حفظ الكمية في العمود المخصص للفرع
        $columnName = "branch_{$branch_id}";
        if (!Schema::hasColumn('products', $columnName)) {
            Schema::table('products', function (Blueprint $table) use ($columnName) {
                $table->integer($columnName)->default(0);
            });
        }
        $products->$columnName = $request->quantity;
    } else {
        // حفظ الكمية في العمود العام
        $products->quantity = $request->quantity;
    }
   $tax=Taxe::where('id',$request->tax_id)->first();
   $taxRate = $tax->amount ?? 0;
$priceWithTax = $request->purchase_price * (1 + $taxRate / 100);

    // حفظ المنتج
    $products->save();
 StockBatch::create([
        'product_id' => $products->id,
        'branch_id'=>$branch_id,
        'quantity' => $request->quantity,
        'price' =>$priceWithTax,

    ]);
    // تسجيل العملية في سجل المنتج
    $productlog = new ProductLog();
    $productlog->product_id = $products->id;
    $productlog->quantity = $request->quantity;
    $productlog->branch_id = $branch_id;
    $productlog->type = 0;
    $productlog->save();

    Toastr::success(translate('تم إضافة المنتج بنجاح'));
    return redirect()->route('admin.product.list');
}
public function storeservice(Request $request): RedirectResponse
{
    $request->validate([
        'name' => 'required|unique:products',
        'product_code' => 'required|unique:products',
        'category_id' => 'required',
        'selling_price' => 'required|numeric|min:1',
    ], [
        'name.required' => translate('Product arabic name is required'),
        'category_id.required' => translate('Category is required'),
    ]);

    // حساب الخصم
    $dis = ($request['discount_type'] == 'percent') ? 
        ($request['selling_price'] / 100) * $request['discount'] 
        : $request['discount'];

    if ($request['selling_price'] <= $dis) {
        Toastr::warning(translate('Discount cannot be more than Selling price'));
        return back();
    }

    // إنشاء المنتج
    $products = new Product();
    $products->name = $request->name;
    $products->name_en = $request->name_en;
    $products->product_code = $request->product_code;
    $products->category_id = $request->category_id;
    $products->discount_type = $request->discount_type;
    $products->discount = $request->discount ?? 0;
    $products->tax = $request->tax ?? 0;
        $products->product_type = 'service' ;
    $products->order_count = 0;
    $products->selling_price = $request->selling_price;
    $products->purchase_price = 0;
        $products->tax_id = $request->tax_id;
   $products->image = Helpers::upload('product/', 'png', $request->file('image'));
    $products->supplier_id = $request->supplier_id;


    // حفظ المنتج
    $products->save();


    Toastr::success(translate('تم إضافة الخدمة بنجاح'));
    return redirect()->route('admin.product.list');
}

public function storeexpire(Request $request): RedirectResponse
{
    // نستخدم معاملة تضمن الذرّية
    DB::beginTransaction();

    try {
        /** ---------------- صلاحيات ---------------- */
        $adminId = Auth::guard('admin')->id();
        $admin   = DB::table('admins')->where('id', $adminId)->first();

        if (!$admin) {
            Toastr::warning('غير مسموح لك! كلم المدير.');
            DB::rollBack();
            return back();
        }

        $role = DB::table('roles')->where('id', $admin->role_id)->first();
        if (!$role) {
            Toastr::warning('غير مسموح لك! كلم المدير.');
            DB::rollBack();
            return back();
        }

        $decodedData = json_decode($role->data, true);
        if (is_string($decodedData)) $decodedData = json_decode($decodedData, true);
        if (!is_array($decodedData) || !in_array("product.expire", $decodedData)) {
            Toastr::warning('غير مسموح لك! كلم المدير.');
            DB::rollBack();
            return back();
        }

        /** ---------------- تحقق المدخلات ---------------- */
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity'   => ['required', 'numeric', 'gt:0'],
            'unit'       => ['required', 'in:0,1'], // 0 صغرى - 1 كبرى
            'note'       => ['nullable', 'string', 'max:500'],
        ]);

        $product   = Product::findOrFail($validated['product_id']);
        $branch_id = auth('admin')->user()->branch_id;
        $note      = $validated['note'] ?? null;

        // تأكيد قيمة unit_value > 0 لتفادي القسمة على صفر
        $unitValue = max(1, (float)($product->unit_value ?? 1));

        /**
         * تحويل الكمية إلى "الوحدة الأساسية للمخزون" (نفترض أن دفعات المخزون بالوحدة الكبرى)
         * unit = 0 (صغرى) => نُحوِّل إلى كبرى بالقسمة على unit_value
         * unit = 1 (كبرى) => تظل كما هي
         */
        $qtyMajor = (int)$validated['unit'] === 0
            ? ((float)$validated['quantity'] / $unitValue)
            : (float)$validated['quantity'];

        // نُقرب لـ 4 منازل لتفادي كسور طويلة
        $qtyMajor = round($qtyMajor, 4);

        /** ---------------- دفعات المخزون FIFO ---------------- */
        $stockBatches = StockBatch::where('product_id', $product->id)
            ->where('branch_id', $branch_id)
            ->where('quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->lockForUpdate() // لمنع تنازع الخصم
            ->get();

        $remainingQty = $qtyMajor;
        $totalWriteOffCost = 0.0;

        // ننشئ سجل الهالك الآن (سنجدد الكمية والسعر بعد الخصم)
        $productExpire = new ProductExpire();
        $productExpire->product_id = $product->id;
        $productExpire->branch_id  = $branch_id;
        $productExpire->quantity   = 0;           // نحدّث لاحقًا
        $productExpire->unit       = (int)$validated['unit']; // 0 صغرى / 1 كبرى
        $productExpire->note       = $note;
        $productExpire->seller_id  = auth('admin')->user()->id;
        $productExpire->price      = 0;           // نحدّث لاحقًا
        $productExpire->save();

        foreach ($stockBatches as $batch) {
            if ($remainingQty <= 0) break;

            $deduct = min($batch->quantity, $remainingQty);

            // خصم من الدفعة
            $batch->quantity = round($batch->quantity - $deduct, 4);
            $batch->save();

            // تكلفة هذه الكمية (نفترض price تكلفة للوحدة الكبرى)
            $totalWriteOffCost += ((float)$batch->price * (float)$deduct);

            // تراكم الكمية المُهلكة
            $productExpire->quantity = round($productExpire->quantity + $deduct, 4);

            // المتبقي
            $remainingQty = round($remainingQty - $deduct, 4);
        }

        // إن تبقّى كمية => المخزون غير كافٍ
        if ($remainingQty > 0) {
            Toastr::error(translate('الكمية المتاحة في المخزون لا تكفي لإثبات الهالك.'));
            DB::rollBack();
            return back();
        }

        // حدّث إجمالي تكلفة الهالك
        $totalWriteOffCost = round($totalWriteOffCost, 4);
        $productExpire->price = $totalWriteOffCost;
        $productExpire->save();

        /** ---------------- سجل حركة المنتج ---------------- */
        $productLog = new ProductLog();
        $productLog->product_id = $product->id;
        $productLog->quantity   = $qtyMajor; // بالوحدة الكبرى
        $productLog->type       = 0; // هالك
        $productLog->seller_id  = auth('admin')->user()->id;
        $productLog->branch_id  = $branch_id;
        $productLog->save();

        /** ---------------- تحديث كميات المنتج ---------------- */
        // كمية الفرع أو العامة
        if ($branch_id != 1) {
            $columnName = "branch_{$branch_id}";
            if (!Schema::hasColumn('products', $columnName)) {
                Toastr::warning(translate('عمود الفرع غير موجود'));
                DB::rollBack();
                return back();
            }
            $product->$columnName = round((float)$product->$columnName - $qtyMajor, 4);
        } else {
            $product->quantity = round((float)$product->quantity - $qtyMajor, 4);
        }
        $product->save();

        /** ---------------- قيود يومية + معاملات مالية ---------------- */
        // حساب الهالك/الخسائر (مثال: 75) — تأكد أن هذا هو حساب المصروف/الخسارة لديك
        $expenseAccountId = 75;
        $expenseAccount   = Account::findOrFail($expenseAccountId);

        // حساب مخزون الفرع
        $branch = Branch::findOrFail($branch_id);
        $stockAccountId = $branch->account_stock_Id;
        $stockAccount   = Account::findOrFail($stockAccountId);

        // (1) قيد يومية رئيسي
        $entry = new JournalEntry();
        $entry->entry_date  = now()->format('Y-m-d');
        $entry->reference   = 'EXP-' . str_pad((string)$productExpire->id, 6, '0', STR_PAD_LEFT);
        $entry->type        = 'waste'; // نوع القيد
        $entry->description = $note ?: ('إثبات هالك مخزون للمنتج: ' . ($product->name ?? $product->id));
        $entry->created_by  = $adminId;
        $entry->branch_id   = $branch_id;
        $entry->save();

        // (2) تفصيلة — مدين (مصروف/هالك)
        $detailDebit = new JournalEntryDetail();
        $detailDebit->journal_entry_id = $entry->id;
        $detailDebit->account_id       = $expenseAccount->id;
        $detailDebit->debit            = $totalWriteOffCost;
        $detailDebit->credit           = 0;
        $detailDebit->cost_center_id   = null;
        $detailDebit->description      = $entry->description;
        $detailDebit->entry_date       = $entry->entry_date;
        $detailDebit->attachment_path  = null;
        $detailDebit->save();

        // (3) تفصيلة — دائن (مخزون)
        $detailCredit = new JournalEntryDetail();
        $detailCredit->journal_entry_id = $entry->id;
        $detailCredit->account_id       = $stockAccount->id;
        $detailCredit->debit            = 0;
        $detailCredit->credit           = $totalWriteOffCost;
        $detailCredit->cost_center_id   = null;
        $detailCredit->description      = $entry->description;
        $detailCredit->entry_date       = $entry->entry_date;
        $detailCredit->attachment_path  = null;
        $detailCredit->save();

        // (4) معاملات Transection — مدين (على حساب المصروف/الهالك)
        $newExpenseBalance = (float)$expenseAccount->balance + $totalWriteOffCost;
        $tDebit = new Transection();
        $tDebit->tran_type               = 50; // نوع العملية (هالك)
        $tDebit->seller_id               = $adminId;
        $tDebit->account_id              = $expenseAccount->id;     // المدين
        $tDebit->account_id_to           = $stockAccount->id;       // الدائن المقابل
        $tDebit->debit                   = $totalWriteOffCost;
        $tDebit->credit                  = 0;
        $tDebit->amount                  = $totalWriteOffCost;
        $tDebit->tax                     = 0;
        $tDebit->description             = $entry->description;
        $tDebit->date                    = $entry->entry_date;
        $tDebit->balance                 = $newExpenseBalance;      // رصيد حساب المصروف بعد الحركة
        $tDebit->branch_id               = $branch_id;
        $tDebit->tax_id                  = null;
        $tDebit->tax_number              = null;
        $tDebit->img                     = null;
        $tDebit->journal_entry_detail_id = $detailDebit->id;
        $tDebit->cost_id                 = null;
        // الحقول الإضافية إن كانت موجودة عندك:
        if (Schema::hasColumn('transections', 'debit_account'))  $tDebit->debit_account  = $totalWriteOffCost;
        if (Schema::hasColumn('transections', 'credit_account')) $tDebit->credit_account = 0;
        if (Schema::hasColumn('transections', 'balance_account'))$tDebit->balance_account= $newExpenseBalance;
        $tDebit->save();

        // (5) معاملات Transection — دائن (على حساب المخزون)
        $newStockBalance = (float)$stockAccount->balance - $totalWriteOffCost;
        $tCredit = new Transection();
        $tCredit->tran_type               = 50; // نفس النوع
        $tCredit->seller_id               = $adminId;
        $tCredit->account_id              = $stockAccount->id;      // الدائن
        $tCredit->account_id_to           = $expenseAccount->id;    // المدين المقابل
        $tCredit->debit                   = 0;
        $tCredit->credit                  = $totalWriteOffCost;
        $tCredit->amount                  = $totalWriteOffCost;
        $tCredit->tax                     = 0;
        $tCredit->description             = $entry->description;
        $tCredit->date                    = $entry->entry_date;
        $tCredit->balance                 = $newStockBalance;       // رصيد حساب المخزون بعد الحركة
        $tCredit->branch_id               = $branch_id;
        $tCredit->tax_id                  = null;
        $tCredit->tax_number              = null;
        $tCredit->img                     = null;
        $tCredit->journal_entry_detail_id = $detailCredit->id;
        $tCredit->cost_id                 = null;
        if (Schema::hasColumn('transections', 'debit_account'))  $tCredit->debit_account  = 0;
        if (Schema::hasColumn('transections', 'credit_account')) $tCredit->credit_account = $totalWriteOffCost;
        if (Schema::hasColumn('transections', 'balance_account'))$tCredit->balance_account= $newStockBalance;
        $tCredit->save();

        // (6) تحديث أرصدة الحسابات
        $expenseAccount->balance  = $newExpenseBalance;
        if (Schema::hasColumn('accounts', 'total_in'))  $expenseAccount->total_in  = (float)$expenseAccount->total_in  + $totalWriteOffCost;
        $expenseAccount->save();

        $stockAccount->balance    = $newStockBalance;
        if (Schema::hasColumn('accounts', 'total_out')) $stockAccount->total_out = (float)$stockAccount->total_out + $totalWriteOffCost;
        $stockAccount->save();

        /** ---------------- إنهاء ---------------- */
        DB::commit();
        Toastr::success(translate('تم إضافة الهالك وتسجيل القيد المحاسبي بنجاح.'));
        return redirect()->route('admin.product.list');

    } catch (\Throwable $e) {
        DB::rollBack();
        // لا نستخدم dd — نسجّل الخطأ ونُظهر رسالة ودّية
        \Log::error('storeexpire error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        Toastr::error('حدث خطأ غير متوقع. من فضلك حاول مرة أخرى.');
        return back()->withInput();
    }
}




    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id)
    { $adminId = Auth::guard('admin')->id();
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

    if (!in_array("product.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $product = $this->product->find($id);
        $product_category = json_decode($product->category_id);
        $categories = $this->category->where(['position' => 0])->get();
        $brands = $this->brand->get();
        $suppliers = $this->supplier->get();
        $units = $this->unit->get();
                $taxes = $this->taxe->get();

                $stores = $this->store->get();
        return view('admin-views.product.edit', compact('product','categories','brands','taxes','product_category','suppliers','units','stores'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
public function update(Request $request, $id): RedirectResponse
{
    $product = Product::findOrFail($id); // Ensure the product exists

    // Validate the request inputs
    $request->validate([
        'name' => 'required',
        'product_code'=> 'required',
        'category_id' => 'required',
    
    ]);

    // Handle discount logic
    if ($request['discount_type'] == 'percent') {
        $dis = ($request['selling_price'] / 100) * $request['discount'];
    } else {
        $dis = $request['discount'];
    }

    if ($request['selling_price'] <= $dis) {
        Toastr::warning(translate('غير مسموح الخصم اكبر من سعر البيع'));
        return back();
    }

    // Update product details
    $product->name = $request->name;
    $product->name_en = $request->name_en;
    $product->product_code = $request->product_code;
    $product->category_id = $request->category_id;
        $product->tax_id = $request->tax_id;

    $product->unit_type = $request->unit_type;
    $product->unit_value = $request->unit_value;
    $product->brand = $request->brand_id;
    $product->discount_type = $request->discount_type;
    $product->discount = $request->discount ?? 0;
    $product->tax = $request->tax ?? 0;
        // $product->tax_id = $request->tax_id ?? 0;


    // Update pricing details
    $product->selling_price = $request->selling_price;
    // $product->selling_price1 = $request->selling_price1;
    // $product->selling_price2 = $request->selling_price2;
    // $product->selling_price3 = $request->selling_price3;
    // $product->selling_price4 = $request->selling_price4;

    $product->purchase_price = $request->purchase_price;
    // $product->purchase_price1 = $request->purchase_price1;
    // $product->purchase_price2 = $request->purchase_price2;
    // $product->purchase_price3 = $request->purchase_price3;
    // $product->purchase_price4 = $request->purchase_price4;

    // Additional product data
    // $product->limit_stock = $request->limit_stock ?? 0;
    // $product->limit_web = $request->limit_web ?? 0;
    $product->expiry_date = $request->expiry_date;
    $product->type = $request->type;

    // Update the image if a new one is provided
    $product->image = $request->has('image') ? Helpers::update('product/', $product->image, 'png', $request->file('image')) : $product->image;

    // Update supplier ID
    $product->supplier_id = $request->supplier_id;

    // Save the updated product
    $product->save();

    // Update stock information
    // $stock = Stock::where('product_id', $product->id)->first();
    // if ($stock) {
    //     $stock->main_stock = $request->quantity;
    //     $stock->stock = $request->quantity;
    //     $stock->save();
    // } else {
    //     // If no stock exists, create a new stock entry
    //     $nstock = new Stock();
    //     $nstock->store_id = $request->store_id;
    //     $nstock->product_id = $product->id;
    //     $nstock->main_stock = $request->quantity;
    //     $nstock->stock = $request->quantity;
    //     $nstock->save();
    // }

    // Success notification
    Toastr::success(translate('تم تحديث بيانات المنتج بنجاح'));

    return redirect()->route('admin.product.list');
}


    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
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

    if (!in_array("product.destroy", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $product = $this->product->find($request->id);
        // if (Storage::disk('public')->exists('product/' . $product->image)) {
        //     Storage::disk('public')->delete('product/' .  $product->image);
        // }

        $product->delete();
        Toastr::success(translate('تم حذف المنتج بنجاح'));
        return back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return Application|Factory|View|RedirectResponse
     */
    public function barcode_generate(Request $request, $id)
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

    if (!in_array("product.barcode", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        if($request->limit >270)
        {
            Toastr::warning(translate('غير مسموح بانشاء اكتر من 270 فالمرة الواحدة'));
            return back();
        }
        $product = $this->product->where('id',$id)->first();
        $limit = $request->limit??4;
        return view('admin-views.product.barcode-generate',compact('product','limit'));
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function barcode($id)
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

    if (!in_array("product.barcode", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $product = $this->product->where('id',$id)->first();
        $limit = 28;
        return view('admin-views.product.barcode',compact('product','limit'));
    }

    /**
     * @return Application|Factory|View
     */
    public function bulk_import_index(): Factory|View|Application
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

    if (!in_array("product.excel.import", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        return view('admin-views.product.bulk-import');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function bulk_import_data(Request $request): RedirectResponse
    {
        try {
            $collections = (new FastExcel)->import($request->file('products_file'));
        } catch (\Exception $exception) {
            Toastr::error(translate('You have uploaded a wrong format file, please upload the right file'));
            return back();
        }

        $col_key = ['name','product_code','unit_type','unit_value','brand','category_id','sub_category_id','purchase_price','selling_price','discount_type','discount','tax','quantity', 'supplier_id'];
        foreach ($collections as $key => $collection) {
            foreach ($collection as $key => $value) {
                if ($key!="" && !in_array($key, $col_key)) {
                    Toastr::error(translate('Please upload the correct format file.'));
                    return back();
                }
            }
        }

        foreach ($collections as $key => $collection) {
            if ($collection['name'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: name ');
                return back();
            } elseif ($collection['product_code'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: product_code ');
                return back();
            } elseif ($collection['unit_type'] ==="") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: product_code ');
                return back();
            } elseif ($collection['unit_value'] ==="") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: unit value ');
                return back();
            } elseif (!is_numeric($collection['unit_value'])) {
                Toastr::error('Unit Value of row ' . ($key + 2) . ' must be number');
                return back();
            } elseif ($collection['brand'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: brand ');
                return back();
            } elseif ($collection['category_id'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: category_id ');
                return back();
            }  elseif ($collection['purchase_price'] ==="") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: purchase price ');
                return back();
            } elseif (!is_numeric($collection['purchase_price'])) {
                Toastr::error('Purchase Price of row ' . ($key + 2) . ' must be number');
                return back();
            } elseif ($collection['selling_price'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: selling_price ');
                return back();
            } elseif (!is_numeric($collection['selling_price'])) {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: number ');
                return back();
            }  elseif ($collection['discount_type'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: discount type');
                return back();
            } elseif ($collection['discount'] ==="") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: discount ');
                return back();
            } elseif (!is_numeric($collection['discount'])) {
                Toastr::error('Discount of row ' . ($key + 2) . ' must be number');
                return back();
            } elseif ($collection['tax'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: tax ');
                return back();
            } elseif (!is_numeric($collection['tax'])) {
                Toastr::error('Tax of row ' . ($key + 2) . ' must be number');
                return back();
            } elseif ($collection['quantity'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: quantity ');
                return back();
            } elseif (!is_numeric($collection['quantity'])) {
                Toastr::error('Quantity of row ' . ($key + 2) . ' must be number');
                return back();
            } 

            $product = [
                'discount_type' => $collection['discount_type'],
                'discount' => $collection['discount'],
            ];
            if ($collection['selling_price'] <= Helpers::discount_calculate($product, $collection['selling_price'])) {
                Toastr::error(translate('Discount can not be more or equal to the price in row '). ($key + 2));
                return back();
            }
            $product =  $this->product->where('product_code',$collection['product_code'])->first();
            if($product)
            {
                Toastr::warning(translate('product code row').' : ' . ($key + 2) .' '.translate('already exist'));
                return back();
            }
        }
        $data = [];
        foreach ($collections as $collection) {
          $product =  $this->product->where('product_code',$collection['product_code'])->first();
          if($product)
          {
              Toastr::success(translate('product code already exist'));
              return back();
          }
            $data[] = [
                'name' => $collection['name'],
                'product_code' => $collection['product_code'],
                'image' => json_encode(['def.png']),
                'unit_type' => $collection['unit_type'],
                'unit_value' => $collection['unit_value'],
                'brand' => $collection['brand'],
                'category_id' => $collection['category_id'],
                'purchase_price' => $collection['purchase_price'],
                'selling_price' => $collection['selling_price'],
                'discount_type' => $collection['discount_type'],
                'discount' => $collection['discount'],
                'tax' => $collection['tax'],
                'quantity' => $collection['quantity'],
                'supplier_id' => $collection['supplier_id'],
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('products')->insert($data);
        Toastr::success(count($data) . ' - '.translate('Products imported successfully'));
        return back();
    }

    /**
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function bulk_export_data(): StreamedResponse|string
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

    if (!in_array("product.excel.export", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $products = $this->product->all();
        $storage = [];
        foreach($products as $item){
            $category_id = 0;
            $sub_category_id = 0;

            // foreach(json_decode($item->category_ids, true) as $category)
            // {
            //     if($category['position']==1)
            //     {
            //         $category_id = $category['id'];
            //     }
            //     else if($category['position']==2)
            //     {
            //         $sub_category_id = $category['id'];
            //     }
            // }

            $storage[] = [
                'name' => $item['name'],
                'product_code' => $item['product_code'],
                'unit_type' => $item['unit_type'],
                'unit_value' => $item['unit_value'],
                'category_id' => $item['category_id'],
                'brand' => $item['brand'],
                'purchase_price' => $item['purchase_price'],
                'selling_price' => $item['selling_price'],
                'discount_type' => $item['discount_type'],
                'discount' => $item['discount'],
                'tax' => $item['tax'],
                'quantity' => $item['quantity'],
                'supplier_id' => $item['supplier_id'],
            ];
        }
        return (new FastExcel($storage))->download('products.xlsx');
    }

}
