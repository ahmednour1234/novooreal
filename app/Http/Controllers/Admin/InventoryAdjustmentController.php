<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\CPU\Helpers;
use App\Models\StockBatch;
use App\Models\Branch;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Toastr;
use App\Models\InventoryAdjustment;
use App\Models\InventoryAdjustmentItem;
use Validator;

class InventoryAdjustmentController extends Controller
{
    private function checkAdminPermission()
    {
        $adminId = Auth::guard('admin')->id();
        $admin = DB::table('admins')->where('id', $adminId)->first();

        if (!$admin) {
            Toastr::warning('غير مسموح لك! كلم المدير.');
            return null;
        }

        $roleId = $admin->role_id;
        $role = DB::table('roles')->where('id', $roleId)->first();

        if (!$role) {
            Toastr::warning('غير مسموح لك! كلم المدير.');
            return null;
        }

        $decodedData = json_decode($role->data, true);
        if (is_string($decodedData)) {
            $decodedData = json_decode($decodedData, true);
        }

        if (!is_array($decodedData)) {
            Toastr::warning('غير مسموح لك! كلم المدير.');
            return null;
        }

        // إعادة بيانات المدير مع بيانات الدور المفككة
        $admin->permissions = $decodedData;
        return $admin;
    }

    /**
     * تحقق من وجود صلاحية محددة.
     *
     * @param string $permission
     * @return bool
     */
    private function checkPermission($permission)
    {
        $admin = $this->checkAdminPermission();
        if (!$admin) {
            return false;
        }
        return in_array($permission, $admin->permissions);
    }

    /**
     * عرض قائمة أوامر التسوية مع التفاصيل.
     */
public function index(Request $request)
{
    if (!$this->checkPermission('InventoryAdjustment.view.all')) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    
    // بناء الاستعلام الأساسي مع ترتيب النتائج حسب تاريخ الإنشاء
    $query = InventoryAdjustment::with('items')->orderBy('created_at', 'desc');
    
    // تطبيق فلترة الفرع إن وُجد
    if ($request->has('branch_id') && !empty($request->branch_id)) {
        $query->where('branch_id', $request->branch_id);
    }
    
    // تطبيق فلترة الفترة الزمنية بناءً على adjustment_date
    if ($request->has('from_date') && !empty($request->from_date)) {
        $query->whereDate('adjustment_date', '>=', $request->from_date);
    }
    if ($request->has('to_date') && !empty($request->to_date)) {
        $query->whereDate('adjustment_date', '<=', $request->to_date);
    }
    
    $adjustments = $query->paginate(10);
    
    // جلب جميع الفروع للفلترة
    $branches = Branch::all();
    
    return view('admin-views.inventory_adjustments.index', compact('adjustments', 'branches'));
}



    /**
     * عرض نموذج إنشاء أمر تسوية جديد.
     */
public function create()
{
    if (!$this->checkPermission('InventoryAdjustment.create')) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    
    // جلب الفروع النشطة
    $branches = Branch::where('active', 1)->get();
    // جلب الفرع الحالي للمستخدم
    $branchId = auth('admin')->user()->branch_id;
    // تحديد عمود الكمية حسب الفرع
    $branchColumn = ($branchId == 1) ? "quantity" : "branch_" . $branchId;
    // جلب كل المنتجات
    $products = Product::where('product_type','product')->get();
    
    // إضافة قيمة الكمية المتوفرة حسب الفرع لكل منتج
    $products->each(function($product) use ($branchColumn) {
        $product->available_quantity = $product->$branchColumn;
    });

    return view('admin-views.inventory_adjustments.create', compact('branches', 'products'));
}


    /**
     * إنشاء أمر تسوية جديد.
     */
    public function store(Request $request)
    {

        // التحقق من المدخلات الرئيسية لأمر التسوية
        $validator = Validator::make($request->all(), [
            'branch_id'                  => 'required|integer',
            'adjustment_date'            => 'required|date',
            'status'                     => 'required|string|max:50',
            'created_by'                 => 'required|integer',
            'notes'                      => 'nullable|string',
            // تفاصيل بنود التسوية
            'items'                      => 'required|array|min:1',
            'items.*.product_id'         => 'required|integer',
            'items.*.adjustment_amount'  => 'required|numeric',
            'items.*.new_system_quantity'=> 'required|numeric',
            'items.*.reason'             => 'nullable|string|max:255',
        ]);

   
        
        // إنشاء أمر التسوية الرئيسي
        $adjustment = InventoryAdjustment::create([
            'inventory_count_id' => $request->input('inventory_count_id'), // اختياري
            'branch_id'          => $request->branch_id,
            'adjustment_date'    => $request->adjustment_date,
            'status'             => $request->status,
            'created_by'         => $request->created_by,
            'notes'              => $request->notes,
        ]);

        // إنشاء تفاصيل البنود الخاصة بأمر التسوية
        foreach ($request->items as $item) {
            $adjustmentItem = new InventoryAdjustmentItem();
            $adjustmentItem->inventory_adjustment_id = $adjustment->id;
            $adjustmentItem->product_id              = $item['product_id'];
            $adjustmentItem->adjustment_amount       = $item['adjustment_amount'];
            $adjustmentItem->new_system_quantity     = $item['new_system_quantity'];
            $adjustmentItem->reason                  = isset($item['reason']) ? $item['reason'] : null;
            $adjustmentItem->save();
        }
        
        toastr()->success('تم إنشاء أمر التسوية بنجاح');
        return redirect()->back();
    }

    /**
     * عرض أمر التسوية مع تفاصيله.
     */
    public function show($id)
    {
        if (!$this->checkPermission('InventoryAdjustment.show')) {
            Toastr::warning('غير مسموح لك! كلم المدير.');
            return redirect()->back();
        }
        $adjustment = InventoryAdjustment::with('items')->find($id);
        if (!$adjustment) {
            toastr()->error('أمر التسوية غير موجود');
            return redirect()->route('admin.inventory_adjustments.index');
        }
            $branchId = auth('admin')->user()->branch_id;
    if ($adjustment->branch_id != $branchId) {
        Toastr::error('لا يمكنك تعديل أمر التسوية من فرع آخر');
        return redirect()->route('admin.inventory_adjustments.index');
    }
        
        return view('admin-views.inventory_adjustments.show', compact('adjustment'));
    }

    /**
     * عرض نموذج تعديل أمر التسوية.
     */
    public function edit($id)
    {
        if (!$this->checkPermission('InventoryAdjustment.edit')) {
            Toastr::warning('غير مسموح لك! كلم المدير.');
            return redirect()->back();
        }
        $adjustment = InventoryAdjustment::with('items')->find($id);
        if (!$adjustment) {
            toastr()->error('أمر التسوية غير موجود');
            return redirect()->route('admin.inventory_adjustments.index');
        }
        // التأكد من أن الشخص الذي يقوم بالتعديل من نفس الفرع الذي أنشأ أمر التسوية
        $branchId = auth('admin')->user()->branch_id;
        if ($adjustment->branch_id != $branchId) {
            toastr()->error('لا يمكنك تعديل أمر التسوية من فرع آخر');
            return redirect()->route('admin.inventory_adjustments.index');
        }
        
        $branches = Branch::where('active', 1)->get();
        $branchColumn = ($branchId == 1) ? "quantity" : "branch_" . $branchId;
        $products = Product::where($branchColumn, '>', 0)->where('product_type','product')->get();
        return view('admin-views.inventory_adjustments.edit', compact('adjustment', 'branches', 'products'));
    }

    /**
     * تحديث أمر التسوية.
     */
public function update(Request $request, $id)
{
    $adjustment = InventoryAdjustment::with('items')->find($id);
    if (!$adjustment) {
        toastr()->error('أمر التسوية غير موجود');
        return redirect()->route('admin.inventory_adjustments.index');
    }
    
    // التأكد من أن الشخص الذي يقوم بالتحديث من نفس الفرع الذي أنشأ أمر التسوية
    $branchId = auth('admin')->user()->branch_id;
    if ($adjustment->branch_id != $branchId) {
        toastr()->error('لا يمكنك تعديل أمر التسوية من فرع آخر');
        return redirect()->route('admin.inventory_adjustments.index');
    }
    
    // التحقق من المدخلات عند التحديث
    $validator = Validator::make($request->all(), [
        'branch_id'                   => 'sometimes|required|integer',
        'adjustment_date'             => 'sometimes|required|date',
        'status'                      => 'sometimes|required|string|max:50',
        'created_by'                  => 'sometimes|required|integer',
        'notes'                       => 'nullable|string',
        'items'                       => 'sometimes|required|array|min:1',
        'items.*.product_id'          => 'required_with:items|integer',
        'items.*.adjustment_amount'   => 'required_with:items|numeric',
        'items.*.new_system_quantity' => 'required_with:items|numeric',
        'items.*.reason'              => 'nullable|string|max:255',
    ]);
    
    if ($validator->fails()) {
        return redirect()->back()
                         ->withErrors($validator)
                         ->withInput();
    }
    
    // تحديث البيانات الرئيسية لأمر التسوية
    $adjustment->update($request->only([
        'inventory_count_id',
        'branch_id',
        'adjustment_date',
        'status',
        'created_by',
        'notes'
    ]));
    
    // حذف كافة البنود القديمة
    InventoryAdjustmentItem::where('inventory_adjustment_id', $adjustment->id)->delete();
    
    // إضافة البنود الجديدة
    if ($request->has('items')) {
        foreach ($request->items as $item) {
            InventoryAdjustmentItem::create([
                'inventory_adjustment_id' => $adjustment->id,
                'product_id'              => $item['product_id'],
                'adjustment_amount'       => $item['adjustment_amount'],
                'new_system_quantity'     => $item['new_system_quantity'],
                'reason'                  => $item['reason'] ?? null,
            ]);
        }
    }
    
    toastr()->success('تم تحديث أمر التسوية بنجاح');
    return redirect()->back();
}

    /**
     * حذف أمر التسوية.
     */
    public function destroy($id)
    {
        if (!$this->checkPermission('InventoryAdjustment.destroy')) {
            Toastr::warning('غير مسموح لك! كلم المدير.');
            return redirect()->back();
        }
        $adjustment = InventoryAdjustment::find($id);
        if (!$adjustment) {
            toastr()->error('أمر التسوية غير موجود');
            return redirect()->route('admin.inventory_adjustments.index');
        }
        
        // حذف تفاصيل البنود أولاً
        $adjustment->items()->delete();
        // حذف أمر التسوية الرئيسي
        $adjustment->delete();
        
        toastr()->success('تم حذف أمر التسوية بنجاح');
        return redirect()->route('admin.inventory_adjustments.index');
    }
    public function approve($id)
{
    if (!$this->checkPermission('InventoryAdjustment.accept')) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    // جلب أمر التسوية بواسطة المعرف
    $adjustment = InventoryAdjustment::find($id);
    if (!$adjustment) {
        Toastr::error('أمر التسوية غير موجود');
        return redirect()->back();
    }
    
    // التأكد من أن الشخص الذي يقوم بالموافقة من نفس الفرع الذي أنشأ أمر التسوية
    $branchId = auth('admin')->user()->branch_id;
    if ($adjustment->branch_id != $branchId) {
        Toastr::error('لا يمكنك تعديل أمر التسوية من فرع آخر');
        return redirect()->route('admin.inventory_adjustments.index');
    }
    
    // تغيير الحالة إلى "معتمد"
    $adjustment->status = 'approved';
    $adjustment->save();

    Toastr::success('تم اعتماد أمر التسوية بنجاح');
    return redirect()->back();
}
public function complete($id)
{
    if (!$this->checkPermission('InventoryAdjustment.end')) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }
    
    // بدء معاملة قاعدة البيانات
    DB::beginTransaction();
    
    try {
        // جلب أمر التسوية مع بنوده
        $adjustment = InventoryAdjustment::with('items')->find($id);
        if (!$adjustment) {
            Toastr::error('أمر التسوية غير موجود');
            return redirect()->back();
        }
        
        // التأكد من أن المستخدم ينتمي لنفس الفرع الذي أنشأ أمر التسوية
        $branchId = auth('admin')->user()->branch_id;
        if ($adjustment->branch_id != $branchId) {
            Toastr::error('لا يمكنك تعديل أمر التسوية من فرع آخر');
            return redirect()->route('admin.inventory_adjustments.index');
        }
        
        // تغيير الحالة إلى "completed" وحفظها
        $adjustment->status = 'completed';
        $adjustment->save();
        
        // تحديد عمود الكمية في جدول المنتجات حسب رقم الفرع:
        // إذا كان الفرع 1 نستخدم عمود "quantity"، وإلا "branch_{branchId}"
        $branchColumn = ($branchId == 1) ? "quantity" : "branch_" . $branchId;
        
        foreach ($adjustment->items as $item) {
            // جلب المنتج
            $product = \App\Models\Product::find($item->product_id);
            if (!$product) {
                continue;
            }
            
            // الحصول على الكمية الحالية للنظام من بند أمر التسوية (المُحدثة)
            $currentStock = $item->new_system_quantity;
            
            /* 
             * الحصول على إجمالي الكمية وإجمالي السعر من stock_batches للمنتج والفرع 
             * باستخدام استعلام مجمع لتقليل عدد الاستعلامات خاصة مع عدد كبير من الصفوف.
             */
            $batchData = \App\Models\StockBatch::where('product_id', $product->id)
                            ->where('branch_id', $branchId)
                            ->selectRaw("SUM(quantity) as totalQty, SUM(price * quantity) as totalPrice")
                            ->first();
                            
            $totalBatchQty = $batchData->totalQty ?? 0;
            $totalPrice    = $batchData->totalPrice ?? 0;
            
            // حساب متوسط التكلفة (إذا كانت إجمالي الكمية > 0)
            $avgCost = ($totalBatchQty > 0) ? $totalPrice / $totalBatchQty : $product->purchase_price;
            
            /* 
             * سواء كانت الكمية الحالية أكبر أو أقل من إجمالي الكميات في الدُفعات،
             * سيتم أرشفة جميع الدُفعات القديمة كما هي،
             * ثم إنشاء سجل دفعة واحد جديد في stock_batches يحتوي على الكمية الحالية مع متوسط التكلفة.
             */
            
            // أرشفة الدُفعات القديمة باستخدام chunkById للتعامل مع عدد كبير من الصفوف
            \App\Models\StockBatch::where('product_id', $product->id)
                ->where('branch_id', $branchId)
                ->chunkById(1000, function ($batches) use ($branchId) {
                    foreach ($batches as $batch) {
                        $archive = new \App\Models\ArchivedStockBatch();
                        $archive->product_id = $batch->product_id;
                        $archive->branch_id  = $branchId;
                        $archive->quantity   = $batch->quantity; // نقل كامل الكمية
                        $archive->price      = $batch->price;
           
                        $archive->save();
                    }
                });
            // حذف الدُفعات القديمة بعد الأرشفة
            \App\Models\StockBatch::where('product_id', $product->id)
                ->where('branch_id', $branchId)
                ->delete();
                
            // إنشاء سجل دفعة جديد في stock_batches بالكمية الحالية مع متوسط التكلفة
            $newBatch = new \App\Models\StockBatch();
            $newBatch->product_id = $product->id;
            $newBatch->branch_id  = $branchId;
            $newBatch->quantity   = $currentStock;
            $newBatch->price      = $avgCost;
            $newBatch->save();
            
            // إنشاء سجل في ProductLog بالنوع 0 مع الكمية الحالية للنظام
            $pLog = new \App\Models\ProductLog();
            $pLog->product_id = $product->id;
            $pLog->quantity   = $currentStock;
            $pLog->type       = 0;  // النوع 0 للترحيل
            $pLog->seller_id  = auth('admin')->user()->id;
            $pLog->branch_id  = $branchId;
            $pLog->save();
            
            // أرشفة سجلات ProductLog الخاصة بهذا المنتج والفرع باستخدام chunkById
            \App\Models\ProductLog::where('branch_id', $branchId)
                ->where('product_id', $product->id)
                ->chunkById(1000, function ($logs) {
                    foreach ($logs as $log) {
                        $archivedLog = new \App\Models\ArchivedProductLog();
                        foreach ($log->toArray() as $key => $value) {
                            $archivedLog->$key = $value;
                        }
                        $archivedLog->save();
                    }
                });
            // حذف سجلات ProductLog بعد الأرشفة
            \App\Models\ProductLog::where('branch_id', $branchId)
                ->where('product_id', $product->id)
                ->delete();
            
            // تحديث كمية المنتج في جدول المنتجات إلى القيمة الحالية الجديدة
            $product->$branchColumn = $currentStock;
            $product->save();
        }
        
        // التزام المعاملة
        DB::commit();
        Toastr::success('تم إنهاء أمر التسوية ومعالجة الكميات وسجلات المنتجات بنجاح');
        return redirect()->back();
    } catch (\Exception $e) {
        // في حال حدوث أي خطأ، نقوم بإرجاع جميع التعديلات
        DB::rollBack();
        Toastr::error('حدث خطأ أثناء معالجة أمر التسوية: ' . $e->getMessage());
        return redirect()->back();
    }
}



}
