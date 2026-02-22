<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TransactionSeller;
use App\Models\Transection;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use App\Models\Salary;
use App\Models\Seller;
use App\Models\Order;
use App\Models\ProductExpire;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionSellerController extends Controller
{
    // List all transactions
  // List all transactions for the authenticated seller
public function index()
{
    // Get the authenticated user's ID as the seller_id
    $sellerId = auth()->user()->id;

    // Fetch transactions only for this seller
    $transactions = TransactionSeller::where('seller_id', $sellerId)->get();

    return response()->json($transactions, 200);
}

    // Create a new transaction
// Create a new transaction
public function store(Request $request)
{
    // Get the authenticated user's ID as the seller_id
    $sellerId = auth()->user()->id;

    // Validate the request data
    $validated = $request->validate([
        'amount' => 'required|numeric',
        'note' => 'nullable|string',
        'img' => 'nullable',
        'account_id'=>'required'
    ]);

    // Include the authenticated user's ID in the validated data
    $validated['seller_id'] = $sellerId;

    // Handle image upload if provided
    if ($request->hasFile('img')) {
        $validated['img'] = $request->file('img')->store('transaction_images', 'public');
    }

    // Create the transaction
    $transaction = TransactionSeller::create($validated);
    $lang = $request->input('lang', 'ar'); // Default language is Arabic

    return response()->json($transaction, 200);
}

public function listalltodaybyseller(Request $request)
{
    // Get authenticated seller ID
    $sellerId = auth()->user()->id;

    // Fallback if the seller is not authenticated
    if (!$sellerId) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Input Filters
    $from = $request['from'];
    $to = $request['to'];
    $sellerw = Seller::where('id', $sellerId)->first(); // Retrieve the seller details
    $lang = $request->input('lang', 'ar'); // Default language is Arabic

    // Date range and seller filter

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
    $totalSales = $totalcashSales + $totalcreditSales + $totalshbakaSales;

    // Transactions
    $totalDoneSales = Transection::where('tran_type', 4)->where($queryFilters)->sum('amount');
    $totalDoneReSales = Transection::where('tran_type', 7)->where($queryFilters)->sum('amount');
    $totalCreditcash = Transection::where('tran_type', 26)
        ->where('cash', 1)
        ->where($queryFilters)
        ->selectRaw('SUM(credit + debit + amount) as total')
        ->value('total');
    $totalCreditshabaka = Transection::where('tran_type', 26)
        ->where('cash', 3)
        ->where($queryFilters)
        ->selectRaw('SUM(amount) as total')
        ->value('total');
  
    $totalTranSeller = TransactionSeller::where($queryFilters)->sum('amount');
$totalBalance = Transection::where('tran_type', 26)
    ->where($queryFilters)
    ->get()
    ->sum(function ($transection) {
        return $transection->amount + $transection->credit + $transection->debit;
    });
    // Tax calculation
    $tax = $totalcashSales + $totalshbakaSales - $totalTranSeller;
$response = [
    'boxlist' => [
        [
            'ar' => 'اجمالي المبيعات كاش',
            'en' => 'Total Cash Sales',
            'value' => $totalcashSales
        ],
        [
            'ar' => 'إجمالي المبيعات شبكة',
            'en' => 'Total Network Sales',
            'value' => $totalshbakaSales
        ],
        [
            'ar' => 'إجمالي المبيعات أجل',
            'en' => 'Total Credit Sales',
            'value' => $totalcreditSales
        ],
        [
            'ar' => 'إجمالي المبيعات',
            'en' => 'Total Sales',
            'value' => $totalSales
        ],
        [
            'ar' => 'إجمالي المبالغ المحصلة من المبيعات',
            'en' => 'Total Amount Collected from Sales',
            'value' => $totalDoneSales
        ],
        [
            'ar' => 'إجمالي المرتجعات',
            'en' => 'Total Returns',
            'value' => $totalReSales
        ],
        [
            'ar' => 'إجمالي المبالغ المدفوع من المرتجعات',
            'en' => 'Total Amount Paid for Returns',
            'value' => $totalDoneReSales
        ],
        [
            'ar' => 'إجمالي المبالغ المحصلة كاش',
            'en' => 'Total Cash Collected',
            'value' => $totalCreditcash
        ],
        [
            'ar' => 'إجمالي المبالغ المحصلة شبكة',
            'en' => 'Total Network Collected',
            'value' => $totalCreditshabaka
        ],
        [
            'ar' => 'إجمالي المبالغ المحولة من المندوب',
            'en' => 'Total Amount Transferred from Representative',
            'value' => $totalTranSeller
        ],
        [
            'ar' => 'أجمالي المبالغ المتبقية مع المندوب',
            'en' => 'Total Remaining Amount with Representative',
            'value' => $tax
        ]
    ],
           [

            'seller' => $sellerw
        ],
];

return response()->json($response);

}


}
