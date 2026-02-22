<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Routing;
use App\Models\BillOfMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use App\CPU\Helpers;

class RoutingController extends Controller
{
    /**
     * عرض قائمة المسارات مع إمكانية التصفية.
     */
    public function index(Request $request)
    {
        $query = Routing::with(['bom.product']);

        // تصفية حسب قائمة المواد (BOM)
        if ($bomId = $request->input('bom_id')) {
            $query->where('bom_id', $bomId);
        }

        // تصفية حسب اسم المسار
        if ($name = $request->input('name')) {
            $query->where('name', 'like', "%{$name}%");
        }

        // تصفية حسب تاريخ البدء - من
        if ($from = $request->input('date_from')) {
            $query->whereDate('effective_date', '>=', $from);
        }

        // تصفية حسب تاريخ البدء - إلى
        if ($to = $request->input('date_to')) {
            $query->whereDate('effective_date', '<=', $to);
        }

        // جلب نتائج البحث مع الترقيم
        $routings = $query
            ->paginate(Helpers::pagination_limit())
            ->appends($request->only(['bom_id', 'name', 'date_from', 'date_to']));

        // جلب كل الـBOMs لخيارات التصفية
        $boms = BillOfMaterial::with('product')->get();

        return view('admin-views.routings.index', compact('routings', 'boms'));
    }

    /**
     * إظهار نموذج إضافة مسار جديد.
     */
    public function create()
    {
        $boms = BillOfMaterial::with('product')->get();
        return view('admin-views.routings.create', compact('boms'));
    }

    /**
     * حفظ مسار جديد.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bom_id'         => 'required|exists:bills_of_materials,id',
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string',
            'effective_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            Routing::create($request->only([
                'bom_id',
                'name',
                'description',
                'effective_date',
            ]));

            DB::commit();
            Toastr::success('تم إضافة المسار بنجاح', 'نجاح');
            return redirect()->route('admin.routings.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('حدث خطأ أثناء إضافة المسار', 'خطأ');
            return back()->withInput();
        }
    }

    /**
     * إظهار نموذج تعديل مسار موجود.
     */
    public function edit($id)
    {
        $routing = Routing::findOrFail($id);
        $boms     = BillOfMaterial::with('product')->get();
        return view('admin-views.routings.edit', compact('routing', 'boms'));
    }

    /**
     * تحديث بيانات المسار.
     */
    public function update(Request $request, $id)
    {
        $routing = Routing::findOrFail($id);

        $request->validate([
            'bom_id'         => 'required|exists:bills_of_materials,id',
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string',
            'effective_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $routing->update($request->only([
                'bom_id',
                'name',
                'description',
                'effective_date',
            ]));

            DB::commit();
            Toastr::success('تم تحديث المسار بنجاح', 'نجاح');
            return redirect()->route('admin.routings.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('حدث خطأ أثناء تحديث المسار', 'خطأ');
            return back()->withInput();
        }
    }

    /**
     * حذف (تعطيل) المسار.
     */
    public function destroy($id)
    {
        $routing = Routing::findOrFail($id);

        DB::beginTransaction();
        try {
            $routing->delete();

            DB::commit();
            Toastr::success('تم حذف المسار بنجاح', 'نجاح');
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('حدث خطأ أثناء حذف المسار', 'خطأ');
            return back();
        }
    }
}
