<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceLog;
use App\Models\Asset;
use App\Models\Branch;
use Illuminate\Http\Request;
use Toastr;
use App\CPU\Helpers;
use Illuminate\Support\Facades\Auth;
use DB;

class MaintenanceLogController extends Controller
{
    /**
     * عرض قائمة سجلات الصيانة.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
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

    if (!in_array("asset.showsayana", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    try {
        $query = MaintenanceLog::with('asset');
        // فلترة حسب الفرع من خلال العلاقة (asset.branch_id)
     

        // فلترة حسب معرف الأصل مباشرة (asset_id)
        

        // فلترة حسب نطاق تاريخ الصيانة (from - to)
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('maintenance_date', [$request->from, $request->to]);
        }
        if ($request->filled('branch_id') ) {
            $query->where('branch_id', $request->branch_id);
        }
           if ($request->filled('asset_id') ) {
            $query->where('id', $request->asset_id);
        }

        // ترتيب النتائج وتطبيق pagination مع ترحيل معطيات الفلترة
        $maintenanceLogs = $query->orderBy('maintenance_date', 'desc')
                                  ->paginate(Helpers::pagination_limit())
                                  ->appends($request->query());

        // استرجاع الفروع النشطة (يمكن استخدامها في نموذج التصفية)
        $branches = Branch::all();
        $assets = Asset::all();

        return view('admin-views.maintenance_logs.index', compact('maintenanceLogs', 'branches','assets'));
    } catch (\Exception $e) {
        \Toastr::error($e->getMessage());
        return redirect()->back();
    }
}

    /**
     * عرض نموذج جدولة صيانة جديدة.
     *
     * @return \Illuminate\View\View
     */
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

    if (!in_array("asset.addsayan", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        // جلب كافة الأصول لعرضها في قائمة الاختيار
$assets = Asset::orderBy('asset_name')
               ->whereNotIn('status', ['sold', 'closed'])
               ->get();

        return view('admin-views.maintenance_logs.create', compact('assets'));
    }

    /**
     * تخزين سجل صيانة جديد في قاعدة البيانات.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id'           => 'required|exists:assets,id',
            'maintenance_date'   => 'required|date',
            'maintenance_type'   => 'required|in:preventive,emergency',
            'estimated_cost'     => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $maintenance = new MaintenanceLog();
                $maintenance->asset_id         = $validated['asset_id'];
                $maintenance->maintenance_date = $validated['maintenance_date'];
                $maintenance->maintenance_type = $validated['maintenance_type'];
                $maintenance->estimated_cost   = $validated['estimated_cost'] ?? 0;
                $maintenance->notes            = $validated['notes'] ?? null;
                $maintenance->status           = 'scheduled'; // الحالة الابتدائية: مجدولة
                $maintenance->branch_id        = auth('admin')->user()->branch_id;
                $maintenance->added_by        = Auth::guard('admin')->id();
                $maintenance->save();
            });

            Toastr::success('تم جدولة الصيانة بنجاح.');
            return redirect()->route('admin.maintenance_logs.index');
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * عرض تفاصيل سجل الصيانة.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $maintenance = MaintenanceLog::with('asset')->findOrFail($id);
            return view('admin-views.maintenance_logs.show', compact('maintenance'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * عرض نموذج تعديل سجل الصيانة.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            $maintenance = MaintenanceLog::findOrFail($id);
            $assets = Asset::orderBy('asset_name')->get();
            return view('admin-views.maintenance_logs.edit', compact('maintenance', 'assets'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * تحديث سجل الصيانة.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'maintenance_date' => 'required|date',
            'maintenance_type' => 'required|in:preventive,emergency',
            'estimated_cost'   => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string',
            'status'           => 'required|in:scheduled,in progress,completed',
        ]);

        try {
            $maintenance = MaintenanceLog::findOrFail($id);
            $maintenance->maintenance_date = $validated['maintenance_date'];
            $maintenance->maintenance_type = $validated['maintenance_type'];
            $maintenance->estimated_cost   = $validated['estimated_cost'] ?? 0;
            $maintenance->notes            = $validated['notes'] ?? null;
            $maintenance->status           = $validated['status'];
            if($request->status=='in progress'){
                $maintenance->approved_by  = Auth::guard('admin')->id();
            }else{
               $maintenance->done_by  = Auth::guard('admin')->id();
            }
            $maintenance->save();

            Toastr::success('تم تحديث سجل الصيانة بنجاح.');
            return redirect()->route('admin.maintenance_logs.index');
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * حذف سجل صيانة.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $maintenance = MaintenanceLog::findOrFail($id);
            $maintenance->delete();
            Toastr::success('تم حذف سجل الصيانة بنجاح.');
            return redirect()->route('admin.maintenance_logs.index');
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
}
