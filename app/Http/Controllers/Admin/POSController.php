<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\CostCenter;
use App\Models\Order;
use App\Models\CurrentOrder;
use App\Models\Coupon;
use App\Models\Transection;
use App\Models\Taxe;
use App\Models\InstallmentContract;
use App\Models\ScheduledInstallment;
use App\Models\StockBatch;
use App\Models\Account;
use App\Models\OrderDetail;
use App\Models\Region;
use App\Models\ProductLog;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\HistoryInstallment;
use App\Models\HistoryTransection;
use App\Models\ReserveProduct;
use App\Models\CurrentReserveProduct;
use App\Models\ReserveProductNotification;
use App\Models\StockOrder;
use App\Models\Seller;
use App\Models\Stock;
use App\Models\SellerPrice;
use App\Models\AdminSeller;
use App\Models\CustomerPrice;
use App\Models\Transaction;
use App\Models\Supplier;
use App\Models\StockHistory;
use App\CPU\Helpers;
use App\Services\ZATCAService;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\PosSession;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class POSController extends Controller
{
    public function __construct(
        private Category $category,
        private Product $product,
        private Order $order,
        private Region $regions,
        private CurrentOrder $c_order,
        private CostCenter $costcenter,
        private Coupon $coupon,
        private Transection $transection,
        private Branch $branch,
        private Supplier $suppliers,
        private ProductLog $product_logs,
        private Account $account,
        private OrderDetail $order_details,
        private StockOrder $stock_order,
        private StockHistory $stock_history,
        private Customer $customer,
        private CurrentReserveProduct $current_reserve_products,
        private HistoryInstallment $installment,
        private ReserveProduct $reserveProduct,
        private HistoryTransection $history_transection,
        private ReserveProductNotification $reserveProductNotification,
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index($type,Request $request)
    {
         if($type==4){
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

    if (!in_array("pos4.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    }elseif($type==7){
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

    if (!in_array("pos7.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    }elseif($type==12){
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

    if (!in_array("pos12.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    }elseif($type==24){
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

    if (!in_array("pos24.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    }else{
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

    if (!in_array("pos1.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    }
        $category = $request->query('category_id', 0);
        $keyword = $request->query('search', false);
        $key = explode(' ', $keyword);
        $categories = $this->category->where('status', 1)->where('position', 0)->where('type',1)->latest()->get();
        
if($type==7||$type==12){
$products = $this->product->active()
    ->when($request['category_id'] !== null, function ($query) use ($request) {
        $query->where('category_id', $request['category_id']);
    })
    ->latest()
    ->paginate(Helpers::pagination_limit());
}else{
$adminBranchId = auth('admin')->user()->branch_id;
$branchColumn = "branch_" . $adminBranchId; // تكوين اسم العمود الديناميكي
}
$adminBranchId = auth('admin')->user()->branch_id;
$branchColumn = "branch_" . $adminBranchId; // تكوين اسم العمود الديناميكي
if($type==1){
$products = $this->product
    ->when($adminBranchId == 1, function ($query) {
        // إذا كان branch_id = 1، تحقق من `quantity`
        return $query->where('quantity', '>', 0);
    }, function ($query) use ($branchColumn) {
        // إذا كان branch_id مختلف، تحقق من `branch_X`
        return $query->where($branchColumn, '>', 0);
    })
   ->latest()->paginate(4);

}elseif ($type == 4) {
    $products = $this->product
        ->when($request->filled('category_id') && (int)$request->category_id > 0, function ($q) use ($request) {
            $q->where('category_id', (int)$request->category_id);
        })
        ->when(
            (int)$adminBranchId === 1,
            fn($q) => $q->where('quantity','>',0),
            fn($q) => $q->where($branchColumn,'>',0)
        )
        ->latest()->paginate(15)->withQueryString();
} else {
    $products = $this->product
        ->when(
            (int)$adminBranchId === 1,
            fn($q) => $q->where('quantity','>',0),
            fn($q) => $q->where($branchColumn,'>',0)
        )
        ->latest()->paginate(15)->withQueryString();

}
        $cart_id = 'wc-' . rand(10, 1000);

        if (!session()->has('current_user')) {
            session()->put('current_user', $cart_id);
        }
        if (strpos(session('current_user'), 'wc')) {
            $user_id = 0;
        } else {
            $user_id = explode('-', session('current_user'))[1];
        }

        if (!session()->has('cart_name')) {
            if (!in_array($cart_id, session('cart_name') ?? [])) {
                session()->push('cart_name', $cart_id);
            }
        }
$costcenters=CostCenter::where('active','1')->get();
$customers=Customer::where('active','1')->get();

$order=1;
if($type==7){
                return view('admin-views.pos.return', compact('categories', 'products', 'cart_id', 'category','user_id','type','costcenters','order'));

}
if($type==24){
                return view('admin-views.purchase_invoices.return', compact('categories', 'products', 'cart_id', 'category','user_id','type','costcenters','order'));

}
if($type==1){
            $user =  Auth::guard('admin')->id(); // أو auth('admin')->user() حسب نظامك
    $admin = DB::table('admins')->where('id', $user)->first();

        // هل فيه جلسة مفتوحة فعلاً؟
        $existingSession = PosSession::where('user_id', $user)
            ->where('status', 'open')
            ->first();
            if($existingSession){
                return view('admin-views.pos.indexcashier', compact('categories', 'products', 'cart_id', 'category','user_id','type','costcenters','order'));
                
            }else{
            return view('admin-views.pos.start-session', compact('categories', 'products', 'cart_id', 'category','user_id','type','costcenters','order'));
            }

}
            return view('admin-views.pos.index', compact('categories', 'products', 'cart_id', 'category','user_id','type','costcenters','order'));

    }

    /**
     * @return RedirectResponse
     */
    public function clear_cart_ids(): RedirectResponse
    {
        session()->forget('cart_name');
      session()->forget(session('current_user'));
        session()->forget('current_user');

return redirect()->back();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function quick_view(Request $request): JsonResponse
    {
        $product = $this->product->findOrFail($request->product_id);

        return response()->json([
            'success' => 1,
            'view' => view('admin-views.pos._quick-view-data', compact('product'))->render(),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
public function addToCart(Request $request, $type): JsonResponse
{
    // Initialize user details and cart ID from session
    $cart_id = session('current_user');
    $user_id = 1;
    $user_type = 'wc';

    if (Str::contains(session('current_user'), 'sc')) {
        $user_id = explode('-', session('current_user'))[1];
        $user_type = 'sc';
    }

    // Retrieve the product based on the provided product ID
    $product = $this->product->find($request->id);

    if (!$product) {
        return response()->json(['error' => 'Product not found'], 404);
    }

    // Determine the default selling price based on user type
    if (in_array($type, [4, 7, 1])) {
        switch ($user_type) {
            case '1':
                $selling_price = $product->selling_price;
                break;
            case '2':
                $selling_price = $product->selling_price1;
                break;
            case '3':
                $selling_price = $product->selling_price2;
                break;
            case '4':
                $selling_price = $product->selling_price3;
                break;
            case '5':
                $selling_price = $product->selling_price4;
                break;
            default:
                $selling_price = $product->selling_price;
                break;
        }
    } else {
        $selling_price = $product->purchase_price; // Default purchase price
    }

    // NEW: If the user is a customer, override with the last price from customer_Prices table if available.
    if ($user_type === 'sc') {
        $lastPrice = CustomerPrice::where('customer_id', $user_id)
            ->where('product_id', $product->id)
            ->latest('updated_at')
            ->value('price');
        if (!is_null($lastPrice)) {
            $selling_price = $lastPrice;
        }
    }

    // Adjust price based on unit value if needed
    if ($request->unit == 0 && $product->unit_value > 0) {
        $selling_price = $selling_price / $product->unit_value;
    }

    // Retrieve the current cart from the session
    $cart = session($cart_id, []);
    $item_exist = false;

    // Get admin branch ID and determine branch column
    $adminBranchId = auth('admin')->user()->branch_id;
    $branchColumn = "branch_" . $adminBranchId; // Dynamic column name

    foreach ($cart as &$cartItem) {
        if ($cartItem['id'] == $request->id) {
            // Determine the available quantity from stock
            if ($adminBranchId == 1) {
                $qty = $product->quantity - $cartItem['quantity'];
            } else {
                $qty = $product->$branchColumn - $cartItem['quantity'];
            }

            if ($type == 4 && $qty == 0) {
                return response()->json([
                    'qty' => $qty,
                    'unit' => 0,
                    'user_type' => $user_type,
                    'user_id' => $user_id,
                    'view' => view('admin-views.pos._cart', compact('cart_id'))->render()
                ]);
            }

            // Update quantity and recalculate tax and discount for existing item
            $cartItem['quantity'] += $request->quantity;
            $cartItem['unit'] = 0; // Force unit to be 0
            $cartItem['tax'] = Helpers::tax_calculate($product, $selling_price);
            $cartItem['discount'] = in_array($type, [12, 24])
                ? 0
                : (Helpers::discountCalculatePrice($product, $selling_price,$cartItem['unit'] ??0));
            
            $item_exist = true;
            break;
        }
    }

    // If item does not exist in cart, add it
    if (!$item_exist) {
        $data = [
            'id'         => $product->id,
            'quantity'   => $request->quantity,
            'unit'       => 0, // Always set unit to 0
            'price'      => $selling_price,
            'last_price'=> $lastPrice,
            'price_unit' => $product->selling_price / $product->unit_value,
            'name'       => $product->name,
            'discount'   => 0, // Default discount value
            'image'      => $product->image,
            'tax'        => 0,
        ];

        if (!in_array($type, [12, 24])) {
            $data['discount'] = Helpers::discountCalculatePrice($product, $selling_price,$cartItem['unit']??0);
        }
        $data['tax'] = Helpers::tax_calculate($product, $selling_price);

        $cart[] = $data;
    }

    // Save the updated cart back to the session
    session()->put($cart_id, $cart);

    return response()->json([
        'user_type' => $user_type,
        'user_id'   => $user_id,
        'type'      => $type,
        'view'      => view('admin-views.pos._cart', compact('cart_id'))->render()
    ]);
}


public function addToCartByBarcode(Request $request, $type): JsonResponse
{
    // Initialize user details and cart ID
    $cart_id = session('current_user');
    $user_id = 0;
    $user_type = 'wc';

    // Check if the user type is 'sc' and extract user_id if needed
    if (Str::contains(session('current_user'), 'sc')) {
        $user_id = explode('-', session('current_user'))[1];
        $user_type = 'sc';
    }

    // Retrieve the product based on the provided barcode
    $product = $this->product->where('product_code', $request->barcode)->first();

    // Ensure the product exists before proceeding
    if (!$product) {
        return response()->json([
            'error' => 'Product not found!',
            'view' => view('admin-views.pos._cart', compact('cart_id'))->render()
        ], 404);
    }

    // Determine the selling price based on user type
    if (in_array($type, [4, 7, 1])) {
        switch ($user_type) {
            case '1': $selling_price = $product->selling_price ?? 0; break;
            case '2': $selling_price = $product->selling_price1 ?? 0; break;
            case '3': $selling_price = $product->selling_price2 ?? 0; break;
            case '4': $selling_price = $product->selling_price3 ?? 0; break;
            case '5': $selling_price = $product->selling_price4 ?? 0; break;
            default: $selling_price = $product->selling_price ?? 0; break;
        }
    } else {
        $selling_price = $product->purchase_price ?? 0; // Default purchase price
    }

    // Adjust price for unit value if applicable
    if ($request->unit == 0 && $product->unit_value > 0) {
        $selling_price = $selling_price / $product->unit_value;
    }

    // Retrieve the admin branch ID
    $adminBranchId = auth('admin')->user()->branch_id;
    $branchColumn = ($adminBranchId == 1) ? 'quantity' : "branch_{$adminBranchId}";

    // Retrieve the current cart from the session
    $cart = session($cart_id, []);

    // Check if the product already exists in the cart
    $item_exist = false;
    foreach ($cart as &$cartItem) {
        if ($cartItem['id'] == $product->id) {
            $qty = $product->$branchColumn - $cartItem['quantity']; 

            if (in_array($type, [4, 1]) && $qty <= 0) {
                return response()->json([
                    'qty' => $qty,
                    'unit' => 0,
                    'user_type' => $user_type,
                    'user_id' => $user_id,
                    'view' => view('admin-views.pos._cart', compact('cart_id'))->render()
                ]);
            }

            // Update quantity for existing item
            $cartItem['quantity'] += 1;
            $cartItem['unit'] = 0; // Force unit to be 0
            $item_exist = true;
            break;
        }
    }

    // If the item doesn't exist in the cart, add a new item
    if (!$item_exist) {
        $data = [
            'id' => $product->id,
            'quantity' => 1, // Default to 1 when scanning
            'unit' => 0, // Always set unit to 0
            'price' => $selling_price,
            'name' => $product->name,
            'discount' => 0, // Default discount value
            'image' => $product->image,
            'tax' =>(Helpers::tax_calculate($product, $selling_price)), 
        ];

        // Apply discount based on the type
         // Apply discount based on type
        if (!in_array($type, [12, 24])) {
            $data['discount'] = (Helpers::discountCalculatePrice($product, $selling_price,$cartItem['unit']??0));
        }


        $cart[] = $data;
    }

    // Save the updated cart back to the session
    session()->put($cart_id, $cart);

    // Return the updated cart view as a response
    return response()->json([
        'user_type' => $user_type,
        'user_id' => $user_id,
        'type' => $type,
        'view' => view('admin-views.pos._cart', compact('cart_id'))->render()
    ]);
}





    /**
     * @return Application|Factory|View
     */
    public function cart_items(): Factory|View|Application
    {
        return view('admin-views.pos._cart');
    }
public function reserveProduct(Request $request)
{
    // Validate if 'data' exists and is not empty
    if (!$request->has('data') || empty($request->data)) {
        return response()->json(['message' => 'Data empty'], 403);
    }

    // Initialize variables
    $data = [];
    foreach ($request->data as $i => $item) {
        $data[$i]['product_name'] = $item['product_name'];
        $data[$i]['product_id'] = $item['product_id'];
        $data[$i]['stock'] = $item['stock'];
        $data[$i]['balance'] = $item['balance'];
    }

    // Generate a unique order ID
    $order_id = 20000000 + ReserveProduct::count() + 1;
    if (ReserveProduct::find($order_id)) {
        $order_id = ReserveProduct::orderBy('id', 'DESC')->first()->id + 1;
    }

    // Save current reservation products
    $current_reserve_products = new CurrentReserveProduct;
    $current_reserve_products->id = $order_id;
    $current_reserve_products->data = json_encode($data);
    $current_reserve_products->seller_id = $request->seller_id;
    $current_reserve_products->date = date('Y-m-d');
    $current_reserve_products->customer_id = $request->customer_id;
    $current_reserve_products->type = $request->type;
    $current_reserve_products->save();

    // Save reserve product details
    $reserveProduct = new ReserveProduct;
    $reserveProduct->id = $order_id;
    $reserveProduct->data = json_encode($data);
    $reserveProduct->seller_id = $request->seller_id;
    $reserveProduct->date = date('Y-m-d');
    $reserveProduct->customer_id = $request->customer_id;
    $reserveProduct->type = $request->type;
    $reserveProduct->save();

    // Update reserve_product_notifications table
    $notification = ReserveProductNotification::find($request->notification_id);
    if ($notification) {
        $notification->active = 1;
        $notification->save();
    }
 $cart = [];
    foreach ($request->data as $i => $item) {
        $cart[$i]['product_name'] = $item['product_name'];
        $cart[$i]['product_id'] = $item['product_id'];
        $cart[$i]['stock'] = $item['stock'];
        $cart[$i]['balance'] = $item['balance'];
    }


    // Initialize variables for order processing
    $user_id = $request->customer_id;
    $coupon_discount = $request->coupon_discount ?? 0;
    $order_details = [];
    $product_price = 0;
    $product_discount = 0;
    $product_tax = 0;

    // Generate unique order ID for the order
    $order_id = Order::count() + 1;
    if (Order::find($order_id)) {
        $order_id = Order::orderBy('id', 'DESC')->first()->id + 1;
    }

    // Create new order
    $order = new Order;
    $order->id = $order_id;
    $order->user_id = $user_id;
        $order->owner_id = $request->seller_id;
    $order->coupon_code = $request->cart['coupon_code'] ?? null;
    $order->coupon_discount_title = $request->cart['coupon_title'] ?? null;
    $order->payment_id = $request->type;
    $order->total_tax = $request->type;
    $order->created_at = now();
    $order->updated_at = now();

    foreach ($cart as $c) {
        if (is_array($c)) {
            $product = Product::find($c['product_id']);
            $seller_price = $product->selling_price;
            if ($product) {
                $stock = Stock::where('seller_id', $request->seller_id)->where('product_id', $c['product_id'])->first();
                if ($stock) {
                    if ($request->order_type == 4) { // e.g., sale
                        if ($stock->stock >= $c['stock']) {
                            $stock->stock -= $c['stock'];
                        } else {
                            continue;
                        }
                    } else { // e.g., restock
                        $stock->stock += $c['stock'];
                        $stock->main_stock += $c['stock'];
                    }
                    $stock->update();
                } else {
                    if ($request->order_type == 7) { // e.g., initial stock
                        $exist_stock = Stock::where('seller_id', $request->seller_id)->where('product_id', $c['id'])->first();
                        if ($exist_stock) {
                            $exist_stock->main_stock += $c['stock'];
                            $exist_stock->stock += $c['stock'];
                            $exist_stock->update();
                        } else {
                            $new_stock = new Stock;
                            $new_stock->seller_id = $request->seller_id;
                            $new_stock->product_id = $c['id'];
                            $new_stock->main_stock = $c['stock'];
                            $new_stock->stock = $c['stock'];
                            $new_stock->save();
                        }
                    } else {
                        continue;
                    }
                }

                $price =$seller_price;
                $customerPrice = CustomerPrice::where('product_id', $product->id)->where('customer_id', $user_id)->first();
                $order_details[] = [
                    'order_id' => $order_id,
                    'product_id' => $c['product_id'],
                    'product_details' => $product,
                    'quantity' => $c['stock'],
                    'price' => $price,
                    'tax_amount' => Helpers::tax_calculate($product,  $price),
                    'discount_on_product' => Helpers::discount_calculate($product, $product->selling_price),
                    'discount_type' => 'discount_on_product',
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $product_price += $price * $c['stock'];
                $product_discount += $c['stock'];
                $product_tax += $product->tax * $c['stock'];

                if ($c['stock'] > $product->quantity) {
                    return redirect()->back()->with('Check On Quantity Product');
                }

                $product->order_count++;
                $product->save();
            }
        }
    }

    $total_price = $product_price;
    $ext_discount = $request->ext_discount_type == 'percent' ? ($product_price * $request->extra_discount) / 100 : $request->extra_discount;
    $total_tax_amount = $request->total_tax;
    $grand_total = $total_price + $total_tax_amount - $ext_discount - $coupon_discount;

    $order->total_tax = $product_tax;
    $order->order_amount = $total_price;
    $order->coupon_discount_amount = $coupon_discount;
    $order->collected_cash = $request->collected_cash ?? $grand_total;
    $order->extra_discount = $ext_discount;
    $order->type = $request->type;
    $order->save();

    foreach ($order_details as $detail) {
        $orderDetail = new OrderDetail($detail);
        $orderDetail->save();
    }

    // Real-time transactions
    $account = Account::find(1);
  

    return redirect()->back()->with('message', 'Order stored successfully');
}



    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function emptyCart(Request $request): JsonResponse
    {
        $cart_id = session('current_user');
        $user_id = 0;
        $user_type = 'wc';
        if (Str::contains(session('current_user'), 'sc')) {
            $user_id = explode('-', session('current_user'))[1];
            $user_type = 'sc';
        }
        session()->forget($cart_id);
        return response()->json([
            'user_type' => $user_type,
            'user_id' => $user_id,
            'view' => view('admin-views.pos._cart', compact('cart_id'))->render()
        ], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
public function updateQuantity(Request $request, $type): JsonResponse
{
    $cart_id = session('current_user');
    $user_id = 0;
    $user_type = 'wc';

    if (Str::contains(session('current_user'), 'sc')) {
        $user_id = explode('-', session('current_user'))[1];
        $user_type = 'sc';
    }

    $cart = session($cart_id, []);
    if (!is_array($cart)) {
        return response()->json(['error' => 'Cart is empty or invalid'], 400);
    }

    $updatedCart = [];
    $totalPrice = 0;
    $price = 0;
    $discount = 0;

    foreach ($cart as &$item) {
        if ($item['id'] == $request->key) {
            $product = $this->product->find($request->key);
            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            $unitType = $item['unit'] ?? 1;
$adminBranchId = auth('admin')->user()->branch_id;
$branchColumn = "branch_" . $adminBranchId; // إنشاء اسم العمود الديناميكي

// الحصول على الكمية المتاحة بناءً على الفرع
if (isset($product->$branchColumn)) {
    $availableQuantity = ($unitType == 0 && $product->unit_value > 0) 
        ? $product->$branchColumn * $product->unit_value 
        : $product->$branchColumn;
} else {
    // إذا لم يكن هناك عمود مطابق، استخدم الكمية العامة
    $availableQuantity = ($unitType == 0 && $product->unit_value > 0) 
        ? $product->quantity * $product->unit_value 
        : $product->quantity;
}

        if (in_array($type, [1, 4, 24]) && $request->quantity > $availableQuantity) {
                return response()->json([
                    'error' => 'Insufficient stock',
                    'qty' => $availableQuantity,
                    'type' => $type,
                    'unit' => $unitType,
                    'price' => $price,
                    'user_type' => $user_type,
                    'user_id' => $user_id,
                    'view' => view('admin-views.pos._cart', compact('cart_id'))->render()
                ], 400);
            }

            // ✅ تحديد السعر والخصم بناءً على `type`
            if (in_array($type, [1, 4, 7])) {
                $price = ($unitType == 0 && $product->unit_value > 0) 
                    ? $product->selling_price / $product->unit_value 
                    : $product->selling_price;

                $discount = Helpers::discountCalculatePrice($product, $price,$unitType??0);
                if ($unitType == 0 && $product->unit_value > 0) {
                    $discount ;
                }
            } else {
                $price = ($unitType == 0) 
                    ? $product->purchase_price / ($product->unit_value ?: 1) 
                    : $product->purchase_price;

                $discount = 0; // لا يوجد خصم في حالة `purchase_price`
            }
              $tax = Helpers::tax_calculate($product, $price);
                if ($unitType == 0 && $product->unit_value > 0) {
                    $tax = $tax;
                }
         

            // تحديث البيانات
            $item['quantity'] = $request->quantity;
            $item['price'] = $price;
            $item['discount'] = $discount;
            $item['tax'] = $tax;
        }

        $updatedCart[] = $item;
    }

    session()->put($cart_id, $updatedCart);

    return response()->json([
        'price' => $price,
        'type' => $type,
        'unit' => $unitType,
        'user_type' => $user_type,
        'user_id' => $user_id,
        'view' => view('admin-views.pos._cart', compact('cart_id'))->render()
    ]);
}





    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeFromCart(Request $request): JsonResponse
    {
        $cart_id = session('current_user');
        $user_id = 0;
        $user_type = 'wc';
        if (Str::contains(session('current_user'), 'sc')) {
            $user_id = explode('-', session('current_user'))[1];
            $user_type = 'sc';
        }
        $cart = session($cart_id);
        $cart_keeper = [];
        if (session()->has($cart_id) && count($cart) > 0) {
            foreach ($cart as $cartItem) {
                if (is_array($cartItem) && $cartItem['id'] != $request['key']) {
                    array_push($cart_keeper, $cartItem);
                }
            }
        }
        session()->put($cart_id, $cart_keeper);

        return response()->json([
            'user_type' => $user_type,
            'user_id' => $user_id,
            'view' => view('admin-views.pos._cart', compact('cart_id'))->render()
        ], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
public function update_discount(Request $request): JsonResponse
{
    $cart_id = session('current_user');
    $user_id = 0;
    $user_type = 'wc';

    if (Str::contains(session('current_user'), 'sc')) {
        $user_id = explode('-', session('current_user'))[1];
        $user_type = 'sc';
    }

    $cart = session($cart_id, collect([]));

    if (!$cart || count($cart) == 0) {
        return response()->json([
            'extra_discount' => "empty",
            'user_type' => $user_type,
            'user_id' => $user_id,
            'view' => view('admin-views.pos._cart', compact('cart_id'))->render()
        ]);
    }

    $total_product_price = 0;
    $product_discount = 0;
    $product_tax = 0;

    // **حساب الإجمالي قبل الخصومات**
    foreach ($cart as &$item) {
        if (is_array($item)) {
            $total_product_price += $item['price'] * $item['quantity'];
            $product_discount += $item['discount'] * $item['quantity'];
                        $product = \App\Models\Product::find($item['id']);

            $product_tax +=  Helpers::tax_calculate_after($product, $item['price'])* $item['quantity'];
        }
    }

    // **حساب الإجمالي بعد خصم المنتجات**
    $price_after_product_discount = max(0, $total_product_price - $product_discount);

    // **حساب الخصم الإضافي بناءً على السعر بعد خصم المنتجات**
    $price_discount = ($request->type == 'percent') 
        ? ($price_after_product_discount * ($request->discount / 100)) 
        : $request->discount;

    // **الإجمالي بعد خصم المنتجات و الخصم الإضافي**
    $total_before_discount = $price_after_product_discount;
    
    $percent_discount = ($total_before_discount > 0) ? $price_discount / $price_after_product_discount : 0;

    // **تحديث كل عنصر في السلة بناءً على الخصم الجديد**
    foreach ($cart as &$item) {
        if (is_array($item)) {
            $item['discountafter'] = (($item['price']  - $item['discount'])* $percent_discount) + $item['discount'];
            $item['price_after_discount'] = max(0, $item['price'] - $item['discountafter']); // تجنب القيم السالبة

            // **جلب المنتج من قاعدة البيانات**
            $product = \App\Models\Product::find($item['id']);

            // **حساب الضريبة بعد الخصم**
            if ($product instanceof \App\Models\Product && !is_null($product->tax_id)) {
                $item['tax'] = Helpers::tax_calculate_after($product, $item['price_after_discount']);
            } else {
                $item['tax'] = 0; // إذا لم يكن هناك ضريبة
            }
        }
    }

    // **الإجمالي بعد تطبيق كل الخصومات والضرائب**
    $total_after_discount = max(0, $price_after_product_discount - $price_discount);
    $total_tax = $product_tax * ($total_after_discount / max(1, $price_after_product_discount)); // تجنب القسمة على صفر
    $final_total = $total_after_discount + $total_tax;

    // **التأكد من عدم وجود قيم سالبة**
    if ($final_total < 0) {
        return response()->json([
            'extra_discount' => "amount_low",
            'user_type' => $user_type,
            'user_id' => $user_id,
            'view' => view('admin-views.pos._cart', compact('cart_id'))->render()
        ]);
    }

    // **تحديث بيانات الخصم في الجلسة**
    $cart['ext_discount'] = $price_discount;
    $cart['ext_discount_type'] = $request->type;
    session()->put($cart_id, $cart);

    return response()->json([
        'extra_discount' => "success",
        'user_type' => $user_type,
        'user_id' => $user_id,
        'view' => view('admin-views.pos._cart', compact('cart_id'))->render()
    ]);
}

    /**
     * @param Request $request
     * @return RedirectResponse
     */


    /**
     * @param $cart
     * @param $price
     * @return float|int
     */
    public function extra_dis_calculate($cart, $price): float|int
    {

        if ($cart['ext_discount_type'] == 'percent') {
            $price_discount = ($price / 100) * $cart['ext_discount'];
        } else {
            $price_discount = $cart['ext_discount'];
        }
        return $price_discount;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
public function coupon_discount(Request $request): JsonResponse
{
    $cart_id = session('current_user');
    $user_id = 0;
    $user_type = 'wc';
    if (Str::contains(session('current_user'), 'sc')) {
        $user_id = explode('-', session('current_user'))[1];
        $user_type = 'sc';
    }
    
    if ($user_id != 0) {
        $couponLimit = $this->order->where('user_id', $user_id)
            ->where('coupon_code', $request['coupon_code'])->count();

        $coupon = $this->coupon->where(['code' => $request['coupon_code']])
            ->where('user_limit', '>', $couponLimit)
            ->where('status', '=', 1)
            ->whereDate('start_date', '<=', now())
            ->whereDate('expire_date', '>=', now())->first();
    } else {
        $coupon = $this->coupon->where(['code' => $request['coupon_code']])
            ->where('status', '=', 1)
            ->whereDate('start_date', '<=', now())
            ->whereDate('expire_date', '>=', now())->first();
    }

    $carts = session($cart_id);
    $total_product_price = 0;
    $product_discount = 0;
    $product_tax = 0;
    $ext_discount = 0;

    if ($coupon != null) {
        if ($carts != null) {
            foreach ($carts as $cart) {
                if (is_array($cart)) {
                    $total_product_price += $cart['price'] * $cart['quantity'];
                    $product_discount += $cart['discount'] * $cart['quantity'];
                    $product_tax += $cart['tax'] * $cart['quantity'];
                }
            }

            if ($total_product_price >= $coupon['min_purchase']) {
                if ($coupon['discount_type'] == 'percent') {
                    $discount = (($total_product_price / 100) * $coupon['discount']) > $coupon['max_discount'] 
                        ? $coupon['max_discount'] 
                        : (($total_product_price / 100) * $coupon['discount']);
                } else {
                    $discount = $coupon['discount'];
                }

                // Apply any extra discount (if applicable)
                if (isset($carts['ext_discount_type'])) {
                    $ext_discount = $this->extra_dis_calculate($carts, $total_product_price);
                }

                // Calculate total after discounts
                $total_after_discount = $total_product_price - $product_discount - $discount - $ext_discount;

                // Calculate tax after the discount is applied
                $total_tax = ($product_tax * ($total_after_discount / $total_product_price));

                // Calculate the final total
                $total = $total_after_discount + $total_tax;

                // Check if the total is valid (non-negative)
                if ($total < 0) {
                    return response()->json([
                        'coupon' => "amount_low",
                        'user_type' => $user_type,
                        'user_id' => $user_id,
                        'view' => view('admin-views.pos._cart', compact('cart_id'))->render()
                    ]);
                }

                // Update the session with the coupon details and discount
                $cart = session($cart_id, collect([]));
                $cart['coupon_code'] = $request['coupon_code'];
                $cart['coupon_discount'] = $discount;
                $cart['coupon_title'] = $coupon->title;
                $cart['tax'] = $total_tax;

                // Store the new cart data in the session
                $request->session()->put($cart_id, $cart);

                return response()->json([
                    'coupon' => 'success',
                    'user_type' => $user_type,
                    'user_id' => $user_id,
                    'view' => view('admin-views.pos._cart', compact('cart_id'))->render()
                ]);
            }
        } else {
            return response()->json([
                'coupon' => 'cart_empty',
                'user_type' => $user_type,
                'user_id' => $user_id,
                'view' => view('admin-views.pos._cart', compact('cart_id'))->render()
            ]);
        }
    }

    // Return an error response if coupon is invalid or not found
    return response()->json([
        'coupon' => 'coupon_invalid',
        'user_type' => $user_type,
        'user_id' => $user_id,
        'view' => view('admin-views.pos._cart', compact('cart_id'))->render()
    ]);
}


    /**
     * @param Request $request
     * @return RedirectResponse
     */
public function place_order(Request $request): RedirectResponse|JsonResponse
{
    // -------------- إعدادات ومتحولات أساسية --------------
    $cart_id = session('current_user');
    $type    = (int) $request->type;         // 1 في الـ POS → يتحول لاحقاً لـ 4 (بيع)
    $admin   = auth('admin')->user();
    $branchId = $admin->branch_id;
    $sellerId = $admin->id;

    // معرّف المستخدم من cart_id (wc/sc)
    $user_type = 'wc';
    $user_id   = 0;
    if (Str::contains((string)$cart_id, 'sc')) {
        $parts   = explode('-', (string)$cart_id);
        $user_id = (int)($parts[1] ?? 0);
        $user_type = 'sc';
    }

    // عربة فارغة؟
    if (!session($cart_id) || count(session($cart_id)) < 1) {
        Toastr::error(translate('cart_empty_warning'));
        return back();
    }

    $cart              = session($cart_id);
    $coupon_discount   = $cart['coupon_discount'] ?? 0;
    $product_price     = 0.0;
    $product_discount  = 0.0;
    $product_tax       = 0.0;
    $ext_discount      = 0.0;
    $order_details     = [];
    $productlogs       = [];
    $totalPriceAllProducts = 0.0; // إجمالي تكلفة المخزون المُستهلك (COGS)

    // -------------- توليد رقم الطلب --------------
    $order_id = 100000 + $this->order->all()->count() + 1;
    if ($this->order->find($order_id)) {
        $order_id = $this->order->orderBy('id', 'DESC')->first()->id + 1;
    }

    // -------------- رفع صورة (اختياري) --------------
    $img = null;
    if ($request->hasFile('img')) {
        $img = $request->file('img')->store('shop', 'public');
    }

    // -------------- رمز QR --------------
    $qrcode_data  = "https://demo.novoosystem.com/real/invoicea2/" . $order_id;
    $qrCode       = new QrCode($qrcode_data);
    $writer       = new PngWriter();
    $qrcode_image = $writer->write($qrCode)->getString();
    $qrcode_path  = "qrcodes/order_$order_id.png";
    Storage::disk('public')->put($qrcode_path, $qrcode_image);

    // -------------- جلسة الكاشير --------------
    $existingSession = PosSession::where('user_id', $sellerId)
        ->where('status', 'open')
        ->first();

    // -------------- إنشاء كيان الطلب (لم يُحفّظ بعد) --------------
    $order = $this->order;
    $order->id                      = $order_id;
    $order->user_id                 = $user_id;
    $order->coupon_code             = $cart['coupon_code']   ?? null;
    $order->coupon_discount_title   = $cart['coupon_title']  ?? null;
    $order->payment_id              = $request->payment_id;
    $order->type                    = ($type === 1) ? 4 : $type; // 1 → 4 بيع
    $order->cash                    = $request->cash;
    $order->date                    = $request->date;
    $order->qrcode                  = $qrcode_path;
    $order->owner_id                = $sellerId;
    $order->branch_id               = $branchId;
    $order->session_id              = $existingSession->id ?? null;
    $order->transaction_reference   = $request->transaction_reference ?? 0;
    $order->img                     = $img;
    $order->created_at              = now();
    $order->updated_at              = now();

    // -------------- تجهيز البيانات المحاسبية --------------
    // حسابات ثابتة مستخدمة في منطقك الحالي
    $salesAccountId     = 40; // المبيعات (دائن)
    $vatAccountId       = 28; // ضريبة مستحقة (دائن)
    $cogsAccountId      = 47; // تكلفة المبيعات (مدين)

    $branch             = \App\Models\Branch::findOrFail($branchId);
    $inventoryAccountId = $branch->account_stock_Id; // المخزون (دائن عند البيع)

    // -------------- معاملة قاعدة البيانات --------------
    return DB::transaction(function () use (
        $request, $admin, $sellerId, $branchId, $cart, $cart_id, $order,
        $type, $salesAccountId, $vatAccountId, $cogsAccountId,
        $inventoryAccountId, $coupon_discount,
        &$product_price, &$product_discount, &$product_tax,
        &$order_details, &$productlogs, &$totalPriceAllProducts
    ) {

        // --------- المرور على عناصر العربة ---------
        foreach ($cart as $c) {
            if (!is_array($c)) continue;

            $product = $this->product->find($c['id']);
            if (!$product) continue;

            // احتساب خصم/ضريبة المنتج بناءً على سعرك في الكارت
            $discount_on_product = \App\CPU\Helpers::discount_calculate($product, $c['price']);
            $taxafter            = $c['price'] - $discount_on_product;

            // حساب متوسط التكلفة من دفعات المخزون (FIFO)
            $stockBatches = \App\Models\StockBatch::where('product_id', $c['id'])
                ->where('branch_id', $branchId)
                ->orderBy('created_at')
                ->get();

            // تحويل الكمية حسب الوحدة
            if ((int)$c['unit'] === 0) {
                $quantityfinal = $c['quantity'] / max(1, (float)$product->unit_value);
            } else {
                $quantityfinal = $c['quantity'];
            }

            // استهلاك دفعات المخزون لحساب المتوسط المُستخدم
            $weightedSumConsumed = 0; $totalConsumedQty = 0; $remainingQty = $quantityfinal;
            foreach ($stockBatches as $batch) {
                if ($remainingQty <= 0) break;
                $available = max(0, (float)$batch->quantity);
                if ($available <= 0) continue;

                $usedQty = min($available, $remainingQty);
                $weightedSumConsumed += ((float)$batch->price) * $usedQty;
                $totalConsumedQty    += $usedQty;
                $remainingQty        -= $usedQty;
            }
            if ($remainingQty > 0) {
                Toastr::error(translate('كمية المنتج غير كافية في المخزن.'));
                return back();
            }
            $weightedAvg = $totalConsumedQty > 0 ? $weightedSumConsumed / $totalConsumedQty : 0;

            // تفاصيل العنصر في الطلب
            $subtotalReq = (float) $request->subtotal;
            $discRatio   = $subtotalReq > 0 ? ((float)$request->extra_discount / $subtotalReq) * 100 : 0;
            $extraDiscAmt = ($discRatio / 100.0) * $c['price'];

            $or_d = [
                'order_id'                => $order->id,
                'product_id'              => $c['id'],
                'product_details'         => $product,
                'quantity'                => $c['quantity'],
                'purchase_price'          => $weightedAvg,
                'unit'                    => $c['unit'],
                'price'                   => $c['price'],
                'extra_discount_on_product' => $extraDiscAmt,
                'tax_amount'              => $c['tax'],
                'discount_on_product'     => $c['discount'],
                'discount_type'           => 'discount_on_product',
                'created_at'              => now(),
                'updated_at'              => now(),
            ];
            $order_details[] = $or_d;

            $product_price    += ((float)$c['price']) * $c['quantity'];
            $product_discount += ((float)$c['discount']) * $c['quantity'];
            $product_tax      += ((float)$c['tax']) * $c['quantity'];

            // تحديث أسعار العميل (لبَيع فقط)
            if (in_array($type, [1,4], true)) {
                $customerPrice = \App\Models\CustomerPrice::firstOrNew([
                    'product_id'  => $c['id'],
                    'customer_id' => $order->user_id,
                ]);

                if ((int)$c['unit'] === 0) {
                    $customerPrice->price = ($c['price'] * $product->unit_value)
                        - ($discount_on_product * $product->unit_value)
                        + ($c['tax'] * $product->unit_value);
                } else {
                    $customerPrice->price = $c['price'] - $discount_on_product + $c['tax'];
                }
                $customerPrice->save();
            }

            // خصم المخزون وفق نوع العملية (بيع/تبرع/pos)
            if (in_array($type, [4, 24, 1], true)) {
                if ((int)$c['unit'] === 0) {
                    $quantity      = $c['quantity'] * max(1, (float)$product->unit_value);
                    $quantityfinal = $c['quantity'] / max(1, (float)$product->unit_value);
                } else {
                    $quantity      = $c['quantity'];
                    $quantityfinal = $c['quantity'];
                }

                $branchColumn = "branch_" . $branchId;
                if ($product->$branchColumn >= $quantityfinal) {
                    $product->$branchColumn -= $quantityfinal;
                } elseif ($product->quantity >= $quantityfinal) {
                    $product->quantity -= $quantityfinal;
                } else {
                    Toastr::error(translate('لا توجد كمية كافية بالمخزن'));
                    return redirect()->back();
                }
            }

            $product->order_count++;
            $product->save();

            // خصم الكميات من دفعات المخزون + حساب تكلفة المنتج (لـ COGS)
            if (in_array($type, [1,4], true)) {
                $stockBatches = \App\Models\StockBatch::where('product_id', $c['id'])
                    ->where('branch_id', $branchId)
                    ->where('quantity', '>', 0)
                    ->orderBy('created_at')
                    ->get();

                $remainingQty = $quantityfinal;
                $totalPriceForProduct = 0;
                foreach ($stockBatches as $batch) {
                    if ($remainingQty <= 0) break;

                    if ($batch->quantity >= $remainingQty) {
                        $totalPriceForProduct += $batch->price * $remainingQty;
                        $batch->quantity      -= $remainingQty;
                        $batch->save();
                        $remainingQty = 0;
                    } else {
                        $totalPriceForProduct += $batch->price * $batch->quantity;
                        $remainingQty         -= $batch->quantity;
                        $batch->quantity       = 0;
                        $batch->save();
                    }
                }
                if ($remainingQty > 0) {
                    Toastr::error(translate('كمية المنتج غير كافية في المخزن.'));
                    return back();
                }

                $totalPriceAllProducts += $totalPriceForProduct;

                // سجل منتج (لوج)
                $typeValue = ($type === 1) ? 4 : $type;
                $productlogs[] = [
                    'product_id' => $c['id'],
                    'quantity'   => $quantityfinal,
                    'type'       => $typeValue,
                    'seller_id'  => $sellerId,
                    'branch_id'  => $branchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        } // end foreach cart

        // --------- إجماليات الفاتورة ---------
        $total_price   = $product_price - $product_discount;
        if (isset($cart['ext_discount_type'])) {
            $ext_discount = $this->extra_dis_calculate($cart, $product_price);
            $order->extra_discount = $ext_discount;
        } else {
            $ext_discount = 0.0;
        }

        $total_tax_amount = $product_tax;
        $grand_total      = $total_price + $total_tax_amount - $ext_discount - $coupon_discount;

        // خزّن بعض الحقول النهائية بالطلب
        $order->total_tax             = $request->tax;
        $order->order_amount          = $request->order_amount;
        $order->coupon_discount_amount= $coupon_discount;
        $order->collected_cash        = $request->collected_cash;
        $order->type                  = ($type === 1) ? 4 : $request->type;
        $order->date                  = $request->date;

        // --------- ZATCA Compliance Fields ---------
        if (empty($order->uuid)) {
            $order->uuid = ZATCAService::generateUUID();
        }
        if (empty($order->currency_code)) {
            $order->currency_code = 'SAR';
        }
        if (empty($order->invoice_counter)) {
            $order->invoice_counter = ZATCAService::getNextInvoiceCounter($order->company_id);
        }
        $order->invoice_number = ZATCAService::generateInvoiceNumber($order->invoice_counter);
        $order->previous_invoice_hash = ZATCAService::getPreviousInvoiceHash($order->company_id);
        
        // Generate ZATCA QR code data
        $businessSettings = \App\Models\BusinessSetting::whereIn('key', ['shop_name', 'number_tax'])->pluck('value', 'key');
        $zatcaQrData = [
            'seller_name' => $businessSettings['shop_name'] ?? '',
            'vat_registration_number' => $businessSettings['number_tax'] ?? '',
            'invoice_date' => $order->date ?: $order->created_at?->format('Y-m-d H:i:s'),
            'invoice_total' => $grand_total,
            'vat_total' => $total_tax_amount,
        ];
        $order->zatca_qr_code = ZATCAService::generateZATCAQRCode($zatcaQrData);

        // --------- قيود اليومية + معاملات الترانزكشن ---------
        // مَن الحساب المدين الرئيسي؟ عميل/نقدي/حساب آخر
        $debitMainAccountId = null;
        $debitCaption       = 'Sales Invoice #'.$order->id;

        if ((int)$request->cash === 2) {
            // بيع آجل (حساب العميل)
            $customer = $this->customer->where('id', $order->user_id)->firstOrFail();
            $debitMainAccountId = (int) $customer->account_id;
        } elseif ((int)$request->cash === 1 && (int)$type === 4) {
            // بيع نقدي (حساب وسيلة الدفع المختارة)
            $debitMainAccountId = (int) $request->payment_id;
        } else {
            // بيع عبر حساب آخر (مثلاً 92)
            $debitMainAccountId = 92;
        }

        // أنشئ قيد يومية رئيسي
        $entry = $this->createJournalEntry(
            entryDate   : $order->date ?: now()->format('Y-m-d'),
            reference   : (string)$order->id,
            type        : 'sale',
            description : $debitCaption,
            branchId    : $branchId,
            createdBy   : $sellerId,
            orderId     : $order->id
        );

        // 1) مدين: عميل/نقدي بالإجمالي (شامل ضريبة)
        $jed_debit_main = $this->addJEDetail($entry->id, $debitMainAccountId, $grand_total, 0, $request->cost_id, $debitCaption, $order->img, $order->date, $order->user_id, $order->id);

        // 2) دائن: المبيعات (بدون ضريبة)
        $salesNet = (float)$request->order_amount - (float)$request->tax;
        $jed_sales = $this->addJEDetail($entry->id, $salesAccountId, 0, $salesNet, $request->cost_id, 'Sales revenue', $order->img, $order->date, $order->user_id, $order->id);

        // 3) دائن: الضريبة
        $vatAmt = (float)$request->tax;
        if ($vatAmt > 0) {
            $jed_vat = $this->addJEDetail($entry->id, $vatAccountId, 0, $vatAmt, $request->cost_id, 'VAT Payable', $order->img, $order->date, $order->user_id, $order->id);
        }

        // 4) تكلفة المبيعات مقابل المخزون
        if ($totalPriceAllProducts > 0) {
            // مدين: COGS
            $jed_cogs = $this->addJEDetail($entry->id, $cogsAccountId, $totalPriceAllProducts, 0, $request->cost_id, 'COGS', $order->img, $order->date, $order->user_id, $order->id);
            // دائن: Inventory
            $jed_inv  = $this->addJEDetail($entry->id, $inventoryAccountId, 0, $totalPriceAllProducts, $request->cost_id, 'Inventory out', $order->img, $order->date, $order->user_id, $order->id);
        }

        // اربط القيد بالطلب (لو عندك عمود)
        if (Schema::hasColumn($order->getTable(), 'journal_entry_id')) {
            $order->journal_entry_id = $entry->id;
        }

        // --------- ترانزكشن/حركات مالية لكل سطر تفصيلي ---------
        // 2-سطر لكل علاقة رئيسية (مدين ↔ دائن) بحيث account_id_to يشير للطرف المقابل

        // (أ) مدين رئيسي ↔ مبيعات
        $this->postDoubleEntryTransaction(
            tranType   : $order->type,
            sellerId   : $sellerId,
            branchId   : $branchId,
            costId     : $request->cost_id,
            fromAcc    : $salesAccountId,           // الطرف الدائن
            toAcc      : $debitMainAccountId,       // الطرف المدين
            amount     : $salesNet,
            desc       : 'Sales revenue',
            date       : $order->date ?: date('Y/m/d'),
            customerId : $order->user_id,
            orderId    : $order->id,
            img        : $order->img,
            debitDetailId  : $jed_debit_main->id,
            creditDetailId : $jed_sales->id
        );

        // (ب) مدين رئيسي ↔ ضريبة (إن وُجدت)
        if ($vatAmt > 0) {
            $this->postDoubleEntryTransaction(
                tranType   : $order->type,
                sellerId   : $sellerId,
                branchId   : $branchId,
                costId     : $request->cost_id,
                fromAcc    : $vatAccountId,          // دائن
                toAcc      : $debitMainAccountId,    // مدين
                amount     : $vatAmt,
                desc       : 'VAT payable on sale',
                date       : $order->date ?: date('Y/m/d'),
                customerId : $order->user_id,
                orderId    : $order->id,
                img        : $order->img,
                debitDetailId  : $jed_debit_main->id,
                creditDetailId : $jed_vat->id ?? null
            );
        }

        // (ج) تكلفة المبيعات ↔ المخزون (إن وُجدت تكلفة)
        if ($totalPriceAllProducts > 0) {
            $this->postDoubleEntryTransaction(
                tranType   : $order->type,
                sellerId   : $sellerId,
                branchId   : $branchId,
                costId     : $request->cost_id,
                fromAcc    : $inventoryAccountId, // دائن
                toAcc      : $cogsAccountId,      // مدين
                amount     : $totalPriceAllProducts,
                desc       : 'Inventory out → COGS',
                date       : $order->date ?: date('Y/m/d'),
                customerId : $order->user_id,
                orderId    : $order->id,
                img        : $order->img,
                debitDetailId  : $jed_cogs->id  ?? null,
                creditDetailId : $jed_inv->id   ?? null,
                inventoryFlow  : true            // لتحديث أرصدة المخزون خاص
            );
        }

        // --------- حفظ الطلب + التفاصيل + لوج المنتجات ---------
        $order->save();
        $this->order_details->insert($order_details);
        $this->product_logs->insert($productlogs);

        // --------- تنظيف العربة والرد ---------
        if ($type === 1) {
            session()->forget($cart_id);
            return response()->json([
                'success'  => true,
                'order_id' => $order->id
            ]);
        }

        Toastr::success(translate('تم تنفيذ الطلب بنجاح') . ' - رقم الطلب: ' . $order->id);
        return redirect()->back()->with('order_id', $order->id);
    });
}

/**
 * إنشاء قيد يومية رئيسي
 */
private function createJournalEntry(
    string $entryDate,
    string $reference,
    string $type,
    string $description,
    int    $branchId,
    int    $createdBy,
    ?int   $paymentVoucherId = null,
    ?int   $orderId = null
): JournalEntry {
    $entry = new JournalEntry();
    $entry->entry_date          = $entryDate;
    $entry->reference           = $reference;
    $entry->type                = $type; // sale / payment / return ...
    $entry->description         = $description;
    $entry->created_by          = $createdBy;
    $entry->payment_voucher_id  = $paymentVoucherId;
    $entry->branch_id           = $branchId;
    if (Schema::hasColumn($entry->getTable(), 'order_id')) {
        $entry->order_id = $orderId;
    }
    $entry->save();
    return $entry;
}

/**
 * إضافة سطر تفصيلي لقيد اليومية
 */
private function addJEDetail(
    int     $journalEntryId,
    int     $accountId,
    float   $debit,
    float   $credit,
    ?int    $costCenterId,
    ?string $description,
    ?string $attachmentPath,
    ?string $entryDate,
    ?int    $customerId = null,
    ?int    $orderId = null
): JournalEntryDetail {
    $detail = new JournalEntryDetail();
    $detail->journal_entry_id = $journalEntryId;
    $detail->account_id       = $accountId;
    $detail->debit            = round($debit, 2);
    $detail->credit           = round($credit, 2);
    $detail->cost_center_id   = $costCenterId;
    $detail->description      = $description;
    $detail->attachment_path  = $attachmentPath;
    $detail->entry_date       = $entryDate ?: now()->format('Y-m-d');
    if (Schema::hasColumn($detail->getTable(), 'customer_id')) {
        $detail->customer_id = $customerId;
    }
    if (Schema::hasColumn($detail->getTable(), 'order_id')) {
        $detail->order_id = $orderId;
    }
    $detail->save();
    return $detail;
}

/**
 * ترحيل ترانزكشن مزدوج (سطرين): دائن ↔ مدين + تحديث أرصدة الحسابات
 * - يُربط كل سطر بـ journal_entry_detail_id
 * - لو inventoryFlow = true: يحدّث رصيد المخزون (خارج/داخل) حسب السيناريو
 */
private function postDoubleEntryTransaction(
    int      $tranType,
    int      $sellerId,
    int      $branchId,
    ?int     $costId,
    int      $fromAcc,                 // الدائن
    int      $toAcc,                   // المدين
    float    $amount,
    string   $desc,
    string   $date,
    ?int     $customerId,
    int      $orderId,
    ?string  $img,
    ?int     $debitDetailId = null,
    ?int     $creditDetailId = null,
    bool     $inventoryFlow = false
): void {

    $amount = round((float)$amount, 2);
    if ($amount <= 0) return;

    // 1) سطر المدين (toAcc)
    $tDebit = new \App\Models\Transection();
    $tDebit->tran_type                 = $tranType;
    $tDebit->seller_id                 = $sellerId;
    $tDebit->branch_id                 = $branchId;
    $tDebit->cost_id                   = $costId;
    $tDebit->account_id                = $toAcc;     // مدين
    $tDebit->account_id_to             = $fromAcc;   // مقابل
    $tDebit->amount                    = $amount;
    $tDebit->description               = $desc;
    $tDebit->debit                     = $amount;
    $tDebit->credit                    = 0;
    $tDebit->date                      = $date ?: date("Y/m/d");
    $tDebit->customer_id               = $customerId;
    $tDebit->order_id                  = $orderId;
    $tDebit->img                       = $img;
    $tDebit->journal_entry_detail_id   = $debitDetailId;
    $tDebit->save();

    // 2) سطر الدائن (fromAcc)
    $tCredit = new \App\Models\Transection();
    $tCredit->tran_type                = $tranType;
    $tCredit->seller_id                = $sellerId;
    $tCredit->branch_id                = $branchId;
    $tCredit->cost_id                  = $costId;
    $tCredit->account_id               = $fromAcc;   // دائن
    $tCredit->account_id_to            = $toAcc;     // مقابل
    $tCredit->amount                   = $amount;
    $tCredit->description              = $desc;
    $tCredit->debit                    = 0;
    $tCredit->credit                   = $amount;
    $tCredit->date                     = $date ?: date("Y/m/d");
    $tCredit->customer_id              = $customerId;
    $tCredit->order_id                 = $orderId;
    $tCredit->img                      = $img;
    $tCredit->journal_entry_detail_id  = $creditDetailId;
    $tCredit->save();

    // --------- تحديث الأرصدة (بنفس أسلوبك القائم) ---------
    $accFrom = Account::find($fromAcc);
    $accTo   = Account::find($toAcc);

    if ($inventoryFlow) {
        // حركة مخزون: اطرح من المخزون وزوّد تكلفة المبيعات
        if ($accFrom) { // المخزون (دائن)
            $accFrom->balance    = (float)$accFrom->balance - $amount;
            $accFrom->total_out  = (float)$accFrom->total_out + $amount;
            $accFrom->save();
        }
        if ($accTo) {   // COGS (مدين)
            $accTo->balance    = (float)$accTo->balance + $amount;
            $accTo->total_in   = (float)$accTo->total_in + $amount;
            $accTo->save();
        }
    } else {
        // تدفّق عادي (زي ما كنت بتعمل): نزود الرصيد للطرفين
        if ($accFrom) {
            $accFrom->balance  = (float)$accFrom->balance + $amount;
            $accFrom->total_in = (float)$accFrom->total_in + $amount;
            $accFrom->save();
        }
        if ($accTo) {
            $accTo->balance    = (float)$accTo->balance + $amount;
            $accTo->total_in   = (float)$accTo->total_in + $amount;
            $accTo->save();
        }
    }
}





public function storeplaceorder(Request $request): RedirectResponse
{
    // Validate the incoming request
    $request->validate([
        'seller_id'    => 'required|exists:admins,id',
        'product_id'   => 'required|array',
        'product_id.*' => 'exists:products,id',
        'stock'        => 'required|array',
        'stock.*'      => 'numeric|min:0',
        'unit'         => 'required|array',
        'unit.*'       => 'in:0,1', // 0 for minor unit, 1 for major unit
        'type'         => 'required|string|in:4,7', // 4 for stock issuance, 7 for stock return
    ]);

    DB::beginTransaction();

    try {
        $success = false;
        $reserveProducts = [];
        $totalPriceAllProducts = 0; // إجمالي سعر المنتجات المُخصومة أو المرتجعة

        // احصل على معرف الفرع الحالي للمشرف
        $adminBranchId = auth('admin')->user()->branch_id;
        $branchColumn = "branch_" . $adminBranchId;

        foreach ($request->product_id as $i => $productId) {
            // استرجاع بيانات المنتج
            $product = $this->product->find($productId);
            if (!$product) {
                continue;
            }

            // حساب الكمية المعدلة بناءً على نوع الوحدة المدخلة
            $inputStock = $request->stock[$i];
            $unit       = $request->unit[$i];
            $adjustedStock = ($unit == 0)
                ? $inputStock / $product->unit_value
                : $inputStock;
            $adjustedStock = round($adjustedStock, 2);

            if ($request->type == '4') {
                // -------------------------------
                // منطق صرف المخزون (type = 4)
                // -------------------------------
                // التأكد من توفر الكمية الكافية في المنتج (حسب الفرع أو الكمية العامة)
                if (isset($product->$branchColumn)) {
                    if ((float)$product->$branchColumn < $adjustedStock) {
                        Toastr::error(translate('(' . $product->name . ') لاتوجد منه كمية كافية'));
                        continue;
                    }
                } else {
                    if ((float)$product->quantity < $adjustedStock) {
                        Toastr::error(translate('(' . $product->name . ') لاتوجد منه كمية كافية'));
                        continue;
                    }
                }

                // تحديث أو إنشاء سجل المخزون الخاص بالمندوب
                $stockEntry = $this->stock
                    ->where('product_id', $productId)
                    ->where('seller_id', $request->seller_id)
                    ->first();

                if ($stockEntry) {
                    $currentStock = (float)$stockEntry->stock;
                    $currentMainStock = (float)$stockEntry->main_stock;
                    $stockEntry->update([
                        'stock'      => (string) round($currentStock + $adjustedStock, 2),
                        'main_stock' => (string) round($currentMainStock + $adjustedStock, 2),
                    ]);
                } else {
                    $this->stock->create([
                        'seller_id'  => $request->seller_id,
                        'product_id' => $productId,
                        'main_stock' => (string)$adjustedStock,
                        'stock'      => (string)$adjustedStock,
                    ]);
                }

                // تسجيل العملية في سجل العمليات (ProductLog)
                $productLog = new ProductLog();
                $productLog->product_id = $productId;
                $productLog->quantity   = (string)$adjustedStock;
                $productLog->seller_id  = $request->seller_id;
                $productLog->branch_id  = $adminBranchId;
                $productLog->type       = 100; // نوع صرف المخزون
                $productLog->save();

                // تطبيق FIFO على دفعات المخزون (StockBatch)
                $remaining = $adjustedStock;
                $priceJson = []; // لتخزين تفاصيل كل دفعة (الكمية والسعر)
                $stockBatches = StockBatch::where('product_id', $productId)
                    ->where('branch_id', $adminBranchId)
                    ->where('quantity', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($stockBatches as $batch) {
                    if ($remaining <= 0) {
                        break;
                    }
                    $batchQty = (float)$batch->quantity;
                    if ($batchQty >= $remaining) {
                        $deducted = $remaining;
                        $newBatchQty = round($batchQty - $remaining, 2);
                        $batch->quantity = (string)$newBatchQty;
                        $batch->save();

                        $priceForDeduction = round($deducted * $batch->price, 2);
                        $totalPriceAllProducts += $priceForDeduction;

                        $priceJson[] = [
                            'quantity' => $deducted,
                            'price' => $batch->price,
                        ];
                        $remaining = 0;
                    } else {
                        $deducted = $batchQty;
                        $batch->quantity = "0";
                        $batch->save();

                        $priceForDeduction = round($deducted * $batch->price, 2);
                        $totalPriceAllProducts += $priceForDeduction;

                        $priceJson[] = [
                            'quantity' => $deducted,
                            'price' => $batch->price,
                        ];
                        $remaining = round($remaining - $batchQty, 2);
                    }
                }

                // تحديث سجل المخزون لتخزين تفاصيل الأسعار بصيغة JSON
                $stockEntry = $this->stock
                    ->where('product_id', $productId)
                    ->where('seller_id', $request->seller_id)
                    ->first();
                if ($stockEntry) {
                    $stockEntry->price = json_encode($priceJson, JSON_UNESCAPED_UNICODE);
                    $stockEntry->save();
                }

                // تحديث كمية المنتج العام: خصم الكمية من العمود الخاص بالفرع إن وجد، وإلا من الكمية العامة
                if (isset($product->$branchColumn)) {
                    $newQuantity = round((float)$product->$branchColumn - $adjustedStock, 2);
                    $product->$branchColumn = (string)$newQuantity;
                    $product->save();
                } else {
                    $newQuantity = round((float)$product->quantity - $adjustedStock, 2);
                    $product->quantity = (string)$newQuantity;
                    $product->save();
                }

                // إنشاء قيد محاسبي لعملية صرف المخزون (نموذج قيد)
                $sellerAccount = Seller::find($request->seller_id);
                $branch = Branch::where('id', $adminBranchId)->first();
                $payable_account_3 = Account::find($branch->account_stock_Id);
                $payable_account_to_3 = Account::find($sellerAccount->account_id);
                $payable_transaction = new Transection();
                $payable_transaction->tran_type = 555;
                $payable_transaction->seller_id = auth('admin')->user()->id;
                $payable_transaction->branch_id = $adminBranchId;
                $payable_transaction->cost_id = $request->cost_id;
                $payable_transaction->account_id = $branch->account_stock_Id;
                $payable_transaction->account_id_to = $sellerAccount->account_id;
                $payable_transaction->amount = $totalPriceAllProducts;
                $payable_transaction->description = 'امر صرف مخزني';
                $payable_transaction->debit = $totalPriceAllProducts;
                $payable_transaction->credit = 0;
                $payable_transaction->balance = round($payable_account_3->balance - $totalPriceAllProducts, 2);
                $payable_transaction->debit_account = 0;
                $payable_transaction->credit_account = $totalPriceAllProducts;
                $payable_transaction->balance_account = round($payable_account_to_3->balance + $totalPriceAllProducts, 2);
                $payable_transaction->date = date("Y/m/d");
                $payable_transaction->save();

                // تحديث أرصدة الحسابات
                $payable_account_3->balance = round($payable_account_3->balance - $totalPriceAllProducts, 2);
                $payable_account_3->total_out += $totalPriceAllProducts;
                $payable_account_3->save();

                $payable_account_to_3->balance = round($payable_account_to_3->balance + $totalPriceAllProducts, 2);
                $payable_account_to_3->total_in += $totalPriceAllProducts;
                $payable_account_to_3->save();

            } else {
                // -------------------------------
                // منطق مرتجع المخزون (type != 4، نفترض هنا type == 7)
                // -------------------------------
                // في المرتجع لا نقوم بخصم الكمية من stock_batches، بل ننشئ دفعة مرتجع جديدة
                $stockEntry = $this->stock
                    ->where('product_id', $productId)
                    ->where('seller_id', $request->seller_id)
                    ->first();

                if ($stockEntry) {
                    $currentStock = (float)$stockEntry->stock;
                    $currentMainStock = (float)$stockEntry->main_stock;
                    // هنا نقوم بتقليل الرصيد المسجل للمندوب، لأنه سيتم تسوية الصرف السابق
                    $stockEntry->update([
                        'stock'      => (string) round($currentStock - $adjustedStock, 2),
                        'main_stock' => (string) round($currentMainStock - $adjustedStock, 2),
                    ]);
                }

                // تسجيل عملية المرتجع في ProductLog مع نوع 200
                $productLog = new ProductLog();
                $productLog->product_id = $productId;
                $productLog->quantity = (string)$adjustedStock;
                $productLog->seller_id = $request->seller_id;
                $productLog->branch_id = $adminBranchId;
                $productLog->type = 200; // مرتجع المخزون
                $productLog->save();

                // إنشاء دفعة جديدة في StockBatch لعملية المرتجع
                $newBatch = new StockBatch();
                $newBatch->product_id = $productId;
                $newBatch->branch_id = $adminBranchId;
                $newBatch->quantity = (string)$adjustedStock;
                // السعر للمرتجع: نستخدم السعر الشرائي مع الضريبة
                $newBatch->price = $product->purchase_price + ($product->purchase_price * ($product->taxe->amount / 100));
                $newBatch->save();

                // تحديث كمية المنتج العام: إضافة الكمية بدلاً من طرحها
                if (isset($product->$branchColumn)) {
                    $newQuantity = round((float)$product->$branchColumn + $adjustedStock, 2);
                    $product->$branchColumn = (string)$newQuantity;
                    $product->save();
                } else {
                    $newQuantity = round((float)$product->quantity + $adjustedStock, 2);
                    $product->quantity = (string)$newQuantity;
                    $product->save();
                }

                // إنشاء قيد محاسبي عكسي للمرتجع
                $sellerAccount = Seller::find($request->seller_id);
                $branch = Branch::where('id', $adminBranchId)->first();
                $payable_account_3 = Account::find($branch->account_stock_Id);
                $payable_account_to_3 = Account::find($sellerAccount->account_id);

                $reverseTransaction = new Transection();
                $reverseTransaction->tran_type = $request->type; // هنا تكون القيمة 7
                $reverseTransaction->seller_id = auth('admin')->user()->id;
                $reverseTransaction->branch_id = $adminBranchId;
                $reverseTransaction->cost_id = $request->cost_id;
                $reverseTransaction->account_id = $branch->account_stock_Id;
                $reverseTransaction->account_id_to = $sellerAccount->account_id;
                // عكس المبلغ: القيمة تصبح سالبة
                $reverseTransaction->amount = -$totalPriceAllProducts;
                $reverseTransaction->description = 'مرتجع صرف مخزني';
                $reverseTransaction->debit = 0;
                $reverseTransaction->credit = $totalPriceAllProducts;
                $reverseTransaction->balance = round($payable_account_3->balance + $totalPriceAllProducts, 2);
                $reverseTransaction->debit_account = $totalPriceAllProducts;
                $reverseTransaction->credit_account = 0;
                $reverseTransaction->balance_account = round($payable_account_to_3->balance - $totalPriceAllProducts, 2);
                $reverseTransaction->date = date("Y/m/d");
                $reverseTransaction->save();

                // تحديث أرصدة الحسابات (عكس المعاملة)
                $payable_account_3->balance = round($payable_account_3->balance + $totalPriceAllProducts, 2);
                $payable_account_3->total_in += $totalPriceAllProducts;
                $payable_account_3->save();

                $payable_account_to_3->balance = round($payable_account_to_3->balance - $totalPriceAllProducts, 2);
                $payable_account_to_3->total_out += $totalPriceAllProducts;
                $payable_account_to_3->save();
            }

            // تجميع بيانات المنتج لسجل الاحتياط (Reserve)
            $reserveProducts[] = [
                'product_name' => $product->name,
                'product_id'   => $productId,
                'stock'        => $adjustedStock,
                'balance'      => $product->quantity, // الرصيد المتبقي
                'price'        => $product->selling_price,
            ];

            $success = true;
        }

        // تخزين سجل الاحتياط إن وُجدت بيانات
        if (!empty($reserveProducts)) {
            \DB::table('reserve_products')->insert([
                'seller_id' => $request->seller_id,
                'data'      => json_encode($reserveProducts, JSON_UNESCAPED_UNICODE),
                'type'      => 3,
                'active'    => 2,
                'branch_id' => $adminBranchId,
            ]);
        }

        DB::commit();

        if ($success) {
            Toastr::success(translate('تم تنفيذ الطلب بنجاح'));
        }

        return redirect()->back()->with('success', 'تم تنفيذ الطلب بنجاح!');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("Stock processing error: " . $e->getMessage());
        Toastr::error(translate('حدث خطأ أثناء تنفيذ الطلب: ' . $e->getMessage()));
        return redirect()->back()->with('error', $e->getMessage())->withInput();
    }
}


public function deactivateReservedProductsByReservationId(Request $request, $reservationId)
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

    if (!in_array("import41.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    // Retrieve the reserved products associated with the given reservation ID
    $reservedProducts = $this->reserveProduct
        ->where('id', $reservationId)
        ->get();

    // Check if there are any reserved products for the given reservation ID
    if ($reservedProducts->isEmpty()) {
        // Handle case where no products are found for the given reservation ID
        return response()->json([
            'status' => false,
            'error' => 'No valid product IDs found for the given reservation ID.'
        ], 400);
    }

    // Loop through each reserved product and set the active status to 0 (deactivate)
    foreach ($reservedProducts as $reservedProduct) {
        $reservedProduct->active = 0;
        $reservedProduct->save();
    }

    // Redirect back with a success message
    return redirect()->back()->with('status', 'تم رفض الامر بنجاح.');
}




    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search_product(Request $request): JsonResponse
    {

        $request->validate([
            'name' => 'required',
        ], [
            'name.required' => translate('Product name is required'),
        ]);

        $key = explode(' ', $request['name']);
        $products = $this->product->where('quantity', '>', 0)->active()->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->where('name', 'like', "%{$value}%");
            }
        })->orWhere(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->where('product_code', 'like', "%{$value}%");
            }
        })->paginate(6);

        $count_p = $products->count();

        return response()->json([
            'result' => view('admin-views.pos._search-result', compact('products'))->render(),
            'count' => $count_p
        ]);
    }


    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function search_by_add_product(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ], [
            'name.required' => translate('Product name is required'),
        ]);

        if (is_numeric($request['name'])) {
            $products = $this->product->where('quantity', '>', 0)->active()->where('product_code', $request['name'])->paginate(6);
        } else {
            $products = $this->product->where('quantity', '>', 0)->active()->where('name', $request['name'])->paginate(6);
        }

        $count_p = $products->count();
        if ($count_p > 0) {
            return response()->json([
                'count' => $count_p,
                'id' => $products[0]->id,
            ]);
        }
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */

public function order_list(Request $request)
{
    /* ===================== التحقق من الصلاحيات ===================== */
    $admin = Auth::guard('admin')->user();
    if (! $admin) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $role = DB::table('roles')->where('id', $admin->role_id)->first();
    if (! $role) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    // بعض قواعد البيانات تخزن JSON كنص نصّي مرتين؛ نتأكد من تحويله لمصفوفة
    $permissions = json_decode($role->data, true);
    if (is_string($permissions)) {
        $permissions = json_decode($permissions, true);
    }
    if (! is_array($permissions) || ! in_array('order4.index', $permissions, true)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    /* ===================== مدخلات الفلترة ===================== */
    $search      = $request->input('search');
    $fromDate    = $request->input('from_date');
    $toDate      = $request->input('to_date');
    $regionId    = $request->input('region_id');
    $seller_id   = $request->input('seller_id');
    $customer_id = $request->input('customer_id');
    $branch_id   = $request->input('branch_id');
    $done        = $request->input('done');        // 1 أو 0
    $type        = $request->input('type');        // نوع العميل
    $account_id  = $request->input('account_id');  // << الفلتر الجديد

    // لو تاريخ النهاية موجود: نزود يوم كامل لإشراك اليوم الأخير
    $toNewDate = $toDate
        ? \Carbon\Carbon::parse($toDate)->addDay()->format('Y-m-d')
        : null;

    // IDs للبائعين المرتبطين بهذا المسؤول (لو محتاجينها لاحقًا)
    $sellerIds = AdminSeller::where('admin_id', $admin->id)
        ->pluck('seller_id')
        ->toArray();

    /* ===================== بناء الاستعلام ===================== */
    $ordersQuery = Order::with(['customer', 'seller', 'details', 'branch'])
        ->whereIn('type', [4, 1])
        ->latest()
        ->when($search, function ($q) use ($search) {
            // بحث بسيط على رقم الطلب
            $q->where('id', 'like', "%{$search}%");
        })
        ->when($customer_id, function ($q) use ($customer_id) {
            $q->where('user_id', $customer_id);
        })
        ->when($branch_id, function ($q) use ($branch_id) {
            $q->where('branch_id', $branch_id);
        })
        ->when($seller_id, function ($q) use ($seller_id) {
            $q->where('owner_id', $seller_id);
        })
        ->when($fromDate && $toNewDate, function ($q) use ($fromDate, $toNewDate) {
            $q->whereBetween('date', [$fromDate, $toNewDate]);
        })
        ->when($regionId, function ($q) use ($regionId) {
            $q->whereHas('customer', function ($q2) use ($regionId) {
                $q2->where('region_id', $regionId);
            });
        })
        ->when($type, function ($q) use ($type) {
            $q->whereHas('customer', function ($q2) use ($type) {
                $q2->where('type', $type);
            });
        })
        ->when(! is_null($done), function ($q) use ($done) {
            $q->where('done', $done);
        })
        ->when($account_id, function ($q) use ($account_id) {
            // << فلترة حسب الحساب المختار
            $q->where('payment_id', $account_id);
        });

    /* ===================== حساب المجاميع ===================== */
    $allFiltered = (clone $ordersQuery)->get();

    $orderAmountSum   = $allFiltered->sum('order_amount');
    $collectedCashSum = $allFiltered->sum('collected_cash');
    $quantitySum      = $allFiltered->sum(fn ($o) => $o->details->sum('quantity'));
    $productCount     = $allFiltered->sum(fn ($o) => $o->details->count());

    /* ===================== ترقيم الصفحات ===================== */
    $orders = $ordersQuery
        ->paginate(Helpers::pagination_limit())
        ->appends($request->only([
            'search', 'from_date', 'to_date',
            'region_id', 'seller_id', 'customer_id',
            'branch_id', 'done', 'type', 'account_id' // << ضفنا account_id
        ]));

    /* ===================== بيانات القوائم المنسدلة ===================== */
    $regions   = $this->regions->get();
    $sellers   = Seller::all();
    $customers = Customer::all();
    $branches  = Branch::all();
    $accounts  = \App\Models\Account::all(); // << لو عندك import استخدم Account::all()

    /* ===================== العناصر المحددة (للعرض) ===================== */
    $sellerw    = Seller::find($seller_id);
    $customerw  = Customer::find($customer_id);
    $branchw    = Branch::find($branch_id);
    $accountw   = \App\Models\Account::find($account_id); // << المحدد من الحسابات

    /* ===================== عرض الصفحة ===================== */
    return view('admin-views.pos.order.list', compact(
        'orders',
        'search', 'fromDate', 'toDate',
        'regions', 'regionId',
        'orderAmountSum', 'collectedCashSum',
        'quantitySum', 'productCount',
        'done', 'type',
        'sellers', 'customers', 'branches', 'accounts',   // << ضفنا accounts
        'sellerw', 'customerw', 'branchw', 'accountw',    // << ضفنا accountw
        'seller_id', 'customer_id', 'branch_id', 'account_id' // << ضفنا account_id
    ));
}




public function refund_list(Request $request)
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

    if (!in_array("order7.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    $search = $request->input('search');
    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');
    $regionId = $request->input('region_id');
    $seller_id = $request->input('seller_id');
    $customer_id = $request->input('customer_id');
    $branch_id = $request->input('branch_id');
    $done = $request->input('done'); // For the 'done' filter (1 or 0)
    $type = $request->input('type'); // Customer type filter
    $toNewDate = date('Y-m-d', strtotime("+1 day", strtotime($toDate))); // Adjust end date
    $adminId = Auth::guard('admin')->id();

    // Fetch related seller and customer
    $sellerw = Seller::where('id', $seller_id)->first();
    $customerw = Customer::where('id', $customer_id)->first();
        $branchw = Branch::where('id', $branch_id)->first();

    $sellers = Seller::all(); 
        $customers = Customer::all();
                $branches = Branch::all();

    // Retrieve seller_id(s) associated with the authenticated admin
    $sellerIds = AdminSeller::where('admin_id', $adminId)->pluck('seller_id');

    // Initialize query for orders
    $ordersQuery = $this->order
        ->where('type', 7)
        ->latest()
        ->with(['customer', 'seller', 'details','branch']); // Assuming relationship for details is set

    // Apply search filter
    if (!empty($search)) {
        $ordersQuery->where(function($query) use ($search) {
            $query->where('id', 'like', '%' . $search . '%');
            });
    }

    // Apply customer ID filter
    if (!empty($customer_id)) {
        $ordersQuery->where('user_id', $customer_id);
    }
       if (!empty($branch_id)) {
        $ordersQuery->where('branch_id', $branch_id);
    }

    // Apply seller ID filter
    if (!empty($seller_id)) {
        $ordersQuery->where('owner_id', $seller_id);
    }

    // Apply date filter
    if (!empty($fromDate) && !empty($toDate)) {
        $ordersQuery->whereBetween('created_at', [$fromDate, $toDate]);
    }

    // Apply region filter for customer
    if (!empty($regionId)) {
        $ordersQuery->whereHas('customer', function($query) use ($regionId) {
            $query->where('region_id', $regionId);
        });
    }

    // Apply customer type filter
    if (!empty($type)) {
        $ordersQuery->whereHas('customer', function($query) use ($type) {
            $query->where('type', $type); // Assuming 'type' is a column in the customers table
        });
    }

    // Get the filtered orders without pagination for sum calculation
    $allRefunds = $ordersQuery->get();

    // Calculate sums before pagination
    $orderAmountSum = $allRefunds->sum('order_amount');
    $collectedCashSum = $allRefunds->sum('collected_cash');
    $quantitySum = $allRefunds->sum(function ($order) {
        return $order->details->sum('quantity'); // Sum of quantities in order details
    });
    $productCount = $allRefunds->sum(function ($order) {
        return $order->details->count(); // Count of order details
    });

    // Paginate the filtered orders for display
    $refunds = $ordersQuery->paginate(Helpers::pagination_limit())->appends([
        'search' => $search,
        'from_date' => $fromDate,
        'to_date' => $toDate,
        'region_id' => $regionId,
        'done' => $done,
        'type' => $type, // Include the new filter in the pagination links
    ]);

    // Get regions for the dropdown
    $regions = $this->regions->get();

    // Return the view with the necessary data
    return view('admin-views.pos.refund.list', compact(
        'refunds',
        'search',
        'fromDate',
        'toDate',
        'regions',
        'regionId',
        'orderAmountSum',
        'collectedCashSum',
        'quantitySum',
        'productCount',
        'done',
        'type',
        'sellers',
        'customers',
        'sellerw',
        'customerw',
        'seller_id',
        'customer_id',
        'branch_id',
        'branchw',
        'branches'
    ));
}





public function sample_list(Request $request)
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

    if (!in_array("order12.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    $search = $request->input('search');
    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');
    $regionId = $request->input('region_id');
        $branch_id = $request->input('branch_id');

    $done = $request->input('done');
    $toNewDate = date('Y-m-d', strtotime("+1 day", strtotime($toDate)));
    $adminId = Auth::guard('admin')->id();

    // Retrieve seller_id(s) associated with the authenticated admin
    $sellerIds = AdminSeller::where('admin_id', $adminId)->pluck('seller_id');

    $orders = $this->order
        ->where('type', 12)
        ->latest()
        ->with(['customer', 'seller', 'details', 'supplier','branch']);

    // Apply search filter
    if (!empty($search)) {
        $orders->where(function ($query) use ($search) {
            $query->where('id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($query) use ($search) {
                      $query->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('supplier', function ($query) use ($search) {
                      $query->where('email', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                  })
                  
                  ->orWhereHas('seller', function ($query) use ($search) {
                      $query->where('email', 'like', "%{$search}%")
                            ->orWhere('l_name', 'like', "%{$search}%");
                  });
                  
        });
    }
    if (!empty($branch_id)) {
        $orders->where('branch_id', $branch_id);
    }
    // Apply date filter
    if (!empty($fromDate) && !empty($toNewDate)) {
        $orders->whereBetween('created_at', [$fromDate, $toNewDate]);
    }

    // Apply region filter for customer
    if (!empty($regionId)) {
        $orders->whereHas('customer', function ($query) use ($regionId) {
            $query->where('region_id', $regionId);
        });
    }

    // Paginate the filtered orders
    $orders = $orders->paginate(Helpers::pagination_limit())->appends([
        'search' => $search,
        'from_date' => $fromDate,
        'to_date' => $toDate,
        'region_id' => $regionId,
        'done' => $done,
    ]);

    // Calculate total discounts grouped by supplier_id
$discountsBySupplier = DB::table('suppliers')
    ->select('id', 'name', DB::raw('SUM(discount) as total_discount'))
    ->groupBy('id', 'name')
    ->get();

// Calculate the grand total discount from all suppliers
$grandTotalDiscount = $discountsBySupplier->sum('total_discount');



    // Calculate additional sums
    $orderAmountSum = $orders->sum('order_amount');
    $collectedCashSum = $orders->sum('collected_cash');
    $DiscountSum = $orders->sum('extra_discount');

    $quantitySum = $orders->sum(function ($order) {
        return $order->details->sum('quantity');
    });

    $productCount = $orders->sum(function ($order) {
        return $order->details->count();
    });

    // Get regions for the dropdown
    $regions = $this->regions->get();
    $branches = $this->branch->get();

    // Return the view with the necessary data
    return view('admin-views.pos.sample.list', compact(
        'orders',
        'search',
        'fromDate',
        'toDate',
        'regions',
        'regionId',
        'orderAmountSum',
        'collectedCashSum',
        'DiscountSum',
        'quantitySum',
        'productCount',
        'done',
        'branches',
        'branch_id',
        'discountsBySupplier',
        'grandTotalDiscount'// Pass discounts grouped by supplier
    ));
}
public function sample_list_report(Request $request)
{
    $search = $request->input('search');
    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');
    $regionId = $request->input('region_id');
    $done = $request->input('done');
    $supplierId = $request->input('supplier_id');  // Added filter for supplier_id
    $toNewDate = date('Y-m-d', strtotime("+1 day", strtotime($toDate)));
    $adminId = Auth::guard('admin')->id();

    // Retrieve seller_id(s) associated with the authenticated admin
    $sellerIds = AdminSeller::where('admin_id', $adminId)->pluck('seller_id');

    // Start the query for orders
    $orders = $this->order
        ->where('type', 12)
        ->latest()
        ->with(['customer', 'seller', 'details', 'supplier']);

    // Apply search filter

    // Apply supplier filter if provided
    if (!empty($supplierId)) {
        $orders->whereHas('supplier', function ($query) use ($supplierId) {
            $query->where('id', $supplierId);
        });
    }

    // Apply date filter
    if (!empty($fromDate) && !empty($toNewDate)) {
        $orders->whereBetween('created_at', [$fromDate, $toNewDate]);
    }

    // Apply region filter for customer

    // Paginate the filtered orders
    $orders = $orders->paginate(Helpers::pagination_limit())->appends([
        'from_date' => $fromDate,
        'to_date' => $toDate,
        'done' => $done,
        'supplier_id' => $supplierId,  // Include the supplier filter in pagination
    ]);

    // Calculate additional sums
    $orderAmountSum = $orders->sum('order_amount');
    $collectedCashSum = $orders->sum('collected_cash');
    $DiscountSum = $orders->sum('extra_discount');

    // Calculate quantity and product count (unique product_id)
    $productSummaries = $orders->flatMap(function ($order) {
        return $order->details->map(function ($detail) {
            return [
                'product_id' => $detail->product_id,
                'quantity' => $detail->quantity,
                'price' => $detail->price,
            ];
        });
    })->groupBy('product_id')->map(function ($details) {
        return [
            'quantity' => $details->sum('quantity'),
            'price' => $details->first()['price'], // Assuming price remains same for each product_id
        ];
    });

    $quantitySum = $productSummaries->sum('quantity');
    $productCount = $productSummaries->count();

    // Get regions for the dropdown
    $suppliers= $this->suppliers->get();

    // Calculate total discounts grouped by supplier_id
    $discountsBySupplier = DB::table('suppliers')
        ->select('id', 'name', DB::raw('SUM(discount) as total_discount'))
        ->groupBy('id', 'name')
        ->get();

    // Calculate the grand total discount from all suppliers
    $grandTotalDiscount = $discountsBySupplier->sum('total_discount');

    // Return the view with the necessary data
    return view('admin-views.pos.sample.report', compact(
        'orders',
        'fromDate',
        'toDate',
        'suppliers',
        'orderAmountSum',
        'collectedCashSum',
        'DiscountSum',
        'quantitySum',
        'productCount',
        'done',
        'discountsBySupplier',
        'grandTotalDiscount', // Pass discounts grouped by supplier
        'productSummaries' // Pass the unique product data
    ));
}


public function donation_list(Request $request)
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

    if (!in_array("order24.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    $search = $request->input('search');
    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');
    $regionId = $request->input('region_id');
            $branch_id = $request->input('branch_id');

    $done = $request->input('done'); // For the 'done' filter (1 or 0)
    $toNewDate = date('Y-m-d', strtotime("+1 day", strtotime($toDate)));
    $adminId = Auth::guard('admin')->id();

    // Retrieve seller_id(s) associated with the authenticated admin
    $sellerIds = AdminSeller::where('admin_id', $adminId)->pluck('seller_id');
    $orders = $this->order
        ->where('type', 24)
      
        ->latest()
        ->with(['customer', 'seller', 'details','supplier','branch']); // Assuming relationship for details is set
// dd($orders);

    // Apply search filter
    if (!empty($search)) {
        $orders->where(function($query) use ($search) {
            $query->where('id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($query) use ($search) {
                      $query->where('name', 'like', "%{$search}%");
                  })
                       ->orWhereHas('supplier', function($query) use ($search) {
                      $query->where('email', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('seller', function($query) use ($search) {
                      $query->where('email', 'like', "%{$search}%")
                            ->orWhere('l_name', 'like', "%{$search}%");
                  });
        });
    }
        if (!empty($branch_id)) {
        $orders->where('branch_id', $branch_id);
    }

    // Apply date filter
    if (!empty($fromDate) && !empty($toNewDate)) {
        $orders->whereBetween('created_at', [$fromDate, $toNewDate]);
    }

    // Apply region filter for customer
    if (!empty($regionId)) {
        $orders->whereHas('customer', function($query) use ($regionId) {
            $query->where('region_id', $regionId);
        });
    }

    // // Apply done status filter (1 or 0)
    // if (isset($done)) {
    //     if ($done == 1) {
    //     $orders = $orders->where('order_amount', '=', \DB::raw('transaction_reference'));
    //     } else {
    //     $orders = $orders->where('order_amount', '>', \DB::raw('transaction_reference'));
    //     }
    // }

    // // Apply done status filter before pagination
    // if ($done == 1) {
    //     // Filter where order_amount = transaction_reference
    //     $orders = $orders->where('order_amount', '=', \DB::raw('transaction_reference'));
    // } else {
    //     // Filter where order_amount > transaction_reference
    //     $orders = $orders->where('order_amount', '>', \DB::raw('transaction_reference'));
    // }

    // Paginate the filtered orders
    $orders = $orders->paginate(Helpers::pagination_limit())->appends([
        'search' => $search,
        'from_date' => $fromDate,
        'to_date' => $toDate,
        'region_id' => $regionId,
        'done' => $done,
    ]);

    // Calculate sums after pagination
    $orderAmountSum = $orders->sum('order_amount');
    $collectedCashSum = $orders->sum('collected_cash');
   $DiscountSum = $orders->sum('extra_discount');
    $quantitySum = $orders->sum(function ($order) {
        return $order->details->sum('quantity'); // Use 'details' instead of 'orderDetails'
    });
    $productCount = $orders->sum(function ($order) {
        return $order->details->count(); // Use 'details' instead of 'orderDetails'
    });

    // Get regions for the dropdown
    $regions = $this->regions->get();
    $branches = $this->branch->get();

    // Return the view with the necessary data
    return view('admin-views.pos.donation.list', compact(
        'orders',
        'search',
        'fromDate',
        'toDate',
        'regions',
        'regionId',
        'orderAmountSum',
        'collectedCashSum',
        'DiscountSum',
        'quantitySum',
        'productCount',
        'done',
        'branch_id',
        'branches'
    ));
}
    




public function installment_list(Request $request)
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

    if (!in_array("installment.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    $search = $request->input('search');
        $seller_id = $request->input('seller_id');
                $customer_id = $request->input('customer_id');
    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');
    $regionId = $request->input('region_id'); // Get region_id from the request
        $branch_id = $request->input('branch_id'); // Get region_id from the request

    $regions = $this->regions->get();

    $adminId = Auth::guard('admin')->id(); // Get the authenticated admin ID

    // Retrieve seller_id(s) associated with the authenticated admin
    $sellerIds = AdminSeller::where('admin_id', $adminId)->pluck('seller_id'); 
    $sellers = Seller::all(); 
        $customers = Customer::all();
        $branches = Branch::all();

  $installments = $this->installment
    ->where(function ($query) use ($sellerIds, $adminId) {
        $query->whereIn('seller_id', $sellerIds)
              ->orWhere('seller_id', $adminId);
    })
    ->latest()
    ->with(['customer', 'seller','branch']);


    // Apply filters based on the search input
   if ($seller_id) {
    $installments->where('seller_id', $seller_id);
}
   if ($customer_id) {
    $installments->where('customer_id', $customer_id);
}
if ($branch_id) {
    $installments->where('branch_id', $branch_id);
}
$sellerw=Seller::where('id', $request->input('seller_id'))->first();
$customerw=Customer::where('id', $request->input('customer_id'))->first();
$branchw=Branch::where('id', $request->input('branch_id'))->first();


    // Apply region filter
    if ($regionId) {
        $installments->whereHas('customer', function($query) use ($regionId) {
            $query->where('region_id', $regionId); // Filter by region_id in customers table
        });
    }
 // Apply date range filter
    if ($branch_id) {
$installments->where('branch_id', $branch_id);
    }
    // Apply date range filter
    if ($fromDate && $toDate) {
$installments->whereBetween('created_at', [Carbon::parse($fromDate), Carbon::parse($toDate)]);
    }

    // Paginate the results and append the query parameters
    $installments = $installments->paginate(Helpers::pagination_limit())->appends($request->query());

    // Calculate the sum of the price (replace 'price' with the correct column name)
    $totalAmount = $installments->sum('total_price'); // Replace 'price' with the correct field name

    return view('admin-views.pos.installment.list', compact('installments', 'search', 'fromDate', 'toDate', 'regions', 'regionId', 'totalAmount','sellerw','sellers','customers','customerw','seller_id','customer_id','branches','branch_id','branchw'));
}



    public function generate_installments_invoice($id)
    {
        $installment = $this->installment->find($id);

        return response()->json([
            'success' => 1,
            'view' => view('admin-views.pos.installment.invoice', compact('installment'))->render(),
        ]);
    }
public function reservation_list(Request $request, $type, $active)
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

    if (!in_array("reservation.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    $search = $request->input('search');
    $fromDate = $request->input('from_date');
        $branch_id = $request->input('branch_id');

    $toDate = $request->input('to_date');
    $toNewDate = date('Y-m-d', strtotime("+1 day", strtotime($toDate))); // Adjust to include the end date

    // Start with reservations filtered by type
    $reservations = ReserveProduct::where('type', $type);

$adminId = Auth::guard('admin')->id(); // Get the authenticated admin ID

    // Retrieve seller_id(s) associated with the authenticated admin
    $sellerIds = AdminSeller::where('admin_id', $adminId)->pluck('seller_id'); 
$branches=Branch::all();
    $reservations = ReserveProduct::where('type', $type)
        ->whereIn('seller_id', $sellerIds)->orwhere('seller_id',$adminId)// Filter by associated seller_id(s)
        ->latest()
        ->with(['customer', 'seller','branch']); // Assuming relationships are named 'customer' and 'seller'

    // Apply active status filter
    if ($active === 'all') {
        $reservations->whereIn('active', [0, 1]);
    } else {
        $reservations->where('active', $active);
    }

    // Apply search filter if provided
    if ($search) {
        $reservations->where(function ($query) use ($search) {
            $query->whereHas('customer', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orWhereHas('seller', function ($query) use ($search) {
                $query->where('f_name', 'like', "%{$search}%")
                      ->orWhere('l_name', 'like', "%{$search}%");
            });
        });
    }

    // Apply date range filter if provided
    if ($fromDate && $toDate) {
        $reservations->whereBetween('created_at', [$fromDate, $toNewDate]);
    }
        if ($branch_id) {
        $reservations->where('branch_id', $branch_id);
    }

    // Paginate results
    $reservations = $reservations->latest()
        ->paginate(Helpers::pagination_limit())
        ->appends($request->query());

    // Pass data to the view
    return view('admin-views.pos.reservations.list', compact('reservations', 'search', 'fromDate', 'toDate','branches','branch_id'));
}
public function reservation_list_notification(Request $request, $type, $active)
{
    $adminId = Auth::guard('admin')->id();
    $admin = DB::table('admins')->where('id', $adminId)->first();

    if (!$admin) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $role = DB::table('roles')->where('id', $admin->role_id)->first();

    if (!$role) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    if (!$this->hasPermission($role->data, $type, $active)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    // بيانات البحث والتصفية
    $search = $request->input('search');
    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');
    $branch_id = $request->input('branch_id');
    $toNewDate = $toDate ? date('Y-m-d', strtotime("+1 day", strtotime($toDate))) : null;
    
    $branches = Branch::all();
    
    // استرجاع الـ seller_id المرتبط بالمشرف
    $sellerIds = AdminSeller::where('admin_id', $adminId)->pluck('seller_id');

    // استعلام الحجزات
    $reservations = ReserveProduct::where('type', $type)
        ->where(function ($query) use ($sellerIds, $adminId) {
            $query->whereIn('seller_id', $sellerIds)
                  ->orWhere('seller_id', $adminId);
        })
        ->where('active', $active)
        ->latest()
        ->with(['customer', 'seller']); // العلاقات المفترضة

    // تطبيق البحث
    if ($search) {
        $reservations->where(function ($query) use ($search) {
            $query->whereHas('customer', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })->orWhereHas('seller', function ($query) use ($search) {
                $query->where('f_name', 'like', "%{$search}%")
                      ->orWhere('l_name', 'like', "%{$search}%");
            });
        });
    }

    // تطبيق التصفية حسب التواريخ
    if ($fromDate && $toNewDate) {
        $reservations->whereBetween('created_at', [$fromDate, $toNewDate]);
    }

    // تصفية حسب الفرع
    if ($branch_id) {
        $reservations->where('branch_id', $branch_id);
    }

    // تنفيذ الاستعلام مع الترقيم
    $reservations = $reservations->paginate(Helpers::pagination_limit())
                                 ->appends($request->query());

    return view('admin-views.pos.reservations.list_notification', compact(
        'reservations', 'search', 'fromDate', 'toDate', 'type', 'branches', 'branch_id'
    ));
}

/**
 * التحقق من صلاحيات المشرف
 */
private function hasPermission($roleData, $type, $active)
{
    $decodedData = json_decode($roleData, true);
    if (is_string($decodedData)) {
        $decodedData = json_decode($decodedData, true);
    }

    if (!is_array($decodedData)) {
        return false;
    }

    // تحديد الصلاحيات المطلوبة بناءً على النوع والحالة
    $permissions = [
        4 => "import41.index",
        7 => "export71.index",
        3 => ($active == 2 ? "export32.index" : null),
    ];

    return isset($permissions[$type]) && in_array($permissions[$type], $decodedData);
}



public function generate_reservation_notification_invoice($id)
{
    // Find the reservation product by ID
    
    $reserveProduct = $this->reserveProductNotification->find($id);
    
    // Fetch all products (you might want to fetch only related products)
    $products = \App\Models\Product::all();
    $sellers = \App\Models\Admin::where('role', 'seller')->get(); // Adjust the query to suit your app's logic

    // Initialize the seller_id and customer_id
    $seller_id = auth()->user()->seller_id ?? null;
    $customer_id = auth()->id(); // Assuming the customer is the logged-in user

    // Loop through each product to get the appropriate price
    foreach ($products as $product) {
        $product->price = $this->getProductPrice($product, $seller_id, $customer_id);
    }

    return response()->json([
        'success' => 1,
        'view' => view('admin-views.pos.reservations.invoice_notification', compact('reserveProduct', 'products','sellers'))->render(),
    ]);
}

/**
 * Method to get the appropriate price for a product
 */
protected function getProductPrice($product, $seller_id = null, $customer_id = null)
{
    // Check if there is a seller-specific price
    if ($seller_id) {
        $sellerPrice = \App\Models\SellerPrice::where('product_id', $product->id)
                                  ->where('seller_id', $seller_id)
                                  ->first();
        if ($sellerPrice) {
            return $sellerPrice->price;
        }
    }

    // Check if there is a customer-specific price
    if ($customer_id) {
        $customerPrice = \App\Models\CustomerPrice::where('product_id', $product->id)
                                      ->where('customer_id', $customer_id)
                                      ->first();
        if ($customerPrice) {
            return $customerPrice->price;
        }
    }

    // Default product price
    return $product->selling_price;
}

    
    
  public function generate_reservation_invoice($id)
    {
        $reserveProduct = $this->reserveProduct->find($id);

        return response()->json([
            'success' => 1,
            'view' => view('admin-views.pos.reservations.invoice', compact('reserveProduct'))->render(),
        ]);
    }
      public function generate_reservation_invoicea2($id)
    {
        $reserveProduct = $this->reserveProduct->find($id);

        return response()->json([
            'success' => 1,
            'view' => view('admin-views.pos.reservations.invoicea2', compact('reserveProduct'))->render(),
        ]);
    }
public function generate_reservation_invoice_notification($id)
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

    if (!in_array("import41.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    // Find the reserve product by its ID
    $reserveProduct = $this->reserveProduct->find($id);

    // Fetch all products
    $products = \App\Models\Product::all();
    $sellers = \App\Models\Admin::where('role', 'seller')->get(); // Adjust the query to suit your app's logic

    // Initialize the seller_id and customer_id
    $seller_id = $reserveProduct->seller_id ?? null;
    $customer_id = $reserveProduct->customer_id; // Assuming the customer is the logged-in user

    // Loop through each product to get the appropriate price
    foreach ($products as $product) {
        // Start with the default product price
        $product->selling_price = $product->selling_price;

        // Check for a specific price in the customer_prices table
        $customerPrice = \DB::table('customer_prices')
            ->where('customer_id', $customer_id)
            ->where('product_id', $product->id)
            ->first();

        if ($customerPrice) {
            // Use price from customer_prices if available
            $product->selling_price = $customerPrice->price;
        } else {
            // If no customer-specific price, check the seller_prices table
            $sellerPrice = \DB::table('seller_prices')
                ->where('seller_id', $seller_id)
                ->where('product_id', $product->id)
                ->first();

            if ($sellerPrice) {
                // Use price from seller_prices if available
                $product->selling_price = $sellerPrice->price;
            }
        }
    }

    return view('admin-views.pos.reservations.invoice_notification', compact('reserveProduct', 'products','sellers'));
}


    
    public function generate_stocks_invoice($id)
    {
$adminId = Auth::guard('admin')->id(); // Get the authenticated admin ID

    // Retrieve seller_id(s) associated with the authenticated admin
    $sellerIds = AdminSeller::where('admin_id', $adminId)->pluck('seller_id'); 


        $order = $this->stock_order->find($id);

$endstock = $this->stock_order->where('created_at', '>', $order->created_at)->where('seller_id',$order->seller_id)->first();

$endstockDate = $endstock ? $endstock->created_at : Carbon::now();

$orderssales = $this->order->whereBetween('created_at', [$order->created_at, $endstockDate])->where('owner_id',$order->seller_id)->where('type',4)->get();
$totalordercash = $this->order->whereBetween('created_at', [$order->created_at, $endstockDate])->where('owner_id',$order->seller_id)->where('type',4)->where('cash',1)->sum('order_amount');
$totalordercredit = $this->order->whereBetween('created_at', [$order->created_at, $endstockDate])->where('owner_id',$order->seller_id)->where('type',4)->where('cash',2)->sum('order_amount');
$totalordershabaka = $this->order->whereBetween('created_at', [$order->created_at, $endstockDate])->where('owner_id',$order->seller_id)->where('type',4)->where('cash',3)->sum('order_amount');

$ordersreturn = $this->order->whereBetween('created_at', [$order->created_at, $endstockDate])->where('owner_id',$order->seller_id)->where('type',7)->get();
$ordersreturncredit = $this->order->whereBetween('created_at', [$order->created_at,$endstockDate])->where('owner_id',$order->seller_id)->where('type',7)->sum('order_amount');

        $order['statistcs'] = json_decode($order->statistcs);
        // return $order->statistcs->products;
        $stocks = $this->stock_history->where('order_id',$id)->get();

        return response()->json([
            'success' => 1,
            'view' => view('admin-views.pos.stocks.invoice', compact('order','stocks','totalordercash','totalordercredit','totalordershabaka','ordersreturncredit'))->render(),
        ]);
    }
        public function generate_stocks_invoicea2($id)
    {
$adminId = Auth::guard('admin')->id(); // Get the authenticated admin ID

    // Retrieve seller_id(s) associated with the authenticated admin
    $sellerIds = AdminSeller::where('admin_id', $adminId)->pluck('seller_id'); 


        $order = $this->stock_order->find($id);

$endstock = $this->stock_order->where('created_at', '>', $order->created_at)->where('seller_id',$order->seller_id)->first();

$endstockDate = $endstock ? $endstock->created_at : Carbon::now();

$orderssales = $this->order->whereBetween('created_at', [$order->created_at, $endstockDate])->where('owner_id',$order->seller_id)->where('type',4)->get();
$totalordercash = $this->order->whereBetween('created_at', [$order->created_at, $endstockDate])->where('owner_id',$order->seller_id)->where('type',4)->where('cash',1)->sum('order_amount');
$totalordercredit = $this->order->whereBetween('created_at', [$order->created_at, $endstockDate])->where('owner_id',$order->seller_id)->where('type',4)->where('cash',2)->sum('order_amount');
$totalordershabaka = $this->order->whereBetween('created_at', [$order->created_at, $endstockDate])->where('owner_id',$order->seller_id)->where('type',4)->where('cash',3)->sum('order_amount');

$ordersreturn = $this->order->whereBetween('created_at', [$order->created_at, $endstockDate])->where('owner_id',$order->seller_id)->where('type',7)->get();
$ordersreturncredit = $this->order->whereBetween('created_at', [$order->created_at,$endstockDate])->where('owner_id',$order->seller_id)->where('type',7)->sum('order_amount');

        $order['statistcs'] = json_decode($order->statistcs);
        // return $order->statistcs->products;
        $stocks = $this->stock_history->where('order_id',$id)->get();

        return response()->json([
            'success' => 1,
            'view' => view('admin-views.pos.stocks.invoicea2', compact('order','stocks','totalordercash','totalordercredit','totalordershabaka','ordersreturncredit'))->render(),
        ]);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function generate_invoice($id)
    {
        $order = $this->order->where('id', $id)->with(['details'])->first();
        //return $order;
        return response()->json([
            'success' => 1,
            'view' => view('admin-views.pos.order.invoice', compact('order'))->render(),
        ]);
    }
       public function generate_invoicea2($id)
    {
        $order = $this->order->where('id', $id)->with(['details'])->first();
        //return $order;
        return response()->json([
            'success' => 1,
            'view' => view('admin-views.pos.order.invoicea2', compact('order'))->render(),
        ]);
    }
     public function sample_generate_invoice($id)
    {
        $order = $this->order->where('id', $id)->with(['details'])->first();
        //return $order;
        return response()->json([
            'success' => 1,
            'view' => view('admin-views.pos.sample.invoice', compact('order'))->render(),
        ]);
    }
      public function sample_generate_invoicea2($id)
    {
        $order = $this->order->where('id', $id)->with(['details'])->first();
        //return $order;
        return response()->json([
            'success' => 1,
            'view' => view('admin-views.pos.sample.invoicea2', compact('order'))->render(),
        ]);
    }
    public function generate_invoicereal($id)
{
    // Fetch the order using the provided ID
    $order = $this->order->where('id', $id)->with(['details'])->first();

    if (!$order) {
        return redirect()->back()->with('error', 'Order not found');
    }

    // Generate QR Code data based on type
    $type = $order->type;
    $order_id = $order->id;

    $qrcode_data =$order->qrcode;

    // Check the order type and determine the view to render
    if ($type == 4 || $type == 7) {
        // For types 4 or 7, render the "generate_invoicereal" view
        return view('admin-views.pos.order.invoicea2', compact('order', 'qrcode_data'));
    } elseif ($type == 12 || $type == 24) {
        // For types 12 or 24, render the "sample_generate_invoicereal" view
        return view('admin-views.pos.sample.invoicea2', compact('order', 'qrcode_data'));
    }

    // If type does not match any condition
    return redirect()->back()->with('error', 'Invalid order type');
}

       public function donation_generate_invoice($id)
    {
        $order = $this->order->where('id', $id)->with(['details'])->first();
        //return $order;
        return response()->json([
            'success' => 1,
            'view' => view('admin-views.pos.donation.invoice', compact('order'))->render(),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get_customers(Request $request,$type): JsonResponse
    {
        $key = explode(' ', $request['q']);
        // dd($type);
        if($type == 4 || $type == 7 ) {
        $data = DB::table('customers')
            ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%")
                        ->orWhere('mobile', 'like', "%{$value}%");
                }
            })->limit(6)
            ->get([DB::raw('id, IF(id <> "0",CONCAT(name,  " (", mobile ,")"), name) as text')]);
                    return response()->json($data);

}elseif( $type==1){
      $data = DB::table('customers')->where('id',1)->get([DB::raw('id, IF(id <> "0",CONCAT(name,  " (", mobile ,")"), name) as text')]);
                    return response()->json($data);
    
}else{
   $data = DB::table('suppliers')
            ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%")
                        ->orWhere('mobile', 'like', "%{$value}%");
                }
            })->limit(6)
            ->get([DB::raw('id, IF(id <> "0",CONCAT(name,  " (", mobile ,")"), name) as text')]); 
                    return response()->json($data);

}
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function customer_balance(Request $request): JsonResponse
    {
        $customer_balance = $this->customer->where('id', $request->customer_id)->first()->balance;
        return response()->json([
            'customer_balance' => $customer_balance
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function remove_coupon(Request $request): JsonResponse
    {
        $cart_id = ($request->user_id != 0 ? 'sc-' . $request->user_id : 'wc-' . rand(10, 1000));
        if (!in_array($cart_id, session('cart_name') ?? [])) {
            session()->push('cart_name', $cart_id);
        }

        $cart = session(session('current_user'));

        $cart_keeper = [];
        if (session()->has(session('current_user')) && count($cart) > 0) {
            foreach ($cart as $cartItem) {

                array_push($cart_keeper, $cartItem);
            }
        }
        if (session('current_user') != $cart_id) {
            $temp_cart_name = [];
            foreach (session('cart_name') as $cart_name) {
                if ($cart_name != session('current_user')) {
                    $temp_cart_name[] = $cart_name;
                }
            }
            session()->put('cart_name', $temp_cart_name);
        }
        session()->put('cart_name', $temp_cart_name);
        session()->forget(session('current_user'));
        session()->put($cart_id, $cart_keeper);
        session()->put('current_user', $cart_id);
        $user_id = explode('-', session('current_user'))[1];
        $current_customer = '';
        if (explode('-', session('current_user'))[0] == 'wc') {
            $current_customer = 'Walking Customer';
        } else {
            $current = $this->customer->where('id', $user_id)->first();
            $current_customer = $current->name . ' (' . $current->mobile . ')';
        }

        return response()->json([
            'cart_nam' => session('cart_name'),
            'current_user' => session('current_user'),
            'current_customer' => $current_customer,
            'view' => view('admin-views.pos._cart', compact('cart_id'))->render()
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function change_cart(Request $request): RedirectResponse
    {

        session()->put('current_user', $request->cart_id);

        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
  public function new_cart_id(Request $request): RedirectResponse
{
    $cart_id = 'wc-' . rand(10, 1000);
    session()->put('current_user', $cart_id);

    // Check if the cart_id is not already in the session's cart_name array
    if (!in_array($cart_id, session('cart_name') ?? [])) {
        session()->push('cart_name', $cart_id);
    }

    // Get the 'type' parameter from the request or define a default value (e.g., 4)
    $type = $request->input('type', 4); // Default to 4 if 'type' is not provided in the request

    // Redirect with the 'type' parameter
    return redirect()->route('admin.pos.index', ['type' => $type]);
}

    /**
     * @param Request $request
     * @return JsonResponse
     */
public function get_cart_ids(Request $request, $type): JsonResponse
{
    $cart_id = session('current_user');
    $cart = session($cart_id, []);
    $user_id = 0;
    $user_type = 'wc';
    $cart_keeper = [];
    $current_customer = 'Walking Customer';
$costcenters=CostCenter::where('active','1')->get();

    // تأكد من أن $cart هو Array وليس JSON
    if (is_string($cart)) {
        $cart = json_decode($cart, true);
    }

    if (!is_array($cart)) {
        $cart = []; // إذا كان فارغًا أو غير صالح، تعيينه كمصفوفة فارغة
    }

    // تحديد نوع المستخدم
    if (Str::contains($cart_id, 'sc')) {
        $user_id = explode('-', $cart_id)[1];
        $user_type = 'sc';
    }

    // تصفية العناصر غير الفارغة وإضافتها إلى $cart_keeper
    foreach ($cart as $cartItem) {
        $cart_keeper[] = $cartItem;
    }

    // تحديث السلة في الجلسة بعد التصفية
    session()->put($cart_id, $cart_keeper);

    // استرجاع بيانات العميل أو المورد بناءً على نوع المستخدم
    if ($user_type === 'sc') {
        if ($type == 4 || $type == 7) {
            $current = $this->customer->find($user_id);
        } else {
            $current = $this->suppliers->find($user_id);
        }

        if ($current) {
            $current_customer = $current->name . ' (' . $current->mobile . ')';
        }
    }

    // إرجاع الاستجابة مع البيانات المحدثة
    return response()->json([
        'current_user' => $cart_id,
        'cart_nam' => session('cart_name') ?? '',
        'current_customer' => $current_customer,
        'user_type' => $user_type,
        'user_id' => $user_id,
        'view' => view('admin-views.pos._cart', compact('cart_id', 'cart_keeper'))->render(),
    ]);
}

public function updateUnit(Request $request,$type)
{
    // Validate the request data
    $validated = $request->validate([
        'product_id' => 'required',
        'unit' => 'required|in:0,1', // Only 0 or 1 are allowed for unit
    ]);

    // Get the cart_id from the current user's session
    $cartId = session('current_user'); // Assuming 'current_user' stores the cart identifier or user-specific session data

    // Retrieve the cart associated with the current user session
    $cart = session()->get($cartId, []); // Use session()->get() to retrieve the cart, default to empty array if not found

    // Debugging: Check if cart exists in session
    \Log::info('Current cart:', $cart);

    $productId = $validated['product_id'];
    $newUnit = $validated['unit'];

    // Loop through the cart to find the product with the matching product_id
    foreach ($cart as $key => $product) {
        if ($product['id'] == $productId) {
            // Find the product details from the database (assuming you have a Product model)
            $productDetails = $this->product->find($productId);

            // Check if the product exists
            if (!$productDetails) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            // Update the unit for the specific product in the cart
            $cart[$key]['unit'] = $newUnit;

            // Update the quantity based on the new unit
            $quantity = ($newUnit == 0) ? $cart[$key]['quantity'] * $productDetails->unit_value : $cart[$key]['quantity'];
            $cart[$key]['quantity'] = $quantity;

            // Calculate the price based on the unit value
            if($type==4 || $type==7 ||  $type==1){
            $price = ($newUnit == 0) ? $productDetails->selling_price / $productDetails->unit_value : $productDetails->selling_price;
            }else{
                            $price = ($newUnit == 0) ? $productDetails->purchase_price / $productDetails->unit_value : $productDetails->purchase_price;

            }
            $cart[$key]['price'] = $price; // Adjust price for updated quantity
                        if($type==4 || $type==7 ||  $type==1){

            if(($newUnit == 0)){
            $cart[$key]['discount'] = Helpers::discount_calculate($productDetails, $price)/$productDetails->unit_value ;
}else{
             $cart[$key]['discount'] = Helpers::discount_calculate($productDetails, $price);
   
}
             $cart[$key]['tax'] = Helpers::tax_calculate($productDetails, $price);
}else{
                 $cart[$key]['discount'] =0;
                              $cart[$key]['tax'] = Helpers::tax_calculate($productDetails, $price);


}
            // Save the updated cart back to the session
            session()->put($cartId, $cart); // Store the updated cart back in session using the same cart_id

            // Debugging: Log updated cart after change
            \Log::info('Updated cart:', $cart);

            // Return success response with updated details
            return response()->json([
                'message' => 'Unit updated successfully',
                'updated_cart' => $cart[$key], // Return the updated product details in the cart
            ]);
        }
    }

    // Log a warning if the product was not found in the cart
    \Log::warning('Product not found in cart', ['product_id' => $productId]);
    return response()->json(['message' => 'Product not found in cart'], 404);
}

public function processReturn(Request $request)
{
    $invoice_number = $request->invoice_number;
    
    // البحث عن الفاتورة باستخدام رقم الفاتورة ونوع الفاتورة (مثلاً 7 أو 24) – في الكود يتم التحقق من الفواتير ذات النوع 4 أو 12 كما هو موجود.
    $order = Order::where('id', $invoice_number)
        ->where(function($query) {
            $query->where('type', 4)
                  ->orWhere('type', 12);
        })->first();

    if (!$order) {
        return redirect()->back()->withErrors('رقم الفاتورة غير موجود.');
    }
    
    // جلب بيانات المنتجات من جدول order_details
    $orderProducts = OrderDetail::where('order_id', $order->id)->get();

    // التحقق من أن جميع المنتجات في الفاتورة تم إرجاعها بالفعل
    $allReturned = true;
    foreach ($orderProducts as $orderProduct) {
        $returnedQty = $orderProduct->quantity_returned ?? 0;
        if ($orderProduct->quantity != $returnedQty) {
            $allReturned = false;
            break;
        }
    }
    if ($allReturned) {
        return redirect()->back()->withErrors('كل المنتجات في هذه الفاتورة تم إرجاعها.');
    }

    // تخزين بيانات الطلب والعميل في السيشن
    session([
        'extra_discount' => $order->extra_discount,
        'total_tax'      => $order->total_tax,
        'order_amount'   => $order->order_amount,
        'order_type'=>$order->order_type,
        'name'           => $order->customer->name ?? '',
        'mobile'         => $order->customer->mobile ?? '',
        'credit'         => $order->customer->credit ?? '',
        'c_history'      => $order->customer->c_history ?? '',
        'tax_number'     => $order->customer->tax_number ?? '',
        'seller'         => $order->seller->f_name ?? '',
        'created_at'     => $order->created_at,
        'orderDetails'   => [
            'order_id'       => $order->id,
            'order_products' => $orderProducts,
        ],
    ]);

    return redirect()->back();
}
public function processReturncashier(Request $request)
{
    $invoice_number = $request->invoice_number;
    
    $order = Order::where('id', $invoice_number)
        ->where(function($query) {
            $query->where('type', 4)
                  ->orWhere('type', 12);
        })->first();

    if (!$order) {
        return redirect()->back()->withErrors('رقم الفاتورة غير موجود.');
    }

    $orderProducts = OrderDetail::where('order_id', $order->id)->get();

    $allReturned = true;
    foreach ($orderProducts as $orderProduct) {
        $returnedQty = $orderProduct->quantity_returned ?? 0;
        if ($orderProduct->quantity != $returnedQty) {
            $allReturned = false;
            break;
        }
    }

    if ($allReturned) {
        return redirect()->back()->withErrors('كل المنتجات في هذه الفاتورة تم إرجاعها.');
    }

   session([
        'extra_discount' => $order->extra_discount,
        'total_tax'      => $order->total_tax,
        'order_amount'   => $order->order_amount,
        'order_type'=>$order->order_type??'service',
        'name'           => $order->customer->name ?? '',
        'mobile'         => $order->customer->mobile ?? '',
        'credit'         => $order->customer->credit ?? '',
        'c_history'      => $order->customer->c_history ?? '',
        'tax_number'     => $order->customer->tax_number ?? '',
        'seller'         => $order->seller->f_name ?? '',
        'created_at'     => $order->created_at,
        'orderDetails'   => [
            'order_id'       => $order->id,
            'order_products' => $orderProducts,
        ],
    ]);
    return view('admin-views.pos.returncashier');
}
    public function forgetReturnSession(Request $request): RedirectResponse
    {
        $keys = [
            'extra_discount',
            'total_tax',
            'order_amount',
            'order_type',
            'name',
            'mobile',
            'credit',
            'c_history',
            'tax_number',
            'seller',
            'created_at',
            'orderDetails', // يحتوي order_id + order_products
        ];

        $request->session()->forget($keys);

        // لو عايز كمان تنظّف الكاش المؤقت للفورم/الريكويست:
        // $request->session()->regenerateToken(); // اختياري

        return redirect()->back()->with('success', 'تم مسح بيانات الإرجاع من الجلسة.');
    }

    // (اختياري) توحيد تخزين السيشن في ميثود خاصة لإعادة الاستخدام
    private function putReturnSession(Order $order, $orderProducts): void
    {
        session([
            'extra_discount' => $order->extra_discount,
            'total_tax'      => $order->total_tax,
            'order_amount'   => $order->order_amount,
            'order_type'     => $order->order_type ?? 'service',
            'name'           => $order->customer->name ?? '',
            'mobile'         => $order->customer->mobile ?? '',
            'credit'         => $order->customer->credit ?? '',
            'c_history'      => $order->customer->c_history ?? '',
            'tax_number'     => $order->customer->tax_number ?? '',
            'seller'         => $order->seller->f_name ?? '',
            'created_at'     => $order->created_at,
            'orderDetails'   => [
                'order_id'       => $order->id,
                'order_products' => $orderProducts,
            ],
        ]);
    }
public function processConfirmedReturn(Request $request)
{
    // ===== 0) Validation =====
    $request->validate([
        'note'                     => 'nullable|string|max:2000',
        'attachment'               => 'nullable|image|max:5120',
        'return_type'              => 'nullable|in:cash,credit',
        'cash_account_id'          => 'nullable|integer|exists:accounts,id',
        'order_id'                 => 'required|integer',
        'return_quantities_hidden' => 'array',
        'return_unit_hidden'       => 'array',
    ]);

    $oldOrder = \App\Models\Order::find((int)$request->order_id);
    if (!$oldOrder) {
        \Toastr::error(translate('الفاتورة الأصلية غير موجودة'));
        return back();
    }

    // تمرير تلقائي لو كانت فاتورة خدمة
    if (strtolower((string)$oldOrder->order_type) === 'service') {
        return $this->processConfirmedReturn_service($request);
    }

    // كاش بدون حساب = خطأ
    if ($request->input('return_type') === 'cash' && empty($request->input('cash_account_id'))) {
        return back()->withErrors(['cash_account_id' => 'برجاء اختيار حساب الكاش عند نوع مرتجع كاش.'])->withInput();
    }

    \DB::beginTransaction();
    try {
        // ===== 1) قراءة بيانات الفاتورة من السيشن =====
        $orderProducts   = session('orderDetails.order_products', []);
        $extraDiscount   = session('extra_discount') ?? 0;
        $totalTaxSession = session('total_tax') ?? 0;

        $totalProductDiscount = 0;
        foreach ($orderProducts as $p) {
            $totalProductDiscount += $p->discount_on_product * $p->quantity;
        }

        $baseOrderAmount = session('order_amount') ?? 0;
        $orderAmount     = $baseOrderAmount + $extraDiscount + $totalProductDiscount - $totalTaxSession;
        $orderAmount     = max(1, $orderAmount);
        $discountRatio   = ($extraDiscount / $orderAmount) * 100;

        $returnQuantities = $request->input('return_quantities_hidden', []); // [product_id => qty]
        $returnUnits      = $request->input('return_unit_hidden', []);       // [product_id => 1|0]
        $returnTypeInput  = $request->input('return_type', 'credit');        // default credit

        // ===== 2) التحقق من الكميات =====
        foreach ($orderProducts as $product) {
            $pid       = $product->product_id;
            $newReturn = (float)($returnQuantities[$pid] ?? 0);
            if ($newReturn <= 0) continue;

            $details    = json_decode($product->product_details);
            $unitValue  = $details->unit_value ?? 1;
            $chosenUnit = (int)($returnUnits[$pid] ?? 1); // 1 كبير، 0 صغير

            $oldDetail = $oldOrder->details->where('product_id', $pid)->first();
            $alreadyReturned = 0;
            if ($oldDetail) {
                $alreadyReturned = ($oldDetail->unit === 0)
                    ? (($oldDetail->quantity_returned ?? 0) * $unitValue)
                    : ($oldDetail->quantity_returned ?? 0);
            }

            $availableQty = ($chosenUnit === 0) ? ($product->quantity * $unitValue) : $product->quantity;
            if (($alreadyReturned + $newReturn) > $availableQty) {
                \DB::rollBack();
                $availableToReturn = $availableQty - $alreadyReturned;
                \Toastr::error(translate("لقد قمت بإرجاع {$alreadyReturned} من المنتج {$product->product->name}. المتاح للإرجاع هو {$availableToReturn}"));
                return back();
            }
        }

        // ===== 3) حساب قيم المرتجع =====
        $productsReturnData = [];
        $totalReturnPrice = $totalReturnDiscount = $totalReturnExtraDiscount = $totalReturnTax = $totalReturnOverall = 0;
        $totalPriceAllProducts = 0; // تكلفة شراء راجعة للمخزون

        foreach ($orderProducts as $product) {
            $pid            = $product->product_id;
            $details        = json_decode($product->product_details);
            $unitValue      = $details->unit_value ?? 1;
            $chosenUnit     = (int)($returnUnits[$pid] ?? 1);

            if ($chosenUnit === 0 && $product->unit == 1) {
                $adjPrice   = $product->price / $unitValue;
                $adjDisc    = $product->discount_on_product / $unitValue;
                $adjExDisc  = (($discountRatio/100) * $product->price) / $unitValue;
                $adjTax     = $product->tax_amount / $unitValue;
            } else {
                $adjPrice   = $product->price;
                $adjDisc    = $product->discount_on_product;
                $adjExDisc  = ($discountRatio/100) * $product->price;
                $adjTax     = $product->tax_amount;
            }

            $retQty   = (float)($returnQuantities[$pid] ?? 0);
            $effUnit  = $adjPrice - $adjDisc - $adjExDisc + $adjTax;

            $productsReturnData[] = [
                'product_id'      => $pid,
                'name'            => $product->product->name,
                'price'           => $adjPrice,
                'discount'        => $adjDisc,
                'extra_discount'  => $adjExDisc,
                'tax'             => $adjTax,
                'unit'            => $chosenUnit,
                'return_quantity' => $retQty,
            ];

            $totalReturnPrice         += $retQty * $adjPrice;
            $totalReturnDiscount      += $retQty * $adjDisc;
            $totalReturnExtraDiscount += $retQty * $adjExDisc;
            $totalReturnTax           += $retQty * $adjTax;
            $totalReturnOverall       += $retQty * $effUnit;

            if ($retQty > 0) {
                $finalQty = ($chosenUnit == 0) ? ($retQty / $unitValue) : $retQty;
                $totalPriceAllProducts += $finalQty * $product->product->purchase_price;

                // سجل مخزون
                \App\Models\ProductLog::create([
                    'product_id' => $pid,
                    'quantity'   => $finalQty,
                    'type'       => 7, // مرتجع
                    'seller_id'  => auth('admin')->id(),
                    'branch_id'  => auth('admin')->user()->branch_id,
                ]);
                \App\Models\StockBatch::create([
                    'product_id' => $pid,
                    'quantity'   => $finalQty,
                    'branch_id'  => auth('admin')->user()->branch_id,
                    'price'      => $product->product->purchase_price,
                ]);
            }
        }

        // ===== 4) إنشاء أمر المرتجع =====
        $newOrder = new \App\Models\Order;
        $newOrder->owner_id   = auth('admin')->id();
        $newOrder->user_id    = $oldOrder->user_id;
        $newOrder->parent_id  = $oldOrder->id;
        $newOrder->branch_id  = auth('admin')->user()->branch_id;
        $newOrder->cash       = 2;
        $newOrder->type       = ($oldOrder->type == 4) ? 7 : (($oldOrder->type == 12) ? 24 : 7);
        $newOrder->total_tax      = round($totalReturnTax, 2);
        $newOrder->order_amount   = round($totalReturnOverall, 2);
        $newOrder->extra_discount = round($totalReturnExtraDiscount, 2);
        $newOrder->date           = $request->date;
        if ($request->filled('note')) $newOrder->note = $request->note;
        if ($request->hasFile('attachment')) {
            $newOrder->attachment_path = $request->file('attachment')->store('returns', 'public');
        }
        $newOrder->save();

        // QR
        $qrcodeData  = "https://posfull.iqbrandx.com/real/invoicea2/" . $newOrder->id;
        $qrCode      = new \Endroid\QrCode\QrCode($qrcodeData);
        $writer      = new \Endroid\QrCode\Writer\PngWriter();
        $qrcodeImage = $writer->write($qrCode)->getString();
        $qrcodePath  = "qrcodes/order_" . $newOrder->id . ".png";
        \Storage::disk('public')->put($qrcodePath, $qrcodeImage);
        $newOrder->qrcode = $qrcodePath;
        $newOrder->save();

        // ===== 5) تعريف الحسابات =====
        $customer         = \App\Models\Customer::find($oldOrder->user_id);
        $accCustomer      = \App\Models\Account::find($customer->account_id);
        $accSalesReturn   = \App\Models\Account::find(40); // مردودات
        $accTax           = \App\Models\Account::find(28); // ضريبة
        $branch           = \App\Models\Branch::find(auth('admin')->user()->branch_id);
        $accCOGS          = \App\Models\Account::find(47);
        $accStock         = \App\Models\Account::find($branch->account_stock_Id);

        $cashAccount = null;
        if ($returnTypeInput === 'cash') {
            $cashAccount = \App\Models\Account::find($request->input('cash_account_id'));
            if (!$cashAccount) {
                \DB::rollBack();
                return back()->withErrors(['cash_account_id' => 'حساب الكاش غير موجود.'])->withInput();
            }
        }

        $journalRef = 'RET-' . now()->format('YmdHis') . '-' . $newOrder->id;

        // ===== 6) اليومية =====
        $journalId = \DB::table('journal_entries')->insertGetId([
            'reference'   => $journalRef,
            'description' => 'قيد يومية مرتجع فاتورة #' . $newOrder->id,
            'branch_id'   => auth('admin')->user()->branch_id,
            'created_by'  => auth('admin')->id(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $lines = [];
        $add = function($accountId, $debit, $credit, $memo) use (&$lines, $journalId){
            if (round($debit,2)==0 && round($credit,2)==0) return;
            $lines[] = [
                'journal_entry_id' => $journalId,
                'account_id'       => $accountId,
                'debit'            => round($debit, 2),
                'credit'           => round($credit, 2),
                'description'      => $memo,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        };

        $netWithoutTax = $totalReturnOverall - $totalReturnTax;

        if ($returnTypeInput === 'cash') {
            // Dr Returns + Dr Tax / Cr Cash
            if ($netWithoutTax   > 0) $add($accSalesReturn->id, $netWithoutTax,    0, 'مردودات مبيعات (صافي) - نقدي');
            if ($totalReturnTax  > 0) $add($accTax->id,         $totalReturnTax,   0, 'ضريبة على المرتجع - نقدي');
            if ($totalReturnOverall>0)$add($cashAccount->id,    0, $totalReturnOverall, 'صرف نقدي للعميل مقابل المرتجع');
        } else {
            // Dr Returns + Dr Tax / Cr Customer
            if ($netWithoutTax   > 0) $add($accSalesReturn->id, $netWithoutTax,    0, 'مردودات مبيعات (صافي) - آجل');
            if ($totalReturnTax  > 0) $add($accTax->id,         $totalReturnTax,   0, 'ضريبة على المرتجع - آجل');
            if ($totalReturnOverall>0)$add($accCustomer->id,    0, $totalReturnOverall, 'ذمم عملاء مقابل المرتجع (آجل)');
        }

        if ($totalPriceAllProducts > 0) {
            $add($accStock->id, $totalPriceAllProducts, 0, 'زيادة مخزون بالمرتجع (تكلفة)');
            $add($accCOGS->id,  0, $totalPriceAllProducts, 'عكس تكلفة المبيعات للمرتجع');
        }

        if ($lines) {
            \DB::table('journal_entries_details')->insert($lines);
        }

        // ===== 7) Transactions (اتجاه مطابق لليومية) =====
        $makeTxn = function($mainAcc, $pairAcc, $amount, $isDebitOnMain, $desc, $type, $customerId, $orderId, $costId = null) {
            if (!$mainAcc || $amount <= 0) return null;
            $t = new \App\Models\Transection;
            $t->tran_type      = $type;
            $t->seller_id      = auth('admin')->id();
            $t->branch_id      = auth('admin')->user()->branch_id;
            $t->cost_id        = $costId;
            $t->account_id     = $mainAcc->id;
            $t->account_id_to  = $pairAcc ? $pairAcc->id : null;
            $t->amount         = $amount;
            $t->description    = $desc;
            $t->debit          = $isDebitOnMain ? $amount : 0;
            $t->credit         = $isDebitOnMain ? 0 : $amount;
            $t->balance        = $isDebitOnMain ? ($mainAcc->balance + $amount) : ($mainAcc->balance - $amount);
            $t->debit_account  = $isDebitOnMain ? $amount : 0;
            $t->credit_account = $isDebitOnMain ? 0 : $amount;
            $t->balance_account= $pairAcc ? ($isDebitOnMain ? ($pairAcc->balance - $amount) : ($pairAcc->balance + $amount)) : 0;
            $t->date           = date("Y/m/d");
            $t->customer_id    = $customerId;
            $t->order_id       = $orderId;
            $t->save();
            return $t;
        };

        if ($returnTypeInput === 'cash') {
            // الكاش يكون دائن في اليومية ⇒ في الترانزاكشن نخليه Credit على الكاش
            if ($netWithoutTax > 0)  $makeTxn($cashAccount, $accSalesReturn, $netWithoutTax, false, "صرف نقدي مقابل مردودات (صافي) [JE:$journalRef]", $newOrder->type, $oldOrder->user_id, $newOrder->id);
            if ($totalReturnTax > 0) $makeTxn($cashAccount, $accTax,        $totalReturnTax, false, "صرف نقدي مقابل ضريبة المرتجع [JE:$journalRef]",   $newOrder->type, $oldOrder->user_id, $newOrder->id);

            // تحديث أرصدة مختصرة
            if ($cashAccount && $totalReturnOverall > 0) { $cashAccount->balance -= $totalReturnOverall; $cashAccount->total_out += $totalReturnOverall; $cashAccount->save(); }
        } else {
            // العميل دائن في اليومية ⇒ في الترانزاكشن نجعل العميل Credit
            if ($netWithoutTax > 0)  $makeTxn($accCustomer, $accSalesReturn, $netWithoutTax, false, "تخفيض ذمم عميل (مردودات صافي) [JE:$journalRef]", $newOrder->type, $oldOrder->user_id, $newOrder->id);
            if ($totalReturnTax > 0) $makeTxn($accCustomer, $accTax,         $totalReturnTax, false, "تخفيض ذمم عميل (ضريبة مرتجع) [JE:$journalRef]",   $newOrder->type, $oldOrder->user_id, $newOrder->id);

            if ($accCustomer && $totalReturnOverall > 0) { $accCustomer->balance -= $totalReturnOverall; $accCustomer->total_out += $totalReturnOverall; $accCustomer->save(); }
            // حسب منطقك السابق:
            $customer->balance += $totalReturnOverall; $customer->save();
        }

        if ($totalPriceAllProducts > 0) {
            // عكس التكلفة/زيادة مخزون: المخزون مدين، التكلفة دائن في اليومية
            $makeTxn($accStock, $accCOGS, $totalPriceAllProducts, true,  "زيادة مخزون (مرتجع) [JE:$journalRef]", $newOrder->type, $oldOrder->user_id, $newOrder->id);
            $makeTxn($accCOGS,  $accStock, $totalPriceAllProducts, false, "عكس تكلفة المبيعات [JE:$journalRef]", $newOrder->type, $oldOrder->user_id, $newOrder->id);

            $accStock->balance += $totalPriceAllProducts; $accStock->total_in  += $totalPriceAllProducts; $accStock->save();
            $accCOGS->balance  -= $totalPriceAllProducts; $accCOGS->total_out += $totalPriceAllProducts; $accCOGS->save();
        }

        // ===== 8) تخزين تفاصيل المرتجع =====
        foreach ($productsReturnData as $p) {
            \App\Models\OrderDetail::create([
                'order_id'            => $newOrder->id,
                'product_id'          => $p['product_id'],
                'product_details'     => json_encode($p),
                'quantity'            => $p['return_quantity'],
                'unit'                => $p['unit'],
                'price'               => $p['price'],
                'tax_amount'          => $p['tax'],
                'discount_on_product' => $p['discount'],
                'discount_type'       => 'discount_on_product',
            ]);
        }

        // تحديث المرتجع في تفاصيل الفاتورة القديمة بوحدة كبيرة
        foreach ($productsReturnData as $p) {
            $oldDetail = $oldOrder->details->where('product_id', $p['product_id'])->first();
            if ($oldDetail) {
                $det = json_decode($oldDetail->product_details);
                $uv  = $det->unit_value ?? 1;
                $qtyBase = ($p['unit']==0) ? ($p['return_quantity'] / $uv) : $p['return_quantity'];
                $oldDetail->quantity_returned = ($oldDetail->quantity_returned ?? 0) + $qtyBase;
                $oldDetail->save();
            }
        }

        \DB::commit();

        \Toastr::success(translate('تم تنفيذ الطلب بنجاح') . ' - رقم الطلب: ' . $newOrder->id);
        session()->forget([
            'extra_discount','total_tax','order_amount','name','mobile','credit',
            'c_history','tax_number','seller','created_at','orderDetails'
        ]);
        return back()->with('success', 'تم تنفيذ المرتجع بنجاح!');

    } catch (\Exception $e) {
        \DB::rollBack();
        \Toastr::error($e->getMessage());
        return back()->with('error', $e->getMessage());
    }
}
public function processConfirmedReturn_service(Request $request)
{
    // ===== 0) Validation =====
    $request->validate([
        'note'                     => 'nullable|string|max:2000',
        'attachment'               => 'nullable|image|max:5120',
        'return_type'              => 'nullable|in:cash,credit',
        'cash_account_id'          => 'nullable|integer|exists:accounts,id',
        'order_id'                 => 'required|integer',
        'return_quantities_hidden' => 'array',
        'return_unit_hidden'       => 'array',
    ]);

    $oldOrder = \App\Models\Order::with('details')->find((int)$request->order_id);
    if (!$oldOrder) {
        \Toastr::error(translate('الفاتورة الأصلية غير موجودة'));
        return back();
    }

    // تأكيد أنها فاتورة خدمة (لو أردت فرض ذلك)
    // إن لم يكن نوعها خدمة يمكن تمريرها لدالة البضاعة:
    if (strtolower((string)$oldOrder->order_type) !== 'service') {
        // لو حابب تمنع:
        // return back()->withErrors(['order_id' => 'هذه ليست فاتورة خدمة.']);
        // أو تعيد توجيهها:
        return $this->processConfirmedReturn($request);
    }

    // كاش بدون حساب = خطأ
    if ($request->input('return_type') === 'cash' && empty($request->input('cash_account_id'))) {
        return back()->withErrors(['cash_account_id' => 'برجاء اختيار حساب الكاش عند نوع مرتجع كاش.'])->withInput();
    }

    \DB::beginTransaction();
    try {
        // ===== 1) قراءة بيانات الفاتورة من السيشن =====
        $orderProducts   = session('orderDetails.order_products', []); // عناصر الخدمة
        $extraDiscount   = session('extra_discount') ?? 0;             // خصم إضافي على الفاتورة
        $totalTaxSession = session('total_tax') ?? 0;                  // إجمالي الضريبة المُسجّلة بالجلسة

        // إجمالي خصم البنود (سطر سطر)
        $totalProductDiscount = 0;
        foreach ($orderProducts as $p) {
            $totalProductDiscount += ((float)$p->discount_on_product) * ((float)$p->quantity);
        }

        $baseOrderAmount = (float)(session('order_amount') ?? 0);
        // ترتيب المبالغ كما بالكود السابق
        $orderAmount   = $baseOrderAmount + $extraDiscount + $totalProductDiscount - $totalTaxSession;
        $orderAmount   = max(1, $orderAmount);
        $discountRatio = ($extraDiscount / $orderAmount) * 100;

        $returnQuantities = $request->input('return_quantities_hidden', []); // [product_id => qty]
        $returnUnits      = $request->input('return_unit_hidden', []);       // [product_id => 1|0] (لن تؤثر على مخزون، فقط تتبع)

        // ===== 2) التحقق من الكميات المسموح إرجاعها (تتبعي فقط) =====
        foreach ($orderProducts as $product) {
            $pid       = (int)$product->product_id;
            $newReturn = (float)($returnQuantities[$pid] ?? 0);
            if ($newReturn <= 0) continue;

            // قراءة وحدة السطر (إن وُجدت) لتوحيد المقارنة
            $details    = json_decode($product->product_details);
            $unitValue  = (float)($details->unit_value ?? 1);
            $chosenUnit = (int)($returnUnits[$pid] ?? 1); // 1 كبير، 0 صغير

            $oldDetail = $oldOrder->details->where('product_id', $pid)->first();
            $alreadyReturned = 0.0;
            if ($oldDetail) {
                // نخزّن المرتجع التاريخي بوحدة الأساس (الكبيرة)
                $alreadyReturned = ($oldDetail->unit === 0)
                    ? ((float)($oldDetail->quantity_returned ?? 0) * $unitValue)
                    : (float)($oldDetail->quantity_returned ?? 0);
            }

            $availableQty = ($chosenUnit === 0)
                ? ((float)$product->quantity * $unitValue)
                : (float)$product->quantity;

            if (($alreadyReturned + $newReturn) > $availableQty) {
                \DB::rollBack();
                $availableToReturn = max(0, $availableQty - $alreadyReturned);
                \Toastr::error(translate("لقد قمت بإرجاع {$alreadyReturned} من الخدمة {$product->product->name}. المتاح للإرجاع هو {$availableToReturn}"));
                return back();
            }
        }

        // ===== 3) حساب قيم المرتجع =====
        $productsReturnData = [];
        $totalReturnPrice = $totalReturnDiscount = $totalReturnExtraDiscount = $totalReturnTax = $totalReturnOverall = 0.0;

        foreach ($orderProducts as $product) {
            $pid        = (int)$product->product_id;
            $retQty     = (float)($returnQuantities[$pid] ?? 0);
            if ($retQty <= 0) continue;

            $details   = json_decode($product->product_details);
            $unitValue = (float)($details->unit_value ?? 1);
            $chosenUnit = (int)($returnUnits[$pid] ?? 1);

            // لا يوجد تعامل مخزني، لكن نحافظ على منطق تسعير الوحدة الصغيرة/الكبيرة لو كان ساريًا على الخدمات
            if ($chosenUnit === 0 && (int)$product->unit === 1) {
                $adjPrice  = (float)$product->price / $unitValue;
                $adjDisc   = (float)$product->discount_on_product / $unitValue;
                $adjExDisc = ((float)$product->price * ($discountRatio/100)) / $unitValue;
                $adjTax    = (float)$product->tax_amount / $unitValue;
            } else {
                $adjPrice  = (float)$product->price;
                $adjDisc   = (float)$product->discount_on_product;
                $adjExDisc = ((float)$product->price * ($discountRatio/100));
                $adjTax    = (float)$product->tax_amount;
            }

            $effUnit = $adjPrice - $adjDisc - $adjExDisc + $adjTax;

            $productsReturnData[] = [
                'product_id'      => $pid,
                'name'            => $product->product->name,
                'price'           => $adjPrice,
                'discount'        => $adjDisc,
                'extra_discount'  => $adjExDisc,
                'tax'             => $adjTax,
                'unit'            => $chosenUnit,
                'return_quantity' => $retQty,
            ];

            $totalReturnPrice         += $retQty * $adjPrice;
            $totalReturnDiscount      += $retQty * $adjDisc;
            $totalReturnExtraDiscount += $retQty * $adjExDisc;
            $totalReturnTax           += $retQty * $adjTax;
            $totalReturnOverall       += $retQty * $effUnit;
        }

        // لو مفيش أي كمية مرتجعة
        if ($totalReturnOverall <= 0) {
            \DB::rollBack();
            return back()->withErrors(['return_quantities_hidden' => 'لا توجد كميات مرتجعة صالحة.']);
        }

        // ===== 4) إنشاء أمر المرتجع (خدمة) =====
        $newOrder = new \App\Models\Order;
        $newOrder->owner_id       = auth('admin')->id();
        $newOrder->user_id        = $oldOrder->user_id;
        $newOrder->parent_id      = $oldOrder->id;
        $newOrder->branch_id      = auth('admin')->user()->branch_id;
        $newOrder->cash           = 2;
        // تحويل النوع: 12 (خدمة) -> 24 (مرتجع خدمة)
        $newOrder->type           = ($oldOrder->type == 12) ? 24 : 24;
        $newOrder->order_type     = 'service';
        $newOrder->total_tax      = round($totalReturnTax, 2);
        $newOrder->order_amount   = round($totalReturnOverall, 2);
        $newOrder->extra_discount = round($totalReturnExtraDiscount, 2);
        $newOrder->date           = $request->date;
        if ($request->filled('note')) $newOrder->note = $request->note;
        if ($request->hasFile('attachment')) {
            $newOrder->attachment_path = $request->file('attachment')->store('returns', 'public');
        }
        $newOrder->save();

        // QR
        $qrcodeData  = "https://posfull.iqbrandx.com/real/invoicea2/" . $newOrder->id;
        $qrCode      = new \Endroid\QrCode\QrCode($qrcodeData);
        $writer      = new \Endroid\QrCode\Writer\PngWriter();
        $qrcodeImage = $writer->write($qrCode)->getString();
        $qrcodePath  = "qrcodes/order_" . $newOrder->id . ".png";
        \Storage::disk('public')->put($qrcodePath, $qrcodeImage);
        $newOrder->qrcode = $qrcodePath;
        $newOrder->save();

        // ===== 5) تعريف الحسابات =====
        $customer       = \App\Models\Customer::find($oldOrder->user_id);
        $accCustomer    = $customer ? \App\Models\Account::find($customer->account_id) : null;
        $accSalesReturn = \App\Models\Account::find(40); // مردودات
        $accTax         = \App\Models\Account::find(28); // ضريبة

        $cashAccount = null;
        $returnTypeInput = $request->input('return_type', 'credit');
        if ($returnTypeInput === 'cash') {
            $cashAccount = \App\Models\Account::find((int)$request->input('cash_account_id'));
            if (!$cashAccount) {
                \DB::rollBack();
                return back()->withErrors(['cash_account_id' => 'حساب الكاش غير موجود.'])->withInput();
            }
        }

        $journalRef = 'RET-SVC-' . now()->format('YmdHis') . '-' . $newOrder->id;

        // ===== 6) اليومية =====
        $journalId = \DB::table('journal_entries')->insertGetId([
            'reference'   => $journalRef,
            'description' => 'قيد يومية مرتجع خدمة #' . $newOrder->id,
            'branch_id'   => auth('admin')->user()->branch_id,
            'created_by'  => auth('admin')->id(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $lines = [];
        $add = function($accountId, $debit, $credit, $memo) use (&$lines, $journalId){
            if (!$accountId) return;
            if (round($debit,2)==0 && round($credit,2)==0) return;
            $lines[] = [
                'journal_entry_id' => $journalId,
                'account_id'       => $accountId,
                'debit'            => round($debit, 2),
                'credit'           => round($credit, 2),
                'description'      => $memo,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        };

        $netWithoutTax = $totalReturnOverall - $totalReturnTax;

        if ($returnTypeInput === 'cash') {
            // Dr Returns + Dr Tax / Cr Cash
            if ($netWithoutTax    > 0) $add($accSalesReturn?->id, $netWithoutTax,   0, 'مردودات خدمات (صافي) - نقدي');
            if ($totalReturnTax   > 0) $add($accTax?->id,        $totalReturnTax,  0, 'ضريبة على المرتجع - نقدي');
            if ($totalReturnOverall>0) $add($cashAccount?->id,   0, $totalReturnOverall, 'صرف نقدي للعميل مقابل مرتجع خدمة');
        } else {
            // Dr Returns + Dr Tax / Cr Customer
            if ($netWithoutTax    > 0) $add($accSalesReturn?->id, $netWithoutTax,   0, 'مردودات خدمات (صافي) - آجل');
            if ($totalReturnTax   > 0) $add($accTax?->id,         $totalReturnTax,  0, 'ضريبة على المرتجع - آجل');
            if ($totalReturnOverall>0) $add($accCustomer?->id,    0, $totalReturnOverall, 'ذمم عميل مقابل مرتجع خدمة (آجل)');
        }

        if ($lines) {
            \DB::table('journal_entries_details')->insert($lines);
        }

        // ===== 7) Transactions =====
        $makeTxn = function($mainAcc, $pairAcc, $amount, $isDebitOnMain, $desc, $type, $customerId, $orderId) {
            if (!$mainAcc || $amount <= 0) return null;
            $t = new \App\Models\Transection;
            $t->tran_type      = $type;
            $t->seller_id      = auth('admin')->id();
            $t->branch_id      = auth('admin')->user()->branch_id;
            $t->cost_id        = null;
            $t->account_id     = $mainAcc->id;
            $t->account_id_to  = $pairAcc ? $pairAcc->id : null;
            $t->amount         = $amount;
            $t->description    = $desc;
            $t->debit          = $isDebitOnMain ? $amount : 0;
            $t->credit         = $isDebitOnMain ? 0 : $amount;
            $t->balance        = $isDebitOnMain ? ($mainAcc->balance + $amount) : ($mainAcc->balance - $amount);
            $t->debit_account  = $isDebitOnMain ? $amount : 0;
            $t->credit_account = $isDebitOnMain ? 0 : $amount;
            $t->balance_account= $pairAcc ? ($isDebitOnMain ? ($pairAcc->balance - $amount) : ($pairAcc->balance + $amount)) : 0;
            $t->date           = date("Y/m/d");
            $t->customer_id    = $customerId;
            $t->order_id       = $orderId;
            $t->save();
            return $t;
        };

        if ($returnTypeInput === 'cash') {
            if ($netWithoutTax > 0)   $makeTxn($cashAccount, $accSalesReturn, $netWithoutTax, false, "صرف نقدي مقابل مردودات خدمة (صافي) [JE:$journalRef]", $newOrder->type, $oldOrder->user_id, $newOrder->id);
            if ($totalReturnTax > 0)  $makeTxn($cashAccount, $accTax,         $totalReturnTax, false, "صرف نقدي مقابل ضريبة مرتجع خدمة [JE:$journalRef]",   $newOrder->type, $oldOrder->user_id, $newOrder->id);

            if ($cashAccount && $totalReturnOverall > 0) {
                $cashAccount->balance -= $totalReturnOverall;
                $cashAccount->total_out += $totalReturnOverall;
                $cashAccount->save();
            }
        } else {
            if ($netWithoutTax > 0)   $makeTxn($accCustomer, $accSalesReturn, $netWithoutTax, false, "تخفيض ذمم عميل (مردودات خدمة صافي) [JE:$journalRef]", $newOrder->type, $oldOrder->user_id, $newOrder->id);
            if ($totalReturnTax > 0)  $makeTxn($accCustomer, $accTax,         $totalReturnTax, false, "تخفيض ذمم عميل (ضريبة مرتجع خدمة) [JE:$journalRef]",   $newOrder->type, $oldOrder->user_id, $newOrder->id);

            if ($accCustomer && $totalReturnOverall > 0) {
                $accCustomer->balance -= $totalReturnOverall;
                $accCustomer->total_out += $totalReturnOverall;
                $accCustomer->save();
            }
            if ($customer) {
                // حسب منطقك السابق لميزان العميل
                $customer->balance += $totalReturnOverall;
                $customer->save();
            }
        }

        // ===== 8) تخزين تفاصيل المرتجع (سطور الخدمة) =====
        foreach ($productsReturnData as $p) {
            \App\Models\OrderDetail::create([
                'order_id'            => $newOrder->id,
                'product_id'          => $p['product_id'],
                'product_details'     => json_encode($p),
                'quantity'            => $p['return_quantity'],
                'unit'                => $p['unit'],
                'price'               => $p['price'],
                'tax_amount'          => $p['tax'],
                'discount_on_product' => $p['discount'],
                'discount_type'       => 'discount_on_product',
            ]);
        }

        // تحديث المرتجع في تفاصيل الفاتورة القديمة (تتبّع فقط)
        foreach ($productsReturnData as $p) {
            $oldDetail = $oldOrder->details->where('product_id', $p['product_id'])->first();
            if ($oldDetail) {
                $det     = json_decode($oldDetail->product_details);
                $uv      = (float)($det->unit_value ?? 1);
                $qtyBase = ((int)$p['unit'] === 0) ? ((float)$p['return_quantity'] / $uv) : (float)$p['return_quantity'];
                $oldDetail->quantity_returned = ((float)($oldDetail->quantity_returned ?? 0)) + $qtyBase;
                $oldDetail->save();
            }
        }

        \DB::commit();

        \Toastr::success(translate('تم تنفيذ مرتجع الخدمة بنجاح') . ' - رقم الطلب: ' . $newOrder->id);
        session()->forget([
            'extra_discount','total_tax','order_amount','name','mobile','credit',
            'c_history','tax_number','seller','created_at','orderDetails'
        ]);
        return back()->with('success', 'تم تنفيذ المرتجع (خدمة) بنجاح!');

    } catch (\Exception $e) {
        \DB::rollBack();
        \Toastr::error($e->getMessage());
        return back()->with('error', $e->getMessage());
    }
}

public function processConfirmedReturnCashier(Request $request)
{
    \DB::beginTransaction();

    try {
        // ===== 0) تحميل بيانات الجلسة والطلب القديم =====
        $orderProducts = session('orderDetails.order_products', []);
        $oldOrderId    = session('orderDetails.order_id');
        $extraDiscount = session('extra_discount') ?? 0;
        $totalTax      = session('total_tax') ?? 0;

        $totalProductDiscount = 0;
        foreach ($orderProducts as $product) {
            $totalProductDiscount += $product->discount_on_product * $product->quantity;
        }

        $baseOrderAmount = session('order_amount') ?? 0;
        $orderAmount     = $baseOrderAmount + $extraDiscount + $totalProductDiscount - $totalTax;
        $orderAmount     = $orderAmount > 0 ? $orderAmount : 1;
        $discountRatio   = ($extraDiscount / $orderAmount) * 100;

        $returnQuantities = $request->input('return_quantities_hidden', []); // [product_id => qty]
        $returnUnits      = $request->input('return_unit_hidden', []);       // [product_id => unit(0/1)]
        $returnType       = $request->input('type', 7);                      // 7 مرتجع بيع

        $oldOrder = \App\Models\Order::find($oldOrderId);

        // جلسة POS
        $existingSession = \App\Models\PosSession::where('user_id', auth('admin')->user()->id)
            ->where('status', 'open')
            ->first();

        // ===== 1) تحقّق كميّات الارتجاع =====
        foreach ($orderProducts as $product) {
            $pid       = $product->product_id;
            $newReturn = isset($returnQuantities[$pid]) ? (float)$returnQuantities[$pid] : 0;

            if ($newReturn > 0) {
                $details   = json_decode($product->product_details);
                $unitValue = $details->unit_value ?? 1;
                $chosenUnit = $returnUnits[$pid] ?? 1;

                $oldDetail = $oldOrder?->details
                                ->where('product_id', $pid)
                                ->first();

                if ($oldDetail) {
                    if ($oldDetail->unit === 0) {
                        $alreadyReturned = ($oldDetail->quantity_returned ?? 0) * $unitValue;
                    } else {
                        $alreadyReturned = $oldDetail->quantity_returned ?? 0;
                    }
                } else {
                    $alreadyReturned = 0;
                }

                if ($chosenUnit === 0) {
                    $availableQty       = $product->quantity * $unitValue;
                    $newReturnConverted = $newReturn;
                } else {
                    $availableQty       = $product->quantity;
                    $newReturnConverted = $newReturn;
                }

                if (($alreadyReturned + $newReturnConverted) > $availableQty) {
                    \DB::rollBack();
                    $availableToReturn = $availableQty - $alreadyReturned;
                    \Toastr::error(
                        translate(
                            "لقد قمت بإرجاع {$alreadyReturned} من المنتج {$product->product->name}. ".
                            "المتاح للإرجاع هو {$availableToReturn}"
                        )
                    );
                    return redirect()->back();
                }
            }
        }

        // ===== 2) تجميع بيانات الارتجاع والإجماليات =====
        $productsReturnData = [];
        $totalReturnPrice = 0;
        $totalReturnDiscount = 0;
        $totalReturnExtraDiscount = 0;
        $totalReturnTax = 0;
        $totalReturnOverall = 0;
        $totalPriceAllProducts = 0;

        foreach ($orderProducts as $product) {
            $pid = $product->product_id;
            $productDetails = json_decode($product->product_details);
            $unitValue = $productDetails->unit_value ?? 1;
            $chosenUnit = $returnUnits[$pid] ?? 1;

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

            $returnQuantity = isset($returnQuantities[$pid]) ? (float)$returnQuantities[$pid] : 0;
            $effectiveFinalUnit = $adjustedPrice - $adjustedDiscount - $adjustedExtraDiscount + $adjustedTax;

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

            $totalReturnPrice         += $returnQuantity * $adjustedPrice;
            $totalReturnDiscount      += $returnQuantity * $adjustedDiscount;
            $totalReturnExtraDiscount += $returnQuantity * $adjustedExtraDiscount;
            $totalReturnTax           += $returnQuantity * $adjustedTax;
            $totalReturnOverall       += $returnQuantity * $effectiveFinalUnit;

            if ($returnQuantity > 0) {
                $finalQuantity = ($chosenUnit == 0) ? ($returnQuantity / $unitValue) : $returnQuantity;

                // log
                \App\Models\ProductLog::create([
                    'product_id' => $pid,
                    'quantity'   => $finalQuantity,
                    'type'       => $returnType,
                    'seller_id'  => auth('admin')->user()->id,
                    'branch_id'  => auth('admin')->user()->branch_id,
                ]);

                // stock batch in
                \App\Models\StockBatch::create([
                    'product_id' => $pid,
                    'quantity'   => $finalQuantity,
                    'branch_id'  => auth('admin')->user()->branch_id,
                    'price'      => $product->product->purchase_price,
                ]);

                // لتجميع تكلفة المخزون المرتجع
                $totalPriceAllProducts += $finalQuantity * $product->product->purchase_price;
            }
        }

        // ===== 3) إنشاء أمر جديد للمرتجع =====
        $newOrder = new \App\Models\Order;
        if ($oldOrder) {
            $newOrder->owner_id  = auth('admin')->user()->id;
            $newOrder->user_id   = $oldOrder->user_id;
            $newOrder->parent_id = $oldOrder->id;
            $newOrder->branch_id = auth('admin')->user()->branch_id;
            $newOrder->cash      = 1;

            if ($oldOrder->type == 4)      $newOrder->type = 7;
            elseif ($oldOrder->type == 12) $newOrder->type = 24;
            else                           $newOrder->type = $returnType;
        }
        $newOrder->total_tax              = $totalReturnTax;
        $newOrder->order_amount           = $totalReturnOverall;
        $newOrder->extra_discount         = $totalReturnExtraDiscount;
        $newOrder->coupon_discount_amount = 0;
        $newOrder->collected_cash         = $totalReturnOverall;
        $newOrder->transaction_reference  = $totalReturnOverall;
        $newOrder->session_id             = $existingSession?->id;
        $newOrder->date                   = $request->date;
        $newOrder->save();

        // ===== 4) إن كانت تقسيط، تعديل عقد الأقساط وعمل قيد عكسي لإيراد التمويل =====
        $installment_contract = \App\Models\InstallmentContract::where('order_id', $oldOrder?->id)->latest()->first();
        if ($installment_contract) {
            $amountRemaining = $oldOrder->order_amount - $totalReturnOverall;

            $oldWithInterest  = $installment_contract->total_amount;
            $oldInterest      = $installment_contract->interest_percent;
            $oldPrincipal     = $oldWithInterest / (1 + ($oldInterest / 100));
            $oldFinanceIncome = $oldWithInterest - $oldPrincipal;

            $returnRatio            = $totalReturnOverall / $oldOrder->order_amount;
            $returnedFinanceIncome  = round($oldFinanceIncome * $returnRatio, 2);

            if ($amountRemaining != 0) {
                $nextInstallment = \App\Models\ScheduledInstallment::where('contract_id', $installment_contract->id)
                    ->where('status', 'pending')
                    ->orderBy('due_date', 'asc')
                    ->first();

                $startDate = $nextInstallment ? \Carbon\Carbon::parse($nextInstallment->due_date) : now();

                $interest = $installment_contract->interest_percent;
                $months   = $installment_contract->duration_months;

                $totalWithInterest = $amountRemaining * (1 + ($interest / 100));
                $monthlyAmount     = round($totalWithInterest / $months, 2);

                $newContract = new \App\Models\InstallmentContract();
                $newContract->customer_id       = $installment_contract->customer_id;
                $newContract->order_id          = $oldOrder->id;
                $newContract->total_amount      = $totalWithInterest;
                $newContract->start_date        = $startDate->toDateString();
                $newContract->duration_months   = $months;
                $newContract->interest_percent  = $interest;
                $newContract->status            = 'active';
                $newContract->save();

                for ($i = 0; $i < $months; $i++) {
                    \App\Models\ScheduledInstallment::create([
                        'contract_id' => $newContract->id,
                        'due_date'    => $startDate->copy()->addMonths($i)->toDateString(),
                        'amount'      => $monthlyAmount,
                        'status'      => 'pending',
                    ]);
                }
            }

            $installment_contract->status = 'canceled';
            $installment_contract->save();

            \App\Models\ScheduledInstallment::where('contract_id', $installment_contract->id)
                ->where('status', 'pending')
                ->update(['status' => 'canceled']);

            // === قيد يومية لإيراد تمويلي عكسي (مدين العميل / دائن حساب إيراد التمويل 100) ===
            $customer = \App\Models\Customer::find($oldOrder->user_id);
            $financeIncomeAccId = 100;
            $customerAccId      = $customer->account_id;

            $entryFi = new \App\Models\JournalEntry();
            $entryFi->entry_date  = $request->date ?? now()->toDateString();
            $entryFi->reference   = 'SR-FI-' . $newOrder->id;
            $entryFi->type        = 'sales_return_finance_income';
            $entryFi->description = 'قيد عكسي لإيراد تمويلي لمرتجع أجل';
            $entryFi->created_by  = auth('admin')->id();
            $entryFi->branch_id   = auth('admin')->user()->branch_id;
            $entryFi->order_id    = $newOrder->id;
            $entryFi->save();

            // مدين: حساب العميل
            $fiDebit = new \App\Models\JournalEntryDetail();
            $fiDebit->journal_entry_id = $entryFi->id;
            $fiDebit->account_id       = $customerAccId;
            $fiDebit->debit            = $returnedFinanceIncome;
            $fiDebit->credit           = 0;
            $fiDebit->cost_center_id   = null;
            $fiDebit->description      = 'عكس إيراد تمويلي (مدين العميل)';
            $fiDebit->entry_date       = $entryFi->entry_date;
            $fiDebit->save();

            // دائن: حساب إيراد التمويل
            $fiCredit = new \App\Models\JournalEntryDetail();
            $fiCredit->journal_entry_id = $entryFi->id;
            $fiCredit->account_id       = $financeIncomeAccId;
            $fiCredit->debit            = 0;
            $fiCredit->credit           = $returnedFinanceIncome;
            $fiCredit->cost_center_id   = null;
            $fiCredit->description      = 'عكس إيراد تمويلي (دائن إيراد التمويل)';
            $fiCredit->entry_date       = $entryFi->entry_date;
            $fiCredit->save();

            // ترانزاكشن مدين (العميل)
            \App\Models\Transection::create([
                'tran_type'               => 7,
                'seller_id'               => auth('admin')->user()->id,
                'branch_id'               => auth('admin')->user()->branch_id,
                'cost_id'                 => $request->cost_id,
                'account_id'              => $customerAccId,
                'account_id_to'           => $financeIncomeAccId,
                'amount'                  => $returnedFinanceIncome,
                'description'             => 'قيد إيراد تمويلي عكسي - مدين العميل',
                'debit'                   => $returnedFinanceIncome,
                'credit'                  => 0,
                'date'                    => date("Y/m/d"),
                'customer_id'             => $oldOrder->user_id,
                'order_id'                => $newOrder->id,
                'journal_entry_detail_id' => $fiDebit->id,
            ]);

            // ترانزاكشن دائن (إيراد التمويل)
            \App\Models\Transection::create([
                'tran_type'               => 7,
                'seller_id'               => auth('admin')->user()->id,
                'branch_id'               => auth('admin')->user()->branch_id,
                'cost_id'                 => $request->cost_id,
                'account_id'              => $financeIncomeAccId,
                'account_id_to'           => $customerAccId,
                'amount'                  => $returnedFinanceIncome,
                'description'             => 'قيد إيراد تمويلي عكسي - دائن إيراد التمويل',
                'debit'                   => 0,
                'credit'                  => $returnedFinanceIncome,
                'date'                    => date("Y/m/d"),
                'customer_id'             => $oldOrder->user_id,
                'order_id'                => $newOrder->id,
                'journal_entry_detail_id' => $fiCredit->id,
            ]);

            // تحديث أرصدة (بنفس منطقك الحالي)
            $accCustomer = \App\Models\Account::find($customerAccId);
            $accFinance  = \App\Models\Account::find($financeIncomeAccId);

            $accCustomer->balance   += $returnedFinanceIncome;
            $accCustomer->total_in  += $returnedFinanceIncome;
            $accCustomer->save();

            $accFinance->balance    -= $returnedFinanceIncome;
            $accFinance->total_out  += $returnedFinanceIncome;
            $accFinance->save();

            $customer->balance += $returnedFinanceIncome;
            $customer->save();
        }

        // ===== 5) قيود اليومية الأساسية للمرتجع (3 قيود) + ترانزاكشنات =====
        $customer  = \App\Models\Customer::find($oldOrder->user_id);
        $branch    = \App\Models\Branch::find(auth('admin')->user()->branch_id);

        $accRevenueReturn = 92;                        // عوائد المبيعات / مرتجع المبيعات
        $accSalesRevenue  = 40;                        // إيراد المبيعات (عندك مستخدمه سابقًا)
        $accVatPayable    = 28;                        // ضرائب
        $accCOGS          = 47;                        // تكلفة المبيعات
        $accInventory     = $branch->account_stock_Id; // مخزون الفرع

        $amountExclTax = $totalReturnOverall - $totalReturnTax;
        $todayDate     = $request->date ?? now()->toDateString();

        // --- إنشاء قيد يومية رئيسي لعملية المرتجع ---
        $entry = new \App\Models\JournalEntry();
        $entry->entry_date  = $todayDate;
        $entry->reference   = 'SR-' . $newOrder->id;
        $entry->type        = 'sales_return';
        $entry->description = 'قيد مرتجع بيع';
        $entry->created_by  = auth('admin')->id();
        $entry->branch_id   = auth('admin')->user()->branch_id;
        $entry->save();

        // (1) مرتجع الإيراد (بدون الضريبة): مدين 92 / دائن 40
        $d1 = new \App\Models\JournalEntryDetail([
            'journal_entry_id' => $entry->id,
            'account_id'       => $accRevenueReturn,
            'debit'            => $amountExclTax,
            'credit'           => 0,
            'description'      => 'مرتجع إيراد (مدين 92)',
            'entry_date'       => $todayDate,
        ]); $d1->save();

        $c1 = new \App\Models\JournalEntryDetail([
            'journal_entry_id' => $entry->id,
            'account_id'       => $accSalesRevenue,
            'debit'            => 0,
            'credit'           => $amountExclTax,
            'description'      => 'مرتجع إيراد (دائن 40)',
            'entry_date'       => $todayDate,
        ]); $c1->save();

        // ترانزاكشنات للقيـد (1)
        \App\Models\Transection::create([
            'tran_type'               => $newOrder->type,
            'seller_id'               => auth('admin')->user()->id,
            'branch_id'               => auth('admin')->user()->branch_id,
            'cost_id'                 => $request->cost_id,
            'account_id'              => $accRevenueReturn,
            'account_id_to'           => $accSalesRevenue,
            'amount'                  => $amountExclTax,
            'description'             => 'فاتورة مرتجع بيع - قيد الإيراد (مدين)',
            'debit'                   => $amountExclTax,
            'credit'                  => 0,
            'date'                    => date("Y/m/d"),
            'customer_id'             => $oldOrder->user_id,
            'order_id'                => $newOrder->id,
            'journal_entry_detail_id' => $d1->id,
        ]);

        \App\Models\Transection::create([
            'tran_type'               => $newOrder->type,
            'seller_id'               => auth('admin')->user()->id,
            'branch_id'               => auth('admin')->user()->branch_id,
            'cost_id'                 => $request->cost_id,
            'account_id'              => $accSalesRevenue,
            'account_id_to'           => $accRevenueReturn,
            'amount'                  => $amountExclTax,
            'description'             => 'فاتورة مرتجع بيع - قيد الإيراد (دائن)',
            'debit'                   => 0,
            'credit'                  => $amountExclTax,
            'date'                    => date("Y/m/d"),
            'customer_id'             => $oldOrder->user_id,
            'order_id'                => $newOrder->id,
            'journal_entry_detail_id' => $c1->id,
        ]);

        // (2) الضرائب: مدين 28 / دائن 92 (إلغاء الضريبة من الإيراد)
        if ($totalReturnTax > 0) {
            $d2 = new \App\Models\JournalEntryDetail([
                'journal_entry_id' => $entry->id,
                'account_id'       => $accVatPayable,
                'debit'            => $totalReturnTax,
                'credit'           => 0,
                'description'      => 'مرتجع ضريبة (مدين 28)',
                'entry_date'       => $todayDate,
            ]); $d2->save();

            $c2 = new \App\Models\JournalEntryDetail([
                'journal_entry_id' => $entry->id,
                'account_id'       => $accRevenueReturn,
                'debit'            => 0,
                'credit'           => $totalReturnTax,
                'description'      => 'مرتجع ضريبة (دائن 92)',
                'entry_date'       => $todayDate,
            ]); $c2->save();

            // ترانزاكشنات للقيـد (2)
            \App\Models\Transection::create([
                'tran_type'               => $newOrder->type,
                'seller_id'               => auth('admin')->user()->id,
                'branch_id'               => auth('admin')->user()->branch_id,
                'cost_id'                 => $request->cost_id,
                'account_id'              => $accVatPayable,
                'account_id_to'           => $accRevenueReturn,
                'amount'                  => $totalReturnTax,
                'description'             => 'قيد مرتجع بيع الضرائب (مدين)',
                'debit'                   => $totalReturnTax,
                'credit'                  => 0,
                'date'                    => date("Y/m/d"),
                'customer_id'             => $oldOrder->user_id,
                'order_id'                => $newOrder->id,
                'journal_entry_detail_id' => $d2->id,
            ]);

            \App\Models\Transection::create([
                'tran_type'               => $newOrder->type,
                'seller_id'               => auth('admin')->user()->id,
                'branch_id'               => auth('admin')->user()->branch_id,
                'cost_id'                 => $request->cost_id,
                'account_id'              => $accRevenueReturn,
                'account_id_to'           => $accVatPayable,
                'amount'                  => $totalReturnTax,
                'description'             => 'قيد مرتجع بيع الضرائب (دائن)',
                'debit'                   => 0,
                'credit'                  => $totalReturnTax,
                'date'                    => date("Y/m/d"),
                'customer_id'             => $oldOrder->user_id,
                'order_id'                => $newOrder->id,
                'journal_entry_detail_id' => $c2->id,
            ]);
        }

        // (3) المخزون/تكلفة المبيعات: مدين المخزون / دائن تكلفة المبيعات بمجموع تكلفة المرتجع
        if ($totalPriceAllProducts > 0) {
            $d3 = new \App\Models\JournalEntryDetail([
                'journal_entry_id' => $entry->id,
                'account_id'       => $accInventory,
                'debit'            => $totalPriceAllProducts,
                'credit'           => 0,
                'description'      => 'مرتجع مخزون (مدين المخزون)',
                'entry_date'       => $todayDate,
            ]); $d3->save();

            $c3 = new \App\Models\JournalEntryDetail([
                'journal_entry_id' => $entry->id,
                'account_id'       => $accCOGS,
                'debit'            => 0,
                'credit'           => $totalPriceAllProducts,
                'description'      => 'مرتجع مخزون (دائن تكلفة المبيعات)',
                'entry_date'       => $todayDate,
            ]); $c3->save();

            // ترانزاكشنات للقيـد (3)
            \App\Models\Transection::create([
                'tran_type'               => $newOrder->type,
                'seller_id'               => auth('admin')->user()->id,
                'branch_id'               => auth('admin')->user()->branch_id,
                'cost_id'                 => $request->cost_id,
                'account_id'              => $accInventory,
                'account_id_to'           => $accCOGS,
                'amount'                  => $totalPriceAllProducts,
                'description'             => 'قيد مرتجع بيع المخزون (مدين)',
                'debit'                   => $totalPriceAllProducts,
                'credit'                  => 0,
                'date'                    => date("Y/m/d"),
                'customer_id'             => $oldOrder->user_id,
                'order_id'                => $newOrder->id,
                'journal_entry_detail_id' => $d3->id,
            ]);

            \App\Models\Transection::create([
                'tran_type'               => $newOrder->type,
                'seller_id'               => auth('admin')->user()->id,
                'branch_id'               => auth('admin')->user()->branch_id,
                'cost_id'                 => $request->cost_id,
                'account_id'              => $accCOGS,
                'account_id_to'           => $accInventory,
                'amount'                  => $totalPriceAllProducts,
                'description'             => 'قيد مرتجع بيع المخزون (دائن)',
                'debit'                   => 0,
                'credit'                  => $totalPriceAllProducts,
                'date'                    => date("Y/m/d"),
                'customer_id'             => $oldOrder->user_id,
                'order_id'                => $newOrder->id,
                'journal_entry_detail_id' => $c3->id,
            ]);
        }

        // تحديث أرصدة الحسابات (بنفس منطقك)
        // (1) الإيراد بدون الضريبة: أنقص 92 و40 كما كنت تفعل (حافظت على أسلوبك)
        $acc92 = \App\Models\Account::find($accRevenueReturn);
        $acc40 = \App\Models\Account::find($accSalesRevenue);
        $acc92->balance    -= $amountExclTax;
        $acc92->total_out  += $amountExclTax;
        $acc92->save();
        $acc40->balance    -= $amountExclTax;
        $acc40->total_out  += $amountExclTax;
        $acc40->save();

        // (2) الضرائب
        if ($totalReturnTax > 0) {
            $acc28 = \App\Models\Account::find($accVatPayable);
            $acc92 = \App\Models\Account::find($accRevenueReturn);
            $acc28->balance   -= $totalReturnTax;
            $acc28->total_out += $totalReturnTax;
            $acc28->save();
            $acc92->balance   -= $totalReturnTax;
            $acc92->total_out += $totalReturnTax;
            $acc92->save();
        }

        // (3) المخزون/تكلفة المبيعات
        if ($totalPriceAllProducts > 0) {
            $accInv = \App\Models\Account::find($accInventory);
            $acc47  = \App\Models\Account::find($accCOGS);
            $acc47->balance    -= $totalPriceAllProducts;
            $acc47->total_out  += $totalPriceAllProducts;
            $acc47->save();
            $accInv->balance   += $totalPriceAllProducts;
            $accInv->total_in  += $totalPriceAllProducts;
            $accInv->save();
        }

        // ===== 6) QR-code وحفظ تفاصيل المرتجع =====
        $qrcodeData  = url('real/invoicea2/' . $newOrder->id);
        $qrCode      = new \Endroid\QrCode\QrCode($qrcodeData);
        $writer      = new \Endroid\QrCode\Writer\PngWriter();
        $qrcodeImage = $writer->write($qrCode)->getString();
        $qrcodePath  = "qrcodes/order_" . $newOrder->id . ".png";
        \Storage::disk('public')->put($qrcodePath, $qrcodeImage);

        $newOrder->qrcode = $qrcodePath;
        $newOrder->save();

        foreach ($productsReturnData as $productData) {
            \App\Models\OrderDetail::create([
                'order_id'            => $newOrder->id,
                'product_id'          => $productData['product_id'],
                'product_details'     => json_encode($productData),
                'quantity'            => $productData['return_quantity'],
                'unit'                => $productData['unit'],
                'price'               => $productData['price'],
                'tax_amount'          => $productData['tax'],
                'discount_on_product' => $productData['discount'],
                'discount_type'       => 'discount_on_product',
            ]);
        }

        // تحديث الكميات المرتجعة على تفاصيل الطلب القديم
        foreach ($productsReturnData as $productData) {
            $pid        = $productData['product_id'];
            $returnQty  = $productData['return_quantity'];
            $chosenUnit = $productData['unit'];

            $oldDetail = $oldOrder?->details->where('product_id', $pid)->first();
            if ($oldDetail) {
                $details   = json_decode($oldDetail->product_details);
                $unitValue = $details->unit_value ?? 1;
                $returnQtyInBase = ($chosenUnit == 0) ? ($returnQty / $unitValue) : $returnQty;

                $oldDetail->quantity_returned = ($oldDetail->quantity_returned ?? 0) + $returnQtyInBase;
                $oldDetail->save();
            }
        }

        // تأثير المرتجع على رصيد العميل
        $customer->balance += $totalReturnOverall;
        $customer->save();

        \DB::commit();

        \Toastr::success(translate('تم تنفيذ الطلب بنجاح') . ' - رقم الطلب: ' . $newOrder->id);

        // تفريغ السيشن
        session()->forget([
            'extra_discount','total_tax','order_amount','name','mobile','credit',
            'c_history','tax_number','seller','created_at','orderDetails'
        ]);

        return redirect()->route('admin.pos.index', ['type' => 1])->with('success', 'تم تنفيذ المرتجع بنجاح!');
    } catch (\Exception $e) {
        \DB::rollBack();
        \Toastr::error($e->getMessage());
        return redirect()->back()->with('error', $e->getMessage());
    }
}



}
