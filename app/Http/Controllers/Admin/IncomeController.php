<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\CostCenter;
use App\Models\Transection;
use App\CPU\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;
  use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IncomeController extends Controller
{
    public function __construct(
        private Transection $transection,
        private Account $account,
                private CostCenter $costcenter,
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function add(Request $request)
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

    if (!in_array("income.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $accounts = $this->account->wherenotnull('parent_id')->orderBy('id','desc')->get();
                $costcenters = $this->costcenter->orderBy('id','desc')->get();
        $search = $request['search'];
        $from = $request->from;
        $to = $request->to;
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $query = $this->transection->where('tran_type','Income')->
                    where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('description', 'like', "%{$value}%");
                        }
                });
            $query_param = ['search' => $request['search']];
        }else
         {
            $query = $this->transection->where('tran_type','Income')
                                ->when($from!=null, function($q) use ($request){
                                     return $q->whereBetween('date', [$request['from'], $request['to']]);
            });

         }
        $incomes = $query->latest()->paginate(Helpers::pagination_limit())->appends(['search' => $request['search'],'from'=>$request['from'],'to'=>$request['to']]);
            $totalAmount = $incomes->sum('amount');

        return view('admin-views.income.add',compact('accounts','incomes','search','from','to','totalAmount','costcenters'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */


public function store(Request $request): RedirectResponse
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

    if (!in_array("income.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    $request->validate([
        'account_id' => 'required',
        'description' => 'required',
        'amount' => 'required|numeric|min:1',
    ]);

    $img = null;
    if ($request->hasFile('img')) {
        $img = Helpers::update('shop/', null, 'png', $request->file('img'));
    }

    $account = $this->account->find($request->account_id);
    $transection = $this->transection;
    $transection->tran_type = 'Income';
    $transection->account_id = $request->account_id;
    $transection->cost_id = $request->cost_id;
    $transection->amount = $request->amount;
    $transection->description = $request->description;
    $transection->balance = $account->balance + $request->amount;
    $transection->debit_account =  $request->amount;
        $transection->credit_account = 0;
        $transection->balance_account = $account->total_in + $request->amount-$account->total_out;
    $transection->date = $request->date;
    $transection->img = $img;
    
    // Set the seller_id from the authenticated admin user
    $transection->seller_id = Auth::guard('admin')->id();
$transection->branch_id = Auth::guard('admin')->user()->branch_id;

    $transection->save();

    $account->total_in += $request->amount;
    $account->balance += $request->amount;
    $account->save();

    Toastr::success(translate('تمت إضافة إيراد بنجاح'));
    return back();
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
    $query = $this->transection->where('tran_type', $type);

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

}
