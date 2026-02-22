<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\CostCenter;
use App\Models\Transection;
use App\CPU\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class TransferController extends Controller
{
    public function __construct(
        private Transection $transection,
        private Account $account,
        private Supplier $supplier,
                private Customer $customer,
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

    if (!in_array("transfer.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $accounts = $this->account
->orderBy('id','desc')->get();
        $search = $request['search'];
        $from = $request->from;
        $to = $request->to;
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $query = $this->transection->where('tran_type','Transfer')->
                    where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('description', 'like', "%{$value}%");
                        }
                });
            $query_param = ['search' => $request['search']];
        }else
         {
            $query = $this->transection->where('tran_type','Transfer')
                ->when($from!=null, function($q) use ($request){
                    return $q->whereBetween('date', [$request['from'], $request['to']]);
            });

         }
                 $costcenters= $this->costcenter->orderBy('id', 'desc')->get();

        $transfers = $query->wherenotnull('account_id_to')->latest()->paginate(Helpers::pagination_limit())->appends(['search' => $request['search'],'from'=>$request['from'],'to'=>$request['to']]);
        return view('admin-views.transfer.add',compact('accounts','transfers','search','from','to','costcenters'));
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

    if (!in_array("transfer.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $accounts = $this->account
->orderBy('id','desc')->get();
        $search = $request['search'];
        $from = $request->from;
        $to = $request->to;
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $query = $this->transection->where('tran_type','Transfer')->
                    where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('description', 'like', "%{$value}%");
                        }
                });
            $query_param = ['search' => $request['search']];
        }else
         {
            $query = $this->transection->where('tran_type','Transfer')
                ->when($from!=null, function($q) use ($request){
                    return $q->whereBetween('date', [$request['from'], $request['to']]);
            });

         }
                 $costcenters= $this->costcenter->orderBy('id', 'desc')->get();

        $transfers = $query->wherenotnull('account_id_to')->latest()->paginate(Helpers::pagination_limit())->appends(['search' => $request['search'],'from'=>$request['from'],'to'=>$request['to']]);
        return view('admin-views.transfer.list',compact('accounts','transfers','search','from','to','costcenters'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
public function store(Request $request): RedirectResponse
{
    $adminId = Auth::guard('admin')->id();
    $admin   = DB::table('admins')->find($adminId);

    if (!$admin) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $roleId = $admin->role_id;
    $role   = DB::table('roles')->find($roleId);

    if (!$role) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $decodedData = json_decode($role->data, true);
    if (is_string($decodedData)) {
        $decodedData = json_decode($decodedData, true);
    }
    if (!is_array($decodedData) || !in_array("transfer.store", $decodedData, true)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $request->validate([
        'entries'               => 'required|array|min:1',
        'entries.*.account_id'  => 'required|exists:accounts,id',
        'entries.*.debit'       => 'required|numeric|min:0',
        'entries.*.credit'      => 'required|numeric|min:0',
        'entries.*.date'        => 'required|date',
        'entries.*.description' => 'required|string',
        'entries.*.cost_id'     => 'nullable|exists:cost_centers,id',
        'entries.*.img'         => 'nullable|file|mimes:jpg,jpeg,png,pdf',
        'reference'             => 'nullable|string|max:100',
    ]);

    $entries = $request->input('entries');
    $files   = $request->file('entries');

    DB::beginTransaction();

    try {
        $headDate = \Carbon\Carbon::parse($entries[0]['date'] ?? now())->toDateString();

        $journalId = DB::table('journal_entries')->insertGetId([
            'entry_date' => $headDate,
            'reference'  => $request->input('reference') ?? ($entries[0]['reference'] ?? null),
            'description'=> $entries[0]['description'] ?? null,
            'created_by' => $adminId,
            'branch_id'  => $admin->branch_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($entries as $index => $entry) {
            $acc = DB::table('accounts')->find($entry['account_id']);
            if (!$acc) {
                Toastr::warning("الحساب غير موجود للمعاملة رقم " . ($index + 1));
                DB::rollBack();
                return redirect()->back();
            }

            $resolvedCostCenterId = $entry['cost_id'] ?? ($acc->default_cost_center_id ?? null);

            $img = null;
            if (isset($files[$index]['img']) && $files[$index]['img']) {
                $img = \App\CPU\Helpers::update('journal/', null, 'png', $files[$index]['img']);
            }

            $journalEntryDetailId = DB::table('journal_entries_details')->insertGetId([
                'journal_entry_id' => $journalId,
                'account_id'       => $entry['account_id'],
                'cost_center_id'   => $resolvedCostCenterId,
                'debit'            => $entry['debit'],
                'credit'           => $entry['credit'],
                'description'      => $entry['description'],
                'attachment_path'  => $img,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // ✅ هنا عكسنا المعادلة
            $newBalance = ($acc->balance ?? 0) + $entry['debit'] - $entry['credit'];

            DB::table('transections')->insert([
                'tran_type'               => 'Journal',
                'account_id'              => $entry['account_id'],
                'account_id_to'           => null,
                'cost_id'                 => $resolvedCostCenterId,
                'branch_id'               => $admin->branch_id,
                'amount'                  => $entry['debit'] > 0 ? $entry['debit'] : $entry['credit'],
                'credit'                  => $entry['credit'],
                'debit'                   => $entry['debit'],
                'balance'                 => $newBalance,
                'balance_account'         => $acc->balance,
                'description'             => $entry['description'],
                'date'                    => \Carbon\Carbon::parse($entry['date'])->toDateString(),
                'img'                     => $img,
                'seller_id'               => $adminId,
                'journal_entry_detail_id' => $journalEntryDetailId,
                'created_at'              => now(),
                'updated_at'              => now(),
            ]);

            // ✅ تحديث الإجمالي بحيث المدين = داخلة فلوس، الدائن = خارجة فلوس
            DB::table('accounts')->where('id', $acc->id)->update([
                'balance'   => $newBalance,
                'total_in'  => ($acc->total_in ?? 0) + $entry['debit'],
                'total_out' => ($acc->total_out ?? 0) + $entry['credit'],
                'updated_at'=> now(),
            ]);
        }

        DB::commit();
        Toastr::success('تم تسجيل جميع القيود بنجاح');
    } catch (\Exception $e) {
        DB::rollBack();
        Toastr::error('حدث خطأ أثناء حفظ القيود: ' . $e->getMessage());
    }

    return redirect()->back();
}



}
