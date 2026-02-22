<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;

class RoleController extends Controller
{
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

    if (!in_array("role.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    } 
    $roles = Role::all();
    return view('admin-views.roles.index', compact('roles'));
  }

  public function store(Request $request)
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

    if (!in_array("role.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    } 
    // Validate the request
    $request->validate([
      'name' => 'required|string|max:255',
      'permissions' => 'required|array',
      'permissions.*.name' => 'nullable|string',
      'permissions.*.actions' => 'array',
      'permissions.*.actions.*' => 'in:read,write,create,update,search,branch',
    ]);
    // dd($request->all());
    // Save role with permissions
    $role = new Role();
    $role->name = $request->name;
    $role->data = json_encode($request->permissions); // Store permissions as JSON
    $role->save();

    return redirect()->back()->with('toast_success', 'تم انشاء الدور بنجاح!');
  }
  public function update(Request $request, $id)
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

    if (!in_array("role.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    } 
    // Validate the request
    $request->validate([
      'name' => 'required|string|max:255',
      'permissions' => 'required|array',
      'permissions.*.actions' => 'array',
      'permissions.*.actions.*' => 'in:read,write,create,update,search,branch',
    ]);

    // Find and update the role
    $role = Role::findOrFail($id);
    $role->name = $request->name;
    $role->data = json_encode($request->permissions);
    $role->save();

    return redirect()->back()->with('toast_success', 'تم تحديث هذا الدور!');
  }


  public function destroy($id)
  {
    // Find and delete the role
    $role = Role::findOrFail($id);
    $role->delete();

    return redirect()->back()->with('toast_success', 'تم حذف هذا الدور!');
  }
}
