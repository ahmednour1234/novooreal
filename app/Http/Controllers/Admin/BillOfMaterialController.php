<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\BillOfMaterial;
use App\Models\Product;
use App\CPU\Helpers;

class BillOfMaterialController extends Controller
{
    /**
     * عرض قائمة بجميع الـBOMs مع الترحيل وإحضار المنتج المرتبط
     */
    public function index()
    {
        $boms = BillOfMaterial::with('product')
            ->paginate(Helpers::pagination_limit());
        return view('admin-views.boms.index', compact('boms'));
    }

    /**
     * عرض نموذج إنشاء قائمة مواد جديدة
     */
    public function create()
    {
        $products = Product::all();
        return view('admin-views.boms.create', compact('products'));
    }

    /**
     * حفظ قائمة المواد الجديدة في قاعدة البيانات
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'  => 'required|exists:products,id',
            'version'     => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            BillOfMaterial::create($data);
            DB::commit();
            Toastr::success('تم إنشاء قائمة المواد بنجاح');
            return redirect()->route('admin-views.boms.index');
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('حدث خطأ أثناء إنشاء قائمة المواد');
            return back()->withInput();
        }
    }

    /**
     * عرض نموذج تعديل قائمة المواد
     */
    public function edit($id)
    {
        $bom = BillOfMaterial::findOrFail($id);
        $products = Product::all();
        return view('admin-views.boms.edit', compact('bom', 'products'));
    }

    /**
     * تحديث بيانات قائمة المواد المحددة
     */
    public function update(Request $request, $id)
    {
        $bom = BillOfMaterial::findOrFail($id);

        $data = $request->validate([
            'product_id'  => 'required|exists:products,id',
            'version'     => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $bom->update($data);
            DB::commit();
            Toastr::success('تم تحديث قائمة المواد بنجاح');
            return redirect()->route('admin-views.boms.index');
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('حدث خطأ أثناء تحديث قائمة المواد');
            return back()->withInput();
        }
    }

    /**
     * تعطيل (حذف) قائمة المواد المحددة
     */
    public function destroy($id)
    {
        $bom = BillOfMaterial::findOrFail($id);

        DB::beginTransaction();
        try {
            $bom->delete();
            DB::commit();
            Toastr::success('تم تعطيل قائمة المواد بنجاح');
            return redirect()->route('admin-views.boms.index');
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('حدث خطأ أثناء تعطيل قائمة المواد');
            return back();
        }
    }
}
