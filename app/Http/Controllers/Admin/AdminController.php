<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\AdminSeller;
use App\Models\Admin;
use App\Models\Seller;
use App\Models\Role;
use App\Models\Branch;
use App\Models\Shift;
use Brian2694\Toastr\Facades\Toastr;
use App\CPU\Helpers;
use App\Models\Category;
use App\Models\Region;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use function App\CPU\translate;
use Illuminate\Support\Facades\Auth;

class adminController extends Controller
{
    public function __construct(
        private Admin $admin,
        private Seller $seller,
                private Shift $shift,

        private AdminSeller $adminseller,
    ){}

   public function index()
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

    if (!in_array("admin.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    $sellers = Seller::all();
$roles=Role::all();
$shifts=Shift::all();

$branches=Branch::all();
    // Pass data to the view using compact
    return view('admin-views.admin.index', compact('sellers','roles','branches','shifts'));
}

  public function showmap()
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

    if (!in_array("seller.map", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $admins = Admin::select('f_name', 'latitude', 'longitude')
                        ->whereNotNull('latitude')
                        ->whereNotNull('longitude')
                        ->get();

        return view('admin-views.map.index', compact('admins'));
    }
  public function store(Request $request): RedirectResponse
{
       
    $request->validate([
        'f_name' => 'required',
        'l_name' => 'required',
        'latitude' => 'nullable',
        'longitude' => 'nullable',
        'email'=> 'required|email|unique:admins',
        'password' => 'required|min:8',
    ]);

    DB::beginTransaction();

    try {
        $admin = $this->admin;
        $admin->f_name = $request->f_name;
        $admin->l_name = $request->l_name;
        $admin->latitude = $request->latitude;
        $admin->longitude = $request->longitude;
        $admin->email = $request->email;
        $admin->role_id = $request->role_id;
        $admin->branch_id = $request->branch_id;
        $admin->shift_id = $request->shift_id??1;

             
        $admin->password = Hash::make($request->password);


        $admin->save();

        foreach ($request->sellers as $item) {
            $adminseller = new AdminSeller;
            $adminseller->seller_id = $item;
            $adminseller->admin_id = $admin->id;
            $adminseller->save();
        }

        DB::commit();

        Toastr::success(translate('تمت إضافة الأدمن بنجاح'));
        return back();

    } catch (\Exception $e) {
        DB::rollBack();
        Toastr::error(translate('Failed to add admin. Please try again.'));
        return back()->withErrors(['error' => $e->getMessage()]);
    }
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

    if (!in_array("admin.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    $admins = $this->admin->where('role', 'admin')->paginate(Helpers::pagination_limit());
    return view('admin-views.admin.list', compact('admins'));
}
    public function edit(Request $request)
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

    if (!in_array("admin.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $roles=Role::all();
$branches=Branch::all();
$shifts=Shift::all();

        $regions = Region::all();
                $sellers = $this->seller->get();
        $categories = Category::all();
        $admin = $this->admin->where('id',$request->id)->first();
        return view('admin-views.admin.edit',compact('admin', 'regions', 'categories','sellers','roles','branches','shifts'));
    }

public function update(Request $request): RedirectResponse
{
    $admin = $this->admin->where('id', $request->id)->first();

    $request->validate([
        'f_name' => 'required',
        'l_name' => 'required',
        'latitude' => 'nullable',
        'longitude' => 'nullable',
        'email' => 'required|email|unique:admins,email,' . $admin->id,
    ]);

    DB::beginTransaction();

    try {
        $admin->f_name = $request->f_name;
        $admin->l_name = $request->l_name;
        $admin->latitude = $request->latitude;
        $admin->longitude = $request->longitude;
        $admin->email = $request->email;
              $admin->role_id = $request->role_id;
        $admin->branch_id = $request->branch_id;

       
        // Delete existing sellers associated with the admin to avoid duplicates
        AdminSeller::where('admin_id', $admin->id)->delete();

        foreach ($request->sellers as $item) {
            $adminseller = new AdminSeller;
            $adminseller->seller_id = $item;
            $adminseller->admin_id = $admin->id;
            $adminseller->save();
        }

        if ($request->password) {
            $request->validate([
                'password' => 'min:8'
            ]);
            $admin->password = Hash::make($request->password);
        }

        $admin->update();

        DB::commit();

        Toastr::success(translate('تم تحديث بيانات الأدمن بنجاح'));
        return redirect()->route('admin.admin.list');

    } catch (\Exception $e) {
        DB::rollBack();
        Toastr::error(translate('فشل تحديث بيانات الأدمن.'));
        return back()->withErrors(['error' => $e->getMessage()]);
    }
}

    public function delete(Request $request): RedirectResponse
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

    if (!in_array("admin.destroy", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        $admin = $this->admin->find($request->id);
        $admin->delete();

        Toastr::success(translate('admin removed successfully'));
        return back();
    }
}
