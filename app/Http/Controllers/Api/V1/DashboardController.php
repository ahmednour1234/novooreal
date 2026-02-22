<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\CPU\Helpers;
use App\Models\Product;
use App\Models\Transection;
use App\Models\Installment;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\StockLimitedProductsResource;

class DashboardController extends Controller
{
    public function __construct(
        private Transection $transection,
                private Order $order,
        private Installment $installment,
        private Product $product
    ){}

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
   public function getIndex(Request $request)
{
    // Existing calculations for Payable, Receivable, Income, etc.
    $total_payable_debit = $this->transection->where('tran_type', 4)->sum('amount');
    $total_payable_credit = $this->transection->where('tran_type', 7)->sum('amount');
    $total_payable = $total_payable_credit - $total_payable_debit;

    $total_receivable_debit = $this->transection->where('tran_type',4)->sum('amount');
    $total_receivable_credit = $this->transection->where('tran_type', 7)->sum('amount');
    $total_receivable = $total_receivable_credit - $total_receivable_debit;

    $total_income = $this->order->where('active',1)->where('type', 4)->where('cash',1)->where('owner_id', auth()->user()->id)->sum('order_amount');
    $total_credit = $this->order->where('active',1)->where('type', 4)->where('cash',2)->where('owner_id', auth()->user()->id)->sum('order_amount');
    $total_shabaka = $this->order->where('active',1)->where('type', 4)->where('cash',3)->where('owner_id', auth()->user()->id)->sum('order_amount');
    $refund_total = $this->order->where('active',1)->where('type', 7)->where('owner_id', auth()->user()->id)->sum('order_amount');
    $installment = $this->installment->where('active',1)->where('seller_id', auth()->user()->id)->sum('total_price');

    // Fetch the visitors and result_visitors from the revenue table (assuming they are in the same table)
    $total_visitors = DB::table('admins')->where('id', auth()->user()->id)->sum('visitors');
    $total_result_visitors = DB::table('admins')->where('id', auth()->user()->id)->sum('result_visitors');
    $salary = DB::table('admins')->where('id', auth()->user()->id)->sum('salary');
    $holidays = DB::table('admins')->where('id', auth()->user()->id)->sum('holidays');

    $balance = DB::table('admins')->where('id', auth()->user()->id)->sum('balance');
    $credit = DB::table('admins')->where('id', auth()->user()->id)->sum('credit');

    // Compile the revenue summary including visitors and result_visitors
$revenueSummary = [
    [
        'type'=>1,
        'ar' => 'اجمالي المبيعات',
        'en' => 'Total Sales',
        'value' => ($total_income + $total_credit + $total_shabaka) ?? 0,
    ],
    [
                'type'=>1,

        'ar' => 'اجمالي المبيعات النقدية',
        'en' => 'Total Cash Sales',
        'value' => $total_income ?? 0,
    ],
    [
                'type'=>1,

        'ar' => 'اجمالي المبيعات الشبكة',
        'en' => 'Total Network Sales',
        'value' => $total_shabaka ?? 0,
    ],
    [
                'type'=>1,

        'ar' => 'اجمالي المبيعات الاجلة',
        'en' => 'Total Credit Sales',
        'value' => $total_credit ?? 0,
    ],
    [
                'type'=>1,

        'ar' => 'اجمالي التحصيلات',
        'en' => 'Total Installments',
        'value' => $installment ?? 0,
    ],
    [
                'type'=>1,

        'ar' => 'إجمالي المرتجعات',
        'en' => 'Total Refunds',
        'value' => $refund_total ?? 0,
    ],
    [
                'type'=>1,

        'ar' => 'صافي المبيعات',
        'en' => 'Net Sales',
        'value' => ($total_income + $total_credit + $total_shabaka) - ($refund_total ?? 0),
    ],
    [
                'type'=>1,

        'ar' => 'اجمالي الزيارات المطلوبة',
        'en' => 'Total Required Visits',
        'currency'=>0,
        'value' => $total_visitors ?? 0,
    ],
    [
                'type'=>1,

        'ar' => 'عدد الزيارات المنفذة',
        'en' => 'Completed Visits',
                'currency'=>0,

        'value' => $total_result_visitors ?? 0,
    ],
    [
        'ar' => 'رصيد الاجازات',
        'en' => 'Holidays Balance',
                'currency'=>0,

        'value' => $holidays ?? 0,
    ],
    [
        'ar' => 'الراتب',
        'en' => 'Salary',
        'value' => $salary ?? 0,
    ],
    [
        'ar' => 'دائن',
        'en' => 'Creditor',
        'value' => $balance ?? 0,
    ],
    [
        'ar' => 'مدين',
        'en' => 'Debtor',
        'value' => $credit ?? 0,
    ],
];

$values = array_column($revenueSummary, 'value');

// Calculate max and min values
$maxValue = max($values);
$minValue = min($values);

// Add max and min to the response
return response()->json([
    'revenueSummary' => $revenueSummary,
    'maxValue' => [
        'ar' => 'القيمة الأكبر',
        'en' => 'Highest Value',
        'value' => $maxValue,
    ],
    'minValue' => [
        'ar' => 'القيمة الأصغر',
        'en' => 'Lowest Value',
        'value' => $minValue,
    ],
], 200);

    // Additional logic for 'today' and 'month' statistics follows the same pattern,
    // add $total_visitors and $total_result_visitors in those blocks as needed.
    if ($request->statistics_type == 'today') {
        // Fetch today's visitors and result_visitors
        $total_visitors = DB::table('admins')->whereDate('created_at', Carbon::today())->where('id', auth()->user()->id)->sum('visitors');
        $total_result_visitors = DB::table('admins')->whereDate('created_at', Carbon::today())->where('id', auth()->user()->id)->sum('result_visitors');
   $salary = DB::table('admins')->where('id', auth()->user()->id)->sum('salary');
    $holidays = DB::table('admins')->where('id', auth()->user()->id)->sum('holidays');

    $balance = DB::table('admins')->where('id', auth()->user()->id)->sum('balance');
    $credit = DB::table('admins')->where('id', auth()->user()->id)->sum('credit');
        $revenueSummary = [
            'totalIncomeExpense' => $this->transection->where('account_id', 1)->where('seller_id', auth()->user()->id)->sum('amount') + $this->transection->where('account_id', 9)->where('seller_id', auth()->user()->id)->sum('amount'),
            'totalIncome' => $this->transection->where('account_id', 1)->where('seller_id', auth()->user()->id)->sum('amount'),
            'totalExpense' => $this->transection->where('account_id', 9)->where('seller_id', auth()->user()->id)->sum('amount'),
            'totalInstallment' => $this->installment->where('seller_id', auth()->user()->id)->sum('total_price'),
            'totalPayable' => $total_payable,
            'totalReceivable' => $total_receivable,
            'visitors' => $total_visitors, // Today's visitors
            'result_visitors' => $total_result_visitors, // Today's result visitors
            'balance' => $balance, // Today's result visitors
        'credit' => $credit, // Today's result visitors
                     'holidays' => $holidays, // Added result visitors
        'salary' => $salary, // Added result visitors
          'balance' => $balance, // Today's result visitors
        'credit' => $credit, // Today's result visitors
        ];

        return response()->json([
            'revenueSummary' => $revenueSummary
        ], 200);
    } elseif ($request->statistics_type == 'month') {
        // Fetch monthly visitors and result_visitors
        $total_visitors = DB::table('admins')->whereMonth('created_at', Carbon::today())->where('id', auth()->user()->id)->sum('visitors');
        $total_result_visitors = DB::table('admins')->whereMonth('created_at', Carbon::today())->where('id', auth()->user()->id)->sum('result_visitors');
   $salary = DB::table('admins')->where('id', auth()->user()->id)->sum('salary');
    $holidays = DB::table('admins')->where('id', auth()->user()->id)->sum('holidays');

    $balance = DB::table('admins')->where('id', auth()->user()->id)->sum('balance');
    $credit = DB::table('admins')->where('id', auth()->user()->id)->sum('credit');
        $revenueSummary = [
            'totalIncome' => $this->transection->where('tran_type', 'Income')->whereMonth('date', '=', Carbon::today())->sum('amount'),
            'totalExpense' => $this->transection->where('tran_type', 'Expense')->whereMonth('date', '=', Carbon::today())->sum('amount'),
            'totalPayable' => $total_payable,
            'totalReceivable' => $total_receivable,
            'visitors' => $total_visitors, // Monthly visitors
            'result_visitors' => $total_result_visitors, // Monthly result visitors
                    'holidays' => $holidays, // Added result visitors
        'salary' => $salary, // Added result visitors
          'balance' => $balance, // Today's result visitors
        'credit' => $credit, // Today's result visitors
        ];

        return response()->json([
            'revenueSummary' => $revenueSummary
        ], 200);
    }
}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function productLimitedStockList(Request $request): JsonResponse
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;

        $stock_limit = Helpers::get_business_settings('stock_limit');
        $stock_limited_product = $this->product->with('unit', 'supplier')->latest()->paginate($limit, ['*'], 'page', $offset);
        $stock_limited_products = StockLimitedProductsResource::collection($stock_limited_product);

        return response()->json([
            'total' => $stock_limited_products->total(),
            'offset' => $offset,
            'limit' => $limit,
            'stock_limited_products' => $stock_limited_products->items(),
        ], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function quantityIncrease(Request $request): JsonResponse
    {
        DB::table('products')->where('id', $request->id)->update(['quantity' => $request->quantity]);
        return response()->json(['message' => 'Product quantity updated successsfully']);
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function getFilter(Request $request)
    {
        if ($request->statistics_type == 'overall') {
            $total_payable_debit = $this->transection->where('tran_type', 'Payable')->where('debit', 1)->sum('amount');
            $total_payable_credit = $this->transection->where('tran_type', 'Payable')->where('credit', 1)->sum('amount');
            $total_payable = $total_payable_credit - $total_payable_debit;

            $total_receivable_debit = $this->transection->where('tran_type', 'Receivable')->where('debit', 1)->sum('amount');
            $total_receivable_credit = $this->transection->where('tran_type', 'Receivable')->where('credit', 1)->sum('amount');
            $total_receivable = $total_receivable_credit - $total_receivable_debit;
            $account = [
                'total_income' => $this->transection->where('tran_type', 'Income')->sum('amount'),
                'total_expense' => $this->transection->where('tran_type', 'Expense')->sum('amount'),
                'total_payable' => $total_payable,
                'total_receivable' => $total_receivable,
            ];
            return response()->json([
                'success' => true,
                'message' => "Overall Statistics",
                'data' => $account
            ], 200);
        } elseif ($request->statistics_type == 'today') {
            $total_payable_debit = $this->transection->where('tran_type', 'Payable')->whereDay('date', '=', Carbon::today())->where('debit', 1)->sum('amount');
            $total_payable_credit = $this->transection->where('tran_type', 'Payable')->whereDay('date', '=', Carbon::today())->where('credit', 1)->sum('amount');
            $total_payable = $total_payable_credit - $total_payable_debit;

            $total_receivable_debit = $this->transection->where('tran_type', 'Receivable')->whereDay('date', '=', Carbon::today())->where('debit', 1)->sum('amount');
            $total_receivable_credit = $this->transection->where('tran_type', 'Receivable')->whereDay('date', '=', Carbon::today())->where('credit', 1)->sum('amount');
            $total_receivable = $total_receivable_credit - $total_receivable_debit;

            $account = [
                'total_income' => $this->transection->where('tran_type', 'Income')->whereDay('date', '=', Carbon::today())->sum('amount'),
                'total_expense' => $this->transection->where('tran_type', 'Expense')->whereDay('date', '=', Carbon::today())->sum('amount'),
                'total_payable' => $total_payable,
                'total_receivable' => $total_receivable,
            ];
            return response()->json([
                'success' => true,
                'message' => "Today Statistics",
                'data' => $account
            ], 200);
        } elseif ($request->statistics_type == 'month') {

            $total_payable_debit = $this->transection->where('tran_type', 'Payable')->whereMonth('date', '=', Carbon::today())->where('debit', 1)->sum('amount');
            $total_payable_credit = $this->transection->where('tran_type', 'Payable')->whereMonth('date', '=', Carbon::today())->where('credit', 1)->sum('amount');
            $total_payable = $total_payable_credit - $total_payable_debit;

            $total_receivable_debit = $this->transection->where('tran_type', 'Receivable')->whereMonth('date', '=', Carbon::today())->where('debit', 1)->sum('amount');
            $total_receivable_credit = $this->transection->where('tran_type', 'Receivable')->whereMonth('date', '=', Carbon::today())->where('credit', 1)->sum('amount');
            $total_receivable = $total_receivable_credit - $total_receivable_debit;

            $account = [
                'total_income' => $this->transection->where('tran_type', 'Income')->whereMonth('date', '=', Carbon::today())->sum('amount'),
                'total_expense' => $this->transection->where('tran_type', 'Expense')->whereMonth('date', '=', Carbon::today())->sum('amount'),
                'total_payable' => $total_payable,
                'total_receivable' => $total_receivable,
            ];
            return response()->json([
                'success' => true,
                'message' => "Monthly Statistics",
                'data' => $account
            ], 200);
        }
    }

    /**
     * @return JsonResponse
     */
    public function incomeRevenue(): JsonResponse
    {
        $year_wise_expense = Transection::selectRaw("sum(`amount`) as 'total_amount', YEAR(`date`) as 'year', MONTH(`date`) as 'month'")->where(['tran_type' => 'Expense'])
            ->groupBy('month')
            ->orderBy('year')
            ->get();

        $year_wise_income = Transection::selectRaw("sum(`amount`) as 'total_amount', YEAR(`date`) as 'year', MONTH(`date`) as 'month'")->where(['tran_type' => 'Income'])
            ->groupBy('month')
            ->orderBy('year')
            ->get();

        return response()->json([
            'year_wise_expense' => $year_wise_expense,
            'year_wise_income' => $year_wise_income
        ], 200);
    }
}
