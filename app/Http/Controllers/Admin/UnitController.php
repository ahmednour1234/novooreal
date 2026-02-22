<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Unit;
use App\Models\SubUnit;
use App\CPU\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    public function __construct(
        private Unit $unit,
       private SubUnit $subunit
    ){}

    /**
     * @return Application|Factory|View
     */
    public function index($type)
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

    if (!in_array("unit.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
           if($type==2){
        $unitss = $this->unit->latest()->paginate(Helpers::pagination_limit());
                return view('admin-views.unit.index',compact('unitss','type'));
           }else{
        $unitss = $this->subunit->latest()->paginate(Helpers::pagination_limit());
             $unitall = $this->unit->latest()->paginate(Helpers::pagination_limit());
        return view('admin-views.unit.index',compact('unitss','type','unitall'));
        }
    }
    

    /**
     * @param Request $request
     * @return RedirectResponse
     */
public function store(Request $request, $units): RedirectResponse
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

    if (!in_array("unit.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    if ($units == 2) {
        // Handle the creation of a unit
        $request->validate([
            'unit_type' => 'required|unique:units,unit_type',
        ]);

        $unit = new $this->unit; // Instantiate the unit model
        $unit->unit_type = $request->unit_type; // Set the unit_type attribute
        $unit->save(); // Save the unit

    } else {
        // Handle the creation of a subunit
        $request->validate([
            'unit_type' => 'required|unique:sub_units,name',
            'unit_id' => 'required|exists:units,id', // Ensures the unit_id exists in the units table
        ]);

        $unit = new $this->subunit; // Instantiate the subunit model
        $unit->name = $request->unit_type; // Set the name attribute
        $unit->unit_id = $request->unit_id; // Set the unit_id attribute
        $unit->save(); // Save the subunit
    }

    return redirect()->back()->with('success', 'تم اضافة وحدة القياس بنجاح.');
}


    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id,$type)
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

    if (!in_array("unit.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
        // dd($type);
        if($type==2){
        $unit = $this->unit->find($id);
                return view('admin-views.unit.edit',compact('unit','type'));
}else{
            $unit = $this->subunit->find($id);
        $unitall = $this->unit->latest()->paginate(Helpers::pagination_limit());
                return view('admin-views.unit.edit',compact('unit','type','unitall'));
}
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id,$type): RedirectResponse
    {
        if($type==2){
        $unit = $this->unit->find($id);
        $request->validate([
            'unit_type' => 'required|unique:units,unit_type,'.$unit->id,
        ]);
        $unit->unit_type = $request->unit_type;
                $unit->unit_id = $request->unit_id;
        $unit->save();
}else{
    $unit = $this->subunit->find($id);
        $request->validate([
            'unit_type' => 'required|unique:sub_units,name,'.$unit->id,
'unit_id' => 'required|exists:units,id',
        ]);
          $unit->name = $request->unit_type;
                $unit->unit_id = $request->unit_id;
        $unit->save();

}
        

        Toastr::success(translate('تم تحديث وحدة القياس بنجاح'));
        return redirect()->back();
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
   public function delete($id, $type): RedirectResponse
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

    if (!in_array("unit.destroy", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    // Fix the comparison operator: use '==' instead of '='
    if ($type ==2) {
        $unit = $this->unit->find($id);
        // Check if unit exists
        if (!$unit) {
            Toastr::error(translate('Unit not found'));
            return back();
        }
        $unit->delete();
    } else {
        $unit = $this->subunit->find($id);
        // Check if subunit exists
        if (!$unit) {
            Toastr::error(translate('Subunit not found'));
            return back();
        }
        $unit->delete();
    }

    // Success message
    Toastr::success(translate('تم حذف وحدة القياس بنجاح'));
    return back();
}

}
