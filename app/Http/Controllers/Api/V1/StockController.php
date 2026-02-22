<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use App\Models\ReserveProduct;
use App\Models\CurrentReserveProduct;
use App\Models\CurrentOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Transection;
use App\Http\Controllers\Controller;
use App\Http\Resources\StocksResource;
use App\Http\Resources\ProductsResource;
use App\Models\ConfirmStock;
use App\Models\Installment;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Seller;
use App\Models\StockOrder;
use App\Models\ProductLog;
use App\Models\StockHistory;
use App\Models\Order;
use App\Models\Stock;
use Illuminate\Support\Facades\Auth;
use function App\CPU\translate;

class StockController extends Controller
{
    public function __construct(
        private Product $product,
        private ReserveProduct $reserve_product,
        private CurrentReserveProduct $current_reserve_products,
        private Stock $stock,
                private ProductLog $product_logs,
        private ConfirmStock $confirm_stock,
        private Installment $installment,
        private StockHistory $stock_history,
        private StockOrder $stock_order,
        private CurrentOrder $current_order,
        private Transection $transection
    ){}
    
public function index(Request $request): JsonResponse
{
    // Extract the request parameters with default values
    $limit = $request->input('limit', 10);
    $offset = $request->input('offset', 1);
    $cat_id = $request->input('category_id', 0);
    $type = $request->input('type');
    $search = $request->input('search'); // Assuming there's a search parameter

    // Initialize the query for stock items
    $query = $this->stock->where('seller_id', auth()->user()->id)
        ->with(['product' => function($query) {
            $query->orderBy('name', 'asc'); // Order products by name alphabetically
        }]);

    // If a category ID is provided, filter the stocks by that category
    if ($cat_id != 0) {
        $product_ids = $this->product->where('category_id', $cat_id)->pluck('id');
    }

    // Check if type is 4 to fetch stocks, else fetch products
    if ($type == 4) {
        // Fetch stocks and paginate the result
        $stocks = $query->paginate($limit, ['*'], 'page', $offset);

        // Loop through stocks and set selling price based on seller's price if available
        foreach ($stocks as $stock) {
            $seller_price = \App\Models\SellerPrice::where([
                'seller_id' => Auth::user()->id,
                'product_id' => $stock->product->id
            ])->first();

            // If seller price exists, use it; otherwise, use the product's default selling price
            $stock->product->selling_price = $seller_price ? $seller_price->price : $stock->product->selling_price;
        }

        // Transform stocks into a resource collection
        $stocks = StocksResource::collection($stocks);

        // Prepare response data for stocks
        $data = [
            'limit' => $limit,
            'offset' => $offset,
            'stocks' => $stocks,
            'current_page' => $stocks->currentPage(),
            'last_page' => $stocks->lastPage()
        ];

        return response()->json($data, 200);
    } else {
        // Fetch products if type is not 4
$cat_ids = \App\Models\SellerCategory::where('seller_id', auth()->user()->id)->pluck('cat_id');
        // Query products based on categories, and apply search functionality
        $query = $this->product
            ->where(function ($query) use ($search) {
                $query->where('product_code', 'LIKE', "%$search%")
                      ->orWhere('name', 'LIKE', "%$search%");
            });

        // Paginate the products
        $products = $query->paginate($limit, ['*'], 'page', $offset);

        // Loop through products and set selling price based on seller's price if available
        foreach ($products as $product) {
            $seller_price = \App\Models\SellerPrice::where([
                'seller_id' => Auth::user()->id,
                'product_id' => $product->id
            ])->first();

            // Set the price based on seller price or default to product's selling price
            $product->selling_price = $seller_price ? $seller_price->price : $product->selling_price;
        }

        // Transform products into a resource collection
        $products = ProductsResource::collection($products);

        // Prepare response data for products
        $data = [
            'limit' => $limit,
            'offset' => $offset,
            'products' => $products,
            'current_page' => $products->currentPage(), // Current page for products
            'last_page' => $products->lastPage() // Last page for products
        ];

        return response()->json($data, 200);
    }
}




public function confirm(Request $request): JsonResponse
{
    $seller = Auth::user();

    // جلب المنتجات التي تم تعديل مخزونها
    $stocks = $this->stock->where('seller_id', $seller->id)->whereRaw('main_stock != stock');
    $remain_stocks = $this->stock->where('seller_id', $seller->id)->whereRaw('main_stock = stock');

    $stock_order = $this->stock_order;
    $order_id = 30000000 + $stock_order->count() + 1;
    if ($stock_order->find($order_id)) {
        $order_id = $stock_order->orderBy('id', 'DESC')->first()->id + 1;
    }

    $stock_order->id = $order_id;
    $stock_order->seller_id = $seller->id;
    $stock_order->save();

    $total_stock = $total_remains_stock = $totalPriceAllProducts = 0;
    $products = $remain_products = [];

    if ($stocks->count() > 0 || $remain_stocks->count() > 0) {
        // حذف التأكيدات القديمة
        $this->confirm_stock->where('seller_id', $seller->id)->delete();

        // دمج المخزنين في مجموعة واحدة
        $all_stocks = $stocks->get()->merge($remain_stocks->get());

        foreach ($all_stocks as $item) {
            // حفظ تأكيد المخزون
            $confirm = new $this->confirm_stock;
            $confirm->fill([
                'seller_id' => $seller->id,
                'product_id' => $item->product_id,
                'main_stock' => $item->main_stock,
                'stock' => $item->stock
            ])->save();

            // تسجيل سجل المخزون
            (new $this->stock_history)->fill([
                'seller_id' => $seller->id,
                'order_id' => $order_id,
                'product_id' => $item->product_id,
                'main_stock' => $item->main_stock,
                'stock' => $item->stock
            ])->save();

            // تسجيل سجل المنتجات
            (new $this->product_logs)->fill([
                'product_id' => $item->product_id,
                'quantity' => $item->stock,
                'branch_id'=>$request->branch_id,
                'type' => 200
            ])->save();

            // تحديث كمية المنتج في المخزون العام
            $product = $this->product->find($item->product_id);
            $product->quantity += $item->stock;
            $product->update();

            $total_stock += $item->stock;
            $total_remains_stock += $item->stock;
            $totalPriceAllProducts += $item->getTotalStockValue();

            // بيانات المنتجات التي تم صرفها
            if ($item->stock != 0) {
                $remain_products[] = [
                    'name' => $item->product->name,
                    'name_en' => $item->product->name_en,
                    'quantity' => $item->stock,
                    'product_code' => $item->product->product_code,
                    'price' => $item->product->selling_price * $item->stock,
                ];
            }
        }

        $orders = $this->current_order->where('owner_id', $seller->id);
        $order_count=0;
        $order_count = $orders->count();
        $product_count = $this->confirm_stock->where('seller_id', $seller->id)->whereDate('created_at', now()->format('Y-m-d'))->count();

        foreach ($this->confirm_stock->where('seller_id', $seller->id)->where('stock', '!=', 0)->get() as $i => $item) {
            $products[$i] = [
                'name' => $item->product->name,
                'name_en' => $item->product->name_en,
                'product_code' => $item->product->product_code,
                'quantity' => $item->stock,
            ];

            foreach ($orders->get() as $order) {
                $detail = \App\Models\OrderDetail::where([
                    'product_id' => $item->product_id,
                    'order_id' => $order->id
                ])->first();
                if ($detail) {
                    $products[$i]['price'] = $detail->price;
                }
            }
        }
    }
            $orders = $this->current_order->where('owner_id', $seller->id);
        $product_count = $this->confirm_stock->where('seller_id', $seller->id)->whereDate('created_at', now()->format('Y-m-d'))->count();

        $order_countw = $orders->count();

    // عمليات مالية
    $total_cash = $this->transection->where('tran_type', 4)->where('cash', 1)->where('seller_id', $seller->id)->sum('amount');
    $total_credit = $this->transection->where('tran_type', 4)->where('cash', 2)->where('seller_id', $seller->id)->sum('amount');
    $refund_total = $this->transection->where('tran_type', 7)->where('seller_id', $seller->id)->sum('amount');
    $installment = $this->installment->where('seller_id', $seller->id)->sum('total_price');

    $data = [
        'vehicle_code' => $this->vehicleCode($seller->vehicle_code),
        'vehicle_name' => \App\Models\Store::where('store_id', $seller->vehicle_code)->value('store_name1'),
        'product_count' => $stocks->count(),
        'total_stock' => $total_stock,
        'order_count' => $order_countw,
        'remain_stock' => $total_remains_stock,
        'total_cash' => $total_cash,
        'total_credit' => $total_credit,
        'installment_total' => $installment,
        'refund_total' => $refund_total,
        'products' => $products,
        'remain_products' => $remain_products,
    ];

    $stock_order->statistcs = json_encode($data);
    $stock_order->update();

    // حذف السجلات القديمة بعد التأكيد
    $stocks->delete();
    $remain_stocks->delete();
    $this->current_order->where('owner_id', $seller->id)->delete();
    $this->current_reserve_products->where('seller_id', $seller->id)->delete();
    $this->transection->where('seller_id', $seller->id)->update(['active' => 0]);

    // قيود محاسبية
    $sellerAccount = Seller::find($seller->id);
    $branch = Branch::find($request->branch_id);
    $payable_from = Account::find($sellerAccount->account_id);
    $payable_to = Account::find($branch->account_stock_Id);

    $tran = new Transection();
    $tran->fill([
        'tran_type' => 1110,
        'seller_id' =>  $seller->id,
        'branch_id' => $request->branch_id,
        'cost_id' => $request->cost_id,
        'account_id' => $payable_to->id,
        'account_id_to' => $payable_from->id,
        'amount' => $totalPriceAllProducts,
        'description' => 'تصفير رحلة',
        'debit' => $totalPriceAllProducts,
        'credit' => 0,
        'balance' => round($payable_from->balance - $totalPriceAllProducts, 2),
        'debit_account' => 0,
        'credit_account' => $totalPriceAllProducts,
        'balance_account' => round($payable_to->balance + $totalPriceAllProducts, 2),
        'date' => now()->format('Y/m/d'),
    ])->save();

    $payable_from->decrement('balance', $totalPriceAllProducts);
    $payable_from->increment('total_out', $totalPriceAllProducts);
    $payable_from->save();

    $payable_to->increment('balance', $totalPriceAllProducts);
    $payable_to->increment('total_in', $totalPriceAllProducts);
    $payable_to->save();

    return response()->json(['message' => 'تم تأكيد المخزون بنجاح', 'data' => $data], 200);
}

    
    public function history(Request $request)
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        $date = $request['date'] ?? null;
        
        $this->stock_order->whereNull('statistcs')->delete();
        $stocks = $this->stock_order->where('seller_id', Auth::user()->id)->latest()->get();
        
        $items = [];
        // dd(json_decode($stocks[0]['statistcs']));
        foreach($stocks as $key => $item) {
            $item['statistcs'] = json_decode($item->statistcs);
            
            $item['seller']->vehicle_code = $this->vehicleCode($item['seller']->vehicle_code);
        }
        
        $data = [
            'limit' => $limit,
            'offset' => $offset,
            'stocks' => $stocks
        ];
        return response()->json($data, 200);
    }
    public function getbranches(Request $request)
    {
       
        $branches = $this->branch->where('active',1)->latest()->get();
    
        
        $data = [
            'branches' => $branches
        ];
        return response()->json($data, 200);
    }



}
