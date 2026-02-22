<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Salary; 
use App\Models\Account;
use App\Models\Transection;
use App\Models\CostCenter;
use App\Models\Seller;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AdminSeller;
use Illuminate\Support\Facades\Validator;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SalaryController extends Controller
{
public function create()
{
    // Retrieve the authenticated admin's ID and record
    $adminId = Auth::guard('admin')->id();
    $admin = DB::table('admins')->where('id', $adminId)->first();

    if (!$admin) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    // Retrieve the admin's role and decode its data
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

    if (!in_array("salary.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    // Retrieve all salary records
    $salaries = Salary::all();

    // Retrieve accounts for "accounts_to":
    // Get accounts whose parent_id is 49 or whose parent's parent_id is 49
    $accounts_to =Account::where('id',49)->
        orwhere(function ($query) {
            $query->whereIn('parent_id', [49,49])
                  ->orWhereHas('parent', function ($q) {
                      $q->whereIn('parent_id', [49,49]);
                  });
        })
        ->orderBy('id', 'desc')
        ->get();

    // Retrieve accounts:
    // Accounts with id in [8, 14] or accounts whose parent_id is in [8, 14] or whose parent's parent_id is in [8, 14]
    $accounts = Account::whereIn('id', [8, 14])
        ->orWhere(function ($query) {
            $query->whereIn('parent_id', [8, 14])
                  ->orWhereHas('parent', function ($q) {
                      $q->whereIn('parent_id', [8, 14]);
                  });
        })
        ->orderBy('id', 'desc')
        ->get();

    // Retrieve cost centers and sellers
    $costcenters = CostCenter::all();
    $sellers = Seller::all();

    return view('admin-views.salary.index', compact('salaries', 'sellers', 'accounts', 'costcenters', 'accounts_to'));
}

     public function createrating()
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

    if (!in_array("staff.rate", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        // Retrieve all salary records
        $salaries = Salary::all();
$adminId = Auth::guard('admin')->id();
$sellerIds = AdminSeller::where('admin_id', $adminId)->pluck('seller_id');

// Get sellers associated with the admin
$sellers = Seller::whereIn('id', $sellerIds)->get();

        return view('admin-views.salary.rating', compact('salaries','sellers'));
    }
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

    if (!in_array("salary.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    } 
    // Fetch all sellers for the dropdown filter
    $sellers = Seller::all();

    // Get search parameters
    $sellerId = $request->input('seller_id');
    $month = $request->input('month');

    // Query salaries based on search parameters
    $query = Salary::with('seller'); // eager load seller details

    // Apply filters if they are set
    if ($sellerId) {
        $query->where('seller_id', $sellerId);
    }

    if ($month) {
        $query->where('month', $month); // Use the month directly as "YYYY-MM"
    }

    // Paginate results
    $salaries = $query->paginate(10);

    // Pass data to the view
    return view('admin-views.salary.list', compact('salaries', 'sellers', 'sellerId', 'month'));
}


public function showsalary($id)
{
    // Assuming the Salary model has a seller relationship
    $salary = Seller::where('id', $id)->first();

    if (!$salary) {
        return response()->json(['message' => 'Salary record not found.'], 404);
    }

    return response()->json($salary);
}

    public function show($id)
    {
        // Find the salary record by ID
        $salary = Salary::find($id);

        if (!$salary) {
            Toastr::error('Salary record not found.');
            return redirect()->route('salaries.index');
        }

        return view('admin.salaries.show', compact('salary'));
    }

public function store(Request $request)
{
    // Uncomment this line if you need to debug request data
    // dd($request->all());

    // Validate incoming request
    $validator = Validator::make($request->all(), [
        'seller_id'             => 'required|exists:admins,id',
        'account_id'            => 'required|exists:accounts,id',
        'account_id_to'         => 'required|exists:accounts,id',
        'salary'                => 'nullable|numeric', // corrected typo
        'commission'            => 'nullable|numeric',
        'number_of_visitors'    => 'nullable|integer',
        'result_of_visitors'    => 'nullable|numeric',
        'salary_of_visitors'    => 'nullable|numeric',
        'number_of_days'        => 'nullable|numeric',
        'transport_amount'      => 'nullable|numeric',
        'taxes'                 => 'nullable|numeric',
        'insurance'             => 'nullable|numeric',
        'discount'              => 'nullable|numeric',
        'other'                 => 'nullable|numeric',
        'total'                 => 'nullable|numeric',
        'score'                 => 'required|numeric',
        'notemanager'           => 'nullable',
        'note'                  => 'nullable',
        'month'                 => 'required|date_format:Y-m',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    // Check for an existing salary record for this seller and month
    $existingSalary = Salary::where('seller_id', $request->seller_id)
        ->where('month', $request->month)
        ->first();

    if ($existingSalary) {
        Toastr::success('انت دفعت للموظف ده المرتب في الشهر ده.');
        return redirect()->route('admin.salaries.index');
    }

    // Start a database transaction
    DB::beginTransaction();
    try {
        // Create and save the salary record manually using save()
        $salaryRecord = new Salary;
        $salaryRecord->seller_id          = $request->seller_id;
        $salaryRecord->month              = $request->month;
        $salaryRecord->salary             = $request->salary;
        $salaryRecord->commission         = $request->commission;
        $salaryRecord->number_of_visitors = $request->number_of_visitors??0;
        $salaryRecord->result_of_visitors = $request->result_of_visitors??0;
        $salaryRecord->salary_of_visitors = $request->salary_of_visitors;
        $salaryRecord->number_of_days     = $request->number_of_days;
        $salaryRecord->transport_amount   = $request->transport_amount;
        $salaryRecord->taxes              = $request->taxes;
        $salaryRecord->insurance          = $request->insurance;
        $salaryRecord->discount           = $request->discount;
        $salaryRecord->other              = $request->other;
        $salaryRecord->total              = $request->total;
        $salaryRecord->score              = $request->score;
        $salaryRecord->notemanager        = $request->notemanager;
        $salaryRecord->note               = $request->note;
        $salaryRecord->save();

        // Retrieve the accounts for updating balances
        $account    = Account::find($request->account_id);
        $account_to = Account::find($request->account_id_to);

        // Calculate net salary amount (salary minus any deductions)
        $netSalary = $request->salary - $request->deductions_total;

        // Transaction for salary payment from account to account_to
        $transection = new Transection;
        $transection->tran_type       = 'salary';
        $transection->seller_id       = Auth::guard('admin')->id();
        $transection->cost_id         = $request->cost_id;
        $transection->account_id      = $request->account_id;
        $transection->account_id_to   = $request->account_id_to;
        $transection->branch_id       = auth('admin')->user()->branch_id;
        $transection->amount          = $netSalary;
        $transection->description     = 'دفع مرتب للموظفيين';
        $transection->debit           = $netSalary;
        $transection->credit          = 0;
        $transection->balance         = $account->balance - $netSalary;
        $transection->debit_account   = 0;
        $transection->credit_account  = $netSalary;
        $transection->balance_account = $account_to->balance + $netSalary;
        $transection->salary_id       = $salaryRecord->id;
        $transection->save();

        // Update sender and receiver account balances
        $account->total_out += $netSalary;
        $account->balance   -= $netSalary;
        $account->save();

        $account_to->total_in += $netSalary;
        $account_to->balance   += $netSalary;
        $account_to->save();

        // Transaction for taxes
        $account_taxes  = Account::find(28);
        $account_taswia = Account::find(29);

        $transection1 = new Transection;
        $transection1->tran_type       = 'salary';
        $transection1->seller_id       = Auth::guard('admin')->id();
        $transection1->cost_id         = $request->cost_id;
        $transection1->account_id      = $account_taswia->id;
        $transection1->account_id_to   = $account_taxes->id;
        $transection1->branch_id       = auth('admin')->user()->branch_id;
        $transection1->amount          = $request->taxes;
        $transection1->description     = 'دفع مرتب قيد الضرائب';
        $transection1->debit           = $request->taxes;
        $transection1->credit          = 0;
        $transection1->balance         = $account_taswia->balance - $request->taxes;
        $transection1->debit_account   = 0;
        $transection1->credit_account  = $request->taxes;
        $transection1->balance_account = $account_taxes->balance + $request->taxes;
        $transection1->salary_id       = $salaryRecord->id;
        $transection1->save();

        $account_taswia->total_out += $request->taxes;
        $account_taswia->balance   -= $request->taxes;
        $account_taswia->save();

        $account_taxes->total_in += $request->taxes;
        $account_taxes->balance   += $request->taxes;
        $account_taxes->save();

        // Transaction for insurance
        $account_insurance = Account::find(88);
        $account_taswia    = Account::find(29); // refresh account taswia data if needed

        $transection2 = new Transection;
        $transection2->tran_type       = 'salary';
        $transection2->seller_id       = Auth::guard('admin')->id();
        $transection2->cost_id         = $request->cost_id;
        $transection2->account_id      = $account_taswia->id;
        $transection2->account_id_to   = $account_insurance->id;
        $transection2->branch_id       = auth('admin')->user()->branch_id;
        $transection2->amount          = $request->insurance;
        $transection2->description     = 'دفع مرتب قيد التامينات';
        $transection2->debit           = $request->insurance;
        $transection2->credit          = 0;
        $transection2->balance         = $account_taswia->balance - $request->insurance;
        $transection2->debit_account   = 0;
        $transection2->credit_account  = $request->insurance;
        $transection2->balance_account = $account_insurance->balance + $request->insurance;
        $transection2->salary_id       = $salaryRecord->id;
        $transection2->save();

        $account_taswia->total_out += $request->insurance;
        $account_taswia->balance   -= $request->insurance;
        $account_taswia->save();

        $account_insurance->total_in += $request->insurance;
        $account_insurance->balance   += $request->insurance;
        $account_insurance->save();

        // Transaction for discount
        $account_discount = Account::find(89);
        $account_taswia   = Account::find(29);

        $transection3 = new Transection;
        $transection3->tran_type       = 'salary';
        $transection3->seller_id       = Auth::guard('admin')->id();
        $transection3->cost_id         = $request->cost_id;
        $transection3->cost_id_to      = $request->cost_id_to;
        $transection3->account_id      = $account_taswia->id;
        $transection3->account_id_to   = $account_discount->id;
        $transection3->branch_id       = auth('admin')->user()->branch_id;
        $transection3->amount          = $request->discount;
        $transection3->description     = 'دفع مرتب قيد الخصومات';
        $transection3->debit           = $request->discount;
        $transection3->credit          = 0;
        $transection3->balance         = $account_taswia->balance - $request->discount;
        $transection3->debit_account   = 0;
        $transection3->credit_account  = $request->discount;
        $transection3->balance_account = $account_discount->balance + $request->discount;
        $transection3->salary_id       = $salaryRecord->id;
        $transection3->save();

        $account_taswia->total_out += $request->discount;
        $account_taswia->balance   -= $request->discount;
        $account_taswia->save();

        $account_discount->total_in += $request->discount;
        $account_discount->balance   += $request->discount;
        $account_discount->save();

        // Transaction for discount transfer back to the receiving account
        $account_taswia = Account::find(29);
        $account_to     = Account::find($request->account_id_to);

        $transection4 = new Transection;
        $transection4->tran_type       = 'salary';
        $transection4->seller_id       = Auth::guard('admin')->id();
        $transection4->cost_id         = $request->cost_id;
        $transection4->account_id      = $account_taswia->id;
        $transection4->account_id_to   = $account_to->id;
        $transection4->branch_id       = auth('admin')->user()->branch_id;
        $transection4->amount          = $request->discount;
        $transection4->description     = 'دفع مرتب قيد الحوافز';
        $transection4->debit           = $request->discount;
        $transection4->credit          = 0;
        $transection4->balance         = $account_taswia->balance - $request->discount;
        $transection4->debit_account   = 0;
        $transection4->credit_account  = $request->discount;
        $transection4->balance_account = $account_to->balance + $request->discount;
        $transection4->salary_id       = $salaryRecord->id;
        $transection4->save();

        $account_taswia->total_out += $request->discount;
        $account_taswia->balance   -= $request->discount;
        $account_taswia->save();

        $account_to->total_in += $request->discount;
        $account_to->balance   += $request->discount;
        $account_to->save();

        // Reset specific seller values after salary payment
        DB::table('admins')
            ->where('id', $request->seller_id)
            ->update([
                'result_visitors' => 0,
                'visitors'        => 0,
                'commission'      => 0,
                'note'            => '',
                'score'           => 0,
                'number_of_days'  => 0,
            ]);

        // Commit transaction if everything is successful
        DB::commit();
        Toastr::success('تم دفع المرتب بنجاح.');
        return redirect()->route('admin.salaries.index');
    } catch (\Exception $e) {
        // Rollback the transaction if any exception occurs
        DB::rollBack();
        dd($e->getMessage());
        // Optionally, log the exception or return an error message
        return redirect()->back()->withError('حدث خطأ أثناء عملية الدفع: ' . $e->getMessage())->withInput();
    }
}


public function storerating(Request $request)
{
    // Validate incoming request
    $validator = Validator::make($request->all(), [
        'seller_id' => 'required|exists:admins,id',
        'score' => 'required|numeric',
                'note' => 'required',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    // Update the score in the sellers table directly
    $seller = Seller::find($request->seller_id);

    if ($seller) {
        // Update the seller's score
        $seller->score = $request->score;
        $seller->note = $request->note;

        // Save the changes to the seller
        $seller->save();

        Toastr::success('Score updated for the seller.');
    } else {
        Toastr::error('Seller not found.');
    }

    return redirect()->route('admin.seller.list');
}

    public function update(Request $request, $id)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'salary' => 'required|numeric',
            'commission' => 'required|numeric',
            'number_of_visitors' => 'required|integer',
            'result_of_visitors' => 'required|numeric',
            'salary_of_visitors' => 'required|numeric',
            'transport_amount' => 'required|numeric',
            'score' => 'required|numeric',
                        'discount' => 'required|numeric',
            'month' => 'required|date_format:Y-m',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Find the salary record
        $salary = Salary::find($id);

        if (!$salary) {
            Toastr::error('Salary record not found.');
            return redirect()->route('admin.salaries.index');
        }

        // Update the record
        $salary->update($request->all());

        Toastr::success('Salary record updated successfully.');
        return redirect()->route('salaries.index');
    }
       public function showSalarySummary($sellerId, $month)
    {
        // Parse the provided month (format: YYYY-MM)
        try {
            $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth(); // e.g., 2025-04-01
            $endDate   = Carbon::createFromFormat('Y-m', $month)->endOfMonth();   // e.g., 2025-04-30
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid month format.'
            ], 400);
        }

        // Calculate total days in the month
        $totalDays = $startDate->daysInMonth;

        // Count weekend days (assume Friday and Saturday as weekends)
        $weekendDays = 0;
        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            if ($d->format('l') == 'Friday' || $d->format('l') == 'Saturday') {
                $weekendDays++;
            }
        }
        // Expected working days: total days minus weekends
        $expectedWorkingDays = $totalDays - $weekendDays;

        // Retrieve the admin to get the assigned shift information.
        $admin = Admin::findOrFail($sellerId);
        $shift = $admin->shift; // Assumes a relation or property 'shift' that provides shift data with 'start' and 'end'
        if (!$shift) {
            return response()->json([
                'success' => false,
                'message' => 'No shift assigned for this employee.'
            ], 400);
        }

        // Calculate daily expected work hours based on the shift's start and end times.
        // We assume that shift->start and shift->end are time strings (ex: "08:30:00" and "17:00:00")
        $shiftStart = Carbon::createFromFormat('Y-m-d H:i:s', $startDate->toDateString() . ' ' . $shift->start);
        $shiftEnd   = Carbon::createFromFormat('Y-m-d H:i:s', $startDate->toDateString() . ' ' . $shift->end);

        // If shift end time is less than or equal to start, it means the shift spans midnight.
        if ($shiftEnd <= $shiftStart) {
            $shiftEnd->addDay();
        }
        $dailyExpectedHours = $shiftStart->diffInHours($shiftEnd);

        // Expected work hours for the month.
        $expectedWorkHours = $expectedWorkingDays * $dailyExpectedHours;

        // Get actual attendance records for the employee for the specified month.
        $attendances = Attendance::where('admin_id', $sellerId)
                        ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                        ->get();

        // Calculate actual working days as unique attendance dates.
        $actualWorkingDays = $attendances->pluck('date')->unique()->count();

        // Sum total worked hours and total late time from all retrieved records.
        $workedHours = $attendances->sum('worked_hours');
        $totalTimeLate = $attendances->sum('time_late');

        // Additional dummy salary-related values; replace with your logic if necessary.
        $salary = $admin->salary;
        $commission = $admin->commission??0;
        $score = $admin->score??0;
        $visitors = $admin->visitors;
        $result_visitors = $admin->result_visitors;
        $holidays = $weekendDays; // For example, weekends

        return response()->json([
            'success'              => true,
            'number_of_days'       => $expectedWorkingDays,
            'holidays'             => $weekendDays,
            'expected_work_hours'  => $expectedWorkHours,
            'actual_work_days'     => $actualWorkingDays,
            'worked_hours'         => $workedHours,
            'time_late'            => $totalTimeLate,
            'salary'               => $salary,
            'commission'           => $commission,
            'score'                => $score,
            'visitors'             => $visitors,
            'result_visitors'      => $result_visitors,
        ], 200);
    }
}
