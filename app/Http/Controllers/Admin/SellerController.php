<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Seller;
use App\Models\Order;
use Brian2694\Toastr\Facades\Toastr;
use App\CPU\Helpers;
use App\Models\Account;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Region;
use App\Models\SellerCategory;
use App\Models\SellerCustomer;
use App\Models\SellerRegion;
use App\Models\StorageSeller;
use App\Models\AdminSeller;
use App\Models\Storage;
use App\Models\SellerPrice;
use App\Models\Shift;
use App\Models\Branch;
use App\Models\Store;
use App\Models\Installment;
use App\Models\HistoryInstallment;
use App\Models\Transection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


use function App\CPU\translate;

class SellerController extends Controller
{
    public function __construct(
        private Seller $seller,
        private SellerCategory $cat,
        private SellerCustomer $cus,
    private StorageSeller $storages,
        private SellerRegion $region,
        private SellerPrice $price,
        private Store $vehicle,
        private Category $category,
        private Customer $customer,
                private Account $account,
                private Transection $transection,
     private Installment $installment,
          private HistoryInstallment $history_installment
    ){}

    public function index()
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

    if (!in_array("seller.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $regions = Region::all();
        $categories = $this->category->where(['position' => 0])->where('type',1)->where('status',1)->get();
        $customers = $this->customer->get();
        $storages = Storage::all();
        $vehicles = $this->vehicle->whereNull('seller_id')->get();
        $branches = Branch::where('active',1)->get();
        $shifts =  Shift::where('active',1)->get();
        return view('admin-views.seller.index', compact('regions', 'categories', 'vehicles','customers','storages','branches','shifts'));
    }

  public function store(Request $request): RedirectResponse
    {
        // Validate the input data
        $request->validate([
            'f_name'            => 'required',
            'l_name'            => 'required',
            'email'             => 'required|email',
            'cats'              => 'required|array',
            'regions'           => '',
            'password'          => 'required',
            'mandob_code'       => 'required',
            'vehicle_code'      => 'required',
            'type'              => 'required',
            'salary'            => 'required|numeric',
            'precent_of_sales'  => 'required|numeric',
            'latitude'          => 'nullable',
            'longitude'         => 'nullable',
        ]);

        DB::beginTransaction();

        try {
            // Create a new seller record
            $seller = new Seller();
            $seller->f_name           = $request->f_name;
            $seller->l_name           = $request->l_name;
            $seller->email            = $request->email;
            $seller->password         = Hash::make($request->password);
            $seller->mandob_code      = $request->mandob_code;
            $seller->vehicle_code     = $request->vehicle_code;
            $seller->type             = $request->type;
            $seller->salary           = $request->salary;
            $seller->precent_of_sales = $request->precent_of_sales;
            $seller->latitude         = $request->latitude ?? 0;
            $seller->longitude        = $request->longitude ?? 0;
            $seller->holidays         = $request->holidays;
            $seller->branch_id        = $request->branch_id;
$seller->shift_id = json_encode($request->shift_id); // نخزنها كـ JSON string

            // Set permissions (if checkbox is checked, set to 1; otherwise, 0)
            $permissions = ['dashboard', 'stock', 'store', 'admin', 'pos', 'sales'];
            foreach ($permissions as $permission) {
                $seller->$permission = $request->has($permission) ? 1 : 0;
            }

            $seller->role = 'seller';
            $seller->save();

            // Update the seller_id in the vehicle table based on vehicle_code
            $this->vehicle->where('store_id', $request->vehicle_code)
                ->update(['seller_id' => $seller->id]);

            // Save seller categories
            foreach ($request->cats as $catId) {
                $sellerCategory = new SellerCategory();
                $sellerCategory->seller_id = $seller->id;
                $sellerCategory->cat_id    = $catId;
                $sellerCategory->save();
            }

            // Save seller customers
            foreach ($request->customers as $customerId) {
                $sellerCustomer = new SellerCustomer();
                $sellerCustomer->seller_id   = $seller->id;
                $sellerCustomer->customer_id = $customerId;
                $sellerCustomer->save();
            }

            // Save seller storage information
  

            // Save seller regions
            foreach ($request->regions as $regionId) {
                $sellerRegion = new SellerRegion();
                $sellerRegion->seller_id = $seller->id;
                $sellerRegion->region_id = $regionId;
                $sellerRegion->save();
            }

            // Associate the seller with the current admin in admin_sellers table
            $adminId = Auth::guard('admin')->id();
            $adminSeller = new AdminSeller();
            $adminSeller->admin_id  = $adminId;
            $adminSeller->seller_id = $seller->id;
            $adminSeller->save();

            DB::commit();

            // Create financial account and (optionally) an initial transaction for the seller
            $this->createFinancialTransaction($seller, 0);

            Toastr::success(translate('تم اضافة المندوب بنجاح'));
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error adding seller: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An error occurred while adding the seller. Please try again. ' . $e->getMessage()]);
        }
    }

    private function createFinancialTransaction(Seller $seller, $credit)
    {
        DB::transaction(function () use ($seller, $credit) {
            // Generate a new account code under the parent account with id 76
            $lastAccount = Account::where('id', 76)->latest('code')->first();
            $newCode     = $lastAccount ? $lastAccount->code + 1 : 101;
            $accountCode = Account::generateAccountCode('asset', 76);

            // Create the seller's financial account
            $account = new Account();
            $account->account        = "حساب المندوب: " . $seller->f_name . " " . $seller->l_name;
            $account->description    = "حساب المندوب: " . $seller->f_name;
            $account->account_number = $accountCode;
            $account->parent_id      = 76;
            $account->account_type   = "asset";
            $account->code           = $accountCode;
            $account->save();

            // Associate the account with the seller (adminSeller)
            $seller->account_id = $account->id;
            $seller->save();

        });
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

    if (!in_array("seller.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    $query_param = [];
    $adminId = Auth::guard('admin')->id(); // Get the authenticated admin's ID
            $accounts = $this->account->orderBy('id')->get();

    // Get the sellers linked to the authenticated admin through the admin_seller table
    $sellers = $this->seller
                    ->join('admin_sellers', 'admins.id', '=', 'admin_sellers.seller_id')
                    ->where('admin_sellers.admin_id', $adminId) // Filter by the authenticated admin
                    ->where('admins.role', 'seller'); // Ensure that only sellers are retrieved

    // Search functionality
    $search = $request['search'];
    if ($request->has('search')) {
        $key = $request['search'];
        $sellers = $sellers->where(function ($q) use ($key) {
            $q->orWhere('f_name', 'like', "%{$key}%")
              ->orWhere('l_name', 'like', "%{$key}%")
              ->orWhere('email', 'like', "%{$key}%"); // Add more columns if needed
        });
        $query_param = ['search' => $request['search']];
    }

    // Paginate the sellers
    $sellers = $sellers->paginate(Helpers::pagination_limit())->appends($query_param);

    return view('admin-views.seller.list', compact('sellers', 'search','accounts'));
}


public function prices(Request $request, $id): View|Factory|Application|RedirectResponse
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

    if (!in_array("seller.price.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    $price = $this->price;

    $products = [];

    foreach (Seller::find($id)->cats as $item) {
        $products = array_merge($item->cat->products->toArray(), $products);
    }

    if ($request->has('price')) {
        $request->validate([
            'price' => 'required|numeric',
            'product_id' => 'required',
            'seller_id' => 'required',
        ]);

        // تحقق مما إذا كان السعر موجودًا بالفعل
        $existingPrice = $price->where('product_id', $request->product_id)
            ->where('seller_id', $request->seller_id)
            ->first();

        if ($existingPrice) {
            // إذا كان السعر موجودًا، قم بتحديثه
            $existingPrice->price = $request->price;
            $existingPrice->save();
            Toastr::success(translate('تم تحديث السعر بنجاح'));
        } else {
            // إذا لم يكن السعر موجودًا، قم بإنشائه
            $price->price = $request->price;
            $price->product_id = $request->product_id;
            $price->seller_id = $request->seller_id;
            $price->save();
            Toastr::success(translate('تم اضافة السعر بنجاح'));
        }

        return back();
    }

    $prices = $price->where('seller_id', $id)->latest()->paginate(Helpers::pagination_limit());
    return view('admin-views.seller.prices', compact('prices'), ['seller_id' => $id, 'products' => $products]);
}

    
    public function edit_price(Request $request, $seller_id, $price_id): View|Factory|Application|RedirectResponse
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

    if (!in_array("seller.price.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $price = $this->price->find($price_id);

        if($request->has('price')) {
            $request->validate([
                'price' => 'required|numeric',
                'product_id' => 'required',
            ]);

            $price->price = $request->price;
            $price->product_id = $request->product_id;
            $price->seller_id = $seller_id;
            $price->update();

            Toastr::success(translate('تم اضافة سعر بنجاح'));
            return back();
        }

        return view('admin-views.seller.edit_price', compact('seller_id', 'price'));
    }

    public function delete_price($id): View|Factory|Application|RedirectResponse
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

    if (!in_array("seller.price.destroy", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $price = $this->price->find($id);
        $price->delete();
        Toastr::success(translate('تم حذف سعر منتج بنجاح'));
        return back();
    }

    public function edit(Request $request)
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

    if (!in_array("seller.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $regions = Region::all();
        $categories = $this->category->where(['position' => 0])->where('type',1)->where('status',1)->get();
        $customers = $this->customer->get();
        $seller = $this->seller->find($request->id);
                $storages = Storage::all();
        $vehicles = $this->vehicle->whereNull('seller_id')->orWhere('seller_id', $request->id)->get();
            $branches = Branch::where('active',1)->get();
        $shifts =  Shift::where('active',1)->get();
        // dd($vehicles);
        return view('admin-views.seller.edit',compact('seller', 'regions', 'categories','customers','vehicles','storages','branches','shifts'));
    }
public function update(Request $request, $id): RedirectResponse
{
    $seller = $this->seller->find($id);

    $request->validate([
        'f_name' => 'required',
        'l_name' => 'required',
        'email' => 'required|email|unique:admins,email,' . $seller->id,
        'cats' => 'required',
        'regions' => 'required',
        'mandob_code' => 'required',
        'vehicle_code' => 'required',
        'type' => 'required',
        'salary' => 'required',
        'precent_of_sales' => 'required',
        'customers' => 'required',
    ]);

    $this->vehicle->where('seller_id', $id)->update(['seller_id' => null]);

    $seller->f_name = $request->f_name;
    $seller->l_name = $request->l_name;
    $seller->email = $request->email;
    $seller->mandob_code = $request->mandob_code;
    $seller->vehicle_code = $request->vehicle_code;
    $seller->type = $request->type;
        $seller->salary = $request->salary;
    $seller->precent_of_sales = $request->precent_of_sales;
        $seller->holidays = $request->holidays;
            $seller->branch_id = $request->branch_id;
$seller->shift_id = json_encode($request->shift_id); // نخزنها كـ JSON string

    $permissions = ['dashboard', 'stock', 'store', 'admin', 'pos', 'sales'];

    foreach ($permissions as $permission) {
        $seller->$permission = $request->has($permission) ? 1 : 0;
    }

    if ($request->password) {
        $seller->password = Hash::make($request->password);
    }

    $seller->update();
    $this->vehicle->where('store_id', $request->vehicle_code)->update(['seller_id' => $id]);

    // Update customers
    $this->cus->where('seller_id', $id)->delete();
    $customers = $request->customers ? (array) $request->customers : [];
    $validCustomers = \App\Models\Customer::whereIn('id', $customers)->pluck('id')->toArray();
    $currentCustomers = $seller->customers->pluck('customer_id')->toArray();
    $customersToAdd = array_diff($validCustomers, $currentCustomers);
    $customersToRemove = array_diff($currentCustomers, $validCustomers);

    foreach ($customersToRemove as $customerId) {
        $this->cus::where('seller_id', $id)->where('customer_id', $customerId)->delete();
    }

    foreach ($customersToAdd as $customerId) {
        $cus = new $this->cus;
        $cus->seller_id = $id;
        $cus->customer_id = $customerId;
        $cus->save();
    }

    // Update regions
    $this->region->where('seller_id', $id)->delete();
    foreach ((array) $request->regions as $item) {
        $region = new $this->region;
        $region->seller_id = $id;
        $region->region_id = $item;
        $region->save();
    }


    // Update categories
    $this->cat->where('seller_id', $id)->delete();
    foreach ((array) $request->cats as $cat) {
        $category = new $this->cat;
        $category->seller_id = $id;
        $category->cat_id = $cat;
        $category->save();
    }

    Toastr::success(translate('تم تحديث بيانات المندوب بنجاح'));
    return redirect()->route('admin.seller.list');
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

    if (!in_array("seller.destroy", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $seller = $this->seller->find($request->id);
        $seller->delete();

        Toastr::success(translate('تم حذف المندوب بنجاح'));
        return back();
    }
    public function update_balance(Request $request): RedirectResponse
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

    if (!in_array("seller.balance", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    // Validate the incoming request data
    $request->validate([
        'seller_id' => 'required',
        'amount' => 'required|numeric|min:0',
        'account_id' => 'required',
        'date' => 'required|date',
        'description' => 'nullable|string', // Description is optional
        'img' => 'required', // Ensure image is required and valid
    ]);

    // Image upload logic
    $img = null;
    if ($request->hasFile('img')) {
        // dd('ahmed');
        $img = Helpers::update('shop/', null, 'png', $request->file('img'));
    }

    // Retrieve customer and account information
    $seller = $this->seller->find($request->seller_id);
    $amount = $request->amount;
    $account = Account::find($request->account_id);

    if ($account && $seller) {
        // Check if the account balance is sufficient
        if ($account->balance >= $amount) {
            // Process the transaction
            $transaction = new Transection();
            $transaction->tran_type = 13;
            $transaction->account_id = $account->id;
            $transaction->amount = $amount;
            $transaction->description = $request->description;
            $transaction->debit_account = $amount;
            $transaction->credit_account = 0;
            $transaction->balance_account = $account->balance - $amount; // Update balance after deduction
            $transaction->date = $request->date;
            $transaction->seller_id = $request->seller_id;
            $transaction->img = $img;
            $transaction->save();

            // Update account and customer balances
            $account->total_out += $amount;
            $account->balance -= $amount;
            $account->save();

            $seller->balance -= $amount;
            $seller->save();

            Toastr::success(translate('تم استلام النقدية'));
        } else {
            // Handle insufficient balance in the account
            Toastr::error(translate('المبلغ المتواجد في هذا الحساب اقل من المبلغ اللي تريد تسليمه لهذا العميل'));
        }
    } else {
        // Handle missing customer or account
        Toastr::error(translate('الحساب أو العميل غير موجود'));
    }

    return back();
}

public function update_credit(Request $request)
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

    if (!in_array("seller.debit", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    $request->validate([
        'seller_id' => 'required',
        'amount' => 'required|numeric|min:0',
        'account_id' => 'required',
        'date' => 'required|date',
        'description' => 'nullable|string',
    ]);

    $seller = $this->seller->find($request->seller_id);
    $amount = $request->amount;
    $account = Account::find($request->account_id);
 $img = null;
    if ($request->hasFile('img')) {
        $img = Helpers::update('shop/', null, 'png', $request->file('img'));
    }
    DB::beginTransaction(); // Start the transaction

    try {

          

            // Process the transaction
            $transaction = new Transection;
            $transaction->tran_type = 26;
            $transaction->account_id = $account->id;
            $transaction->amount = $amount;
            $transaction->description = $request->description;
            $transaction->debit_account =$amount;
            $transaction->credit_account = 0;
            $transaction->balance_account = $account->balance + $amount;
            $transaction->date = $request->date;
            $transaction->seller_id = $request->seller_id;
            $transaction->img = $img;
            $transaction->save();

            // Update account balance
            $account->total_in += $amount;
            $account->balance += $amount;
            $account->save();

            // Update customer credit
            $seller->credit -= $amount;
            $seller->save();

            DB::commit(); // Commit the transaction

            Toastr::success(translate('تم دفع النقدية'));
     
    } catch (\Exception $e) {
        DB::rollBack(); // Rollback transaction
        Toastr::error(translate('لم يتم دفع النقدية: ') . $e->getMessage()); // Show error message
    }

    return redirect()->back(); // Redirect back after processing
}
public function update_loan(Request $request)
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

    if (!in_array("seller.loan", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    $request->validate([
        'seller_id' => 'required',
        'amount' => 'required|numeric|min:0',
        'account_id' => 'required',
        'date' => 'required|date',
        'description' => 'nullable|string',
    ]);

    $seller = $this->seller->find($request->seller_id);
    $amount = $request->amount;
    $account = Account::find($request->account_id);
 $img = null;
    if ($request->hasFile('img')) {
        $img = Helpers::update('shop/', null, 'png', $request->file('img'));
    }
    DB::beginTransaction(); // Start the transaction

    try {
        if ($account->balance >= $amount) {
          

            // Process the transaction
            $transaction = new Transection;
            $transaction->tran_type = 34;
            $transaction->account_id = $account->id;
            $transaction->amount = $amount;
            $transaction->description = $request->description;
            $transaction->debit_account =0;
            $transaction->credit_account =$amount ;
            $transaction->balance_account = $account->balance - $amount;
            $transaction->date = $request->date;
            $transaction->seller_id = $request->seller_id;
            $transaction->img = $img;
            $transaction->save();

            // Update account balance
            $account->total_out += $amount;
            $account->balance -= $amount;
            $account->save();

            // Update customer credit
            $seller->loan += $amount;
            $seller->save();

            DB::commit(); // Commit the transaction

            Toastr::success(translate('تم دفع السلفة'));
        } else {
            Toastr::error(translate('الرصيد غير كافٍ')); // Not enough balance
        }
    } catch (\Exception $e) {
        DB::rollBack(); // Rollback transaction
        Toastr::error(translate('لم يتم دفع النقدية: ') . $e->getMessage()); // Show error message
    }

    return redirect()->back(); // Redirect back after processing
}
}
