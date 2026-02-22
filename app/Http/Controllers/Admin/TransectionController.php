<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use App\Models\Transection;
use App\Models\ProductLog;
use App\Models\OrderDetail;
use App\Models\Branch;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use App\Models\Salary;
use App\Models\Seller;
use App\Models\CostCenter;
use App\Models\Order;
use App\Models\ReserveProduct;
use App\Models\ProductExpire;
use App\Models\Product;
use App\Models\TransactionSeller;
use App\Models\Stock;
use App\CPU\Helpers;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransectionController extends Controller
{
    public function __construct(
        private Transection $transection,
        private Account $account,
                private Branch $branch,
                                private CostCenter $costcenter,
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function listall(Request $request)
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

    if (!in_array("report.tax", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    $accounts = $this->account->orderBy('id', 'desc')->get();
    
    // Input Filters
    $acc_id = $request['account_id'];
    $tran_type = $request['tran_type'];
    $from = $request['from'];
    $to = $request['to'];
    $percentTax = $request['percent_tax']; // Corrected typo to 'percent_tax'
    
    // Date range filter
    $queryFilters = function ($query) use ($from, $to) {
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
    };

    // Orders
    $totalPurchases = Order::where('type', 12)->where($queryFilters)->sum('order_amount');
    $totalRePurchases = Order::where('type', 24)->where($queryFilters)->sum('order_amount');
    $totalSales = Order::where('type', 4)->where($queryFilters)->sum('order_amount');
    $totalReSales = Order::where('type', 7)->where($queryFilters)->sum('order_amount');

    // Transactions
    $totalDonePurchases = Transection::where('tran_type', 12)->where($queryFilters)->sum('amount');
    $totalDoneRePurchases = Transection::where('tran_type', 24)->where($queryFilters)->sum('amount');
    $totalDoneSales = Transection::where('tran_type', 4)->where($queryFilters)->sum('amount');
    $totalDoneReSales = Transection::where('tran_type', 7)->where($queryFilters)->sum('amount');
    $totalStart = Transection::where('tran_type', 1)->where($queryFilters)->sum('amount');
    $totalIncome = Transection::where('tran_type', 'Income')->where($queryFilters)->sum('amount');
    $totalExpense = Transection::where('tran_type', 'Expense')->where($queryFilters)->sum('amount');
    $totalCredit = Transection::where('tran_type', 13)->where($queryFilters)->sum('amount');
    $totalBalance = Transection::where('tran_type', 26)->where($queryFilters)->sum('amount');
    $totalStillStart = Transection::where('tran_type', 2)->where($queryFilters)->sum('amount');

    // Tax calculation
    $tax = $totalDoneSales + $totalBalance - $totalDonePurchases - $totalDoneReSales - $totalExpense - $totalCredit - $totalStillStart;
    $taxWithPercent = $tax * ($percentTax / 100); // Apply percentage tax if provided

    return view('admin-views.transection.listall', compact(
        'accounts',
        'totalPurchases',
        'totalRePurchases',
        'tran_type',
        'from',
        'to',
        'totalSales',
        'totalReSales',
        'totalDonePurchases',
        'totalDoneRePurchases',
        'totalDoneSales',
        'totalDoneReSales',
        'totalStart',
        'totalIncome',
        'totalExpense',
        'totalCredit',
        'totalBalance',
        'totalStillStart',
        'tax',
        'taxWithPercent',
        'percentTax'
    ));
}
    public function listalltoday(Request $request)
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

    if (!in_array("report.box", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    $accounts = $this->account->orderBy('id', 'desc')->get();
    
    // Input Filters
    $acc_id = $request['account_id'];
    $tran_type = $request['tran_type'];
    $from = $request['from'];
    $to = $request['to'];
    $percentTax = $request['percent_tax']; // Corrected typo to 'percent_tax'
    
    // Date range filter
    $queryFilters = function ($query) use ($from, $to) {
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
    };

    // Orders
    $totalPurchases = Order::where('type', 12)->where($queryFilters)->sum('order_amount');
    $totalRePurchases = Order::where('type', 24)->where($queryFilters)->sum('order_amount');
    $totalSales = Order::where('type', 4)->where($queryFilters)->sum('order_amount');
    $totalReSales = Order::where('type', 7)->where($queryFilters)->sum('order_amount');

    // Transactions
    $totalDonePurchases = Transection::where('tran_type', 12)->where($queryFilters)->sum('amount');
    $totalDoneRePurchases = Transection::where('tran_type', 24)->where($queryFilters)->sum('amount');
    $totalDoneSales = Transection::where('tran_type', 4)->where($queryFilters)->sum('amount');
    $totalDoneReSales = Transection::where('tran_type', 7)->where($queryFilters)->sum('amount');
    $totalStart = Transection::where('tran_type', 1)->where($queryFilters)->sum('amount');
    $totalIncome = Transection::where('tran_type', 'Income')->where($queryFilters)->sum('amount');
    $totalExpense = Transection::where('tran_type', 'Expense')->where($queryFilters)->sum('amount');
    $totalCredit = Transection::where('tran_type', 13)->where($queryFilters)->sum('amount');
    $totalBalance = Transection::where('tran_type', 26)->where($queryFilters)->sum('amount');
    $totalStillStart = Transection::where('tran_type', 2)->where($queryFilters)->sum('amount');
    $totallaon = Transection::where('tran_type', 34)->where($queryFilters)->sum('amount');
    $totalSalary = Salary::where($queryFilters)->sum('total');

    // Tax calculation
    $tax = $totalDoneSales +$totalDoneRePurchases +$totalBalance+$totalIncome - $totalDonePurchases - $totalDoneReSales - $totalExpense - $totalCredit - $totalStillStart-$totallaon-$totalSalary;
    $taxWithPercent = $tax * ($percentTax / 100); // Apply percentage tax if provided

    return view('admin-views.transection.listalltoday', compact(
        'accounts',
        'totalPurchases',
        'totalRePurchases',
        'tran_type',
        'from',
        'to',
        'totalSales',
        'totalReSales',
        'totalDonePurchases',
        'totalDoneRePurchases',
        'totalDoneSales',
        'totalDoneReSales',
        'totalStart',
        'totalIncome',
        'totalExpense',
        'totalCredit',
        'totalBalance',
        'totalStillStart',
        'tax',
        'taxWithPercent',
        'percentTax',
        'totallaon',
        'totalSalary'
    ));
}
public function listalltodaynew(Request $request)
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

    if (!in_array("report.pointsales", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    $from = $request['from'];
    $to = $request['to'];

    // Date range filter
    $queryFilters = function ($query) use ($from, $to) {
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
    };

    // Orders
    $totalSales = Order::where('type', 4)->where($queryFilters)
        ->selectRaw('SUM(order_amount + extra_discount) as total')->value('total');
    $totalSalesCash = Order::where('type', 4)->where('cash', 1)->where($queryFilters)
        ->selectRaw('SUM(order_amount + extra_discount) as total')->value('total');
    $totalSalesDoneCash = Order::where('type', 4)->where('cash', 1)->where($queryFilters)->sum('collected_cash');
    $totalSalesCredit = Order::where('type', 4)->where('cash', 2)->where($queryFilters)
        ->selectRaw('SUM(order_amount + extra_discount) as total')->value('total');
    $totalSalesDoneCredit = Order::where('type', 4)->where('cash', 2)->where($queryFilters)->sum('collected_cash');
    $totalSalesShabaka = Order::where('type', 4)->where('cash', 3)->where($queryFilters)
        ->selectRaw('SUM(order_amount + extra_discount) as total')->value('total');
    $totalSalesDoneShabaka = Order::where('type', 4)->where('cash', 3)->where($queryFilters)->sum('collected_cash');
    $totalSalesDiscountCash = Order::where('type', 4)->where('cash', 1)->where($queryFilters)->sum('extra_discount');
    $totalSalesDiscountCredit = Order::where('type', 4)->where('cash', 2)->where($queryFilters)->sum('extra_discount');
    $totalSalesDiscountShabaka = Order::where('type', 4)->where('cash', 3)->where($queryFilters)->sum('extra_discount');
    $totalSalesafyCash = $totalSalesCash - $totalSalesDiscountCash;
    $totalSalesafyCredit = $totalSalesCredit - $totalSalesDiscountCredit;
    $totalSalesafyShabaka = $totalSalesShabaka - $totalSalesDiscountShabaka;

    // Resales
    $totalreSales = Order::where('type', 7)->where($queryFilters)
        ->selectRaw('SUM(order_amount + extra_discount) as total')->value('total');
    $totalreSalesCash = Order::where('type', 7)->where('cash', 1)->where($queryFilters)
        ->selectRaw('SUM(order_amount + extra_discount) as total')->value('total');
    $totalreSalesDoneCash = Order::where('type', 7)->where('cash', 1)->where($queryFilters)->sum('collected_cash');
    $totalreSalesCredit = Order::where('type', 7)->where('cash', 2)->where($queryFilters)
        ->selectRaw('SUM(order_amount + extra_discount) as total')->value('total');
    $totalreSalesDoneCredit = Order::where('type', 7)->where('cash', 2)->where($queryFilters)->sum('collected_cash');
    $totalreSalesShabaka = Order::where('type', 7)->where('cash', 3)->where($queryFilters)
        ->selectRaw('SUM(order_amount + extra_discount) as total')->value('total');
    $totalreSalesDoneShabaka = Order::where('type', 7)->where('cash', 3)->where($queryFilters)->sum('collected_cash');
        $totalreSalesDiscount = Order::where('type', 7)->where($queryFilters)->sum('extra_discount');
    $totalreSalesDiscountCash = Order::where('type', 7)->where('cash', 1)->where($queryFilters)->sum('extra_discount');
    $totalreSalesDiscountCredit = Order::where('type', 7)->where('cash', 2)->where($queryFilters)->sum('extra_discount');
    $totalreSalesDiscountShabaka = Order::where('type', 7)->where('cash', 3)->where($queryFilters)->sum('extra_discount');
    $totalreSalesafyCash = $totalreSalesCash - $totalreSalesDiscountCash;
    $totalreSalesafyCredit = $totalreSalesCredit - $totalreSalesDiscountCredit;
    $totalreSalesafyShabaka = $totalreSalesShabaka - $totalreSalesDiscountShabaka;

    // Safy
    $totalsafysafycash = $totalSalesafyCash - $totalreSalesafyCash;
    $totalsafysafycredit = $totalSalesafyCredit - $totalreSalesafyCredit;
    $totalsafysafyshabaka = $totalSalesafyShabaka - $totalreSalesafyShabaka;
    $totalsafyelsafyall = $totalsafysafycash + $totalsafysafycredit;

    // Transactions
    $totalStart = Transection::where('tran_type', 1)->where($queryFilters)->sum('amount');
    $totalIncome = Transection::where('tran_type', 'Income')->where($queryFilters)
        ->selectRaw('SUM(amount + debit + credit) as total')->value('total');
    $totalExpense = Transection::where('tran_type', 'Expense')->where($queryFilters)
        ->selectRaw('SUM(amount + debit + credit) as total')->value('total');
    $totalCredit = Transection::where('tran_type', 13)->where($queryFilters)
        ->selectRaw('SUM(amount + debit + credit) as total')->value('total');
    $totalBalance = Transection::where('tran_type', 26)->where($queryFilters)
        ->selectRaw('SUM(amount + debit + credit) as total')->value('total');
    $totalabd = Transection::where('tran_type', 200)->where($queryFilters)
        ->selectRaw('SUM(amount + debit ) as total')->value('total');
    $totalsrf = Transection::where('tran_type', 100)->where($queryFilters)
        ->selectRaw('SUM(amount + debit) as total')->value('total');
    $totalStillStart = Transection::where('tran_type', 2)->where($queryFilters)
        ->selectRaw('SUM(amount + debit + credit) as total')->value('total');
    $totallaon = Transection::where('tran_type', 34)->where($queryFilters)->sum('amount');
    $totalSalary = Salary::where($queryFilters)->sum('total');

    // Tax calculation
    $tax = $totalBalance + $totalIncome + $totalabd;
    $taxexpense = $totalExpense + $totalsrf + $totalCredit + $totalStillStart;
    $final = $totalsafyelsafyall + $tax - $taxexpense;

    return view('admin-views.transection.final', compact(
        'from', 'to', 'totalSales', 'totalSalesCash', 'totalSalesDoneCash', 'totalSalesCredit',
        'totalSalesDoneCredit', 'totalSalesShabaka', 'totalSalesDoneShabaka', 'totalSalesDiscountCash',
        'totalSalesDiscountCredit', 'totalSalesDiscountShabaka', 'totalSalesafyCash', 'totalSalesafyCredit',
        'totalSalesafyShabaka', 'totalreSales', 'totalreSalesCash', 'totalreSalesDoneCash', 'totalreSalesCredit',
        'totalreSalesDoneCredit', 'totalreSalesShabaka','totalreSalesDiscount', 'totalreSalesDoneShabaka', 'totalreSalesDiscountCash',
        'totalreSalesDiscountCredit', 'totalreSalesDiscountShabaka', 'totalreSalesafyCash', 'totalreSalesafyCredit',
        'totalreSalesafyShabaka', 'totalsafysafycash', 'totalsafysafycredit', 'totalsafysafyshabaka',
        'totalsafyelsafyall', 'totalStart', 'totalIncome', 'totalExpense', 'totalCredit', 'totalBalance',
        'totalabd', 'totalsrf', 'totalStillStart', 'totallaon', 'totalSalary', 'tax', 'taxexpense', 'final'
    ));
}

public function listalltodaybyseller(Request $request)
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

    if (!in_array("report.boxseller", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    $accounts = $this->account->orderBy('id', 'desc')->get();

    // Input Filters
    $acc_id = $request['account_id'];
    $tran_type = $request['tran_type'];
    $from = $request['from'];
    $to = $request['to'];
    $percentTax = $request['percent_tax'];
    $sellerId = $request['seller_id']; // Add seller_id filter
    $sellerw = Seller::where('id', $sellerId)->first(); // Retrieve the seller details
    $sellers = Seller::all();

    // Date range and seller filter
    $queryFilters = function ($query) use ($from, $to, $sellerId) {
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
        if ($sellerId) {
            $query->where('seller_id', $sellerId); // Apply seller_id filter for transactions
        }
    };

    $orderFilters = function ($query) use ($from, $to, $sellerId) {
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
        if ($sellerId) {
            $query->where('owner_id', $sellerId); // Apply seller_id filter for orders
        }
    };

    // Orders
    $totalPurchases = Order::where('type', 12)->where($orderFilters)->sum('order_amount');
    $totalRePurchases = Order::where('type', 24)->where($orderFilters)->sum('order_amount');
    $totalSales = Order::where('type', 4)->where($orderFilters)->sum('order_amount');
    $totalcashSales = Order::where('type', 4)->where($orderFilters)->where('cash', 1)->sum('order_amount');
    $totalcreditSales = Order::where('type', 4)->where($orderFilters)->where('cash', 2)->sum('order_amount');
        $totalshbakaSales = Order::where('type', 4)->where($orderFilters)->where('cash', 3)->sum('order_amount');
    $totalReSales = Order::where('type', 7)->where($orderFilters)->sum('order_amount');
    $totalSales = $totalcashSales + $totalcreditSales+$totalshbakaSales;

    // Transactions
    $totalDonePurchases = Transection::where('tran_type', 12)->where($queryFilters)->sum('amount');
    $totalDoneRePurchases = Transection::where('tran_type', 24)->where($queryFilters)->sum('amount');
    $totalDoneSales = Transection::where('tran_type', 4)->where($queryFilters)->sum('amount');
    $totalDoneReSales = Transection::where('tran_type', 7)->where($queryFilters)->sum('amount');
    $totalStart = Transection::where('tran_type', 1)->where($queryFilters)->sum('amount');
    $totalIncome = Transection::where('tran_type', 'Income')->where($queryFilters)->sum('amount');
    $totalExpense = Transection::where('tran_type', 'Expense')->where($queryFilters)->sum('amount');
    $totalCreditcash = Transection::where('tran_type', 26)
    ->where('cash', 1)
    ->where($queryFilters)
    ->selectRaw('SUM(credit + debit+amount) as total')
    ->value('total');
        $totalCreditshabaka = Transection::where('tran_type', 26)
    ->where('cash', 3)
    ->where($queryFilters)
    ->selectRaw('SUM(credit + debit+amount) as total')
    ->value('total');;
$totalBalance = Transection::where('tran_type', 26)
    ->where($queryFilters)
    ->get()
    ->sum(function ($transection) {
        return $transection->amount + $transection->credit + $transection->debit;
    });
    // dd($totalBalance);
        $totalTranSeller = TransactionSeller::where($queryFilters)->sum('amount');
    $totalStillStart = Transection::where('tran_type', 2)->where($queryFilters)->sum('amount');
    $totallaon = Transection::where('tran_type', 34)->where($queryFilters)->sum('amount');
    $totalSalary = Salary::sum('total');

    // Tax calculation
    $tax =$totalDoneSales+$totalBalance-$totalTranSeller;

    $taxWithPercent = $percentTax ? $tax * ($percentTax / 100) : 0;

    return view('admin-views.transection.listalltodayseller', compact(
        'accounts',
        'totalPurchases',
        'totalRePurchases',
        'totalcashSales',
        'totalcreditSales',
        'totalshbakaSales',
        'tran_type',
        'from',
        'to',
        'totalSales',
        'totalReSales',
        'totalDonePurchases',
        'totalDoneRePurchases',
        'totalDoneSales',
        'totalDoneReSales',
        'totalStart',
        'totalIncome',
        'totalExpense',
        'totalCreditcash',
        'totalCreditshabaka',
        'totalBalance',
        'totalStillStart',
        'tax',
        'taxWithPercent',
        'percentTax',
        'totallaon',
        'totalSalary',
        'totalTranSeller',
        'sellerId', // Include seller_id for reference in the view
        'sellers',
        'sellerw'
    ));
}



    public function listallbox(Request $request)
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

    if (!in_array("incomelist.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    } 
    $accounts = $this->account->orderBy('id', 'desc')->get();
    
    // Input Filters
    $acc_id = $request['account_id'];
    $tran_type = $request['tran_type'];
    $from = $request['from'];
    $to = $request['to'];
    $percentTax = $request['percent_tax']; // Corrected typo to 'percent_tax'
    
    // Date range filter
    $queryFilters = function ($query) use ($from, $to) {
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
    };

    // Orders
    $totalPurchases = Order::where('type', 12)
        ->where($queryFilters)
        ->sum(DB::raw('order_amount  - total_tax'));

        $totalPurchasesdiscount = Order::where('type', 12)->where($queryFilters)->sum('extra_discount');
        $totalPurchasesdiscountgain = Transection::where('tran_type', 30)->where($queryFilters)->sum('amount');
$finalPurchasediscount=$totalPurchasesdiscount+$totalPurchasesdiscountgain;
   $totalRePurchases = Order::where('type', 24)
    ->where($queryFilters)
    ->sum(DB::raw('order_amount - total_tax'));

$totalSales = Order::where('type', 4)
    ->where($queryFilters)
    ->sum(DB::raw('order_amount + extra_discount - total_tax'));

    $totalReSales = Order::where('type', 7)
    ->where($queryFilters)
    ->sum(DB::raw('order_amount+extra_discount-total_tax'));


    $totalSalesdiscount = Order::where('type', 4)->where($queryFilters)->sum('extra_discount');
$totalReSales = Order::where('type', 7)
    ->where($queryFilters)
    ->sum(DB::raw('order_amount'));
$taxSetting = \App\Models\BusinessSetting::where('key', 'tax')->first();
$taxRate = $taxSetting ? $taxSetting->value : 0; 

$productExpireData = ProductExpire::where($queryFilters)
    ->with('product') // Ensure the relation is defined in ProductExpire model
    ->get()
    ->sum(function ($item) use ($taxRate) { // Pass $taxRate inside the closure
        $subtotal = $item->quantity * ($item->price ?? 0);
        $taxAmount = $subtotal * ($taxRate / 100);
        return $subtotal + $taxAmount;
    });

  if ($request->has('from') && $request->has('to')) {
    // إذا كانت القيم موجودة، احسب اليوم السابق لها
    $previousDayStart = Carbon::parse($request->input('from'))->subDay()->startOfDay(); // بداية اليوم السابق
    $previousDayEnd = Carbon::parse($request->input('to'))->subDay()->endOfDay(); // نهاية اليوم السابق
} else {
    // إذا لم تكن القيم موجودة، استخدم اليوم السابق للتاريخ الحالي
    $previousDayStart = now()->subDay()->startOfDay(); // بداية اليوم السابق
    $previousDayEnd = now()->subDay()->endOfDay(); // نهاية اليوم السابق
}$totalStartValue = 0; // Total start balance
$totalEndValue = 0; // Total end balance
$totalStartQuantity = 0; // Total start quantity
$totalEndQuantity = 0; // Total end quantity

$start_date = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : null;
$end_date = $request->input('to') ? Carbon::parse($request->input('to'))->endOfDay() : null;

$finalProducts = collect(); // Collection to hold final product data

// Query product logs with optional date range filtering
$productLogsQuery = ProductLog::query();
if ($start_date && $end_date) {
    $productLogsQuery->whereBetween('created_at', [$start_date, $end_date]);
}
$productLogs = $productLogsQuery->get()->groupBy('product_id');

// First Loop: Calculate start balances and quantities
foreach ($productLogs as $productId => $logs) {
    $product = Product::find($productId);

    // Query logs before start date
    $startLogsQuery = ProductLog::where('product_id', $productId);
    if ($start_date) {
        $startLogsQuery->where('created_at', '<', $start_date);
    }
    $startLogs = $startLogsQuery->get();

    // Initialize start summary
    $startSummary = [
        'sold' => 0,
        'sale_returned' => 0,
        'purchased' => 0,
        'purchase_returned' => 0,
        'issued' => 0,
        'delegate_returned' => 0,
        'damaged' => 0,
        'initial' => 0,
    ];

    // Summarize logs for start period
    foreach ($startLogs as $log) {
        switch ($log->type) {
            case 4: $startSummary['sold'] += $log->quantity; break;
            case 7: $startSummary['sale_returned'] += $log->quantity; break;
            case 12: $startSummary['purchased'] += $log->quantity; break;
            case 24: $startSummary['purchase_returned'] += $log->quantity; break;
            case 100: $startSummary['issued'] += $log->quantity; break;
            case 200: $startSummary['delegate_returned'] += $log->quantity; break;
            case 0: $startSummary['damaged'] += $log->quantity; break;
            case 1: $startSummary['initial'] += $log->quantity; break;
        }
    }

    // Calculate start quantity
    $startQuantity = $startSummary['purchased'] + $startSummary['sale_returned'] + $startSummary['delegate_returned'] + $startSummary['initial']
        - $startSummary['damaged'] - $startSummary['issued'] - $startSummary['purchase_returned'] - $startSummary['sold'];

    // Fetch latest order details before start date
    $orderDetails = OrderDetail::where('product_id', $productId)
        ->join('orders', 'orders.id', '=', 'order_details.order_id')
        ->where('orders.type', 12)
        ->when($start_date, fn($query) => $query->where('order_details.created_at', '<', $start_date))
        ->latest('order_details.created_at')
        ->first();

    // Determine purchase price and unit price
    $purchasePrice = $orderDetails ? $orderDetails->price : $product->purchase_price;
    $unitPrice = $orderDetails ? $orderDetails->unit : 1;

    if ($orderDetails) {
        $order = $orderDetails->order;
        $finalOrder = $order->order_amount - $order->total_tax + $order->extra_discount;
        $discountPercentage = ($order->extra_discount / $finalOrder) * 100;
        $purchasePrice -= ($purchasePrice * ($discountPercentage / 100));
    }

    // Calculate start balance
    $startBalance = $purchasePrice * $startQuantity;

    // Aggregate total start values
    $totalStartValue += $startBalance;
    $totalStartQuantity += $startQuantity;

    // Add product to final collection
    $finalProducts->push([
        'product_id' => $productId,
        'product_name' => $product->name ?? 'N/A',
        'product_code' => $product->product_code ?? 'N/A',
        'start' => $startQuantity,
        'start_balance' => $startBalance,
    ]);
}

// Second Loop: Calculate end balances and quantities
foreach ($productLogs as $productId => $logs) {
    $product = Product::find($productId);

    // Calculate end quantity
    $endQuantity = $logs->where('type', 12)->sum('quantity') // Purchased
        + $logs->where('type', 7)->sum('quantity') // Sale Returned
        + $logs->where('type', 200)->sum('quantity') // Delegate Returned
        + $logs->where('type', 1)->sum('quantity') // Initial
        - $logs->where('type', 0)->sum('quantity') // Damaged
        - $logs->where('type', 100)->sum('quantity') // Issued
        - $logs->where('type', 24)->sum('quantity') // Purchase Returned
        - $logs->where('type', 4)->sum('quantity'); // Sold

    // Calculate end balance
    $purchasePrice = $product->purchase_price;
    $endBalance = $purchasePrice * $endQuantity;

    // Aggregate total end values
    $totalEndValue += $endBalance;
    $totalEndQuantity += $endQuantity;

    // Update product details in final collection
    $finalProducts->where('product_id', $productId)->transform(function ($productData) use ($endQuantity, $endBalance) {
        $productData['now'] = $endQuantity;
        $productData['now_balance'] = $endBalance;
        return $productData;
    });
}

// Final calculations
$productsstart = $totalStartValue;
$productsnow = $totalEndValue + $totalStartValue;
$productsnoww = $totalEndValue + $totalStartValue;

    // Transactions
    $totalDonePurchases = Transection::where('tran_type', 12)->where($queryFilters)->sum('amount');
    $totalDoneRePurchases = Transection::where('tran_type', 24)->where($queryFilters)->sum('amount');
    $totalDoneSales = Transection::where('tran_type', 4)->where($queryFilters)->sum('amount');
    $totalDoneReSales = Transection::where('tran_type', 7)->where($queryFilters)->sum('amount');
    $totalStart = Transection::where('tran_type', 1)->where($queryFilters)->sum('amount');
    $totalIncome = Transection::where('tran_type', 'Income')->where($queryFilters)->sum('amount');
    $totalExpense = Transection::where('tran_type', 'Expense')->where($queryFilters)->sum('amount');
    $totalCredit = Transection::where('tran_type', 13)->where($queryFilters)->sum('amount');
    $totalBalance = Transection::where('tran_type', 26)->where($queryFilters)->sum('amount');
    $totalStillStart = Transection::where('tran_type', 2)->where($queryFilters)->sum('amount');
        $totalStillStartaccounts = Transection::where('tran_type', 0)->wherenotnull('account_id')->where($queryFilters)->sum('debit_account');
    $totallaon = Transection::where('tran_type', 34)->where($queryFilters)->sum('amount');
        $expense = Transection::where('tran_type', 100)->where($queryFilters)->sum('amount');
    $totalSalary = Salary::where($queryFilters)->sum('total');
$netsales=$totalSales-$totalSalesdiscount-$totalReSales-$productExpireData;
$netpurchase=$totalPurchases-$totalRePurchases-$finalPurchasediscount;
    // Tax calculation
    $totalStart=$totalStart+$totalStillStartaccounts;
    $tax = $totalDoneSales +$totalDoneRePurchases +$totalBalance+$totalIncome - $totalDonePurchases - $totalDoneReSales - $totalExpense-$expense - $totalCredit - $totalStillStart-$totallaon-$totalSalary;
    $taxWithPercent = $tax * ($percentTax / 100); // Apply percentage tax if provided
$totalexpenses=$totalSalary+$totallaon+$expense;
$totalincomes=$totalIncome+$totalStart;
    return view('admin-views.transection.listallbox', compact(
        'accounts',
        'totalPurchases',
        'totalRePurchases',
        'tran_type',
        'from',
        'to',
        'totalSales',
        'totalReSales',
        'totalDonePurchases',
        'totalDoneRePurchases',
        'totalDoneSales',
        'totalDoneReSales',
        'totalStart',
        'totalIncome',
        'totalExpense',
        'totalCredit',
        'totalBalance',
        'totalStillStart',
        'tax',
        'taxWithPercent',
        'percentTax',
        'totallaon',
        'totalSalesdiscount',
        'totalSalary',
        'productExpireData',
        'netsales',
        'productsstart',
        'finalPurchasediscount',
        'netpurchase',
        'expense',
        'productsnow',
        'productsnoww',
        'totalexpenses',
        'totalincomes'
    ));
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

    if (!in_array("transection.listkoyod", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    } 
        $accounts = $this->account->doesntHave('childrenn')->orderBy('id','desc')->get();
                $branches = $this->branch->orderBy('id','desc')->get();
        $acc_id = $request['account_id'];
        $branch_id = $request['branch_id'];
        $tran_type = $request['tran_type'];
        $from = $request['from'];
        $to = $request['to'];

      $query = $this->transection
    ->when($acc_id != null, function ($q) use ($request) {
        return $q->where(function ($query) use ($request) {
            // إذا كانت tran_type = 'Transfer'
            $query->where(function ($subQuery) use ($request) {
                $subQuery
                         ->where('account_id', $request['account_id']);
            })
            // إذا كانت tran_type ليست 'Transfer'
            ->orWhere(function ($subQuery) use ($request) {
                $subQuery
                         ->where(function ($innerQuery) use ($request) {
                             $innerQuery->where('account_id', $request['account_id'])
                                        ->orWhere('account_id_to', $request['account_id']);
                         });
            });
        });
    })
            ->when($tran_type!=null, function($q) use ($request){
                return $q->where('tran_type',$request['tran_type']);
            })
                ->when($branch_id!=null, function($q) use ($request){
                return $q->where('branch_id',$request['branch_id']);
            })
            ->when($from!=null, function($q) use ($request){
                return $q->whereBetween('date', [$request['from'], $request['to']]);
            });

        $transections = $query->orderBy('id','asc')->paginate(Helpers::pagination_limit())->appends(['account_id' => $request['account_id'],'tran_type'=>$request['tran_type'],'from'=>$request['from'],'to'=>$request['to']]);
        return view('admin-views.transection.list',compact('accounts','transections','acc_id','tran_type','from','to','branch_id','branches'));
    }
        public function listkoyod(Request $request)
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

    if (!in_array("transection.list", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    } 
        $accounts = $this->account->orderBy('id','desc')->get();
                $branches = $this->branch->orderBy('id','desc')->get();
                                $costcenters = $this->costcenter->orderBy('id','desc')->get();
        $acc_id = $request['account_id'];
                $cost_id = $request['cost_id'];
        $branch_id = $request['branch_id'];
        $tran_type = $request['tran_type'];
        $from = $request['from'];
        $to = $request['to'];

      $query = $this->transection
    ->when($acc_id != null, function ($q) use ($request) {
        return $q->where(function ($query) use ($request) {
            // إذا كانت tran_type = 'Transfer'
            $query->where(function ($subQuery) use ($request) {
                $subQuery
                         ->where('account_id', $request['account_id']);
            })
            // إذا كانت tran_type ليست 'Transfer'
            ->orWhere(function ($subQuery) use ($request) {
                $subQuery
                         ->where(function ($innerQuery) use ($request) {
                             $innerQuery->where('account_id', $request['account_id'])
                                        ->orWhere('account_id_to', $request['account_id']);
                         });
            });
        });
    })
            ->when($tran_type!=null, function($q) use ($request){
                return $q->where('tran_type',$request['tran_type']);
            })
                ->when($branch_id!=null, function($q) use ($request){
                return $q->where('branch_id',$request['branch_id']);
            })
            ->when($from!=null, function($q) use ($request){
                return $q->whereBetween('date', [$request['from'], $request['to']]);
            });

        $transections = $query->orderBy('id','asc')->paginate(Helpers::pagination_limit())->appends(['account_id' => $request['account_id'],'tran_type'=>$request['tran_type'],'from'=>$request['from'],'to'=>$request['to']]);
        return view('admin-views.transection.listkoyod',compact('accounts','transections','acc_id','tran_type','from','to','branch_id','branches','cost_id','costcenters'));
    }

    /**
     * @param Request $request
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function export(Request $request): StreamedResponse|string
    {
        $acc_id = $request['account_id'];
        $tran_type = $request['tran_type'];
        $from = $request['from'];
        $to = $request['to'];
        if($acc_id==null && $tran_type==null && $to==null && $from !=null)
        {
            $transections = $this->transection->whereMonth('date',Carbon::now()->month)->get();

        }else{
            $transections = $this->transection->
                when($acc_id!=null, function($q) use ($request){
                    return $q->where('account_id',$request['account_id']);
                })
                ->when($tran_type!=null, function($q) use ($request){
                    return $q->where('tran_type',$request['tran_type']);
                })
                ->when($from!=null, function($q) use ($request){
                    return $q->whereBetween('date', [$request['from'], $request['to']]);
                })->get();
        }

        $storage = [];
        foreach($transections as $transection)
        {
            $storage[] = [
                'transection_type' => $transection->tran_type,
                'account' => $transection->account ?  $transection->account->account : '',
                'amount' => $transection->amount,
                'description' => $transection->description,
                'debit' => $transection->debit == 1 ? $transection->amount : 0,
                'credit' => $transection->credit == 1 ? $transection->amount : 0,
                'balance' => $transection->balance,
                'date' => $transection->date,
            ];
        }
        return (new FastExcel($storage))->download('transection_history.xlsx');
    }
}
