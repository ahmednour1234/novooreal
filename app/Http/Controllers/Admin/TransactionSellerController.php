<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransactionSeller;
use App\Models\Seller;
use App\Models\Account;
use App\Models\Transection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;

class TransactionSellerController extends Controller
{
    // List all transactions for the authenticated admin
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

    if (!in_array("transectionseller.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    $adminId = Auth::guard('admin')->id(); // Get the authenticated admin's ID

    // Fetch sellers linked to the authenticated admin through the admin_seller table
    $sellers = Seller::join('admin_sellers', 'admins.id', '=', 'admin_sellers.seller_id')
        ->where('admin_sellers.admin_id', $adminId)
        ->select('admins.*')
        ->get();

    // Fetch transactions for selected seller and date range filters
    $sellerId = $request->input('seller_id');
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    $search = $request->input('search');

    $query = TransactionSeller::where('seller_id', $sellerId);

    if (!empty($sellerId)) {
        $query->where('seller_id', $sellerId);
    }

    if (!empty($startDate) && !empty($endDate)) {
        $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    if (!empty($search)) {
        $query->where(function ($query) use ($search) {
            $query->where('sellers.f_name', 'like', "%$search%")
                  ->orWhere('sellers.l_name', 'like', "%$search%")
                  ->orWhere('sellers.email', 'like', "%$search%");
        });
    }

    // Paginate results for the view
    $transactions = $query->paginate(10);
// dd($transactions);

    return view('admin-views.transaction_sellers.index', compact('transactions', 'sellers'));
}


 public function status(Request $request, $id)
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

    if (!in_array("transectionseller.approve", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    // Fetch the TransactionSeller record or fail
    $transactionseller = TransactionSeller::findOrFail($id);


    // Extract transaction details
    $amount = $transactionseller->amount; // Transaction amount
    $seller = $transactionseller->sellers; // Assuming `sellers` is a defined relationship

    // Get the account based on the account_id in TransactionSeller
    $account = Account::findOrFail($transactionseller->account_id); // Fetch the account using account_id
$account_to=Account::findOrFail($seller->account_id);
    // Update the transaction status (active/inactive)
    $transactionseller->active = $request->input('active');
    $transactionseller->save();

    // Create a new Transection record
    $transaction = new Transection;
    $transaction->tran_type = 500;
    $transaction->account_id = $account->id; // Now using the account's ID from the fetched account
       $transaction->account_id_to = $account_to->id; // Now using the account's ID from the fetched account
    $transaction->amount = $amount;
    $transaction->description = $transactionseller->note; // Assuming `note` exists on TransactionSeller
            $transaction->debit = $amount ; // Using the fetched account's balance
    $transaction->credit =0 ; // Using the fetched account's balance

    $transaction->balance = $account->balance - $amount ; // Using the fetched account's balance
        $transaction->debit_account = 0 ; // Using the fetched account's balance
    $transaction->credit_account =$amount ; // Using the fetched account's balance

    $transaction->balance_account = $account_to->balance + $amount; // Using the fetched account's balance
    $transaction->date = now(); // Use current timestamp for the transaction date
    $transaction->seller_id = $seller->id; // Seller ID
        $transaction->branch_id = $seller->branch_id; // Seller ID
    $transaction->img = $transactionseller->img; // Assuming `img` exists on TransactionSeller
    $transaction->save();
    $seller = Seller::findOrFail($seller->id);

    // Update the seller's account
    $account->total_out += $amount;
    $account->balance -= $amount;
    $account->save();
    $account_to->total_in += $amount;
    $account_to->balance += $amount;
    $account_to->save();
    // Adjust the seller's credit
    if ($seller) {
        $seller->credit -= $amount;
        $seller->save();
    }

    // Display success notification
    Toastr::success('تمت الموافقة علي التحويل');

    return redirect()->back();
}

}
