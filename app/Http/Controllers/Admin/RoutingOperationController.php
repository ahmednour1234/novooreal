<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoutingOperation;
use App\Models\Routing;
use App\Models\WorkCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use App\CPU\Helpers;

class RoutingOperationController extends Controller
{
    /**
     * عرض قائمة الخطوات مع فلتر حسب المسار ومركز العمل.
     */
    public function index(Request $request)
    {
        $query = RoutingOperation::with(['routing.bom.product', 'workCenter']);

        // فلترة حسب المسار
        if ($rid = $request->input('routing_id')) {
            $query->where('routing_id', $rid);
        }
        // فلترة حسب مركز العمل
        if ($wc = $request->input('work_center_id')) {
            $query->where('work_center_id', $wc);
        }

        $operations = $query
            ->orderBy('routing_id')->orderBy('sequence')
            ->paginate(Helpers::pagination_limit())
            ->appends($request->only(['routing_id', 'work_center_id']));

        $routings    = Routing::with('bom.product')->get();
        $workCenters = WorkCenter::all();

        return view('admin-views.routing-operations.index', compact('operations','routings','workCenters'));
    }

    /**
     * إظهار نموذج إضافة خطوة جديدة.
     */
    public function create()
    {
        $routings    = Routing::with('bom.product')->get();
        $workCenters = WorkCenter::all();
        return view('admin-views.routing-operations.create', compact('routings','workCenters'));
    }

    /**
     * تخزين خطوة جديدة.
     */
    public function store(Request $request)
    {
        $request->validate([
            'routing_id'     => 'required|exists:routings,id',
            'work_center_id' => 'required|exists:work_centers,id',
            'sequence'       => 'required|integer|min:1',
            'setup_time'     => 'required|numeric|min:0',
            'run_time'       => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            RoutingOperation::create($request->only([
                'routing_id','work_center_id','sequence','setup_time','run_time'
            ]));
            DB::commit();
            Toastr::success('تم إضافة الخطوة بنجاح','نجاح');
            return redirect()->route('admin.routing-operations.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('حدث خطأ أثناء الإضافة','خطأ');
            return back()->withInput();
        }
    }

    /**
     * إظهار نموذج تعديل خطوة.
     */
    public function edit($id)
    {
        $operation   = RoutingOperation::findOrFail($id);
        $routings    = Routing::with('bom.product')->get();
        $workCenters = WorkCenter::all();
        return view('admin-views.routing-operations.edit', compact('operation','routings','workCenters'));
    }

    /**
     * تحديث خطوة موجودة.
     */
    public function update(Request $request, $id)
    {
        $operation = RoutingOperation::findOrFail($id);

        $request->validate([
            'routing_id'     => 'required|exists:routings,id',
            'work_center_id' => 'required|exists:work_centers,id',
            'sequence'       => 'required|integer|min:1',
            'setup_time'     => 'required|numeric|min:0',
            'run_time'       => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $operation->update($request->only([
                'routing_id','work_center_id','sequence','setup_time','run_time'
            ]));
            DB::commit();
            Toastr::success('تم تحديث الخطوة بنجاح','نجاح');
            return redirect()->route('admin.routing-operations.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('حدث خطأ أثناء التحديث','خطأ');
            return back()->withInput();
        }
    }

    /**
     * حذف (تعطيل) خطوة.
     */
    public function destroy($id)
    {
        $operation = RoutingOperation::findOrFail($id);
        DB::beginTransaction();
        try {
            $operation->delete();
            DB::commit();
            Toastr::success('تم حذف الخطوة بنجاح','نجاح');
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('حدث خطأ أثناء الحذف','خطأ');
            return back();
        }
    }
}
