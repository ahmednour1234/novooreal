<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkCenter;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use App\CPU\Helpers;

class WorkCenterController extends Controller
{
    /**
     * عرض قائمة مراكز العمل.
     */
     public function index(Request $request)
    {
        // تجهيز الاستعلام مع تحميل علاقة الفرع
        $query = WorkCenter::with('branch');

        // تصفية حسب الفرع
        if ($branchId = $request->input('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        // ترتيب حسب تكلفة الساعة
        if (in_array($request->input('cost_sort'), ['asc', 'desc'])) {
            $query->orderBy('cost_per_hour', $request->input('cost_sort'));
        }

        // ترتيب حسب سعة اليوم
        if (in_array($request->input('capacity_sort'), ['asc', 'desc'])) {
            $query->orderBy('capacity_per_day', $request->input('capacity_sort'));
        }

        // تنفيذ الاستعلام مع الترقيم
        $workCenters = $query
            ->paginate(Helpers::pagination_limit())
            ->appends($request->only(['branch_id', 'cost_sort', 'capacity_sort']));

        // جلب الفروع النشطة لفلترة القائمة
        $branches = Branch::where('active', 1)->get();

        return view('admin-views.work-centers.index', compact('workCenters', 'branches'));
    }

    /**
     * إظهار نموذج إضافة مركز عمل جديد.
     */
    public function create()
    {
        // نُحضّر قائمة الفروع النشطة فقط
        $branches = Branch::where('active', 1)->get();

        return view('admin-views.work-centers.create', compact('branches'));
    }

    /**
     * حفظ مركز عمل جديد.
     */
    public function store(Request $request)
    {
        $request->validate([
            'branch_id'        => 'required|exists:branches,id',
            'name'             => 'required|string|max:100',
            'description'      => 'nullable|string',
            'capacity_per_day' => 'nullable|numeric|min:0',
            'cost_per_hour'    => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            WorkCenter::create($request->only([
                'branch_id',
                'name',
                'description',
                'capacity_per_day',
                'cost_per_hour',
            ]));

            DB::commit();
            Toastr::success('تم إضافة مركز العمل بنجاح', 'نجاح');

            return redirect()->route('admin.work-centers.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('حدث خطأ أثناء إضافة مركز العمل', 'خطأ');
            return back()->withInput();
        }
    }

    /**
     * عرض نموذج تعديل مركز العمل.
     */
    public function edit($id)
    {
        $workCenter = WorkCenter::findOrFail($id);
        $branches   = Branch::where('active', 1)->get();

        return view('admin-views.work-centers.edit', compact('workCenter', 'branches'));
    }

    /**
     * تحديث بيانات مركز العمل.
     */
    public function update(Request $request, $id)
    {
        $workCenter = WorkCenter::findOrFail($id);

        $request->validate([
            'branch_id'        => 'required|exists:branches,id',
            'name'             => 'required|string|max:100',
            'description'      => 'nullable|string',
            'capacity_per_day' => 'nullable|numeric|min:0',
            'cost_per_hour'    => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $workCenter->update($request->only([
                'branch_id',
                'name',
                'description',
                'capacity_per_day',
                'cost_per_hour',
            ]));

            DB::commit();
            Toastr::success('تم تحديث مركز العمل بنجاح', 'نجاح');

            return redirect()->route('admin.work-centers.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('حدث خطأ أثناء تحديث مركز العمل', 'خطأ');
            return back()->withInput();
        }
    }

    /**
     * تعطيل (حذف) مركز العمل.
     */
    public function destroy($id)
    {
        $workCenter = WorkCenter::findOrFail($id);

        DB::beginTransaction();
        try {
            $workCenter->delete();

            DB::commit();
            Toastr::success('تم تعطيل مركز العمل بنجاح', 'نجاح');

            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('حدث خطأ أثناء تعطيل مركز العمل', 'خطأ');
            return back();
        }
    }
}
