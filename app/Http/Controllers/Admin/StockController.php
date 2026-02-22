<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\CPU\Helpers;
use App\Models\ConfirmStock;
use App\Models\StockOrder;
use App\Models\Order;
use App\Models\StockHistory;
use App\Models\StockBatch;
use App\Models\Account;
use App\Models\Transection;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\Seller;
use App\Models\Stock;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use function App\CPU\translate;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function __construct(
        private ConfirmStock $confirm_stock,
        private Stock $stock,
                private ProductLog $product_logs,
        private StockOrder $stock_order,
                private Order $order,
                                private StockHistory $stock_history,

        private Product $product,
        private Seller $seller,
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
public function index(Request $request)
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

    if (!in_array("export32.show", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    $query = $this->stock
        ->join('products', 'stocks.product_id', '=', 'products.id')
        ->join('admins', 'stocks.seller_id', '=', 'admins.id');

    // Filter by search query if provided
    if ($request->has('search') && $request->search) {
        $searchTerm = $request->search;
        
        $query = $query->where(function ($subQuery) use ($searchTerm) {
            $subQuery->where('products.product_code', 'LIKE', '%' . $searchTerm . '%')
                     ->orWhere('admins.mandob_code', 'LIKE', '%' . $searchTerm . '%')
                     ->orWhere('admins.f_name', 'LIKE', '%' . $searchTerm . '%')
                     ->orWhere('admins.l_name', 'LIKE', '%' . $searchTerm . '%');
        });
    }

    // Select the fields you need
    $query = $query->select('stocks.*', 'products.name as product_name', 'admins.f_name as seller_first_name', 'admins.l_name as seller_last_name');

    // Paginate the results
    $stocks = $query->paginate(Helpers::pagination_limit());

    return view('admin-views.vehicle_stocks.index', compact('stocks'));
}


  public function vehicles(Request $request)
{
    // Get the authenticated admin's ID
    $adminId = Auth::guard('admin')->id();

    // Get the sellers associated with the authenticated admin
    $sellers = Seller::whereHas('adminSellers', function ($query) use ($adminId) {
        $query->where('admin_id', $adminId); // Filter by admin_id in admin_sellers table
    })->get();

    // Get the search date if provided
    $date = $request['search'];

    // Pass the filtered sellers and the search date to the view
    return view('admin-views.vehicle_stocks.vehicles', compact('sellers', 'date'));
}

    
    public function vehicle_products($seller_id): Factory|View|Application
    {
        $stocks = $this->confirm_stock->where('seller_id', $seller_id)->get();
  
        return view('admin-views.vehicle_stocks.products', compact('stocks', 'remain_stocks'));
    }
    
    public function stock_products($seller_id): Factory|View|Application
    {
        $stocks = $this->stock->where('seller_id', $seller_id);
        $remain_stocks = $this->stock->where('seller_id', $seller_id);
        $seller = Seller::find($seller_id);
        $orders = \App\Models\CurrentOrder::where('owner_id', $seller_id);
        return view('admin-views.vehicle_stocks.stocks', compact('stocks', 'remain_stocks', 'orders', 'seller'));
    }

    public function create(Request $request)
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

    if (!in_array("export32.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
            $adminId = Auth::guard('admin')->id();

   $sellers = Seller::whereHas('adminSellers', function ($query) use ($adminId) {
        $query->where('admin_id', $adminId)->where('role','seller'); // Filter by admin_id in admin_sellers table
    })->get();
        $products = [];

        // dd();
        if($request->has('seller')) {
            $sel = $this->seller->find($request->seller);
            foreach($sel->cats as $c) {
                $id = $c->cat->id;
                $products[] = $this->product->where('product_type','product')->where('category_id', $id)->get();
                // dd($products->get());
            }
            // dd($products);
            return response()->json([
                'option' => $products
            ]);
            // $products = $products->get();
            
        }
        return view('admin-views.vehicle_stocks.create', compact('sellers', 'products'));
    }
public function store(Request $request): RedirectResponse
{
    // Validate the incoming request
    $request->validate([
        'seller_id'  => 'required|exists:admins,id',
        'product_id' => 'required|array',
        'product_id.*' => 'exists:products,id',
        'stock'      => 'required|array',
        'stock.*'    => 'numeric|min:0',
        'unit'       => 'required|array',
        'unit.*'     => 'in:0,1', // 0 for minor unit, 1 for major unit
    ]);

    DB::beginTransaction();

    try {
        $success = false;
        $reserveProducts = [];
        $totalPriceAllProducts = 0; // إجمالي سعر المنتجات التي تم صرفها

        foreach ($request->product_id as $i => $productId) {
            // Retrieve the product
            $product = $this->product->find($productId);
            if (!$product) {
                continue;
            }

            // Calculate adjusted stock based on unit type
            $inputStock = $request->stock[$i];
            $unit = $request->unit[$i];
            $adjustedStock = ($unit == 0) 
                ? $inputStock / $product->unit_value 
                : $inputStock;
            // تقريب الكمية المعدلة إلى رقمين عشريين
            $adjustedStock = round($adjustedStock, 2);

            // Check if the product has sufficient overall quantity
        
 $adminBranchId = auth('admin')->user()->branch_id;
            $branchColumn = "branch_" . $adminBranchId;
            if ($product->$branchColumn >= $adjustedStock) {
                $product->$branchColumn -= $adjustedStock;
            } elseif ($product->quantity >= $adjustedStock) {
                $product->quantity -= $adjustedStock;
            } else {
                Toastr::error(translate('لا توجد كمية كافية بالمخزن'));
                return redirect()->back();
            }
            // Update or create the seller stock entry
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
                    // لن يتم تحديد عمود "price" هنا؛ سيتم تحديثه بعد عملية FIFO
                ]);
            }

            // Log the product transaction
            $productLog = new ProductLog();
            $productLog->product_id = $productId;
            $productLog->quantity = (string)$adjustedStock;
            $productLog->seller_id = $request->seller_id;
            $productLog->branch_id = auth('admin')->user()->branch_id;
            $productLog->type = 100; // نوع عملية الصرف
            $productLog->save();

            // Apply FIFO: Deduct the adjusted stock from stock_batches and calculate price per batch
            $remaining = $adjustedStock;
            $priceJson = []; // مصفوفة لتخزين تفاصيل الدفعات المستخدمة (الكمية والسعر)
            $stockBatches = StockBatch::where('product_id', $productId)
                ->where('branch_id', auth('admin')->user()->branch_id)
                ->where('quantity', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($stockBatches as $batch) {
                if ($remaining <= 0) {
                    break;
                }
                $batchQty = (float)$batch->quantity;
                if ($batchQty >= $remaining) {
                    // Deduct the remaining quantity from this batch
                    $deducted = $remaining;
                    $newBatchQty = round($batchQty - $remaining, 2);
                    $batch->quantity = (string)$newBatchQty;
                    $batch->save();

                    $priceForDeduction = round($deducted * $batch->price, 2);
                    $totalPriceAllProducts += $priceForDeduction;

                    // Add deduction details to priceJson
                    $priceJson[] = [
                        'quantity' => $deducted,
                        'price' => $batch->price,
                    ];

                    $remaining = 0;
                } else {
                    // Deduct the entire batch and move to the next
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

            // After FIFO loop, update the seller stock entry to include the JSON price details
            $stockEntry = $this->stock
                ->where('product_id', $productId)
                ->where('seller_id', $request->seller_id)
                ->first();
            if ($stockEntry) {
                $stockEntry->price = json_encode($priceJson, JSON_UNESCAPED_UNICODE);
                $stockEntry->save();
            } else {
                // In case it was not created (should not happen due to earlier branch)
                $this->stock->create([
                    'seller_id'  => $request->seller_id,
                    'product_id' => $productId,
                    'main_stock' => (string)$adjustedStock,
                    'stock'      => (string)$adjustedStock,
                    'price'      => json_encode($priceJson, JSON_UNESCAPED_UNICODE),
                ]);
            }

            // Update the product's overall quantity based on branch column if exists
            $adminBranchId = auth('admin')->user()->branch_id;
            $branchColumn = "branch_" . $adminBranchId;
            if (isset($product->$branchColumn)) {
                $newQuantity = round((float)$product->$branchColumn - $adjustedStock, 2);
                $product->$branchColumn = (string)$newQuantity;
                $product->save();
            } else {
                $newQuantity = round((float)$product->quantity - $adjustedStock, 2);
                $product->quantity = (string)$newQuantity;
                $product->save();
            }

            // Collect product data for reserve record
            $reserveProducts[] = [
                'product_name' => $product->name,
                'product_id'   => $productId,
                'stock'        => $adjustedStock,
                'balance'      => $product->quantity, // Remaining balance
                'price'        => $product->selling_price,
            ];

            $success = true;
        }

        // Insert reserve products record if any
        if (!empty($reserveProducts)) {
            \DB::table('reserve_products')->insert([
                'seller_id' => $request->seller_id,
                'data'      => json_encode($reserveProducts, JSON_UNESCAPED_UNICODE),
                'type'      => 3,
                'active'    => 2,
                'branch_id' => auth('admin')->user()->branch_id,
            ]);
        }

        // Example: Create an accounting transaction to record the stock expense
        $sellerAccount = Seller::find($request->seller_id);
        $branch = Branch::where('id', auth('admin')->user()->branch_id)->first();
        $payable_account_3 = Account::find($branch->account_stock_Id);
        $payable_account_to_3 = Account::find($sellerAccount->account_id);
        $payable_transaction = new Transection();
        $payable_transaction->tran_type = 555;
        $payable_transaction->seller_id = auth('admin')->user()->id;
        $payable_transaction->branch_id = auth('admin')->user()->branch_id;
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

        // Update account balances accordingly
        $payable_account_3->balance = round($payable_account_3->balance - $totalPriceAllProducts, 2);
        $payable_account_3->total_out += $totalPriceAllProducts;
        $payable_account_3->save();

        $payable_account_to_3->balance = round($payable_account_to_3->balance + $totalPriceAllProducts, 2);
        $payable_account_to_3->total_in += $totalPriceAllProducts;
        $payable_account_to_3->save();

        DB::commit();

        if ($success) {
            Toastr::success(translate('تم صرف مخزني بنجاح'));
        }

        return back();
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("Stock processing error: " . $e->getMessage());
        Toastr::error(translate('حدث خطأ أثناء صرف المخزون' . $e->getMessage()));
        return back();
    }
}






    public function edit(Request $request, $id)
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

    if (!in_array("export32.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
 $adminId = Auth::guard('admin')->id();

   $sellers = Seller::whereHas('adminSellers', function ($query) use ($adminId) {
        $query->where('admin_id', $adminId); // Filter by admin_id in admin_sellers table
    })->get();        $stock = $this->stock->find($id);
        $products = [];
        if($request->has('seller')) {
            $sel = $this->seller->find($request->seller);
            foreach($sel->cats as $c) {
                $id = $c->cat->id;
                $products[] = $this->product->where('product_type','product')->where('category_id', $id)->get();
            }

            return response()->json([
                'option' => $products
            ]);
        }
        else {
            foreach($stock->seller->cats as $c) {
                $id = $c->cat->id;
                $products[] = $this->product->where('product_type','product')->where('category_id', $id)->get();
            }
        }
        return view('admin-views.vehicle_stocks.edit', compact('sellers', 'products', 'stock'));
    }

    public function update(Request $request, $id): Factory|RedirectResponse|Application
    {
        $product = $this->product->find($request->product_id);
        $max = $product->quantity;
        $request->validate([
            'seller_id' => 'required',
            'product_id' => 'required',
            'stock' => "required|numeric|max:$max",
        ]);
        

        $stock = $this->stock->find($id);
        $stock->seller_id = $request->seller_id;
        $stock->product_id = $request->product_id;
        $stock->main_stock = $request->stock;
        $stock->stock = $request->stock;
        $stock->update();
        $new_stock = $product->quantity - $request->stock;
        $product->quantity = $new_stock;
        $product->update();

        Toastr::success(translate('تم تعديل أمر الصرف بنجاح'));
        return redirect()->route('admin.stock.index');
    }

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

    if (!in_array("export32.destroy", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    // Find the stock record
    $stock = $this->stock->find($request->id);
    if (!$stock) {
        Toastr::error(translate('Stock not found.'));
        return back();
    }

    // Find the associated product
    $product = $this->product->find($stock->product_id);
    if (!$product) {
        Toastr::error(translate('Product not found.'));
        return back();
    }
$adminBranchId = auth('admin')->user()->branch_id;
$branchColumn = "branch_" . $adminBranchId; // إنشاء اسم العمود الديناميكي

// الحصول على الكمية المتاحة بناءً على الفرع
if (isset($product->$branchColumn)) {

            // Calculate the new stock quantity
    $new_stock = (int)$product->$branchColumn + (int)$stock->stock;
    $product->$branchColumn = $new_stock;
    $product->update();
}else{
    
    // Calculate the new stock quantity
    $new_stock = (int)$product->quantity + (int)$stock->stock;
    $product->quantity = $new_stock;
    $product->update();
}    
$productLog = new ProductLog();
$productLog->product_id = $product->id;
$productLog->quantity = $stock->stock; // Store as string
$productLog->seller_id = auth('admin')->user()->id; // Store as string
$productLog->branch_id = auth('admin')->user()->branch_id; // Store as string
$productLog->type = 200; // Store as integer
$productLog->save();

    // Delete the stock record
    $stock->delete();

    // Notify success
    Toastr::success(translate('تم حذف المنتج من هذه الرحلة بنجاح'));
    return back();
}

    
    public function history(Request $request)
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

    if (!in_array("stock.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        // $limit = $request['limit'] ?? 10;
        // $offset = $request['offset'] ?? 1;
        // $date = $request['date'] ?? null;
        $search = $request->input('search');
        
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $stocks = $this->stock_order->latest()
                              ->with(['seller']);
    
        if (!empty($search)) {
            $stocks->where(function($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                      ->orWhereHas('seller', function($query) use ($search) {
                          $query->where('f_name', 'like', "%{$search}%")
                                ->orWhere('l_name', 'like', "%{$search}%");
                      });
            });
        }
    
        if (!empty($fromDate) && !empty($toDate)) {
            $stocks->whereBetween('created_at', [$fromDate, $toDate]);
        }
    
        $stocks = $stocks->paginate(Helpers::pagination_limit())->appends([
            'search' => $search,
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]);
        
        // $items = $stocks->items();
        foreach($stocks as $key => $item) {
            $item['statistcs'] = json_decode($item->statistcs);
        }
        
        // dd($stocks);
        // return $stocks[0]->statistcs->products[0]->price . ' ' . \App\CPU\Helpers::currency_symbol();
        return view('admin-views.pos.stocks.list', ['orders' => $stocks, 'fromDate' => $fromDate, 'toDate' => $toDate, 'search' => $search]);
        // return response()->json($data, 200);
    }
}
