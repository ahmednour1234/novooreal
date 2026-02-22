<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\HistoryTransection;
use App\Models\Transection;
use App\CPU\Helpers;
use App\Exceptions\Handler;
use Carbon\Carbon;
use App\Models\Account;
use App\Models\AdminSeller;
use App\Models\Product;
use App\Models\Region;
use App\Models\Installment;
use App\Models\Salary;
use App\Models\PosSession;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Stock;
use App\Models\Seller;
use App\Models\HistoryInstallment;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use function App\CPU\translate;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        private Transection $transection,
        private Installment $installment,
        private Account $account,
        private Product $product,
        private Seller $seller,
        private Order $order,
                private PosSession $possession,
        
    ){}

  public function dashboard()
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

    if (!in_array("dashboard.list", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    } 
    $adminId = Auth::guard('admin')->id(); // Use the 'admin' guard to get the admin's ID

    // Retrieve all seller IDs associated with the admin from the `admin_seller` table
    $sellerIds = AdminSeller::where('admin_id', $adminId)->pluck('seller_id');

    // Use the seller IDs to filter active installments, orders, and stocks
    $installments = Installment::where('active', 1)
        ->whereIn('seller_id', $sellerIds)
        ->get();

    $orders = Order::where('active', 1)
        ->whereIn('owner_id', $sellerIds)
        ->get();
$bigsellers = Order::where('type', 4)
    ->whereIn('owner_id', $sellerIds)
    ->get();

// Group by owner_id and count occurrences
$topOwners = $bigsellers->groupBy('owner_id')
    ->map(function ($group) {
        return $group->count(); // Count the occurrences of each owner_id
    })
    ->sortDesc() // Sort by count in descending order
    ->take(5); // Take the top 5

// Get the owner_ids of the top 5 owners
$topOwnerIds = $topOwners->keys();

// Retrieve the actual sellers for the top 5 owners
$bestsellers = Seller::whereIn('id', $topOwnerIds)->get();

    $stocks = Stock::where('active', 1)
        ->whereIn('seller_id', $sellerIds)
        ->get();
$previousMonth = now()->subMonth()->format('Y-m'); // Get previous month in 'YYYY-MM' format

$perviousSalaries = Salary::where('month', $previousMonth)->sum('total');
$salaries = Admin::sum('salary');
$sellerscredit = Admin::sum('credit');
$sellersbalance = Admin::sum('balance');


    $total_payable_debit = $this->transection->where('tran_type', '4')->where('cash', 1)->sum('amount');
    $total_payable_credit = $this->transection->where('tran_type', '4')->where('cash', 2)->sum('amount');
    $total_payable = $total_payable_credit - $total_payable_debit;

    $total_receivable_debit = $this->transection->where('tran_type', '7')->sum('amount');
    $total_receivable_credit = $this->transection->where('tran_type', '7')->sum('amount');
    $total_receivable = $total_receivable_credit - $total_receivable_debit;

   $account = [
    'total_income' => $this->order->where('cash', 1)
        ->where('type', 4)
        ->selectRaw('SUM(order_amount) as total_income, MONTH(created_at) as month, YEAR(created_at) as year')
        ->groupBy('month', 'year') // Group by both month and year
        ->pluck('total_income', 'month'), // Get total income per month

    'total_expense' => $this->order->where('cash', 2)
        ->where('type', 4)
        ->selectRaw('SUM(order_amount) as total_expense, MONTH(created_at) as month, YEAR(created_at) as year')
        ->groupBy('month', 'year') // Group by both month and year
        ->pluck('total_expense', 'month'), // Get total expense per month

    'total_refund' => $this->order->where('type', 7)
        ->selectRaw('SUM(order_amount) as total_refund, MONTH(created_at) as month, YEAR(created_at) as year')
        ->groupBy('month', 'year') // Group by both month and year
        ->pluck('total_refund', 'month'), // Get total refund per month

    'total_installment' => $this->installment->selectRaw('SUM(total_price) as total_installment, MONTH(created_at) as month, YEAR(created_at) as year')
        ->groupBy('month', 'year') // Group by both month and year
        ->pluck('total_installment', 'month'), // Get total installment per month
];
$currentYear = date('Y'); // Get the current year

$accountw = [
    'total_income' => $this->order->where('cash', 1)
        ->where('type', 4)
        ->whereYear('created_at', $currentYear) // Filter by the current year
        ->selectRaw('SUM(order_amount) as total_income, MONTH(created_at) as month, YEAR(created_at) as year')
        ->groupBy('month', 'year') // Group by both month and year
        ->pluck('total_income', 'month'), // Get total income per month

    'total_expense' => $this->order->where('cash', 2)
        ->where('type', 4)
        ->whereYear('created_at', $currentYear) // Filter by the current year
        ->selectRaw('SUM(order_amount) as total_expense, MONTH(created_at) as month, YEAR(created_at) as year')
        ->groupBy('month', 'year') // Group by both month and year
        ->pluck('total_expense', 'month'), // Get total expense per month
   'total_shabaka' => $this->order->where('cash', 3)
        ->where('type', 4)
        ->whereYear('created_at', $currentYear) // Filter by the current year
        ->selectRaw('SUM(order_amount) as total_shabaka, MONTH(created_at) as month, YEAR(created_at) as year')
        ->groupBy('month', 'year') // Group by both month and year
        ->pluck('total_shabaka', 'month'),
    'total_refund' => $this->order->where('type', 7)
        ->whereYear('created_at', $currentYear) // Filter by the current year
        ->selectRaw('SUM(order_amount) as total_refund, MONTH(created_at) as month, YEAR(created_at) as year')
        ->groupBy('month', 'year') // Group by both month and year
        ->pluck('total_refund', 'month'), // Get total refund per month

    'total_installment' => $this->installment->whereYear('created_at', $currentYear) // Filter by the current year
        ->selectRaw('SUM(total_price) as total_installment, MONTH(created_at) as month, YEAR(created_at) as year')
        ->groupBy('month', 'year') // Group by both month and year
        ->pluck('total_installment', 'month'), // Get total installment per month
];


    // Initialize empty arrays for each month
    $months = range(1, 12); // Array with months 1 to 12
    $total_income = array_fill(1, 12, 0);
    $total_refund = array_fill(1, 12, 0);

    // Populate data from the database query results
    foreach ($account['total_income'] as $month => $value) {
        $total_income[$month] = $value;
    }

    foreach ($account['total_refund'] as $month => $value) {
        $total_refund[$month] = $value;
    }

    $labels = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];

    $monthly_income = [];
    for ($i = 1; $i <= 12; $i++) {
        $from = date('Y-' . $i . '-01');
        $to = date('Y-' . $i . '-30');
        $monthly_income[$i] = $this->transection->where(['tran_type' => 'Income'])->whereBetween('date', [$from, $to])->sum('amount');
    }

    $monthly_expense = [];
    for ($i = 1; $i <= 12; $i++) {
        $from = date('Y-' . $i . '-01');
        $to = date('Y-' . $i . '-30');
        $monthly_expense[$i] = $this->transection->where(['tran_type' => 'Expense'])->whereBetween('date', [$from, $to])->sum('amount');
    }

    $month = date('t');
    $first_day = strtotime(date('Y-m-1'));
    $curr_day = strtotime(date('Y-m-d'));

    $total_day = Carbon::now()->daysInMonth;

    $last_month_income = [];
    for ($i = 1; $i <= $total_day; $i++) {
        $day = date('Y-m-' . $i);
        $last_month_income[$i] = $this->transection->where(['tran_type' => 'Income'])->where('date', $day)->sum('amount');
    }

    $last_month_expense = [];
    for ($i = 1; $i <= $total_day; $i++) {
        $day = date('Y-m-' . $i);
        $last_month_expense[$i] = $this->transection->where(['tran_type' => 'Expense'])->where('date', $day)->sum('amount');
    }

    $stock_limit = Helpers::get_business_settings('stock_limit');

    $products = $this->product->where('quantity', '<', $stock_limit)->orderBy('quantity')->take(5)->get();
$productmoreselles = $this->product
    ->orderByDesc('order_count')
    ->limit(5)
    ->get();  
    
    $productmorerefunds = $this->product
    ->orderByDesc('refund_count')
    ->limit(5)
    ->get();  
    $accounts = $this->account->take(5)->get();

    $sellers = $this->seller->where('role', 'seller')->get();
    return view('admin-views.dashboard', compact('account', 'monthly_income', 'monthly_expense', 'accounts', 'products', 'last_month_income', 'last_month_expense', 'month', 'total_day', 'sellers', 'installments', 'orders', 'stocks', 'labels','accountw','bestsellers','salaries','perviousSalaries','sellerscredit','sellersbalance','productmoreselles','productmorerefunds'));
}


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function account_stats(Request $request): JsonResponse
    {
        if($request->statistics_type=='overall')
        {
            $total_payable_debit = $this->transection->where('tran_type','Payable')->where('debit',1)->sum('amount');
            $total_payable_credit = $this->transection->where('tran_type','Payable')->where('credit',1)->sum('amount');
            $total_payable = $total_payable_credit - $total_payable_debit;

            $total_receivable_debit = $this->transection->where('tran_type','Receivable')->where('debit',1)->sum('amount');
            $total_receivable_credit = $this->transection->where('tran_type','Receivable')->where('credit',1)->sum('amount');
            $total_receivable = $total_receivable_credit - $total_receivable_debit;

            $account = [
                'total_income' => $this->transection->where('tran_type','Income')->sum('amount'),
                'total_expense' => $this->transection->where('tran_type','Expense')->sum('amount'),
                'total_payable' => $total_payable,
                'total_receivable' => $total_receivable,
            ];
        }elseif ($request->statistics_type=='today') {

            $total_payable_debit = $this->transection->where('tran_type','Payable')->whereDay('date', '=', Carbon::today())->where('debit',1)->sum('amount');
            $total_payable_credit = $this->transection->where('tran_type','Payable')->whereDay('date', '=', Carbon::today())->where('credit',1)->sum('amount');
            $total_payable = $total_payable_credit - $total_payable_debit;

            $total_receivable_debit = $this->transection->where('tran_type','Receivable')->whereDay('date', '=', Carbon::today())->where('debit',1)->sum('amount');
            $total_receivable_credit = $this->transection->where('tran_type','Receivable')->whereDay('date', '=', Carbon::today())->where('credit',1)->sum('amount');
            $total_receivable = $total_receivable_credit - $total_receivable_debit;

            $account = [
                'total_income' => $this->transection->where('tran_type','Income')->whereDay('date', '=', Carbon::today())->sum('amount'),
                'total_expense' => $this->transection->where('tran_type','Expense')->whereDay('date', '=', Carbon::today())->sum('amount'),
                'total_payable' => $total_payable,
                'total_receivable' => $total_receivable,
            ];
        }elseif ($request->statistics_type=='month') {

            $total_payable_debit = $this->transection->where('tran_type','Payable')->whereMonth('date', '=', Carbon::today())->where('debit',1)->sum('amount');
            $total_payable_credit = $this->transection->where('tran_type','Payable')->whereMonth('date', '=', Carbon::today())->where('credit',1)->sum('amount');
            $total_payable = $total_payable_credit - $total_payable_debit;

            $total_receivable_debit = $this->transection->where('tran_type','Receivable')->whereMonth('date', '=', Carbon::today())->where('debit',1)->sum('amount');
            $total_receivable_credit = $this->transection->where('tran_type','Receivable')->whereMonth('date', '=', Carbon::today())->where('credit',1)->sum('amount');
            $total_receivable = $total_receivable_credit - $total_receivable_debit;

            $account = [
                'total_income' => $this->transection->where('tran_type','Income')->whereMonth('date', '=', Carbon::today())->sum('amount'),
                'total_expense' => $this->transection->where('tran_type','Expense')->whereMonth('date', '=', Carbon::today())->sum('amount'),
                'total_payable' => $total_payable,
                'total_receivable' => $total_receivable,
            ];
        }
        return response()->json([
            'view'=> view('admin-views.partials._dashboard-balance-stats',compact('account'))->render()
        ],200);
    }

    public function regionList()
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

    if (!in_array("region.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
                return redirect()->back();

    }
        $regions = Region::paginate(Helpers::pagination_limit());
        return view('admin-views.seller.regions.index', compact('regions'));
    }

    public function regionStore(Request $request)
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

    if (!in_array("region.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
                return redirect()->back();

    }
        $reg = new Region();
        $reg->name = $request->region_name;
        $reg->save();

        Toastr::success(translate('تم إضافة المنطقة بنجاح'));
        return back();
    }
    
    public function regionEdit($id)
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

    if (!in_array("region.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
                return redirect()->back();

    }
        $region = Region::find($id);
        return view('admin-views.seller.regions.edit', compact('region'));
    }
    
    public function regionUpdate(Request $request, $id): Factory|RedirectResponse|Application
    {
        $reg = Region::find($id);
        $reg->name = $request->region_name;
        $reg->update();

        Toastr::success(translate('تم تحديث المنطقة بنجاح'));
        return redirect()->route('admin.regions.list');
    }

    public function regionDelete($id)
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

    if (!in_array("region.destroy", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
                return redirect()->back();

    }
        $reg = Region::find($id);
        $reg->delete();
        Toastr::success(translate('تم حذف المنطقة بنجاح'));
        return back();
    }
}
