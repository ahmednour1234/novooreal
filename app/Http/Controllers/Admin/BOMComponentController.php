<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BOMComponent;
use App\Models\BillOfMaterial;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use App\CPU\Helpers;

class BOMComponentController extends Controller
{
    /**
     * عرض قائمة المكونات مع إمكانية تصفية حسب المنتج النهائي.
     */
    public function index(Request $request)
    {
        $product_id = $request->input('product_id');
        $products   = Product::all();

        $components = BOMComponent::with(['componentProduct', 'billOfMaterial'])
            ->when($product_id, function ($q) use ($product_id) {
                $q->whereHas('billOfMaterial', function ($q2) use ($product_id) {
                    $q2->where('product_id', $product_id);
                });
            })
            ->paginate(Helpers::pagination_limit());

        return view('admin-views.bom-components.index', compact('components', 'products', 'product_id'));
    }

    /**
     * إظهار نموذج إضافة مكون جديد.
     */
    public function create()
    {
        $boms     = BillOfMaterial::with('product')->get();
        $products = Product::all();

        return view('admin-views.bom-components.create', compact('boms', 'products'));
    }

    /**
     * حفظ مكون جديد ضمن BOM.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bom_id'               => 'required|exists:bills_of_materials,id',
            'component_product_id' => 'required|exists:products,id',
            'quantity'             => 'required|numeric|min:0.0001',
            'unit'              => 'required',
        ]);

        DB::beginTransaction();
        try {
            BOMComponent::create($request->only([
                'bom_id',
                'component_product_id',
                'quantity',
                'unit',
            ]));

            DB::commit();
            Toastr::success('تم إضافة المكون بنجاح', 'نجاح');

            return redirect()
                ->route('admin.bomcomponents.index', ['product_id' => $request->input('product_id')]);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('حدث خطأ أثناء إضافة المكون', 'خطأ');
            return back();
        }
    }

    /**
     * إظهار نموذج تعديل مكون موجود.
     */
    public function edit($id)
    {
        $component = BOMComponent::findOrFail($id);
        $boms      = BillOfMaterial::with('product')->get();
        $products  = Product::all();

        return view('admin-views.bom-components.edit', compact('component', 'boms', 'products'));
    }

    /**
     * تحديث بيانات المكون.
     */
    public function update(Request $request, $id)
    {
        $component = BOMComponent::findOrFail($id);

        $request->validate([
            'bom_id'               => 'required|exists:bills_of_materials,id',
            'component_product_id' => 'required|exists:products,id',
            'quantity'             => 'required|numeric|min:0.0001',
            'unit'              => 'required',
        ]);

        DB::beginTransaction();
        try {
            $component->update($request->only([
                'bom_id',
                'component_product_id',
                'quantity',
                'unit',
            ]));

            DB::commit();
            Toastr::success('تم تحديث المكون بنجاح', 'نجاح');

            return redirect()
                ->route('admin.bomcomponents.index', ['product_id' => $component->billOfMaterial->product_id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('حدث خطأ أثناء تحديث المكون', 'خطأ');
            return back();
        }
    }

    /**
     * حذف (تعطيل) المكون.
     */
    public function destroy($id)
    {
        $component = BOMComponent::findOrFail($id);

        DB::beginTransaction();
        try {
            $component->delete();

            DB::commit();
            Toastr::success('تم حذف المكون بنجاح', 'نجاح');

            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('حدث خطأ أثناء حذف المكون', 'خطأ');
            return back();
        }
    }
}
