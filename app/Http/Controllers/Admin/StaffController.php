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
use App\Models\AdminDetail;
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
use App\Models\Admin;
use App\Models\Shift;
use App\Models\Branch;
use App\Models\SellerPrice;
use App\Models\Store;
use App\Models\Installment;
use App\Models\HistoryInstallment;
use App\Models\Transection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


use function App\CPU\translate;

class StaffController extends Controller
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
                                private Admin $admin,
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

    if (!in_array("staff.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $regions = Region::all();
        $categories = $this->category->where(['position' => 0])->where('type',1)->where('status',1)->get();
        $customers = $this->customer->get();
        $storages = Storage::all();
            $branches = Branch::where('active',1)->get();
        $shifts =  Shift::where('active',1)->get();

        $vehicles = $this->vehicle->whereNull('seller_id')->get();
        return view('admin-views.staff.index', compact('regions', 'categories', 'vehicles','customers','storages','branches','shifts'));
    }

public function store(Request $request): RedirectResponse
{
    // التحقق من صحة البيانات الأساسية والإضافية معاً
    $request->validate([
        'f_name'            => 'required',
        'l_name'            => 'required',
        'email'             => 'required|email',
        'password'          => 'required',
        'salary'            => 'required|numeric',
        'phone'             => 'nullable',
        'department'        => 'nullable|string',
        'job_title'         => 'nullable|string',
        'hire_date'         => 'nullable|date',
        'qualifications'    => 'nullable|string',
        'contract_details'  => 'nullable|string',
        // إضافة التحقق من الحقول الخاصة بالفروع والشفت
        'branch_id'         => 'required',
        'shift_id'          => 'required',
    ]);

    // التحقق من صلاحية Admin والـ Role
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
    if (!in_array("staff.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    DB::beginTransaction();
    try {
        // إنشاء سجل الموظف (Seller)
        $seller = new Seller;
        $seller->f_name   = $request->f_name;
        $seller->l_name   = $request->l_name;
        $seller->email    = $request->email;
        $seller->mandob_code    = $request->mandob_code;
        $seller->password = Hash::make($request->password);
        $seller->role     = 'staff';
        $seller->salary   = $request->salary;
        $seller->holidays = $request->holidays; // إن وُجد
        // تعيين بيانات الفرع والشفت
        $seller->branch_id = $request->branch_id;
$seller->shift_id = json_encode($request->shift_id); // نخزنها كـ JSON string
        $seller->save();

        // إضافة الموظف إلى جدول admin_sellers لربطه بالمشرف الحالي
        $adminSeller = new AdminSeller;
        $adminSeller->admin_id  = $adminId;
        $adminSeller->seller_id = $seller->id;
        $adminSeller->save();

        // إنشاء سجل للبيانات الإضافية للموظف (SellerDetail)
        $sellerDetail = new AdminDetail;
        $sellerDetail->admin_id        = $seller->id;
        $sellerDetail->full_name        = $seller->f_name . ' ' . $seller->l_name;
        $sellerDetail->phone            = $request->phone;
        $sellerDetail->department       = $request->department;
        $sellerDetail->job_title        = $request->job_title;
        $sellerDetail->hire_date        = $request->hire_date;
        $sellerDetail->qualifications   = $request->qualifications;
        $sellerDetail->contract_details = $request->contract_details;
        $sellerDetail->save();

        DB::commit();
        Toastr::success(translate('تم اضافة الموظف بنجاح'));
        return redirect()->back();

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error adding seller: ' . $e->getMessage());
        return redirect()->back()->withErrors(['error' => 'An error occurred while adding the seller. Please try again.'.$e->getMessage()]);
    }
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

    if (!in_array("staff.index", $decodedData)) {
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
                    ->where('admins.role', 'staff'); // Ensure that only sellers are retrieved

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

    return view('admin-views.staff.list', compact('sellers', 'search','accounts'));
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

    if (!in_array("staff.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $regions = Region::all();
        $categories = $this->category->where(['position' => 0])->where('type',1)->where('status',1)->get();
        $customers = $this->customer->get();
        $seller = $this->admin->find($request->id);
                $storages = Storage::all();
        $vehicles = $this->vehicle->whereNull('seller_id')->orWhere('seller_id', $request->id)->get();
                    $branches = Branch::where('active',1)->get();
        $shifts =  Shift::where('active',1)->get();
        // dd($vehicles);
        return view('admin-views.staff.edit',compact('seller', 'regions', 'categories','customers','vehicles','storages','branches','shifts'));
    }
public function update(Request $request, $id): RedirectResponse
{
    // إيجاد الموظف أو إعادة خطأ 404 إن لم يُوجد
    $seller = $this->seller->findOrFail($id);

    // التحقق من صحة البيانات الواردة
    $request->validate([
        'f_name'            => 'required|string|max:255',
        'l_name'            => 'required|string|max:255',
        'email'             => 'required|email|unique:admins,email,' . $seller->id,
        'salary'            => 'required|numeric',
        'latitude'          => 'nullable',
        'longitude'         => 'nullable',
        'branch_id'         => 'required',
        'shift_id'          => 'required',
        // الحقول الإضافية
        'phone'             => 'nullable|string|max:50',
        'department'        => 'nullable|string|max:100',
        'job_title'         => 'nullable|string|max:100',
        'hire_date'         => 'nullable|date',
        'qualifications'    => 'nullable|string',
        'contract_details'  => 'nullable|string',
    ]);

    // في حال كان هناك أي مركبات مرتبطة بالموظف، نقوم بفك العلاقة
    $this->vehicle->where('seller_id', $id)->update(['seller_id' => null]);

    // تحديث البيانات الأساسية للموظف
    $seller->f_name    = $request->f_name;
    $seller->l_name    = $request->l_name;
    $seller->email     = $request->email;
    $seller->type      = $request->type;
    $seller->salary    = $request->salary;
    $seller->latitude  = $request->latitude;
    $seller->longitude = $request->longitude;
    $seller->holidays  = $request->holidays;
    $seller->branch_id = $request->branch_id;
$seller->shift_id = json_encode($request->shift_id); // نخزنها كـ JSON string

    if ($request->filled('password')) {
        $seller->password = Hash::make($request->password);
    }

    $seller->save();

    // تحديث البيانات الإضافية للموظف (العلاقة detail)
    // نفترض أن العلاقة تحولت إلى hasOne بحيث يمكن الوصول إليها مباشرةً عبر $seller->detail
    if ($seller->detail) {
        $seller->detail->update([
            'phone'            => $request->phone,
            'department'       => $request->department,
            'job_title'        => $request->job_title,
            'hire_date'        => $request->hire_date,
            'qualifications'   => $request->qualifications,
            'contract_details' => $request->contract_details,
        ]);
    } else {
        // في حال عدم وجود سجل تفاصيل للموظف، يمكن إنشاؤه
        $seller->detail()->create([
            'phone'            => $request->phone,
            'department'       => $request->department,
            'job_title'        => $request->job_title,
            'hire_date'        => $request->hire_date,
            'qualifications'   => $request->qualifications,
            'contract_details' => $request->contract_details,
        ]);
    }

    Toastr::success(translate('تم تحديث بيانات الموظف'));
    return redirect()->route('admin.staff.list');
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

    if (!in_array("staff.destroy", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $seller = $this->seller->find($request->id);
        $seller->delete();

        Toastr::success(translate('Seller removed successfully'));
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

    if (!in_array("staff.balance", $decodedData)) {
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

    if (!in_array("staff.debit", $decodedData)) {
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

    if (!in_array("staff.loan", $decodedData)) {
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
