<?php

namespace App\Http\Controllers\Admin;

use App\CPU\Helpers;
use App\Models\Branch;
use App\Models\Account;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use function App\CPU\translate;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;


class BranchController extends Controller
{
    public function __construct(
        private Branch $branch
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request): View|Factory|Application
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

    if (!in_array("branch.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $categories = $this->branch;
        $query_param = [];
        $search = $request['search'];

        if($request->has('search')) {
            $key = explode(' ', $request['search']);
            $categories=$categories->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        }
$account = Account::where('id', 16)->first();
if ($account) {
    // التحقق إذا الحساب الأساسي عنده أولاد
    $children = Account::where('parent_id', $account->id)->get();

    if ($children->isEmpty()) {
        // ما له أولاد: رجع الحساب الأساسي
        $accounts = collect([$account]);
    } else {
    // 1. جلب الحسابات التي parent_id = $account->id
    $accounts = Account::where('parent_id', $account->id)
        ->whereDoesntHave('children') // لا تحتوي على أولاد
        ->whereNotIn('id', function ($query) {
            // 2. استبعاد الحسابات الموجودة في عمود account_stock_id من جدول branches
            $query->select('account_stock_id')->from('branches')->whereNotNull('account_stock_id');
        })
        ->get();
}
} else {
    $accounts = collect([]);
    
}        $categories = $categories->latest()->paginate(Helpers::pagination_limit())->appends($query_param);
        return view('admin-views.branch.index',compact('categories', 'search','accounts'));
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

    $role = DB::table('roles')->where('id', $admin->role_id)->first();

    if (!$role) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $decodedData = json_decode($role->data, true);
    if (is_string($decodedData)) {
        $decodedData = json_decode($decodedData, true);
    }

    if (!is_array($decodedData) || !in_array("branch.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    // التحقق من صحة البيانات
    $request->validate([
        'name' => 'required|unique:branches,name',
        'code' => 'required|unique:branches,code',
        'lang' => 'nullable',
        'lat' => 'nullable',
        'active' => 'nullable'
    ]);

    // إنشاء الفرع الجديد
    $branch = new Branch();
    $branch->name = $request->name;
    $branch->code = $request->code;
    $branch->lang = $request->lang;
    $branch->lat = $request->lat;
    $branch->active = $request->active ?? 0;
    $branch->save();

    // إنشاء حساب مخزون وربطه بالفرع
    $this->createFinancialTransaction($branch, 0);

    // إضافة عمود للمنتجات خاص بالفرع الجديد
    $columnName = 'branch_' . $branch->id;
    if (!Schema::hasColumn('products', $columnName)) {
        Schema::table('products', function (Blueprint $table) use ($columnName) {
            $table->string($columnName)->default(0);
        });
    }

    Toastr::success(translate('تم انشاء الفرع بنجاح'));
    return back();
}

private function createFinancialTransaction(Branch $branch, $credit)
{
    DB::transaction(function () use ($branch, $credit) {
        // إنشاء رقم كود للحساب المحاسبي
        $lastAccount = Account::where('parent_id', 16)->latest('code')->first();
        $accountCode = $lastAccount ? $lastAccount->code + 1 : 101;

        $account = new Account();
        $account->account = "حساب مخزون: " . $branch->name;
        $account->description = "حساب مخزون: " . $branch->name;
        $account->account_number = $accountCode;
        $account->parent_id = 16;
        $account->account_type = "asset";
        $account->code = $accountCode;
        $account->save();

        // ربط الحساب بالفرع
        $branch->account_stock_id = $account->id;
        $branch->save();

        // يمكنك هنا إضافة قيود محاسبية لاحقاً إذا لزم
    });
}
    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
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

    if (!in_array("branch.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    
        $category = $this->branch->find($request->id);
$category->active = $category->active ? 0 : 1;
        $category->save();
        Toastr::success(translate('تم تغيير حالة الفرع'));
        return back();
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id): View|Factory|Application
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

    if (!in_array("branch.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
  $account = Account::where('id', 16)->where('parent_id', 1)->first();

if ($account) {
    // التحقق إذا الحساب الأساسي عنده أولاد
    $children = Account::where('parent_id', $account->id)->get();

    if ($children->isEmpty()) {
        // ما له أولاد: رجع الحساب الأساسي
        $accounts = collect([$account]);
    } else {
        // جلب الحسابات التي ليس لها أولاد وغير مستخدمة في فروع أخرى غير الفرع الحالي
        $accounts = Account::where('parent_id', $account->id)
            ->whereDoesntHave('children')
            ->whereNotIn('id', function ($query) use ($id) {
                $query->select('account_stock_id')
                    ->from('branches')
                    ->whereNotNull('account_stock_id')
                    ->where('id', '!=', $id); // استثناء الحساب المرتبط بنفس الفرع
            })
            ->get();
    }
} else {
    $accounts = collect([]);
}
        $category = $this->branch->find($id);
        return view('admin-views.branch.edit', compact('category','accounts'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        
        $request->validate([
            'name' =>'required|unique:branches,name,'.$request->id
        ], [
            'name.required' => translate('Name is required'),
        ]);
        $category = $this->branch->find($id);
        $category->name = $request->name;
                $category->code = $request->code;
        $category->lang = $request->lang;
        $category->lat = $request->lat;
        $category->active = $request->active;

        $category->save();

        Toastr::success(translate('تم تحديث بيانات الفرع بنجاح'));
        return redirect()->back();
    }



}
