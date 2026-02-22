<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\CPU\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Account;
use App\Models\Transection;
use App\Models\Storage;
use function App\CPU\translate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function __construct(
        private Account $account,
        private Storage $storage
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
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

    if (!in_array("account.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $query_param = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $query = $this->account->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->where('account', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        } else {
            $query = $this->account;
        }
        $storages= $this->storage;

        $accounts = $query->wherenull('parent_id')->orderBy('id','desc')->paginate(Helpers::pagination_limit());
        return view('admin-views.account.list', compact('accounts','search','storages'));
    }
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

    if (!in_array("account.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $query_param = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $query = $this->account->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->where('account', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        } else {
            $query = $this->account;
        }
        $storages= $this->storage;

        $accounts = $query->orderBy('id','desc')->wherenot('account_type','other')->paginate(Helpers::pagination_limit());
        return view('admin-views.account.list', compact('accounts','search','storages'));
    }
  public function listone(Request $request, $id)
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

    if (!in_array("account.listone", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    // Initialize query parameters
    $query_param = [];
    $search = $request->input('search');

    // Account Query
    $query = $this->account->where('parent_id', $id);

    // Apply Search Filter
    if ($search) {
        $keywords = explode(' ', $search);
        $query->where(function ($q) use ($keywords) {
            foreach ($keywords as $word) {
                $q->orWhere('account', 'like', "%{$word}%")
                  ->orWhere('name', 'like', "%{$word}%"); // Add name field if applicable
            }
        });
        $query_param['search'] = $search;
    }

    // Fetch paginated results
    $accounts = $query->with('children')->latest()
        ->paginate(Helpers::pagination_limit())
        ->appends($query_param);
$account=Account::where('id',$id)->first();
    // Fetch storages if needed
    $storages = $this->storage;

    // Return view with compacted data
    return view('admin-views.account.listone', compact('accounts', 'search', 'storages','id','account'));
}

    

    /**
     * @return Application|Factory|View
     */
public function add()
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

    if (!in_array("account.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    // Get all storages from the database
    $storages = Storage::all(); // or Storage::paginate(10) if you want pagination

    return view('admin-views.account.add', compact('storages'));
}
public function addone($id)
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

    if (!in_array("account.storeone", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    // Get all storages from the database
    $storages = Storage::all(); // or Storage::paginate(10) if you want pagination
$account=Account::where('id',$id)->first();
    return view('admin-views.account.addone', compact('storages','id','account'));
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

    if (!in_array("account.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $request->validate([
            'account' => 'required',
            'balance'=> 'nullable',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense,other',
            'account_number' => 'nullable',
            'cost_center'=>'nullable'
        ]);
$accountCode = Account::generateAccountCode($request->account_type, $request->parent_id);

        $account = $this->account;
        $account->account = $request->account;
              $account->code = $accountCode;

        $account->description = $request->description;
        $account->balance = $request->balance ??0;
      $account->parent_id = $request->parent_id;
    $account->total_in = $request->balance??0;
        $account->total_out = 0;
        $account->default_cost_center_id=$request->default_cost_center_id;

                $account->type = $request->type ??0;
        $account->account_number =$accountCode;
                $account->cost_center = $request->cost_center??0;
        $account->account_type = $request->account_type;
        $account->save();


        Toastr::success(translate('تم إضافة دليل محاسبي بنجاح'));
return redirect(url()->previous());
    }
public function destroy(Request $request, int $id): RedirectResponse
{
    // ===== صلاحيات =====
    $adminId = Auth::guard('admin')->id();
    $admin   = DB::table('admins')->where('id', $adminId)->first();
    // if (!$admin) { Toastr::warning('غير مسموح لك! كلم المدير.'); return back(); }

    // $role = DB::table('roles')->where('id', $admin->role_id)->first();
    // if (!$role) { Toastr::warning('غير مسموح لك! كلم المدير.'); return back(); }

    // $data = $role->data;
    // $decoded = is_string($data) ? json_decode($data, true) : (is_array($data) ? $data : []);
    // if (!is_array($decoded) || ! (in_array('account.destroy', $decoded) || in_array('account.delete', $decoded))) {
    //     Toastr::warning('غير مسموح لك! كلم المدير.');
    //     return back();
    // }

    // ===== الحساب =====
    $account = DB::table('accounts')->where('id', $id)->first();
    if (!$account) { Toastr::warning('الحساب غير موجود'); return back(); }

    // ممنوع حذف حساب أب
    $hasChildren = DB::table('accounts')->where('parent_id', $id)->exists();
    if ($hasChildren) {
        Toastr::warning('لا يمكن حذف هذا الحساب لأنه يحتوي على حسابات فرعية.');
        return back();
    }

    // ممنوع لو عليه قيود يومية
    $inJED = DB::table('journal_entries_details')->where('account_id', $id)->exists();
    if ($inJED) {
        Toastr::warning('لا يمكن حذف هذا الحساب لارتباطه بقيود يومية.');
        return back();
    }

    // ممنوع لو عليه حركات (أي طرف)
    $inTx = DB::table('transections')
        ->where(function($q) use ($id){
            $q->where('account_id', $id)->orWhere('account_id_to', $id);
        })->exists();
    if ($inTx) {
        Toastr::warning('لا يمكن حذف هذا الحساب لارتباطه بحركات مالية.');
        return back();
    }

    // (اختياري) رصيد غير صفر
    $balance = (float) ($account->balance ?? 0);
    if (round($balance, 2) != 0.0) {
        Toastr::warning('لا يمكن حذف حساب غير صفري الرصيد.');
        return back();
    }

    // ===== الحذف =====
    try {
        DB::transaction(function() use ($id) {
            DB::table('accounts')->where('id', $id)->delete();
        });
        Toastr::success('تم حذف الحساب بنجاح');
    } catch (\Throwable $e) {
        \Log::error('Account delete failed', ['account_id' => $id, 'error' => $e->getMessage()]);
        Toastr::error('فشل الحذف! حاول لاحقًا.');
    }

    return back();
}
    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id)
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

    if (!in_array("account.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $account = $this->account->find($id);
        return view('admin-views.account.edit', compact('account'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
public function update(Request $request, $id): RedirectResponse
{
    // تحديث بيانات الحساب الحالي
    $account = $this->account->findOrFail($id);
    $account->account = $request->account;
    $account->account_number = $request->account_number;
    $account->description = $request->description;
    $account->default_cost_center_id = $request->default_cost_center_id;
    $account->cost_center = $request->cost_center ?? 0;
    $account->save();

    // بادئات حسب نوع الحساب
    $typePrefixes = [
        'asset'     => 1,
        'liability' => 2,
        'equity'    => 3,
        'revenue'   => 4,
        'expense'   => 5,
    ];

    // إعادة ترقيم جميع الحسابات الرئيسية وأبنائها
    foreach ($typePrefixes as $type => $prefix) {
        $mainAccounts = $this->account
            ->where('account_type', $type)
            ->whereNull('parent_id')
            ->orderBy('id', 'asc')
            ->get();

        $counter = 1;
        foreach ($mainAccounts as $main) {
            // مستوى أول = بادئة النوع + رقم متسلسل
            $main->code = $prefix . str_pad($counter, 1, '', STR_PAD_LEFT);
            $main->save();

            // تحديث الأكواد للأبناء والأحفاد
            $this->updateChildCodes($main, $main->code);
            $counter++;
        }
    }

    Toastr::success(translate('تم تحديث بيانات دليل محاسبي بنجاح'));
    return redirect()->back();
}

/**
 * تحديث أكواد الأبناء بشكل متسلسل
 */
protected function updateChildCodes($parentAccount, $prefixCode = null)
{
    $prefixCode = $prefixCode ?? $parentAccount->code;

    $children = $this->account
        ->where('parent_id', $parentAccount->id)
        ->orderBy('id', 'asc')
        ->get();

    $counter = 1;
    foreach ($children as $child) {
        $level = $this->getAccountLevel($parentAccount);

        if ($level == 1) {
            // المستوى 2 → ثلاثة أرقام
            $child->code = $prefixCode . $counter;
        } elseif ($level == 2) {
            // المستوى 3 → أربعة أرقام
            $child->code = $prefixCode . $counter;
        } elseif ($level >= 3) {
            // المستوى الرابع وما بعده → 0001, 0002 ...
            $child->code = $prefixCode . str_pad($counter, 4, '0', STR_PAD_LEFT);
        }

        $child->save();
        $this->updateChildCodes($child, $child->code);
        $counter++;
    }
}

/**
 * تحديد مستوى الحساب من الكود
 */
protected function getAccountLevel($account)
{
    $len = strlen($account->code);

    if ($len <= 2) return 1; // مثال: 11
    if ($len == 3) return 2; // مثال: 111
    if ($len == 4) return 3; // مثال: 1111
    return 4;                // مثال: 11110001 أو أكثر
}


    /**
     * @param $id
     * @return RedirectResponse
     */
    public function delete($id): RedirectResponse
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

    if (!in_array("account.destroy", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $account = $this->account->find($id);
        $account->delete();

        Toastr::success(translate('تم حذف دليل محاسبي بنجاح'));
        return back();
    }

public function download()
{
    // Ensure the user is authenticated as a seller
    $seller = Auth::guard('admin')->user();

    if (!$seller) {
        abort(403, 'Unauthorized');
    }

    // Fetch account data
    $accounts = Account::all();
    $search = request('search', '');

    // Render Blade view and include seller's email
    $html = view('admin-views.account.pdf', compact('accounts', 'search', 'seller'))->render();

    // Save HTML to a temporary file
    $filePath = storage_path('app/public/account_report.html');
    file_put_contents($filePath, $html);

    // Download the file and delete after sending
    return response()->download($filePath, 'account_report.html')->deleteFileAfterSend(true);
}
public function getAccounts($storage_id)
{
    $accounts = Account::where('storage_id', $storage_id)->get();
    return response()->json(['accounts' => $accounts]);
}

public function getSubAccounts($account_id)
{
    $accounts = Account::where('parent_id', $account_id)->get();
    return response()->json(['accounts' => $accounts]);
}
public function getSubItems($type, $id)
{
    if ($type === 'storage') {
        $storages = Storage::where('parent_id', $id)->get();
        $accounts = Account::where('storage_id', $id)->whereNull('parent_id')->get();
    } else {
        $storages = [];
        $accounts = Account::where('parent_id', $id)->get();
    }

    return response()->json([
        'storages' => $storages,
        'accounts' => $accounts
    ]);
}
public function getAccountsByTypeOrParent(Request $request)
{
    $type = $request->query('type');
    $parentId = $request->query('parent_id');

    if ($type) {
        $accounts = Account::where('account_type', $type)->whereNull('parent_id')->get();
    }elseif($parentId==15){
                $accounts = Account::where('parent_id', 808077812365598754451212154797977977996461122134646464)->get();
    } elseif ($parentId) {
        $accounts = Account::where('parent_id', $parentId)->get();
    } else {
        $accounts = [];
    }

    return response()->json($accounts);
}
public function search(Request $request)
{
    $accounts = \App\Models\Account::where('account', 'LIKE', '%' . $request->name . '%')
        ->select('id', 'account', 'account_number','description','account_type','code')
        ->limit(20)
        ->get();

    return response()->json($accounts);
}


}
