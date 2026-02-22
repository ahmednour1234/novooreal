<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\CPU\Helpers;
use App\Models\Storage;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StorageController extends Controller
{
    public function __construct(
        private Storage $storage
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

    if (!in_array("storage.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $storages = $this->storage->latest()->paginate(Helpers::pagination_limit());
        return view('admin-views.storage.index', compact('storages'));
    }
    public function indextree()
{
    $accountTypes = ['asset', 'liability', 'equity', 'revenue', 'expense'];
    return view('admin-views.storage.indextree', compact('accountTypes'));
}

    public function create()
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

    if (!in_array("storage.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    $storages=Storage::wherenull('parent_id')->get();
        return view('admin-views.storage.create',compact('storages'));
    }

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

    if (!in_array("storage.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $request->validate([
            'name' => 'required',
        ], [
            'name.required' => translate('Name is required'),
        ]);

        $storage = new Storage();
        $storage->name = $request->name;
                $storage->parent_id = $request->parent_id;
        $storage->save();

        Toastr::success(translate('تمت إضافة الخزنة بنجاح'));
        return back();
    }

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

    if (!in_array("storage.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $storage = $this->storage->find($id);
            $storages=Storage::wherenull('parent_id')->get();

        return view('admin-views.storage.edit', compact('storage','storages'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'name' => 'required',
        ], [
            'name.required' => translate('Name is required'),
        ]);

        $storage = $this->storage->find($id);
        $storage->name = $request->name;
        $storage->parent_id = $request->parent_id;
        $storage->save();

        Toastr::success(translate('تم تحديث الخزنة بنجاح'));
        return back();
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

    if (!in_array("storage.destroy", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $storage = $this->storage->find($request->id);
        $storage->delete();

        Toastr::success(translate('تم حذف الخزنة بنجاح'));
        return back();
    }
}
