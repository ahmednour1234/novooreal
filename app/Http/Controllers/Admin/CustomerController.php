<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\SellerCustomer;
use App\Models\Order;
use Brian2694\Toastr\Facades\Toastr;
use App\CPU\Helpers;
use App\Models\Account;
use App\Models\Seller;
use App\Models\Installment;
use App\Models\HistoryInstallment;
use App\Models\CustomerPrice;
use App\Models\Category;
use App\Models\Transection;
use App\Models\Guarantor;
use App\Models\Region;
use function App\CPU\translate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Auth;


class CustomerController extends Controller
{
    public function __construct(
        private CustomerPrice $price,
        private Customer $customer,
        private Region $region,
        private Order $order,
        private Account $account,
        private Transection $transection,
      private Category $category,
     private Installment $installment,
          private HistoryInstallment $history_installment
    ){}

    /**
     * @return Application|Factory|View
     */
      public function getDetails($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'error' => 'العميل غير موجود'
            ], 404);
        }

        return response()->json([
            'name'      => $customer->name,
            'c_history' => $customer->c_history,
            'tax_number'=> $customer->tax_number,
            'mobile'     => $customer->mobile,
            'credit'     => number_format($customer->credit-$customer->balance,2),
        ]);
    }
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

    if (!in_array("customer.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    $categories = $this->category->where('type', 0)->get(); // Fetch categories with type 0
    $regions = $this->region->get(); // Fetch categories with type 0

    return view('admin-views.customer.index', compact('categories','regions')); // Pass categories to the view
}
 public function getCustomerData($id)
    {
        $customer = Customer::find($id);

        if ($customer) {
            return response()->json([
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                // Add any other fields you need
            ]);
        }

        return response()->json(['error' => 'Customer not found'], 404);
    }
    /**
     * @param Request $request
     * @return RedirectResponse
     */
  public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'                    => 'required|unique:customers',
            'name_en'                 => 'nullable|unique:customers',
            'mobile'                  => 'required|unique:customers',
            'email'                   => 'nullable|email',
            'longitude'               => 'nullable',
            'latitude'                => 'nullable',
            'type'                    => 'nullable',
            'category_id'             => 'nullable|exists:categories,id',
            'region_id'               => 'nullable|exists:regions,id',
            // guarantor fields optional
            'guarantor_name'          => 'nullable|string|max:255',
            'guarantor_national_id'   => 'nullable|string|max:50',
            'guarantor_phone'         => 'nullable|string|max:20',
            'guarantor_address'       => 'nullable|string|max:255',
            'guarantor_job'           => 'nullable|string|max:100',
            'guarantor_monthly_income'=> 'nullable|numeric|min:0',
            'guarantor_relation'      => 'nullable|string|max:50',
            'guarantor_images.*'      => 'nullable|image|max:2048',
        ]);

        // Upload main customer image
        $image_name = Helpers::upload('customer/', 'png', $request->file('image'))
            ?? 'def.png';

        DB::transaction(function () use ($request, $image_name) {
            $guarantorId = null;

            // Create guarantor if name provided
            if ($request->filled('guarantor_name')) {
                $imagePaths = [];
                if ($request->hasFile('guarantor_images')) {
                    foreach ($request->file('guarantor_images') as $file) {
                        $imagePaths[] = $file->store('uploads/guarantors', 'public');
                    }
                }

                $guarantor = new Guarantor();
                $guarantor->name           = $request->guarantor_name;
                $guarantor->national_id    = $request->guarantor_national_id;
                $guarantor->phone          = $request->guarantor_phone;
                $guarantor->address        = $request->guarantor_address;
                $guarantor->job            = $request->guarantor_job;
                $guarantor->monthly_income = $request->guarantor_monthly_income;
                $guarantor->relation       = $request->guarantor_relation;
                $guarantor->images         = json_encode($imagePaths);
                $guarantor->save();

                $guarantorId = $guarantor->id;
            }

            // Create customer
            $customer = new Customer();
            $customer->name         = $request->name;
            $customer->name_en      = $request->name_en;
            $customer->region_id    = $request->region_id??1;
            $customer->mobile       = $request->mobile;
            $customer->email        = $request->email;
            $customer->image        = $image_name;
            $customer->state        = $request->state??1;
            $customer->city         = $request->city??1;
            $customer->zip_code     = $request->zip_code??'';
            $customer->address      = $request->address??'';
            $customer->longitude    = $request->longitude??'';
            $customer->latitude     = $request->latitude??'';
            $customer->type         = $request->type??1;
            $customer->category_id  = $request->category_id??1;
            $customer->tax_number   = $request->tax_number ?: 1;
            $customer->c_history    = $request->c_history ?: 1;
            $customer->guarantor_id = $guarantorId??null;
            $customer->save();

            // Link seller to customer
            $sellerCustomer = new SellerCustomer();
            $sellerCustomer->seller_id   = Auth::guard('admin')->id();
            $sellerCustomer->customer_id = $customer->id;
            $sellerCustomer->save();

            // Create initial financial transaction if credit provided
                $this->createFinancialTransaction($customer,0);
            
        });

        Toastr::success(translate('تمت إضافة العميل بنجاح'));
        return redirect()->back();
    }
/**
 * إنشاء الحساب والمعاملات المالية للعميل
 */
private function createFinancialTransaction(Customer $customer, $credit)
{
    DB::transaction(function () use ($customer, $credit) {
        // 1️⃣ **إنشاء الحساب المحاسبي**
        $lastAccount = Account::where('parent_id', 15)->latest('code')->first();
        $newCode = $lastAccount ? $lastAccount->code + 1 : 101;
$accountCode = Account::generateAccountCode('asset', 15);

        $account = new Account();
        $account->account = "حساب العميل: " . $customer->name;
        $account->description = "حساب العميل: " . $customer->name;
        $account->account_number = $accountCode;
        $account->parent_id = 15;
        $account->account_type = "asset";
        $account->code = $accountCode;
        $account->save();

        // 2️⃣ **ربط الحساب بالعميل**
        $customer->account_id = $account->id;
        $customer->save();

        // // 3️⃣ **إنشاء القيد المحاسبي**
        // $transaction = new Transection();
        // $transaction->seller_id = auth('admin')->user()->id;
        // $transaction->amount = $credit;
        // $transaction->tran_type = 0;
        // $transaction->account_id = $customer->account_id;
        // $transaction->account_id_to = 15; // الرصيد الافتتاحي

        // $transaction->description = 'رصيد افتتاحي';
        // $transaction->debit = $credit;
        // $transaction->credit = $credit;
        // $transaction->balance = $account->balance;
        // $transaction->balance_account = $account->balance;
        // $transaction->date = now();
        // $transaction->customer_id = $customer->id;
        // $transaction->save();
    });
}


    /**
     * @param Request $request
     * @return Application|Factory|View
     */
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

    if (!in_array("customer.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    // Fetch the accounts data
    $accounts = $this->account->orderBy('id')->get();

    // Initialize query parameters for pagination
    $query_param = [];

    // Get the search and specialization filter input
    $search = $request->input('search');
    $specialization_id = $request->input('specialist'); // Get specialization filter

    // Get the seller filter from the query string
    $sellerId = $request->input('seller');

    // Get the authenticated admin's ID
    $adminId = Auth::guard('admin')->id();

    // Fetch sellers linked to the authenticated admin through the admin_seller table
    $sellers = Seller::join('admin_sellers', 'admin_sellers.seller_id', '=', 'admins.id')
        ->where('admin_sellers.admin_id', $adminId)
        ->select('admins.id') // Select only seller IDs
        ->get()->pluck('id')->toArray(); // Convert to an array of seller IDs

    // Use the seller filter if provided and ensure it is part of the admin's sellers
    if ($sellerId && in_array($sellerId, $sellers)) {
        $sellers = [$sellerId]; // Limit to the specific seller
    }

    // Get the first seller ID or use the filtered seller
    $mandobId = $sellers[0] ?? null;

    // Get the name of the seller if $mandobId is not null
    $mandob = $mandobId ? Seller::find($mandobId) : null;

    // Use a default value if $mandob is null
    $mandobName = $mandob ? $mandob->f_name . ' ' . $mandob->l_name : 'كل المناديب';

    // Filter customers by seller_id through the seller_customers table
    $customersQuery = $this->customer->query();
    $customersQuery->whereIn('id', function ($query) use ($sellers) {
        $query->select('customer_id')
            ->from('seller_customers');    });

    // Apply search filter if provided
    if ($search) {
        $key = explode(' ', $search);
        $customersQuery->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%")
                  ->orWhere('mobile', 'like', "%{$value}%");
            }
        });
        $query_param['search'] = $search;
    }

    // Apply specialization filter if provided
    if ($specialization_id) {
        $customersQuery->where('specialist', $specialization_id);
        $query_param['specialist'] = $specialization_id;
    }

    // Get additional data for the view
    $walk_customer = $this->customer->where('id', 0)->first();
    $categories = $this->category->where('type', 0)->get();

    // Get the sum of balances, credits, and discounts without pagination
    $totalBalance = $this->customer->whereIn('id', function ($query) use ($sellers) {
        $query->select('customer_id')
            ->from('seller_customers');    })->sum('balance');

    $totalCredit = $this->customer->whereIn('id', function ($query) use ($sellers) {
        $query->select('customer_id')
            ->from('seller_customers');    })->sum('credit');

    $totalDiscount = $this->customer->whereIn('id', function ($query) use ($sellers) {
        $query->select('customer_id')
            ->from('seller_customers');    })->sum('discount');

    // Paginate the filtered customers with query parameters
    $customers = $customersQuery->paginate(Helpers::pagination_limit())->appends($query_param);

    // Return the view with the necessary data
    return view('admin-views.customer.list', compact(
        'customers',
        'accounts',
        'search',
        'walk_customer',
        'categories',
        'sellers',
        'mandob',
        'mandobName',
        'specialization_id',
        'totalBalance',
        'totalCredit',
        'totalDiscount'
    ));
}



public function export(Request $request)
{
    // Get the search parameter
    $search = $request->input('search');

    // Query customers with search functionality
    $query = $this->customer->query();
    if ($search) {
        $key = explode(' ', $search);
        $query->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%")
                  ->orWhere('mobile', 'like', "%{$value}%");
            }
        });
    }

    // Get the customers data
    $customers = $query->get([
        'name', 'mobile', 'email', 'address', 'pharmacy_name', 'state', 'city', 'zip_code', 'balance','tax_number','c_history'
    ]);

    // Export the data using FastExcel
    return (new FastExcel($customers))->download('customers.xlsx');
}

    public function prices(Request $request, $id)
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

    if (!in_array("customer.price.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    $price = $this->price;

    if ($request->has('price')) {
        $request->validate([
            'price' => 'required|numeric',
            'product_id' => 'required',
            'customer_id' => 'required',
        ]);

        // تحقق مما إذا كان السعر موجودًا بالفعل
        $existingPrice = $price->where('product_id', $request->product_id)
            ->where('customer_id', $request->customer_id)
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
            $price->customer_id = $request->customer_id;
            $price->save();
            Toastr::success(translate('تمت إضافة السعر بنجاح'));
        }

        return back();
    }

    $prices = $price->where('customer_id', $id)->latest()->paginate(Helpers::pagination_limit());
    return view('admin-views.customer.prices', compact('prices'), ['customer_id' => $id]);
}

    
    public function edit_price(Request $request, $customer_id, $price_id)
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

    if (!in_array("customer.price.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $price = $this->price->find($price_id);

        if($request->has('price')) {
            $request->validate([
                'price' => 'required|numeric',
                'product_id' => 'required',
                // 'customer_id' => 'required',
            ]);

            $price->price = $request->price;
            $price->product_id = $request->product_id;
            $price->customer_id = $customer_id;
            $price->update();

            Toastr::success(translate('تم إضافة السعر بنجاح'));
            return back();
        }

        return view('admin-views.customer.edit_price', compact('customer_id', 'price'));
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

    if (!in_array("customer.price.destroy", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $price = $this->price->find($id);
        $price->delete();
        Toastr::success(translate('تم حذف السعر بنجاح'));
        return back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return Application|Factory|View|RedirectResponse
     */
    public function view(Request $request, $id)
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

    if (!in_array("customer.show", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    $customer = $this->customer->where('id', $id)->first();
    if (isset($customer)) {
        $query_param = [];
        $search = $request['search'];
        $orderType = $request['order_type'];
        $startDate = $request['start_date'];
        $endDate = $request['end_date'];

        $orders = $this->order->where(['user_id' => $id]);

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $orders->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->where('id', 'like', "%{$value}%");
                }
            });
        }

        if ($orderType) {
            $orders->where('type', $orderType);
        }

        if ($startDate) {
            $orders->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $orders->where('created_at', '<=', $endDate);
        }

        $query_param = array_merge($query_param, [
            'search' => $search,
            'order_type' => $orderType,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $orders = $orders->latest()->paginate(Helpers::pagination_limit())->appends($query_param);
        return view('admin-views.customer.view', compact('customer', 'orders', 'search'));
    }

    Toastr::error('Customer not found!');
    return back();
}


    /**
     * @param Request $request
     * @param $id
     * @return Application|Factory|View|RedirectResponse
     */
public function transaction_list(Request $request, $id)
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

    if (!in_array("supplier.show", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $accounts = $this->account->get();
    $customer = $this->customer->where('id', $id)->first();
$accountcustomer=Account::where('id',$customer->account_id)->first();
    if (isset($customer)) {
        $acc_id = $request->input('account_id');
        $tran_type = $request->input('tran_type');
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');

        $orders = $this->order->where(['user_id' => $id])->get();

        // جلب جميع المعاملات المرتبطة بالحساب سواء في account_id أو account_id_to
        $transactions = $this->transection
            ->where(function ($query) use ($customer) {
                $query->where('account_id', $customer->account_id)
                      ->orWhere('account_id_to', $customer->account_id);
            })
            ->when($tran_type != null, function ($q) use ($tran_type) {
                return $q->where('tran_type', $tran_type);
            })
            ->when($from_date != null && $to_date != null, function ($q) use ($from_date, $to_date) {
                return $q->whereBetween('created_at', [$from_date, $to_date]);
            })
            ->paginate(Helpers::pagination_limit())
            ->appends([
                'account_id' => $acc_id,
                'tran_type' => $tran_type,
                'from_date' => $from_date,
                'to_date' => $to_date,
            ]);

        return view('admin-views.customer.transaction-list', compact(
            'customer',
            'transactions',
            'orders',
            'tran_type',
            'accounts',
            'acc_id',
            'from_date',
            'to_date',
            'accountcustomer'
        ));
    }

    Toastr::error(translate('Customer not found'));
    return back();
}



    /**
     * @param Request $request
     * @return Application|Factory|View
     */
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

    if (!in_array("customer.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $customer = $this->customer->where('id',$request->id)->first();
    
         $categories = $this->category->where('type', 0)->get(); // Fetch categories with type 0
    $regions = $this->region->get(); // Fetch categories with type 0
        return view('admin-views.customer.edit',compact('customer','categories','regions'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        // جلب العميل
        $customer = Customer::find($request->id);
        if (! $customer) {
            Toastr::error(translate('Customer not found'));
            return back();
        }

        // قواعد التحقق
        $rules = [
            'name'                    => 'required|unique:customers,name,' . $customer->id,
            'name_en'                 => 'nullable|unique:customers,name_en,' . $customer->id,
            'mobile'                  => 'required|unique:customers,mobile,' . $customer->id,
            'email'                   => 'nullable|email',
            'region_id'               => 'nullable|exists:regions,id',
            'tax_number'              => 'nullable|string',
            'c_history'               => 'nullable|string',
            'image'                   => 'nullable|image|max:2048',
            'city'                    => 'nullable|string',
            'zip_code'                => 'nullable|string',
            'address'                 => 'nullable|string',
            'lat'                     => 'nullable|numeric',
            'lng'                     => 'nullable|numeric',
            'type'                    => 'nullable',
            'category_id'             => 'nullable|exists:categories,id',
            // guarantor optional fields
            'guarantor_name'          => 'nullable|string|max:255',
            'guarantor_national_id'   => 'nullable|string|max:50',
            'guarantor_phone'         => 'nullable|string|max:20',
            'guarantor_address'       => 'nullable|string|max:255',
            'guarantor_job'           => 'nullable|string|max:100',
            'guarantor_monthly_income'=> 'nullable|numeric|min:0',
            'guarantor_relation'      => 'nullable|string|max:50',
            'guarantor_images.*'      => 'nullable|image|max:2048',
        ];
        $request->validate($rules);

        DB::transaction(function () use ($request, $customer) {
            // تحديث بيانات العميل
            $customer->name         = $request->name;
            $customer->name_en      = $request->name_en;
            $customer->mobile       = $request->mobile;
            $customer->email        = $request->email;
            $customer->region_id    = $request->region_id;
            $customer->tax_number   = $request->tax_number;
            $customer->c_history    = $request->c_history;
            // تحديث صورة العميل
            if ($request->hasFile('image')) {
                $customer->image = Helpers::update('customer/', $customer->image, 'png', $request->file('image'));
            }
            $customer->city         = $request->city;
            $customer->zip_code     = $request->zip_code;
            $customer->address      = $request->address;
            $customer->latitude     = $request->lat;
            $customer->longitude    = $request->lng;
            $customer->type         = $request->type;
            $customer->category_id  = $request->category_id;
            $customer->save();

            // تحديث أو إنشاء بيانات الضامن إن وُجد اسمه
            if ($request->filled('guarantor_name')) {
                $guarantor = $customer->guarantor ?? new Guarantor();
                // لو يوجد ضامن سابق
                $guarantor->name           = $request->guarantor_name;
                $guarantor->national_id    = $request->guarantor_national_id;
                $guarantor->phone          = $request->guarantor_phone;
                $guarantor->address        = $request->guarantor_address;
                $guarantor->job            = $request->guarantor_job;
                $guarantor->monthly_income = $request->guarantor_monthly_income;
                $guarantor->relation       = $request->guarantor_relation;
                // دمج مرفقات الضامن القديمة والجديدة
                $oldImages = json_decode($guarantor->images ?? '[]', true);
                if ($request->hasFile('guarantor_images')) {
                    foreach ($request->file('guarantor_images') as $file) {
                        $oldImages[] = $file->store('uploads/guarantors', 'public');
                    }
                }
                $guarantor->images = json_encode($oldImages);
                $guarantor->save();

                // ربط الضامن بالعميل
                $customer->guarantor_id = $guarantor->id;
                $customer->save();
            }
        });

        Toastr::success(translate('تم تحديث بيانات العميل بنجاح'));
        return redirect()->route('admin.customer.list');
    }
public function status(Request $request): RedirectResponse
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

    if (!in_array("customer.show", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    $customer = $this->customer->find($request->id);
    
    // Toggle the active status (if 0, set 1; if 1, set 0)
    $customer->active = $customer->active ? 0 : 1;
    
    $customer->save();

    Toastr::success(translate('تم تغيير حالة العميل'));

    return back();
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

    if (!in_array("customer.destroy", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $customer = $this->customer->find($request->id);
        Helpers::delete('customer/' . $customer['image']);
        $customer->delete();

        Toastr::success(translate('تم حذف العميل بنجاح'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
     
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

    if (!in_array("customer.balance", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    // Validate the incoming request data
    $request->validate([
        'customer_id' => 'required',
        'amount' => 'required|numeric|min:0',
        'account_id' => 'required',
        'date' => 'required|date',
        'description' => 'nullable|string', // Description is optional
        'img' => 'required', // Ensure image is required and valid
    ]);

    // Image upload logic
  $img = null;
   
     if ($request->hasFile('img')) {
        $img = $request->file('img')->store('shop', 'public'); // Store the image
        $fileName = $request->file('img')->getClientOriginalName(); // Get original filename (optional)
    }
    // Retrieve customer and account information
    $customer = $this->customer->find($request->customer_id);
    $amount = $request->amount;
    $account = Account::find($request->account_id);
    $seller_id = auth('admin')->id(); // Get the admin ID from the authenticated admin user

    if ($account && $customer) {
        // Check if the account balance is sufficient
        if ($account->balance >= $amount) {
            // Process the transaction
            $transaction = new Transection();
            $transaction->tran_type = 13;
            $transaction->account_id = $account->id;
            $transaction->amount = 0;
            $transaction->description = $request->description;
            $transaction->debit =0;
            $transaction->credit = $amount ;
                $transaction->branch_id =  auth('admin')->user()->branch_id;
            $transaction->balance = $customer->credit + $amount- $customer->balance- $customer->discount; // Update balance after deduction
                   $transaction->debit_account =0;
                    $transaction->credit_account = $amount;
                    $transaction->balance_account = $account->total_in-$account->total_out-$amount;
            $transaction->date = $request->date;
            $transaction->customer_id = $request->customer_id;
            $transaction->seller_id = $seller_id;
            $transaction->img = $img;
            $transaction->save();

            // Update account and customer balances
            $account->total_out += $amount;
            $account->balance -= $amount;
            $account->save();

            $customer->credit += $amount;
            $customer->save();

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

    if (!in_array("customer.debit", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    $request->validate([
        'customer_id' => 'required',
        'amount' => 'required|numeric|min:0',
        'account_id' => 'required',
        'date' => 'required|date',
        'description' => 'nullable|string',
    ]);

    $customer = $this->customer->find($request->customer_id);
    $amount = $request->amount;
    $account = Account::find($request->account_id);
    $seller_id = auth('admin')->id(); // Get the admin ID from the authenticated admin user
    $img = null;

    if ($request->hasFile('img')) {
        $img = $request->file('img')->store('shop', 'public'); // Store the image
    }

    DB::beginTransaction(); // Start the transaction

    try {
        // $order = Order::findOrFail($request->order_id);

        // // Calculate the transaction reference (total paid so far)
        // $transaction_reference = Order::where('id', $order->id)
        //     ->sum('transaction_reference');

        // // Add the new payment to the transaction reference
        // $new_transaction_reference = $transaction_reference + $request->amount;

        // // Check order payment status
        // if ($new_transaction_reference == $order->order_amount) {
        //     $status = 'تم تحصيل كامل المبلغ';
        // } elseif ($new_transaction_reference < $order->order_amount) {
        //     $remaining = $order->order_amount - $new_transaction_reference;
        //     $status = "جزء من المبلغ تم تحصيله. الباقي: $remaining";
        // } else {
        //     return redirect()->back()->withErrors(['error' => 'هذه الفاتورة تم تحصيلها بالكامل']);
        // }

        // // Check if the customer's credit is sufficient
        // if ($customer->credit < $request->price) {
        //     return redirect()->back()->withErrors(['error' => 'المبلغ المحصل أكبر من مديونية العميل']);
        // }

        // $order->transaction_reference += $request->amount;
        // $order->save();

        // Create installment record
        $installment = $this->installment;
        $installment->seller_id = $seller_id;
        $installment->customer_id = $request->customer_id;
                                $installment->branch_id =  auth('admin')->user()->branch_id;
        $installment->total_price = $request->amount;
        $installment->note = $request->description;
        $installment->img = $img;
        $installment->save();

        // Create history installment record
        $history_installment = $this->history_installment;
        $history_installment->seller_id = $seller_id;
                        $history_installment->branch_id =  auth('admin')->user()->branch_id;
        $history_installment->customer_id = $request->customer_id;
        $history_installment->total_price = $request->amount;
        $history_installment->note = $request->description;
        $history_installment->img = $img;
        $history_installment->save();

        // Process the transaction
        $transaction = new Transection;
        $transaction->tran_type = 26;
        $transaction->account_id = $account->id;
        $transaction->amount = 0;
        $transaction->description = $request->description;
        $transaction->debit = $amount;
        $transaction->credit =  0;
        $transaction->balance = $customer->credit - $amount-$customer->balance -$customer->discount ;
                        $transaction->branch_id =  auth('admin')->user()->branch_id;

             $transaction->debit_account =$amount;
                    $transaction->credit_account = 0;
                    $transaction->balance_account = $account->total_in-$account->total_out+$amount;
        $transaction->date = $request->date;
        $transaction->customer_id = $request->customer_id;
        $transaction->seller_id = $seller_id;
        $transaction->img = $img;
        $transaction->save();

        // Update account balance
        $account->total_in += $amount;
        $account->balance += $amount;
        $account->save();

        // Update customer credit
        $customer->balance += $amount;
        $customer->save();

        DB::commit(); // Commit the transaction

        Toastr::success(translate('تم دفع النقدية'));
    } catch (\Exception $e) {
        DB::rollBack(); // Rollback transaction
        return redirect()->back()->withErrors(['error' => 'لم يتم دفع النقدية: ' . $e->getMessage()]);
    }

    return redirect()->back()->with('success', 'تم تحديث الرصيد بنجاح');
}

public function extra_discount(Request $request)
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

    if (!in_array("customer.discount", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    $request->validate([
        // 'supplier_id' => 'required',
        'amount' => 'required|numeric|min:0',
        'date' => 'required|date',
        'description' => 'nullable|string',
    ]);

    $supplier = $this->customer->find($request->customer_id);
    $amount = $request->amount;
    $seller_id = auth('admin')->id(); // Get the admin ID from the authenticated admin user

    DB::beginTransaction(); // Start the transaction
     $img = null;
    if ($request->hasFile('img')) {
        $img = Helpers::update('shop/', null, 'png', $request->file('img'));
    }

    try {
    
            // Create installment record

            
        

            // Process the transaction
            $transaction = new Transection;
            $transaction->tran_type = 30;
            $transaction->amount = 0;
            $transaction->description = $request->description;
            $transaction->branch_id =  auth('admin')->user()->branch_id;
            $transaction->debit = $amount;
            $transaction->credit = 0;
           $transaction->balance = $supplier->credit -$amount- $supplier->balance-$supplier->discount;
            $transaction->date = $request->date;
            $transaction->customer_id = $request->customer_id;
            $transaction->seller_id = $seller_id;
            $transaction->img = $img;
            $transaction->save();


            // Update customer credit
                        $supplier->discount += $amount;
            $supplier->save();

            DB::commit(); // Commit the transaction

            Toastr::success(translate('تم إضافة خصم مكتسب'));
 
    } catch (\Exception $e) {
        DB::rollBack(); // Rollback transaction
        Toastr::error(translate('لم يتم دفع النقدية: ') . $e->getMessage()); // Show error message
    }

    return redirect()->back(); // Redirect back after processing
}
 public function generate_expense_invoice($id)
    {
        $expense = $this->transection->find($id);

        return response()->json([
            'success' => 1,
            'view' => view('admin-views.customer.invoice', compact('expense'))->render(),
        ]);
    }
}
