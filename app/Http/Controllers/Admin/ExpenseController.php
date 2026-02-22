<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Taxe;
use App\Models\Transection;
use App\Models\Seller;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Asset;
use App\Models\CostCenter;
use App\Models\Branch;
use App\Models\Order;
use App\CPU\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentVoucher;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;
use Illuminate\Support\Facades\Schema;

class ExpenseController extends Controller
{
    public function __construct(
        private Transection $transection,
        private Supplier $supplier,
        private Customer $customer,
        private Account $account,
                private Expense $expense,
                                private Branch $branch,
        private CostCenter $costcenter,
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
public function getOrdersByAccount(Request $request)
{
    $accountId = $request->input('account_id');

    // جلب العميل والمورد بناءً على account_id
    $customer = Customer::where('account_id', $accountId)->first();
    $supplier = Supplier::where('account_id', $accountId)->first();

    // إنشاء مجموعة فارغة لتجميع الطلبات من العميل والمورد
    $orders = collect();

    // إذا وُجد عميل، نجلب طلباته بأنواع البيع (4 أو 12)
    if ($customer) {
        $customerOrders = Order::whereIn('type', [4, 12])
            ->where('user_id', $customer->id)
            ->whereColumn('order_amount', '!=', 'transaction_reference')
            ->get();

        $orders = $orders->merge($customerOrders);
    }

    // إذا وُجد مورد، نجلب طلباته بأنواع البيع (4 أو 12)
    if ($supplier) {
        $supplierOrders = Order::whereIn('type', [4, 12])
            ->where('supplier_id', $supplier->id)
            ->whereColumn('order_amount', '!=', 'transaction_reference')
            ->get();

        $orders = $orders->merge($supplierOrders);
    }

    if ($orders->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'لا توجد فواتير لم يتم سدادها.'
        ]);
    }

    // لكل طلب، نبحث عن فاتورة مرتجع (من الأنواع 7 أو 24) مرتبطة بالطلب الأصلي (parent_id)
    $orders->transform(function ($order) {
        $returnOrder = Order::whereIn('type', [7, 24])
            ->where('parent_id', $order->id)
            ->first();
        $order->return_order = $returnOrder;
        return $order;
    });

    return response()->json(['success' => true, 'orders' => $orders]);
}

   public function add(Request $request, $type)
{
    if($type==2){
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

    if (!in_array("expense2.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    }elseif ($type=='Expense') {
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

    if (!in_array("Expense.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }       
        
        
    }elseif($type==100){
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

    if (!in_array("expense100.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }      
    }elseif($type==200){
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

    if (!in_array("expense200.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }    
    }
    // Retrieve all accounts ordered by ID in descending order
if($type == 100){
    $accounts = $this->account
        ->where(function ($query) {
            $query->where('parent_id', 26)
                  ->orWhere('account_type', 'expense')
                  ->orWhereHas('parent', function ($q) {
                      $q->where('parent_id',  26);
                  });
        })
        ->whereNotNull('parent_id')
        ->doesntHave('childrenn') // لا يرجع الحساب إذا له أولاد
        ->orderBy('id', 'desc')
        ->get();

    $accounts_to = $this->account
        ->whereIn('id', [8, 14])
        ->orWhere(function ($query) {
            $query->whereIn('parent_id', [8, 14])
                  ->orWhereHas('parent', function ($q) {
                      $q->whereIn('parent_id', [8, 14]);
                  });
        })
        ->whereNotNull('parent_id')
        ->doesntHave('childrenn') // لا يرجع الحساب إذا له أولاد
        ->orderBy('id', 'desc')
        ->get();

} elseif($type == 200){
    $accounts_to = $this->account
        ->where(function ($query) {
            $query->whereIn('parent_id', [15])
                  ->orWhereHas('parent', function ($q) {
                      $q->whereIn('parent_id', [26]);
                  });
        })
        ->doesntHave('childrenn')
        ->orderBy('id', 'desc')
        ->get();

    $accounts = $this->account
        ->whereIn('id', [8, 14])
        ->orWhere(function ($query) {
            $query->whereIn('parent_id', [8, 14])
                  ->orWhereHas('parent', function ($q) {
                      $q->whereIn('parent_id', [8, 14]);
                  });
        })
        ->doesntHave('childrenn')
        ->orderBy('id', 'desc')
        ->get();

} elseif($type == 2){
    $accounts = $this->account
        ->where(function ($query) {
            // هنا استخدمنا [4] فقط بدل [4, 4] لأنها نفس القيمة
            $query->whereIn('parent_id', [4])
                  ->orWhereHas('parent', function ($q) {
                      $q->whereIn('parent_id', [4]);
                  });
        })
        ->doesntHave('childrenn')
        ->orderBy('id', 'desc')
        ->get();

    $accounts_to = $this->account
        ->whereIn('id', [8, 14])
        ->orWhere(function ($query) {
            $query->whereIn('parent_id', [8, 14])
                  ->orWhereHas('parent', function ($q) {
                      $q->whereIn('parent_id', [8, 14]);
                  });
        })
        ->doesntHave('childrenn')
        ->orderBy('id', 'desc')
        ->get();

} elseif($type == 'Expense'){
    $accounts = $this->account
        ->where(function ($query) {
            // هنا استخدمنا [44] فقط بدل [44,44]
            $query->whereIn('parent_id', [44])
                  ->orWhereHas('parent', function ($q) {
                      $q->whereIn('parent_id', [44]);
                  });
        })
        ->doesntHave('childrenn')
        ->orderBy('id', 'desc')
        ->get();

    $accounts_to = $this->account
        ->whereIn('id', [8, 14])
        ->orWhere(function ($query) {
            $query->whereIn('parent_id', [8, 14])
                  ->orWhereHas('parent', function ($q) {
                      $q->whereIn('parent_id', [8, 14]);
                  });
        })
        ->doesntHave('childrenn')
        ->orderBy('id', 'desc')
        ->get();
}


    // جلب النتائج
        $suppliers = $this->supplier->orderBy('id', 'desc')->get();
        $customers = $this->customer->orderBy('id', 'desc')->get();
                $branches = $this->branch->where('active',1)->orderBy('id', 'desc')->get();
$costcenters = $this->costcenter->doesntHave('children')->orderBy('id', 'desc')->get();

    // Retrieve the search, from, and to parameters from the request
    $search = $request->input('search');
    $from = $request->input('from');
    $to = $request->input('to');
    

    // Initialize query for transactions with type matching the provided $type
    if($type==100){
    $query = $this->transection->where('tran_type', $type)->orWhere('tran_type','salary');
}else{
        $query = $this->transection->where('tran_type', $type);

}
    // Apply search filter if the search parameter is provided
    if ($search) {
        $key = explode(' ', $search);
        $query = $query->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('description', 'like', "%{$value}%");
            }
        });
    }

    // Apply date range filter if 'from' and 'to' are provided
    if ($from && $to) {
        $query = $query->whereBetween('date', [$from, $to]);
    }

    // Order by transaction ID in descending order and paginate the results
    $taxes = Taxe::all();
if($type==100){
     return view('admin-views.expense.addsandExpense', compact('accounts', 'accounts_to','taxes', 'search', 'from', 'to', 'type','suppliers','customers','costcenters'));
   
}else{
    // Return the view with the necessary data
    return view('admin-views.expense.add', compact('accounts', 'accounts_to', 'branches','search', 'from', 'to', 'type','suppliers','customers','costcenters'));
}}
   public function addExpense(Request $request)
{     $adminId = Auth::guard('admin')->id();
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

    if (!in_array("Expense.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    } $accounts = $this->account
    ->where('account_type', 'expense')
    ->doesntHave('childrenn')
    ->orderBy('id', 'desc')
    ->get();

$costcenters = $this->costcenter
    ->doesntHave('children')
    ->orderBy('id', 'desc')
    ->get();



    


    // Return the view with the necessary data
    return view('admin-views.expense.addExpense', compact('accounts','costcenters'));
}
public function listExpense(Request $request)
{
    // استرجاع معايير التصفية من الطلب
    $search     = $request->input('search');
    $start_date = $request->input('start_date');
    $end_date   = $request->input('end_date');
    $branchId   = $request->input('branch_id');
    $sellerId   = $request->input('seller_id');
    $costId     = $request->input('cost_id');

    // بدء بناء استعلام المصروفات
    $query = $this->expense; // تأكد من أن هذا يمثل استعلام Eloquent وليس مجموعة بيانات جاهزة

    // تطبيق تصفية البحث على الوصف إذا تم إدخال قيمة
    if ($search) {
        $keys = explode(' ', $search);
        $query->where(function ($q) use ($keys) {
            foreach ($keys as $value) {
                $q->orWhere('description', 'like', "%{$value}%");
            }
        });
    }

    // تطبيق تصفية النطاق الزمني إذا تم توفير كل من start_date و end_date
    if ($start_date && $end_date) {
        $query->whereBetween('created_at', [$start_date, $end_date]);
    }

    // تطبيق تصفية الفرع إذا تم توفير branch_id
    if ($branchId) {
        $query->where('branch_id', $branchId);
    }

    // تطبيق تصفية الكاتب إذا تم توفير seller_id
    if ($sellerId) {
        $query->where('seller_id', $sellerId);
    }

    // تطبيق تصفية مركز التكلفة إذا تم توفير cost_id
    if ($costId) {
        $query->where('cost_id', $costId);
    }

    // استرجاع البيانات الإضافية المطلوبة للعرض
    $sellers     = Seller::where('role', 'admin')->get();
    $accounts    = $this->account
                      ->where('account_type', 'expense')
                      ->doesntHave('childrenn')
                      ->orderBy('id', 'desc')
                      ->get();
    $costcenters = $this->costcenter
                      ->doesntHave('children')
                      ->orderBy('id', 'desc')
                      ->get();
    $branches    = $this->branch
                      ->orderBy('id', 'desc')
                      ->get();

    // ترتيب النتائج تنازلياً، تطبيق الترقيم وإلحاق معايير التصفية برابط الترقيم
$expenses = $query->orderBy('id', 'desc')->paginate(Helpers::pagination_limit());

    // حساب إجمالي مبلغ المصروفات للنتائج الحالية
    $totalAmount = $expenses->sum('amount');

    // إرجاع العرض مع كافة البيانات المطلوبة
    return view('admin-views.expense.listExpense', compact(
        'accounts',
        'expenses',
        'totalAmount',
        'search',
        'start_date',
        'end_date',
        'costcenters',
        'sellers',
        'branches'
    ));
}



public function storeExpense(Request $request)
    {
        // Validate input data
        $validated = $request->validate([
            'account_id'     => 'nullable|exists:accounts,id',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'description'    => 'required|string',
            'amount'         => 'nullable|numeric|min:1',
            'date'           => 'nullable|date',
            'img'            => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Begin transaction
        DB::beginTransaction();

        try {    
            // Create new expense record
            $expense = new Expense();
            $expense->account_id     = $request->account_id;
            $expense->cost_center_id = $request->cost_center_id??''; // optional field
            $expense->seller_id = Auth::guard('admin')->id();
            $expense->branch_id = auth('admin')->user()->branch_id;
            $expense->description    = $request->description;
            $expense->amount         = $request->amount;
            $expense->date           = $request->date;

            // If a file is uploaded, process the image using your helper function
            if ($request->hasFile('img')) {
                $expense->attachment = \App\CPU\Helpers::update('expenses/', null, 'png', $request->file('img'));
            }

            $expense->save();

            DB::commit();
            Toastr::success(translate('تم حفظ المصروف بنجاح'));
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error($e->getMessage());
            return redirect()->back()->withInput();
        }
    }
   public function list(Request $request, $type)
{
    if($type==2){
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

    if (!in_array("expense2.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    }elseif ($type=='Expense') {
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

    if (!in_array("Expense.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }       
        
        
    }elseif($type==100){
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

    if (!in_array("expense100.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }      
    }elseif($type==200){
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

    if (!in_array("expense200.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }    
    }
    // Retrieve all accounts ordered by ID in descending order
    if($type==100){
$accounts = $this->account
    ->where(function ($query) {
        $query->whereIn('parent_id', [15, 26])
              ->orWhereHas('parent', function ($q) {
                  $q->whereIn('parent_id', [15, 26]);
              });
    })
    ->orderBy('id', 'desc')
    ->get();

$accounts_to = $this->account->whereIn('id',[8, 14])
    ->orwhere(function ($query) {
        $query->whereIn('parent_id', [8, 14])
              ->orWhereHas('parent', function ($q) {
                  $q->whereIn('parent_id', [8, 14]);
              });
    })
    ->orderBy('id', 'desc')
    ->get();

    }elseif($type==200){
        $accounts_to = $this->account
    ->where(function ($query) {
        $query->whereIn('parent_id', [15, 26])
              ->orWhereHas('parent', function ($q) {
                  $q->whereIn('parent_id', [15, 26]);
              });
    })
    ->orderBy('id', 'desc')
    ->get();

$accounts = $this->account->whereIn('id',[8, 14])
    ->orwhere(function ($query) {
        $query->whereIn('parent_id', [8, 14])
              ->orWhereHas('parent', function ($q) {
                  $q->whereIn('parent_id', [8, 14]);
              });
    })
    ->orderBy('id', 'desc')
    ->get();
        
    }elseif($type==2){
           $accounts_to = $this->account
    ->where(function ($query) {
        $query->whereIn('parent_id', [4, 4])
              ->orWhereHas('parent', function ($q) {
                  $q->whereIn('parent_id', [4, 4]);
              });
    })
    ->orderBy('id', 'desc')
    ->get();

$accounts = $this->account->whereIn('id',[8, 14])
    ->orwhere(function ($query) {
        $query->whereIn('parent_id', [8, 14])
              ->orWhereHas('parent', function ($q) {
                  $q->whereIn('parent_id', [8, 14]);
              });
    })
    ->orderBy('id', 'desc')
    ->get();
    }elseif($type=='Expense'){
           $accounts_to = $this->account
    ->where(function ($query) {
        $query->whereIn('parent_id', [44,44])
              ->orWhereHas('parent', function ($q) {
                  $q->whereIn('parent_id', [44,44]);
              });
    })
    ->orderBy('id', 'desc')
    ->get();

$accounts = $this->account->whereIn('id',[8, 14])
    ->orwhere(function ($query) {
        $query->whereIn('parent_id', [8, 14])
              ->orWhereHas('parent', function ($q) {
                  $q->whereIn('parent_id', [8, 14]);
              });
    })
    ->orderBy('id', 'desc')
    ->get();
    }
    // جلب النتائج
        $suppliers = $this->supplier->orderBy('id', 'desc')->get();
        $customers = $this->customer->orderBy('id', 'desc')->get();
        $costcenters= $this->costcenter->orderBy('id', 'desc')->get();

    // Retrieve the search, from, and to parameters from the request
    $search = $request->input('search');
    $from = $request->input('from');
    $to = $request->input('to');
    

    // Initialize query for transactions with type matching the provided $type
    if($type==100){
    $query = $this->transection->where('tran_type', $type)->orWhere('tran_type','salary');
}else{
        $query = $this->transection->where('tran_type', $type);

}
    // Apply search filter if the search parameter is provided
    if ($search) {
        $key = explode(' ', $search);
        $query = $query->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('description', 'like', "%{$value}%");
            }
        });
    }

    // Apply date range filter if 'from' and 'to' are provided
    if ($from && $to) {
        $query = $query->whereBetween('date', [$from, $to]);
    }

    // Order by transaction ID in descending order and paginate the results
    $expenses = $query->orderBy('id', 'desc')
                      ->paginate(Helpers::pagination_limit())
                      ->appends([
                          'search' => $search,
                          'from' => $from,
                          'to' => $to
                      ]);
    $totalAmount = $expenses->sum('amount');

    // Return the view with the necessary data
    return view('admin-views.expense.list', compact('accounts', 'accounts_to','expenses','totalAmount', 'search', 'from', 'to', 'type','suppliers','customers','costcenters'));
}
public function indexreport(Request $request): View|Factory|Application
{
    // Retrieve all accounts, suppliers, and customers
    $accounts = $this->account->orderBy('id', 'desc')->get();
    $suppliers = $this->supplier->orderBy('id', 'desc')->get();
    $customers = $this->customer->orderBy('id', 'desc')->get();

    // Retrieve the search, from, and to parameters from the request
    $search = $request->input('search');
    $from = $request->input('from');
    $to = $request->input('to');

    // Retrieve all transactions with tran_type = 100
    $transactions = Transection::where('tran_type', '100')->orWhere('tran_type','salary')->get();

    // Apply search filter if the search parameter is provided
    if ($search) {
        $transactions = $transactions->filter(function ($transaction) use ($search) {
            return stripos($transaction->description, $search) !== false;
        });
    }

    // Apply date range filter if 'from' and 'to' are provided
    if ($from && $to) {
        $transactions = $transactions->filter(function ($transaction) use ($from, $to) {
            return $transaction->date >= $from && $transaction->date <= $to;
        });
    }

    // Get unique account_ids from the transactions
    $accountIds = $transactions->pluck('account_id_to')->unique();

    // Initialize an array to store account details with their sums
    $accountSummary = [];

    // Loop through each account_id to calculate the sum of amounts for each account
    foreach ($accountIds as $accountId) {
        // Get total sum of amounts for each account_id
        $accountTotalAmount = $transactions->where('account_id_to', $accountId)->sum('amount');

        // Get account number and account name
        $account = $this->account->find($accountId);
        $accountNumber = $account->account_number ?? 'غير محدد';
        $accountName = $account->account ?? 'غير محدد';

        // Add account details to the summary array
        $accountSummary[] = [
            'account_id' => $accountId,
            'account_number' => $accountNumber,
            'account' => $accountName,
            'total_amount' => $accountTotalAmount,
        ];
    }

    // Paginate the filtered transactions
    $paginatedTransactions = $transactions->sortByDesc('id')->forPage(1, Helpers::pagination_limit()); // Customize page number as needed
    $totalAmount = $paginatedTransactions->sum('amount');

    // Return the view with the necessary data
    return view('admin-views.expense.report', compact('accounts', 'paginatedTransactions', 'totalAmount', 'search', 'from', 'to', 'suppliers', 'customers', 'accountSummary'));
}

public function storesandExpense(Request $request)
{
    // هل إدخال متعدد؟
    $isMultiple = $request->has('multiple') && is_array($request->multiple) && count(array_filter(array_column($request->multiple, 'date'))) > 0;

    // قواعد التحقق
    if ($isMultiple) {
        $rules = [
            'type'                       => 'required',
            'account_id_multiple'        => 'required|exists:accounts,id',
            'multiple.*.voucher_number'  => 'nullable|string|max:50',
            'multiple.*.payee_name'      => 'required|string|max:255',
            'multiple.*.payment_method'  => 'required|in:cash,bank_transfer,cheque,other',
            'multiple.*.cheque_number'   => 'nullable|string|max:100',
            'multiple.*.account_id_to'   => 'required|exists:accounts,id',
            'multiple.*.date'            => 'required|date',
            'multiple.*.amount'          => 'required|numeric|min:0',
            'multiple.*.description'     => 'required|string',
            'multiple.*.cost_id'         => 'nullable|exists:cost_centers,id',
            'multiple.*.tax_type'        => 'nullable|exists:taxes,id',
            'multiple.*.tax_number'      => 'nullable|string|max:100',
            'multiple.*.img'             => 'nullable|file|mimes:jpg,jpeg,png,pdf',
        ];
    } else {
        $chequeRule = $request->payment_method === 'cheque' ? 'required|string' : 'nullable|string';
        $rules = [
            'type'            => 'required',
            'voucher_number'  => 'nullable|string|max:50',
            'payee_name'      => 'required|string|max:255',
            'payment_method'  => 'required|in:cash,bank_transfer,cheque,other',
            'cheque_number'   => $chequeRule,
            'account_id'      => 'required|exists:accounts,id',
            'account_id_to'   => 'required|exists:accounts,id',
            'date'            => 'required|date',
            'amount'          => 'required|numeric|min:0',
            'description'     => 'required|string',
            'cost_id'         => 'nullable|exists:cost_centers,id',
            'tax_id'          => 'nullable|exists:taxes,id',
            'tax_number'      => 'nullable|string|max:100',
            'img'             => 'nullable|file|mimes:jpg,jpeg,png,pdf',
        ];
    }

    $validated = $request->validate($rules);

    // ✅ هيلبر لتحديد مركز التكلفة: المرسل > الافتراضي من الحساب > null
    $resolveCostCenterId = function ($explicitCostId, $account) {
        if (!empty($explicitCostId)) {
            return $explicitCostId;
        }
        // خُد الافتراضي من الحساب لو موجود
        if ($account && !empty($account->default_cost_center_id)) {
            return $account->default_cost_center_id;
        }
        return null;
    };

    DB::beginTransaction();
    try {
        if ($isMultiple) {
            $creditor = Account::findOrFail($request->account_id_multiple); // خزنة/بنك ثابت
            $seq = (int) (PaymentVoucher::max('id') ?? 0);

            foreach ($request->multiple as $row) {
                if (empty($row['date']) || empty($row['amount']) || empty($row['account_id_to']) || empty($row['payee_name']) || empty($row['payment_method'])) {
                    continue;
                }

                if (($row['payment_method'] ?? null) === 'cheque' && empty($row['cheque_number'])) {
                    throw new \Exception(__('رقم الشيك مطلوب عند اختيار طريقة الدفع شيك في أحد الصفوف.'));
                }

                $debtor  = Account::findOrFail($row['account_id_to']);
                $isTax   = !empty($row['has_tax']);
                $taxRate = $isTax && !empty($row['tax_type'])
                    ? (optional(\App\Models\Taxe::find($row['tax_type']))->amount ?? 0)
                    : 0;

                $netAmount    = (float) $row['amount'];
                $taxAmount    = $isTax ? round($netAmount * $taxRate / 100, 2) : 0;
                $totalWithTax = $netAmount + $taxAmount;

                // ✅ حل مركز التكلفة للمدين/الدائن
                $debitCostId  = $resolveCostCenterId($row['cost_id'] ?? null, $debtor);
                $creditCostId = $resolveCostCenterId(null, $creditor); // مفيش مرسل؟ استخدم الافتراضي للدائن لو موجود

                // مرفق
                $rowAttachment = null;
                if (!empty($row['img']) && $row['img'] instanceof \Illuminate\Http\UploadedFile) {
                    $rowAttachment = $row['img']->store('payment_vouchers', 'public');
                }

                $seq++;
                $rowVoucherNumber = !empty($row['voucher_number'])
                    ? $row['voucher_number']
                    : ('PV-' . now()->format('Ym') . '-' . str_pad((string)$seq, 4, '0', STR_PAD_LEFT));

                // (1) السند
                $voucher = new PaymentVoucher();
                $voucher->voucher_number    = $rowVoucherNumber;
                $voucher->date              = $row['date'];
                $voucher->payee_name        = $row['payee_name'];
                $voucher->debit_account_id  = $debtor->id;
                $voucher->credit_account_id = $creditor->id;
                $voucher->amount            = $totalWithTax;
                $voucher->branch_id         = auth('admin')->user()->branch_id;
                $voucher->payment_method    = $row['payment_method'];
                $voucher->cheque_number     = ($row['payment_method'] === 'cheque') ? ($row['cheque_number'] ?? null) : null;
                $voucher->description       = $row['description'] ?? null;
                $voucher->attachment        = $rowAttachment;
                $voucher->created_by        = Auth::guard('admin')->id();
                // لو عندك عمود cost_id في جدول السندات وعايز تخزّن المدين:
                if (Schema::hasColumn('payment_vouchers', 'cost_id')) {
                    $voucher->cost_id = $debitCostId;
                }
                $voucher->save();

                // (2) رأس القيد
                $entry = new JournalEntry();
                $entry->entry_date         = $row['date'];
                $entry->reference          = $voucher->voucher_number;
                $entry->type               = 'paymet';
                $entry->description        = $row['description'] ?? ('Payment Voucher #' . $voucher->voucher_number);
                $entry->created_by         = Auth::guard('admin')->id();
                $entry->payment_voucher_id = $voucher->id;
                $entry->branch_id          = auth('admin')->user()->branch_id;
                $entry->save();

                $voucher->journal_entry_id = $entry->id;
                $voucher->save();

                // (3) تفاصيل — مدين
                $detailDebit = new JournalEntryDetail();
                $detailDebit->journal_entry_id = $entry->id;
                $detailDebit->account_id       = $debtor->id;
                $detailDebit->debit            = $totalWithTax;
                $detailDebit->credit           = 0;
                $detailDebit->cost_center_id   = $debitCostId;   // ✅
                $detailDebit->description      = $row['description'] ?? null;
                $detailDebit->attachment_path  = $rowAttachment;
                $detailDebit->entry_date       = $row['date'];
                $detailDebit->save();

                // (4) تفاصيل — دائن
                $detailCredit = new JournalEntryDetail();
                $detailCredit->journal_entry_id = $entry->id;
                $detailCredit->account_id       = $creditor->id;
                $detailCredit->debit            = 0;
                $detailCredit->credit           = $totalWithTax;
                $detailCredit->cost_center_id   = $creditCostId; // ✅
                $detailCredit->description      = $row['description'] ?? null;
                $detailCredit->attachment_path  = $rowAttachment;
                $detailCredit->entry_date       = $row['date'];
                $detailCredit->save();

                // (5) ترانزاكشن — دائن
                $newCredBalance = $creditor->balance - $totalWithTax;
                $tCred = new Transection();
                $tCred->tran_type                = 100;
                $tCred->seller_id                = Auth::guard('admin')->id();
                $tCred->account_id               = $creditor->id;
                $tCred->account_id_to            = $debtor->id;
                $tCred->debit                    = 0;
                $tCred->credit                   = $totalWithTax;
                $tCred->amount                   = $totalWithTax;
                $tCred->tax                      = $taxAmount;
                $tCred->description              = $row['description'] ?? null;
                $tCred->date                     = $row['date'];
                $tCred->balance                  = $newCredBalance;
                $tCred->branch_id                = auth('admin')->user()->branch_id;
                $tCred->tax_id                   = $isTax ? ($row['tax_type'] ?? null) : null;
                $tCred->tax_number               = $isTax ? ($row['tax_number'] ?? null) : null;
                $tCred->img                      = $rowAttachment;
                $tCred->journal_entry_detail_id  = $detailCredit->id;
                $tCred->cost_id                  = $creditCostId; // ✅
                $tCred->save();

                // (6) ترانزاكشن — مدين
                $isSupplier     = \App\Models\Supplier::where('account_id', $debtor->id)->exists();
                $newDebBalance  = $isSupplier ? ($debtor->balance - $totalWithTax) : ($debtor->balance + $totalWithTax);
                $tDeb = new Transection();
                $tDeb->tran_type                = 100;
                $tDeb->seller_id                = Auth::guard('admin')->id();
                $tDeb->account_id               = $debtor->id;
                $tDeb->account_id_to            = $creditor->id;
                $tDeb->debit                    = $totalWithTax;
                $tDeb->credit                   = 0;
                $tDeb->amount                   = $totalWithTax;
                $tDeb->tax                      = $taxAmount;
                $tDeb->description              = $row['description'] ?? null;
                $tDeb->date                     = $row['date'];
                $tDeb->balance                  = $newDebBalance;
                $tDeb->branch_id                = auth('admin')->user()->branch_id;
                $tDeb->tax_id                   = $isTax ? ($row['tax_type'] ?? null) : null;
                $tDeb->tax_number               = $isTax ? ($row['tax_number'] ?? null) : null;
                $tDeb->img                      = $rowAttachment;
                $tDeb->journal_entry_detail_id  = $detailDebit->id;
                $tDeb->cost_id                  = $debitCostId; // ✅
                $tDeb->save();

                // (7) تحديث الأرصدة
                $creditor->total_out += $totalWithTax;
                $creditor->balance    = $newCredBalance;
                $creditor->save();

                if ($isSupplier) {
                    $debtor->total_out += $totalWithTax;
                    $debtor->balance    = $newDebBalance;
                } else {
                    $debtor->total_in  += $totalWithTax;
                    $debtor->balance    = $newDebBalance;
                }
                $debtor->save();

                // (8) توزيع على فواتير
                $this->allocateOrdersForAccount($debtor->id, $totalWithTax);
            }

        } else {
            // ======================= سند واحد =======================
            $img = null;
            if ($request->hasFile('img')) {
                $img = $request->file('img')->store('payment_vouchers', 'public');
            }

            $creditor = Account::findOrFail($request->account_id);
            $debtor   = Account::findOrFail($request->account_id_to);

            $isTax     = $request->boolean('is_tax_invoice');
            $taxRate   = $isTax && $request->tax_id ? (optional(\App\Models\Taxe::find($request->tax_id))->amount ?? 0) : 0;
            $netAmount = (float) $request->amount;
            $taxAmount = $isTax ? round($netAmount * $taxRate / 100, 2) : 0;
            $totalWithTax = $netAmount + $taxAmount;

            // ✅ حل مركز التكلفة
            $debitCostId  = $resolveCostCenterId($request->cost_id, $debtor);
            $creditCostId = $resolveCostCenterId(null, $creditor);

            $voucherNumber = $request->voucher_number ?: ('PV-' . now()->format('Ym') . '-' . str_pad((string)(PaymentVoucher::max('id') + 1), 4, '0', STR_PAD_LEFT));

            // (1) السند
            $voucher = new PaymentVoucher();
            $voucher->voucher_number    = $voucherNumber;
            $voucher->date              = $request->date;
            $voucher->payee_name        = $request->payee_name;
            $voucher->debit_account_id  = $debtor->id;
            $voucher->credit_account_id = $creditor->id;
            $voucher->amount            = $totalWithTax;
            $voucher->payment_method    = $request->payment_method;
            $voucher->cheque_number     = $request->payment_method === 'cheque' ? $request->cheque_number : null;
            $voucher->description       = $request->description;
            $voucher->attachment        = $img;
            $voucher->created_by        = Auth::guard('admin')->id();
            // لو جدول السند فيه cost_id
            if (Schema::hasColumn('payment_vouchers', 'cost_id')) {
                $voucher->cost_id = $debitCostId;
            }
            $voucher->save();

            // (2) رأس القيد
            $entry = new JournalEntry();
            $entry->entry_date         = $request->date;
            $entry->reference          = $voucher->voucher_number;
            $entry->description        = $request->description;
            $entry->branch_id          = auth('admin')->user()->branch_id;
            $entry->type               = 'paymet';
            $entry->created_by         = Auth::guard('admin')->id();
            $entry->payment_voucher_id = $voucher->id;
            $entry->save();

            $voucher->journal_entry_id = $entry->id;
            $voucher->save();

            // (3) تفاصيل — مدين
            $detailDebit = new JournalEntryDetail();
            $detailDebit->journal_entry_id = $entry->id;
            $detailDebit->account_id       = $debtor->id;
            $detailDebit->debit            = $totalWithTax;
            $detailDebit->credit           = 0;
            $detailDebit->cost_center_id   = $debitCostId;  // ✅
            $detailDebit->description      = $request->description;
            $detailDebit->attachment_path  = $img;
            $detailDebit->entry_date       = $request->date;
            $detailDebit->save();

            // (4) تفاصيل — دائن
            $detailCredit = new JournalEntryDetail();
            $detailCredit->journal_entry_id = $entry->id;
            $detailCredit->account_id       = $creditor->id;
            $detailCredit->debit            = 0;
            $detailCredit->credit           = $totalWithTax;
            $detailCredit->cost_center_id   = $creditCostId; // ✅
            $detailCredit->description      = $request->description;
            $detailCredit->attachment_path  = $img;
            $detailCredit->entry_date       = $request->date;
            $detailCredit->save();

            // (5) ترانزاكشن — دائن
            $newCredBalance = $creditor->balance - $totalWithTax;
            $tCred = new Transection();
            $tCred->tran_type                = 100;
            $tCred->seller_id                = Auth::guard('admin')->id();
            $tCred->account_id               = $creditor->id;
            $tCred->account_id_to            = $debtor->id;
            $tCred->debit                    = 0;
            $tCred->credit                   = $totalWithTax;
            $tCred->amount                   = $totalWithTax;
            $tCred->tax                      = $taxAmount;
            $tCred->description              = $request->description;
            $tCred->date                     = $request->date;
            $tCred->balance                  = $newCredBalance;
            $tCred->branch_id                = auth('admin')->user()->branch_id;
            $tCred->tax_id                   = $isTax ? ($request->tax_id ?? null) : null;
            $tCred->tax_number               = $isTax ? ($request->tax_number ?? null) : null;
            $tCred->img                      = $img;
            $tCred->journal_entry_detail_id  = $detailCredit->id;
            $tCred->cost_id                  = $creditCostId; // ✅
            $tCred->save();

            // (6) ترانزاكشن — مدين
            $isSupplier     = \App\Models\Supplier::where('account_id', $debtor->id)->exists();
            $newDebBalance  = $isSupplier ? ($debtor->balance - $totalWithTax) : ($debtor->balance + $totalWithTax);
            $tDeb = new Transection();
            $tDeb->tran_type                = 100;
            $tDeb->seller_id                = Auth::guard('admin')->id();
            $tDeb->account_id               = $debtor->id;
            $tDeb->account_id_to            = $creditor->id;
            $tDeb->debit                    = $totalWithTax;
            $tDeb->credit                   = 0;
            $tDeb->amount                   = $totalWithTax;
            $tDeb->tax                      = $taxAmount;
            $tDeb->description              = $request->description;
            $tDeb->date                     = $request->date;
            $tDeb->balance                  = $newDebBalance;
            $tDeb->branch_id                = auth('admin')->user()->branch_id;
            $tDeb->tax_id                   = $isTax ? ($request->tax_id ?? null) : null;
            $tDeb->tax_number               = $isTax ? ($request->tax_number ?? null) : null;
            $tDeb->img                      = $img;
            $tDeb->journal_entry_detail_id  = $detailDebit->id;
            $tDeb->cost_id                  = $debitCostId; // ✅
            $tDeb->save();

            // (7) تحديث الأرصدة
            $creditor->total_out += $totalWithTax;
            $creditor->balance    = $newCredBalance;
            $creditor->save();

            if ($isSupplier) {
                $debtor->total_out += $totalWithTax;
                $debtor->balance    = $newDebBalance;
            } else {
                $debtor->total_in  += $totalWithTax;
                $debtor->balance    = $newDebBalance;
            }
            $debtor->save();

            // (8) توزيع على فواتير الحساب المدين
            $this->allocateOrdersForAccount($debtor->id, $totalWithTax);
        }

        DB::commit();
        Toastr::success(translate('تم صرف المبلغ وتسجيل القيود بنجاح (سند/سندات).'));
        return redirect()->back()->with('success', 'تم حفظ السند/السندات والقيد/القيود بنجاح');

    } catch (\Throwable $e) {
        DB::rollBack();
        Toastr::error(translate($e->getMessage()));
        return redirect()->back()->with('error', $e->getMessage());
    }
}

/** توزيع المدفوعات على فواتير العميل/المورد إن وجدت */
protected function allocateOrdersForAccount(int $accountId, float $amount): void
{
    if ($customer = \App\Models\Customer::where('account_id', $accountId)->first()) {
        $customer->credit += $amount;
        $customer->save();

        $orders = Order::where('type', 4)
            ->where('user_id', $customer->id)
            ->whereColumn('order_amount', '>', 'transaction_reference')
            ->orderBy('created_at', 'asc')
            ->get();

        $payment = $amount;
        foreach ($orders as $order) {
            $remaining = $order->order_amount - $order->transaction_reference;
            $use = min($payment, $remaining);
            if ($use <= 0) break;
            $order->transaction_reference += $use;
            $payment -= $use;
            $order->save();
        }
        return;
    }

    if ($supplier = \App\Models\Supplier::where('account_id', $accountId)->first()) {
        $supplier->credit += $amount;
        $supplier->save();

        $orders = Order::where('type', 12)
            ->where('supplier_id', $supplier->id)
            ->whereColumn('order_amount', '>', 'transaction_reference')
            ->orderBy('created_at', 'asc')
            ->get();

        $payment = $amount;
        foreach ($orders as $order) {
            $remaining = $order->order_amount - $order->transaction_reference;
            $use = min($payment, $remaining);
            if ($use <= 0) break;
            $order->transaction_reference += $use;
            $payment -= $use;
            $order->save();
        }
    }
}






public function store(Request $request, $type): RedirectResponse
{
    DB::beginTransaction();

    try {
        // 0) صلاحيات
        $adminId = Auth::guard('admin')->id();
        $admin   = DB::table('admins')->find($adminId);
        $role    = $admin ? DB::table('roles')->find($admin->role_id) : null;
        $permissions = $role ? json_decode($role->data, true) : [];
        if (!is_array($permissions)) {
            $permissions = json_decode($permissions ?? '[]', true) ?: [];
        }

        $permissionKeys = [
            2         => 'expense2.store',
            'Expense' => 'Expense.store',
            100       => 'expense100.store',
            200       => 'expense200.store',
        ];
        if (!isset($permissionKeys[$type]) || !in_array($permissionKeys[$type], $permissions, true)) {
            Toastr::warning('غير مسموح لك! كلم المدير.');
            return back();
        }

        // 1) قواعد التحقق
        $rules = [];
        if ($type !== 2) {
            $rules = [
                'account_id'     => 'required|exists:accounts,id',               // الدائن
                'account_id_to'  => 'required|exists:accounts,id|different:account_id', // المدين
                'amount'         => 'required|numeric|min:1',
                'date'           => 'required|date',
                'description'    => 'required',
                'payee_name'     => 'required|string',
                'payment_method' => 'required|in:cash,bank,check,cheque,card',
                'voucher_number' => 'nullable|max:50',
                'cheque_number'  => 'nullable|max:100',

                // اختيارية: لو هتبعتهم بشكل منفصل
                'cost_id'            => 'nullable|exists:cost_centers,id',
                'cost_id_debit'      => 'nullable|exists:cost_centers,id',
                'cost_id_credit'     => 'nullable|exists:cost_centers,id',
            ];
        }

        if ($type == 2 && $request->account_id !== null) {
            $rules = array_merge($rules, [
                'account_id'     => 'required|exists:accounts,id',
                'account_id_to'  => 'required|exists:accounts,id|different:account_id',
                'amount'         => 'required|numeric|min:1',
                'date'           => 'required|date',
                'description'    => 'required',
                'payee_name'     => 'required|string',
                'payment_method' => 'required|in:cash,bank,check,cheque,card',
                'voucher_number' => 'nullable|max:50',
                'cheque_number'  => 'nullable|max:100',

                'cost_id'        => 'nullable|exists:cost_centers,id',
                'cost_id_debit'  => 'nullable|exists:cost_centers,id',
                'cost_id_credit' => 'nullable|exists:cost_centers,id',
            ]);
        }

        if ($type == 2 && $request->account_id == null) {
            $rules = array_merge($rules, [
                'account_id'     => 'nullable|exists:accounts,id',
                'account_id_to'  => 'nullable|exists:accounts,id|different:account_id',
                'amount'         => 'nullable|numeric|min:1',
                'date'           => 'nullable|date',
                'description'    => 'nullable',
                'payee_name'     => 'nullable|string',
                'payment_method' => 'nullable|in:cash,bank,check,cheque,card',
                'voucher_number' => 'nullable|max:50',
                'cheque_number'  => 'nullable|max:100',

                'cost_id'        => 'nullable|exists:cost_centers,id',
                'cost_id_debit'  => 'nullable|exists:cost_centers,id',
                'cost_id_credit' => 'nullable|exists:cost_centers,id',
            ]);
        }

        if ($type == 2 && $request->asset_name !== null) {
            $rules = array_merge($rules, [
                'asset_name'          => 'required|string',
                'purchase_price'      => 'required|numeric|min:0',
                'useful_life'         => 'required|integer|min:1',
                'commencement_date'   => 'required|date',
                'code'                => 'required|unique:assets,code',
                'depreciation_method' => 'required|in:straight_line,declining_balance,units_of_production',
                'depreciation_rate'   => 'nullable|numeric|min:0',
            ]);
            if ($request->depreciation_method === 'declining_balance') {
                $rules['depreciation_rate'] = 'required|numeric|min:0';
            }
        }

        $validated = $request->validate($rules);

        // 2) فلاغات
        $isFinancial = $request->filled('account_id') && $request->filled('account_id_to')
                       && $request->filled('amount') && $request->filled('date');
        $isAssetOnly = ($type == 2) && $request->filled('asset_name') && !$isFinancial;

        // 3) أصل (اختياري)
        $asset = null;
        if ($type == 2 && $request->asset_name !== null) {
            $asset = new Asset();
            $asset->fill($request->only([
                'asset_name','purchase_price','additional_costs','salvage_value',
                'accumulated_depreciation','useful_life','code','commencement_date',
                'depreciation_method','depreciation_rate','invoice_number',
                'purchase_date','location','status','branch_id','code','branch_id'
            ]));
            $asset->total_cost = $asset->purchase_price + ($asset->additional_costs ?? 0);
            $asset->book_value = $asset->total_cost;

            if ($request->hasFile('asset_img')) {
                $asset->asset_img = \App\CPU\Helpers::update('assets/', null, 'png', $request->file('asset_img'));
            }
            $asset->save();
        }

        if ($isAssetOnly) {
            DB::commit();
            Toastr::success(translate('تم حفظ الأصل الثابت بنجاح'));
            return back();
        }

        // 4) مرفق
        $img = $request->hasFile('img')
            ? \App\CPU\Helpers::update('shop/', null, 'png', $request->file('img'))
            : null;

        // 5) الحسابات (مالية)
        $account    = null; // الدائن
        $account_to = null; // المدين
        if ($isFinancial) {
            $account    = Account::find($request->account_id);
            $account_to = Account::find($request->account_id_to) ?? $account;
        }

        // ** تحديد مركز التكلفة لكل طرف **
        // أولوية (أ) cost_id_debit / cost_id_credit من الطلب
        // ثم (ب) cost_id العام لو مَبْعوت
        // ثم (ج) default_cost_center_id من الحساب نفسه
        $costIdFromRequest = $request->input('cost_id');
        $costIdDebit   = $request->input('cost_id_debit')
                         ?? $costIdFromRequest
                         ?? ($account_to->default_cost_center_id ?? null);
        $costIdCredit  = $request->input('cost_id_credit')
                         ?? $costIdFromRequest
                         ?? ($account->default_cost_center_id ?? null);

        // 6) مبالغ
        $totalWithTax  = $request->total_with_tax ?? $request->amount;
        $paymentMethod = $request->payment_method ? strtolower($request->payment_method) : null;
        if ($paymentMethod === 'check') { $paymentMethod = 'cheque'; }

        // رقم سند ونوعه
        $voucherNumber = $request->voucher_number ?: ('PV-' . now()->format('Ymd') . '-' . str_pad((string)mt_rand(1, 9999), 4, '0', STR_PAD_LEFT));
        $voucherType   = in_array((int)$type, [100, 200], true) ? 'receipt' : 'payment';

        // 7) القيود والمعاملات
        if ($isFinancial) {
            $debtor   = $account_to; // مدين
            $creditor = $account;    // دائن

            // (أ) PaymentVoucher
            $voucher = new PaymentVoucher();
            $voucher->voucher_number    = $voucherNumber;
            $voucher->date              = $request->date;
            $voucher->type              = $voucherType;
            $voucher->branch_id         = Auth::guard('admin')->user()->branch_id ?? null;
            $voucher->payee_name        = $request->payee_name;
            $voucher->debit_account_id  = $debtor?->id;
            $voucher->credit_account_id = $creditor?->id;
            $voucher->amount            = $totalWithTax;
            $voucher->payment_method    = $paymentMethod;
            $voucher->cheque_number     = ($paymentMethod === 'cheque') ? ($request->cheque_number ?: null) : null;
            $voucher->description       = $request->description;
            $voucher->attachment        = $img;
            $voucher->created_by        = Auth::guard('admin')->id();
            $voucher->save();

            // (ب) JournalEntry (رأس)
            $entry = new JournalEntry();
            $entry->entry_date         = $request->date;
            $entry->reference          = $voucher->voucher_number;
            $entry->description        = $request->description;
            $entry->created_by         = Auth::guard('admin')->id();
            $entry->payment_voucher_id = $voucher->id;
            $entry->type               = 'receipt';
            $entry->branch_id          = $voucher->branch_id ?? null;
            $entry->save();

            $voucher->journal_entry_id = $entry->id;
            $voucher->save();

            // (ج) تفاصيل اليومية — مدين (مع costIdDebit)
            $detailDebit = new JournalEntryDetail();
            $detailDebit->journal_entry_id = $entry->id;
            $detailDebit->account_id       = $debtor?->id;
            $detailDebit->debit            = $totalWithTax;
            $detailDebit->credit           = 0;
            $detailDebit->cost_center_id   = $costIdDebit;   // ✅
            $detailDebit->description      = $request->description;
            $detailDebit->attachment_path  = $img;
            $detailDebit->entry_date       = $request->date;
            $detailDebit->save();

            // (د) تفاصيل اليومية — دائن (مع costIdCredit)
            $detailCredit = new JournalEntryDetail();
            $detailCredit->journal_entry_id = $entry->id;
            $detailCredit->account_id       = $creditor?->id;
            $detailCredit->debit            = 0;
            $detailCredit->credit           = $totalWithTax;
            $detailCredit->cost_center_id   = $costIdCredit; // ✅
            $detailCredit->description      = $request->description;
            $detailCredit->attachment_path  = $img;
            $detailCredit->entry_date       = $request->date;
            $detailCredit->save();

            // (هـ) معاملات منفصلة — وربط cost_id المناسب
            $trxModelClass = get_class($this->transection);

            // 1) الدائن
            $trxCredit = new $trxModelClass();
            $trxCredit->fill($request->only([
                'type','description','amount','date','supplier_id','customer_id'
            ]));
            $trxCredit->branch_id               = Auth::guard('admin')->user()->branch_id;
            $trxCredit->img                     = $img;
            $trxCredit->tran_type               = $type;
            $trxCredit->seller_id               = Auth::guard('admin')->id();
            $trxCredit->debit                   = 0;
            $trxCredit->credit                  = $totalWithTax;
            $trxCredit->amount                   = $totalWithTax;
            $trxCredit->balance                 = ($creditor->balance ?? 0) - $totalWithTax;
            $trxCredit->debit_account           = 0;
            $trxCredit->credit_account          = $totalWithTax;
            $trxCredit->account_id              = $creditor?->id;
            $trxCredit->account_id_to           = $debtor?->id;
            $trxCredit->journal_entry_detail_id = $detailCredit->id;
            $trxCredit->cost_id                 = $costIdCredit; // ✅
            if (isset($asset)) $trxCredit->asset_id = $asset->id;
            $trxCredit->save();

            // 2) المدين
            $trxDebit = new $trxModelClass();
            $trxDebit->fill($request->only([
                'type','description','amount','date','supplier_id','customer_id'
            ]));
            $trxDebit->branch_id               = Auth::guard('admin')->user()->branch_id;
            $trxDebit->img                     = $img;
            $trxDebit->tran_type               = $type;
            $trxDebit->seller_id               = Auth::guard('admin')->id();
            $trxDebit->debit                   = $totalWithTax;
            $trxDebit->credit                  = 0;
            $trxDebit->amount                   = $totalWithTax;
            $trxDebit->balance                 = ($debtor->balance ?? 0) + $totalWithTax;
            $trxDebit->debit_account           = $totalWithTax;
            $trxDebit->credit_account          = 0;
            $trxDebit->account_id              = $debtor?->id;
            $trxDebit->account_id_to           = $creditor?->id;
            $trxDebit->journal_entry_detail_id = $detailDebit->id;
            $trxDebit->cost_id                 = $costIdDebit; // ✅
            if (isset($asset)) $trxDebit->asset_id = $asset->id;
            $trxDebit->save();

            // (و) تحديث أرصدة
            if ($creditor) {
                $creditor->total_out = ($creditor->total_out ?? 0) + $totalWithTax;
                $creditor->balance   = ($creditor->balance ?? 0) - $totalWithTax;
                $creditor->save();
            }
            if ($debtor) {
                $debtor->total_in  = ($debtor->total_in ?? 0) + $totalWithTax;
                $debtor->balance   = ($debtor->balance ?? 0) + $totalWithTax;
                $debtor->save();
            }

            // (ز) عملاء/موردين (لو محتاج)
            foreach ([Customer::class => 'balance', Supplier::class => 'due_amount'] as $model => $field) {
                foreach ([$request->account_id, $request->account_id_to] as $id) {
                    if (!$id) continue;
                    $entity = $model::where('account_id', $id)->first();
                    if ($entity) {
                        $entity->{$field} = ($entity->{$field} ?? 0) + $request->amount;
                        $entity->save();
                    }
                }
            }

            // (ح) أي معالجة إضافية
            $this->handleOrderPayments($type, $request);
        }

        DB::commit();

        if ($isFinancial) {
            if (in_array((int)$type, [100,200], true)) {
                Toastr::success(translate('تم تسجيل سند القبض بنجاح'));
            } else {
                Toastr::success(translate('تم صرف/تسجيل السند بنجاح'));
            }
        } else {
            Toastr::success(translate('تم الحفظ بنجاح'));
        }

        return back();

    } catch (\Exception $e) {
        DB::rollBack();
        Toastr::error($e->getMessage());
        return back();
    }
}



    protected function handleOrderPayments($type, $request)
    {
        $accountKey = in_array($type, [100]) ? 'account_id' : 'account_id_to';
        $orderType = $type == 100 ? 4 : 12;
        $relationKey = $type == 100 ? 'user_id' : 'supplier_id';
        $model = $type == 100 ? Customer::class : Supplier::class;

        $entity = $model::where('account_id', $request->{$accountKey})->first();

        if ($entity) {
            $orders = Order::where('type', $orderType)
                ->where($relationKey, $entity->id)
                ->whereColumn('order_amount', '>', 'transaction_reference')
                ->orderBy('created_at', 'asc')
                ->get();

            $payment = $request->amount;

            foreach ($orders as $order) {
                $remaining = $order->order_amount - $order->transaction_reference;
                $applied = min($payment, $remaining);
                $order->transaction_reference += $applied;
                $order->save();
                $payment -= $applied;
                if ($payment <= 0) break;
            }
        }
    }


public function download(Request $request, $type)
{
    // Ensure the user is authenticated as an admin (or seller if needed)
    $seller = Auth::guard('admin')->user();
    if (!$seller) {
        abort(403, 'Unauthorized');
    }

    // Get the necessary filters from the request
    $search = $request->input('search');
    $from = $request->input('from');
    $to = $request->input('to');

    // Initialize query for transactions with type matching the provided $type
    if($type==100){
    $query = $this->transection->where('tran_type', $type)->orWhere('tran_type','salary');
}else{
        $query = $this->transection->where('tran_type', $type);

}
    // Apply search filter if the search parameter is provided
    if ($search) {
        $key = explode(' ', $search);
        $query = $query->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('description', 'like', "%{$value}%");
            }
        });
    }

    // Apply date range filter if 'from' and 'to' are provided
    if ($from && $to) {
        $query = $query->whereBetween('date', [$from, $to]);
    }

    // Order by transaction ID in descending order and fetch the results
    $expenses = $query->orderBy('id', 'desc')->get();
    $totalAmount = $expenses->sum('amount');

    // Render the Blade view to generate the HTML content
    $html = view('admin-views.expense.pdf', compact('expenses', 'search', 'seller','type','totalAmount'))->render();

    // Save HTML to a temporary file
    $filePath = storage_path('app/public/account_report.html');
    file_put_contents($filePath, $html);

    // Optional: If you need to generate PDF, you can use a PDF library (e.g., DomPDF)
    // For now, we'll keep it as HTML for downloading as an HTML file
    // If you want to use DomPDF, you would replace the code above with PDF generation logic.

    // Return the file for download and delete it after sending
    return response()->download($filePath, 'account_report.html')->deleteFileAfterSend(true);
}


  public function generate_expense_invoice($id)
    {
        $expense = $this->transection->find($id);

        return response()->json([
            'success' => 1,
            'view' => view('admin-views.expense.invoice', compact('expense'))->render(),
        ]);
    }
 public function getAccountsByType(Request $request)
{
    // التحقق من وجود قيمة النوع في الطلب
    $accountType = $request->input('type');

    if (!$accountType) {
        return response()->json([
            'success' => false,
            'message' => 'نوع الحساب مطلوب'
        ], 422);
    }

    // بناء الاستعلام بناءً على نوع الحساب
    // ملاحظة: يُفترض أن موديل Account يحتوي على علاقة children تُعيد الحسابات التابعة
    if ($accountType === 'مصروف') {
        // جلب الحسابات التي:
        // - account_type = 'expense'
        // - parent_id ليس null
        // - ليس لها حسابات فرعية (لا يوجد لها أبناء)
        $accounts = Account::where('account_type', 'expense')
            ->whereNotNull('parent_id')
            ->whereDoesntHave('children')
            ->get();
    } elseif ($accountType === 'عميل') {
        // جلب الحسابات التي:
        // - parent_id = 15
        // - ليس لها أبناء
        $accounts = Account::where('parent_id', 15)
            ->whereDoesntHave('children')
            ->get();
    } elseif ($accountType === 'مورد') {
        // جلب الحسابات التي:
        // - parent_id = 26
        // - ليس لها أبناء
        $accounts = Account::where('parent_id', 26)
            ->whereDoesntHave('children')
            ->get();
    } else {
        // إذا كان النوع غير مطابق، يمكن إرجاع مصفوفة فارغة أو رسالة
        $accounts = collect([]);
    }

    return response()->json([
        'success' => true,
        'accounts' => $accounts
    ]);
}
  public function reverseJournalEntry(Request $request, $id)
    {
        // اجلب المعرّف من الـ route (إلزامي) + خيار إعادة الاحتساب من query
        $journalEntryId = $id;
        $hardRecalc = (bool) $request->boolean('hard_recalc');

        // تحقق من وجود القيد
        $request->merge(['journal_entry_id' => $journalEntryId]);
        $request->validate([
            'journal_entry_id' => ['required', Rule::exists('journal_entries', 'id')],
            // hard_recalc اختياري من الـ query
        ]);

        DB::beginTransaction();

        try {
            /** @var \App\Models\JournalEntry $original */
            $original = JournalEntry::with(['details','details.account','branch','writer'])
                ->lockForUpdate()
                ->findOrFail($journalEntryId);

            // لو كان مقلوب قبل كده أو نفسه قيد عكسي، امنع
            if ((int)$original->is_reversal === 1 || !empty($original->reversal_of_id) || (int)$original->is_reversal === 2) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'هذا القيد تم عكسه من قبل أو هو قيد عكسي بالفعل.'
                ], 409);
            }

            // علّم الأصلي إنه اتعكس
            $original->is_reversal = 2;
            $original->reversed_at = now();
            $original->save();

            // أنشئ القيد العكسي
            $reverse = new JournalEntry();
            $reverse->type            = $original->type;
            $reverse->branch_id       = $original->branch_id;
            $reverse->writer_id       = auth('admin')->id();
            $reverse->head_date       = now();
            $reverse->head_ref        = 'REV-' . ($original->head_ref ?? $original->reference ?? $original->id);
            $reverse->description     = 'عكس: ' . (string) $original->description;
            $reverse->reversal_of_id  = $original->id;
            $reverse->is_reversal     = 1;
            $reverse->save();

            // فك ارتباط حسب النوع
            if (in_array($original->type, ['payment','receipt'], true)) {
                PaymentVoucher::where('journal_entry_id', $original->id)
                    ->update(['journal_entry_id' => null, 'reversed_at' => now()]);
            }
            if ($original->type === 'asset_solid') {
                AssetSold::where('journal_entry_id', $original->id)
                    ->update(['journal_entry_id' => null, 'reversed_at' => now()]);
            }

            // تتبّع الحسابات/الأطراف لو هنفعل إعادة احتساب شاملة
            $touchedAccountIds   = [];
            $touchedSupplierIds  = [];
            $touchedCustomerIds  = [];

            foreach ($original->details as $detail) {
                // سطر عكسي (قلب المدين/الدائن)
                $revDetail = new JournalEntryDetail();
                $revDetail->journal_entry_id   = $reverse->id;
                $revDetail->account_id         = $detail->account_id;
                $revDetail->cost_center_id     = $detail->cost_center_id;
                $revDetail->description        = 'عكس: ' . (string) $detail->description;
                $revDetail->debit              = $detail->credit;
                $revDetail->credit             = $detail->debit;
                $revDetail->reversal_of_detail_id = $detail->id;
                $revDetail->head_date          = $reverse->head_date;
                $revDetail->save();

                // اعكس كل الترانزاكشنات المرتبطة بالسطر
                $txs = Transection::where('journal_entry_detail_id', $detail->id)->get();

                foreach ($txs as $tx) {
                    $revTx = new Transection();
                    $revTx->journal_entry_id        = $reverse->id;
                    $revTx->journal_entry_detail_id = $revDetail->id;

                    // اقلب الحسابين
                    $revTx->account_id      = $tx->account_id_to;
                    $revTx->account_id_to   = $tx->account_id;

                    // نفس النوع والمبلغ وغيره
                    $revTx->tran_type       = $tx->tran_type;
                    $revTx->seller_id       = auth('admin')->id();
                    $revTx->branch_id       = auth('admin')->user()->branch_id ?? $tx->branch_id;
                    $revTx->amount          = $tx->amount;

                    // اقلب المدين/الدائن
                    $revTx->debit           = $tx->credit;
                    $revTx->credit          = $tx->debit;
                    $revTx->debit_account   = $tx->credit_account;
                    $revTx->credit_account  = $tx->debit_account;

                    // وصف/ضرائب/تاريخ
                    $revTx->description     = 'عكس: ' . (string) $tx->description;
                    $revTx->tax             = $tx->tax;
                    $revTx->tax_id          = $tx->tax_id;
                    $revTx->tax_number      = $tx->tax_number;
                    $revTx->name            = $tx->name;
                    $revTx->date            = now();
                    $revTx->is_reversal     = 1;

                    $revTx->save();

                    // === (اختياري) تحديث إجماليات الحساب فورًا (لو عندك دوال مساعدة) ===
                    $this->bumpAccountTotals($revTx->account_id,    +$revTx->amount, 'in');
                    $this->bumpAccountTotals($revTx->account_id_to, +$revTx->amount, 'out');

                    $touchedAccountIds[$revTx->account_id]    = true;
                    $touchedAccountIds[$revTx->account_id_to] = true;

                    // تحديث مورد/عميل بحسب الحسابات (لو عندك منطق ربط)
                    $this->bumpPartyByAccounts($tx, $revTx, $touchedSupplierIds, $touchedCustomerIds);
                }
            }

            // تحديث أثر القيد على أوامر العملاء/الموردين (لو موجود عندك)
            $this->reverseOrdersForCustomersIfAny($original);
            $this->reverseOrdersForSuppliersIfAny($original);

            // إعادة احتساب شاملة (اختياري)
            if ($hardRecalc) {
                $this->hardRecalcAccounts(array_keys($touchedAccountIds));
                $this->hardRecalcSuppliers(array_keys($touchedSupplierIds));
                $this->hardRecalcCustomers(array_keys($touchedCustomerIds));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم عكس القيد وتحديث الأرصدة بالكامل بنجاح.',
                'reversal_entry_id' => $reverse->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success'=>false,
                'message'=>$e->getMessage(),
            ], 500);
        }
    }
public function getVouchers(Request $request,$type)
{
    $query = PaymentVoucher::query()
        ->with(['creditAccount', 'debitAccount', 'creator']) // تحميل العلاقات
        ->orderBy('date', 'desc')->where('type',$type);

    // فلتر التاريخ
    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('date', [$request->from_date, $request->to_date]);
    }

    // فلتر الكاتب عبر الإيميل
    if ($request->filled('created_by')) {
        $seller = \App\Models\Seller::where('email', $request->created_by)->first();
        if ($seller) {
            $query->where('created_by', $seller->id);
        } else {
            // لو الإيميل مش موجود، نخلي النتيجة فاضية
            $query->whereNull('id');
        }
    }

    // فلتر الحساب الدائن
    if ($request->filled('credit_account_id')) {
        $query->where('credit_account_id', $request->credit_account_id);
    }

    // فلتر الحساب المدين
    if ($request->filled('debit_account_id')) {
        $query->where('debit_account_id', $request->debit_account_id);
    }

    // فلتر المبلغ
    if ($request->filled('amount_min') && $request->filled('amount_max')) {
        $query->whereBetween('amount', [$request->amount_min, $request->amount_max]);
    }

    // فلتر طريقة الدفع
    if ($request->filled('payment_method')) {
        $query->where('payment_method', $request->payment_method);
    }

    // فلتر الوصف
    if ($request->filled('description')) {
        $query->where('description', 'like', '%' . $request->description . '%');
    }

    // فلتر رقم السند
    if ($request->filled('voucher_number')) {
        $query->where('voucher_number', $request->voucher_number);
    }

    $vouchers = $query->paginate(Helpers::pagination_limit());

    return view('admin-views.vouchers.index', compact('vouchers','type'));
}
public function showVouchers(string $voucher_number)
{
    $voucher = PaymentVoucher::with(['creditAccount', 'debitAccount', 'creator'])
        ->where('id', $voucher_number)
        ->firstOrFail();

    return view('admin-views.vouchers.show', compact('voucher'));
}


}
