<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transfer;
use App\Models\Admin;
use App\Models\TransferItem;
use App\Models\StockBatch;
use App\Models\Branch;
use App\Models\Transection;
use App\Models\Account;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Toastr;
use stdClass;
use  App\CPU\Helpers\translate;
use App\Models\PaymentVoucher;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;
use Illuminate\Support\Facades\Schema;
class TransferProductController extends Controller
{
    /**
     * تحقق من صلاحيات المدير الأساسي.
     *
     * @return stdClass|null
     */
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
     * عرض قائمة التحويلات.
     * يتطلب صلاحية "transfer.view.all"
     */
  public function index(Request $request)
{
    if (!$this->checkPermission('transfer.view.all')) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    // Start a query on the Transfer model with the needed relationships
    $query = Transfer::with([
            'sourceAccount', 
            'destinationAccount', 
            'sourceBranch', 
            'destinationBranch', 
            'createdBy', 
            'approvedBy'
        ]);

    // Filter by date range (assuming the created_at field represents the transfer date)
    if ($request->filled('start_date')) {
        $query->whereDate('created_at', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('created_at', '<=', $request->end_date);
    }

    // Filter by source branch (الفرع المحول)
    if ($request->filled('source_branch_id')) {
        $query->where('source_branch_id', $request->source_branch_id);
    }

    // Filter by destination branch (الفرع المحول له)
    if ($request->filled('destination_branch_id')) {
        $query->where('destination_branch_id', $request->destination_branch_id);
    }

    // Filter by the user who created the transfer (تم التحويل بواسطة)
    if ($request->filled('created_by')) {
        $query->where('created_by', $request->created_by);
    }

    // Filter by transfer status (حالة التحويل)
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // Get paginated results (you can adjust the per-page limit as needed)
    $transfers = $query->orderBy('created_at', 'desc')->paginate(15);
    $branches = Branch::all();
    $users = Admin::all();

    return view('admin-views.transfersproducts.index', compact('transfers','branches','users'));
}


    /**
     * عرض نموذج إنشاء تحويل جديد.
     * يتطلب صلاحية "transfer.create"
     */
    public function create()
    {
        if (!$this->checkPermission('transfer.create')) {
            Toastr::warning('غير مسموح لك! كلم المدير.');
            return redirect()->back();
        }

        $branches = Branch::where('active', 1)->get();
        $branchId = auth('admin')->user()->branch_id;

        $branchColumn = ($branchId == 1) ? "quantity" : "branch_" . $branchId;
        
        $products = Product::where($branchColumn, '>', 0)->where('product_type','product')->get();

        return view('admin-views.transfersproducts.create', compact('branches', 'products'));
    }

    /**
     * حفظ تحويل جديد (أو مسودة) مع تفاصيل الأصناف.
     * يتطلب صلاحية "transfer.create"
     */
public function store(Request $request)
{
    if (!$this->checkPermission('transfer.create')) {
        return redirect()->back();
    }

    $rules = [
        'transfer_number'       => 'required|unique:transfers,transfer_number',
        'destination_branch_id' => 'required|exists:branches,id',
        'total_amount'          => 'required|numeric|min:0',
        'items'                 => 'required|array|min:1',
        'items.*.product_id'    => 'required|exists:products,id',
        'items.*.quantity'      => 'required|numeric|min:0.1',
        'items.*.unit'          => 'required|string',
    ];
    $request->validate($rules);

    DB::beginTransaction();
    try {
        // الحصول على بيانات الفرع المصدر والوجهة
        $sourceBranch = Branch::find($request->source_branch_id);
        $destinationBranch = Branch::find($request->destination_branch_id);

        // إنشاء سجل التحويل
        $transfer = new Transfer();
        $transfer->transfer_number       = $request->transfer_number;
        $transfer->source_branch_id      = $request->source_branch_id;
        $transfer->destination_branch_id = $request->destination_branch_id;
        $transfer->account_id            = $sourceBranch->account_stock_Id;
        $transfer->account_id_to         = $destinationBranch->account_stock_Id;
        $transfer->total_amount          = $request->total_amount;
        $transfer->created_by            = Auth::guard('admin')->id();
        $transfer->status                = $request->has('draft') ? 'draft' : 'pending';
        $transfer->notes                 = $request->notes;
        $transfer->save();

        // العمود الذي يمثل كمية المخزون في الفرع الحالي (بالوحدة "كبري")
        $branchId = auth('admin')->user()->branch_id;
        $branchColumn = ($branchId == 1) ? "quantity" : "branch_" . $branchId;

        // المرور على كل صنف من الأصناف في التحويل
        foreach ($request->items as $itemData) {
            // إيجاد المنتج
            $product = Product::find($itemData['product_id']);
            $inputQuantity = $itemData['quantity']; // كما أدخلها المستخدم
            $unit = $itemData['unit']; // "كبري" أو "صغري"

            // تحويل الكمية المدخلة إلى وحدة "كبري" للحساب الداخلي
            if ($unit == 'صغري') {
                $requestedQty = $inputQuantity / $product->unit_value;
            } else {
                $requestedQty = $inputQuantity;
            }

            // استرجاع دفعات المخزون للمنتج (حسب FIFO)
            $stockBatches = \App\Models\StockBatch::where('product_id', $product->id)
                ->where('branch_id', $branchId)
                ->where('quantity', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get();

            // حساب إجمالي الكمية المتوفرة (بوحدة "كبري")
            $totalAvailable = $stockBatches->sum('quantity');
            if ($totalAvailable < $requestedQty) {
                DB::rollBack();
                toastr()->error(__('المخزن نفذ. الكمية المتاحة: ') . $totalAvailable);
                return redirect()->back();
            }

            $remaining = $requestedQty;
            $batchesUsed = []; // لتخزين بيانات كل دفعة مستخدمة

            // المرور على دفعات المخزون وتنفيذ الحساب باستخدام FIFO
            foreach ($stockBatches as $batch) {
                if ($remaining <= 0) {
                    break;
                }
                $batchAvailable = $batch->quantity;
                if ($batchAvailable >= $remaining) {
                    $used = $remaining;
                    $remainingBatch = $batchAvailable - $used;
                    $remaining = 0;
                } else {
                    $used = $batchAvailable;
                    $remainingBatch = 0;
                    $remaining -= $batchAvailable;
                }
                $batchCost = $batch->price * $used;
                // إعداد بيانات الدفعة المستخدمة
                $batchData = [
                    'batch_id'          => $batch->id,
                    'used_quantity_big' => $used, // بالوحدة "كبري"
                    'price'             => $batch->price,
                    'cost_big'          => $batchCost,
                    'created_at'        => $batch->created_at,
                    'remaining_in_batch'=> $remainingBatch,
                ];
                // إذا كانت الوحدة المطلوبة "صغري" نحسب الكمية والتكلفة بوحدة "صغري"
                if ($unit == 'صغري') {
                    // نحول الكمية المستخدمة من "كبري" إلى "صغري" بضربها في قيمة الوحدة ونحولها إلى عدد صحيح
                    $batchData['used_quantity_small'] = (int) ($used * $product->unit_value);
                    // التكلفة بوحدة "صغري" تُحسب عن طريق قسمة التكلفة على قيمة الوحدة (يمكن تقريبها)
                    $batchData['cost_small'] = round($batchCost / $product->unit_value, 2);
                }
                $batchesUsed[] = $batchData;

                // تحديث كمية الدفعة في المخزون (نصبح القيمة المتبقية)
                $batch->quantity = $remainingBatch;
                $batch->save();
            }

            // تحديث كمية المنتج في الفرع (بالوحدة "كبري")
            $currentStock = $product->$branchColumn;
            $newStock = $currentStock - $requestedQty;
            if ($newStock < 0) {
                DB::rollBack();
                toastr()->error(__('خطأ: كمية المنتج في الفرع غير كافية.'));
                return redirect()->back();
            }
            $product->$branchColumn = $newStock;
            $product->save();

            // تسجيل العملية في سجل المنتجات (ProductLog) – النوع 100
            $productLog = new \App\Models\ProductLog();
            $productLog->product_id = $product->id;
            $productLog->quantity = $requestedQty; // بالوحدة "كبري"
            $productLog->type = 100;
            $productLog->seller_id = auth('admin')->user()->id;
            $productLog->branch_id = $branchId;
            $productLog->save();

            // إنشاء سجل تحويل (transfer item) لكل دفعة مستخدمة
            foreach ($batchesUsed as $batchUsed) {
                $item = new TransferItem();
                $item->transfer_id = $transfer->id;
                $item->product_id  = $product->id;
                // إذا كانت الوحدة المطلوبة "صغري" نستخدم الكمية المحولة إلى "صغري" (عدد صحيح)
                if ($unit == 'صغري') {
                    $item->quantity = $batchUsed['used_quantity_small'];
                } else {
                    $item->quantity = $batchUsed['used_quantity_big'];
                }
                $item->unit = $unit; // يحفظ كما هو ("صغري" أو "كبري")
                // سعر الوحدة: إذا كانت "صغري" نستخدم السعر المحسوب بوحدة "صغري"، وإلا نستخدم السعر الأصلي
                if ($unit == 'صغري') {
                    // نحسب السعر بوحدة "صغري" بضرب سعر الدفعة بقيمة الوحدة (يمكن تعديل التقريب)
                    $item->cost = round($batchUsed['price'] / $product->unit_value, 2);
                    // التكلفة الإجمالية بوحدة "صغري"
                    $item->total_cost = $batchUsed['cost_small'];
                } else {
                    $item->cost = $batchUsed['price'];
                    $item->total_cost = $batchUsed['cost_big'];
                }
                $item->save();
            }
        }

        DB::commit();
        toastr()->success(__('تم إنشاء التحويل بنجاح'));
        return redirect()->route('admin.transfer.index');
    } catch (\Exception $e) {
        DB::rollBack();
        toastr()->error(__($e->getMessage()));
        return redirect()->back();
    }
}




    /**
     * عرض تفاصيل تحويل واحد.
     * يتطلب صلاحية "transfer.view"
     */
    public function show($id)
    {
        if (!$this->checkPermission('transfer.view')) {
            return redirect()->back();
        }

        $transfer = Transfer::with('items')->findOrFail($id);
        
        return response()->json([
            'success' => 1,
            'view' => view('admin-views.transfersproducts.invoice', compact('transfer'))->render(),
        ]);
    }

    /**
     * عرض نموذج تعديل تحويل في حالة عدم الموافقة.
     * يتطلب صلاحية "transfer.edit"
     */
    public function edit($id)
    {
        if (!$this->checkPermission('transfer.edit')) {
            return redirect()->back();
        }

        $transfer = Transfer::findOrFail($id);
                $branches = Branch::where('active',1)->get();
        $branchId = auth('admin')->user()->branch_id;
        $branchColumn = "branch_" . $branchId;
        $products = Product::where($branchColumn, '>', 0)->where('product_type','product')->get();
        if (!in_array($transfer->status, ['draft', 'pending'])) {
            Toastr::error(translate('لا يمكن تعديل التحويل بعد الموافقة عليه'));
            return redirect()->back();
        }
        return view('admin-views.transfersproducts.edit', compact('transfer','branches','products'));
    }

    /**
     * تحديث تحويل موجود.
     * يتطلب صلاحية "transfer.edit"
     */
 public function update(Request $request, $id)
{
    if (!$this->checkPermission('transfer.edit')) {
        return redirect()->back();
    }

    $transfer = Transfer::findOrFail($id);
    if (!in_array($transfer->status, ['draft', 'pending'])) {
        Toastr::error(translate('لا يمكن تعديل التحويل بعد الموافقة عليه'));
        return redirect()->back();
    }

    $rules = [
        'transfer_number'       => 'required|unique:transfers,transfer_number,'.$transfer->id,
        'destination_branch_id' => 'required|exists:branches,id',
        'total_amount'          => 'required|numeric|min:0',
        'items'                 => 'required|array|min:1',
        'items.*.product_id'    => 'required|exists:products,id',
        'items.*.quantity'      => 'required|numeric|min:0.1',
        'items.*.unit'          => 'required|string',
    ];
    $request->validate($rules);

    DB::beginTransaction();
    try {
        // 1. الحصول على الأصناف الأصلية (قبل التحديث)
        $originalItems = TransferItem::where('transfer_id', $transfer->id)->get();
        $originalData = [];
        foreach ($originalItems as $orig) {
            $key = $orig->product_id . '_' . $orig->unit;
            if (!isset($originalData[$key])) {
                $originalData[$key] = 0;
            }
            $originalData[$key] += $orig->quantity;
        }

        // 2. تحديث سجل التحويل الرئيسي
        $transfer->transfer_number       = $request->transfer_number;
        $transfer->source_branch_id      = $request->source_branch_id;
        $transfer->destination_branch_id = $request->destination_branch_id;
        $transfer->account_id            = $request->account_id;
        $transfer->account_id_to         = $request->account_id_to;
        $transfer->total_amount          = $request->total_amount;
        $transfer->notes                 = $request->notes;
        $transfer->status                = $request->has('draft') ? 'draft' : 'pending';
        $transfer->save();

        // 3. بناء بيانات الأصناف الجديدة من الطلب
        $newItems = $request->items;
        $newData = [];
        foreach ($newItems as $item) {
            $key = $item['product_id'] . '_' . $item['unit'];
            if (!isset($newData[$key])) {
                $newData[$key] = 0;
            }
            $newData[$key] += $item['quantity'];
        }

        // تحديد الفرع والعمود الخاص بالمخزون (تخزين الكمية بالوحدة "كبري")
        $branchId = auth('admin')->user()->branch_id;
        $branchColumn = "branch_" . $branchId;

        // 4. مقارنة الأصناف الأصلية والجديدة وتحديث المخزون والدفعات
        foreach ($originalData as $key => $origQty) {
            list($productId, $unit) = explode('_', $key);
            $newQty = isset($newData[$key]) ? $newData[$key] : 0;
            $product = Product::find($productId);

            // تحويل الكميات إلى وحدة "كبري" للحساب الداخلي
            if ($unit == 'صغري') {
                $origBig = $origQty / $product->unit_value;
                $newBig  = $newQty / $product->unit_value;
            } else {
                $origBig = $origQty;
                $newBig  = $newQty;
            }

            if ($origBig > $newBig) {
                // الكمية المُرجعة = الفرق بين الأصلية والجديدة
                $returnedQty = $origBig - $newBig;
                // إرجاع الكمية إلى المخزون
                $product->$branchColumn += $returnedQty;
                $product->save();
                // إضافة دفعة مخزون جديدة (يمكن تعديل السعر حسب نظامك)
                \App\Models\StockBatch::create([
                    'product_id' => $product->id,
                    'branch_id'  => $branchId,
                    'quantity'   => $returnedQty,
                    'price'      => 0, // يمكن تعيين السعر المناسب
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                // تسجيل العملية في سجلات المنتجات بنوع 200
                $log = new \App\Models\ProductLog();
                $log->product_id = $product->id;
                $log->quantity   = $returnedQty; // بالوحدة "كبري"
                $log->type       = 200;
                $log->seller_id  = auth('admin')->user()->id;
                $log->branch_id  = $branchId;
                $log->save();
            } elseif ($newBig > $origBig) {
                // الكمية الإضافية = الفرق بين الجديدة والأصلية
                $extraQty = $newBig - $origBig;
                if ($product->$branchColumn < $extraQty) {
                    DB::rollBack();
                    Toastr::error(translate('الكمية الإضافية للمنتج غير متوفرة في المخزن'));
                    return redirect()->back();
                }
                // خصم الكمية الإضافية من المخزون
                $product->$branchColumn -= $extraQty;
                $product->save();
                // تحديث دفعات المخزون باستخدام FIFO (نفس منطق طريقة store)
                $stockBatches = \App\Models\StockBatch::where('product_id', $product->id)
                    ->where('branch_id', $branchId)
                    ->where('quantity', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->get();
                $remainingExtra = $extraQty;
                foreach ($stockBatches as $batch) {
                    if ($remainingExtra <= 0) break;
                    if ($batch->quantity >= $remainingExtra) {
                        $batch->quantity -= $remainingExtra;
                        $batch->save();
                        $remainingExtra = 0;
                    } else {
                        $remainingExtra -= $batch->quantity;
                        $batch->quantity = 0;
                        $batch->save();
                    }
                }
                // تسجيل العملية في سجلات المنتجات بنوع 100
                $log = new \App\Models\ProductLog();
                $log->product_id = $product->id;
                $log->quantity   = $extraQty; // بالوحدة "كبري"
                $log->type       = 100;
                $log->seller_id  = auth('admin')->user()->id;
                $log->branch_id  = $branchId;
                $log->save();
            }
            // إزالة المفتاح المعالج من البيانات الجديدة
            unset($newData[$key]);
        }
        // معالجة الأصناف الجديدة التي لم تكن موجودة في الأصل
        foreach ($newData as $key => $qty) {
            list($productId, $unit) = explode('_', $key);
            $product = Product::find($productId);
            if ($unit == 'صغري') {
                $newBig = $qty / $product->unit_value;
            } else {
                $newBig = $qty;
            }
            if ($product->$branchColumn < $newBig) {
                DB::rollBack();
                Toastr::error(translate('الكمية الإضافية للمنتج غير متوفرة في المخزن'));
                return redirect()->back();
            }
            $product->$branchColumn -= $newBig;
            $product->save();
            // تحديث دفعات المخزون
            $stockBatches = \App\Models\StockBatch::where('product_id', $product->id)
                ->where('branch_id', $branchId)
                ->where('quantity', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get();
            $remainingExtra = $newBig;
            foreach ($stockBatches as $batch) {
                if ($remainingExtra <= 0) break;
                if ($batch->quantity >= $remainingExtra) {
                    $batch->quantity -= $remainingExtra;
                    $batch->save();
                    $remainingExtra = 0;
                } else {
                    $remainingExtra -= $batch->quantity;
                    $batch->quantity = 0;
                    $batch->save();
                }
            }
            // تسجيل العملية بنوع 100
            $log = new \App\Models\ProductLog();
            $log->product_id = $product->id;
            $log->quantity   = $newBig;
            $log->type       = 100;
            $log->seller_id  = auth('admin')->user()->id;
            $log->branch_id  = $branchId;
            $log->save();
        }

        // حذف سجلات التحويل الأصلية وإعادة إنشائها بناءً على البيانات الجديدة
        TransferItem::where('transfer_id', $transfer->id)->delete();
        foreach ($request->items as $itemData) {
            $item = new TransferItem();
            $item->transfer_id = $transfer->id;
            $item->product_id  = $itemData['product_id'];
            $item->quantity    = $itemData['quantity'];
            $item->unit        = $itemData['unit'];
            $item->cost        = $itemData['cost'] ?? null;
            $item->total_cost  = $itemData['total_cost'] ?? null;
            $item->save();
        }

        DB::commit();
        Toastr::success(translate('تم تحديث التحويل بنجاح'));
        return redirect()->route('transfer.index');
    } catch (\Exception $e) {
        DB::rollBack();
        Toastr::error(translate($e->getMessage()));
        return redirect()->back();
    }
}


    /**
     * حذف تحويل إذا لم تتم الموافقة عليه.
     * يتطلب صلاحية "transfer.delete"
     */
public function destroy($id)
{
    if (!$this->checkPermission('transfer.delete')) {
        return redirect()->back();
    }

    $transfer = Transfer::findOrFail($id);
    if (!in_array($transfer->status, ['draft', 'pending'])) {
        Toastr::error(('لا يمكن حذف التحويل بعد الموافقة عليه'));
        return redirect()->back();
    }
    // التأكد من أن الفرع الذي يقوم بالحذف هو الفرع الذي أرسل التحويل
    if (auth('admin')->user()->branch_id != $transfer->source_branch_id) {
        Toastr::error(('يجب أن يتم الحذف من الفرع المصدر فقط'));
        return redirect()->back();
    }

    DB::beginTransaction();
    try {
        $branchId = auth('admin')->user()->branch_id;
        // إذا كان الفرع 1، نستخدم عمود "quantity" بدلاً من "branch_{id}"
        $branchColumn = ($branchId == 1) ? "quantity" : "branch_" . $branchId;
        
        foreach ($transfer->items as $item) {
            $product = Product::find($item->product_id);
            // زيادة الكمية في المخزن للفرع
            $product->$branchColumn += $item->quantity;
            $product->save();
            
            // إنشاء سجل دفعة مخزون جديد مع الكمية المستعادة
            \App\Models\StockBatch::create([
                'product_id' => $product->id,
                'branch_id'  => $branchId,
                'quantity'   => $item->quantity,
                'price'      => $item->cost, // السعر كما هو
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // تسجيل العملية في سجلات المنتجات بنوع 200 (استرجاع الكمية)
            $log = new \App\Models\ProductLog();
            $log->product_id = $product->id;
            $log->quantity   = $item->quantity; // يتم التعامل مع الكمية بوحدة "كبري"
            $log->type       = 200;
            $log->seller_id  = auth('admin')->user()->id;
            $log->branch_id  = $branchId;
            $log->save();
        }

        // حذف سجل التحويل
        $transfer->delete();

        DB::commit();
        Toastr::success(('تم حذف التحويل بنجاح'));
        return redirect()->route('transfer.index');
    } catch (\Exception $e) {
        DB::rollBack();
        Toastr::error(($e->getMessage()));
        return redirect()->back();
    }
}



    /**
     * الموافقة على التحويل (قبول الطلب).
     * يتطلب صلاحية "transfer.accept" ويجب أن يكون المدير من نفس فرع التحويل.
     */
public function accept(Request $request, $id)
{
    // 1) صلاحية
    if (!$this->checkPermission('transfer.accept')) {
        return redirect()->back();
    }

    // 2) تحميل التحويل + عناصره + المنتج
    $transfer = Transfer::with(['items.product'])->findOrFail($id);

    if (!in_array($transfer->status, ['pending'])) {
        Toastr::error('لا يمكن تعديل حالة هذا التحويل');
        return back();
    }

    // يجب أن يعتمد من الفرع الوجهة
    $adminBranchId = auth('admin')->user()->branch_id;
    if ((int)$transfer->destination_branch_id !== (int)$adminBranchId) {
        Toastr::error('يجب أن تكون من نفس الفرع الذي وُجه إليه التحويل');
        return back();
    }

    $action = $request->input('action');
    if (!in_array($action, ['approve', 'reject'])) {
        Toastr::error('إجراء غير صحيح');
        return back();
    }

    DB::beginTransaction();
    try {
        if ($action === 'reject') {
            // ====== رفض التحويل: رجّع المخزون للمصدر ======
            foreach ($transfer->items as $item) {
                $product = $item->product;

                // حوّل الكمية إلى وحدة كبري إن كانت صغري
                $unitValue = max(1, (float)($product->unit_value ?? 1));
                $qtyBig    = ($item->unit === 'صغري')
                           ? ((float)$item->quantity / $unitValue)
                           : (float)$item->quantity;

                $sourceBranchId = (int)$transfer->source_branch_id;
                $sourceCol      = ($sourceBranchId === 1) ? 'quantity' : ('branch_' . $sourceBranchId);

                // أضف للمصدر
                $product->$sourceCol = (float)$product->$sourceCol + $qtyBig;
                $product->save();

                // أضف دفعة مخزون في المصدر
                $batch = new \App\Models\StockBatch();
                $batch->product_id = $product->id;
                $batch->branch_id  = $sourceBranchId;
                $batch->quantity   = $qtyBig;                // دائماً كبري
                $batch->price      = (float)$item->cost;     // التكلفة من التحويل
                $batch->created_at = now();
                $batch->updated_at = now();
                $batch->save();

                // سجل حركة منتج
                $log = new \App\Models\ProductLog();
                $log->product_id = $product->id;
                $log->quantity   = $qtyBig;       // كبري
                $log->type       = 100;           // نوع مخصص للعمليات الإدارية
                $log->seller_id  = auth('admin')->id();
                $log->branch_id  = $sourceBranchId;
                $log->save();
            }

            $transfer->status      = 'rejected';
            $transfer->approved_by = auth('admin')->id();
            $transfer->save();

            DB::commit();
            Toastr::success('تم رفض التحويل وإرجاع المخزون للمصدر.');
            return back();
        }

        // ====== اعتماد التحويل ======
        foreach ($transfer->items as $item) {
            $product   = $item->product;
            $unitValue = max(1, (float)($product->unit_value ?? 1));
            $qtyBig    = ($item->unit === 'صغري')
                       ? ((float)$item->quantity / $unitValue)
                       : (float)$item->quantity;

            $destBranchId = (int)$transfer->destination_branch_id;
            $destCol      = ($destBranchId === 1) ? 'quantity' : ('branch_' . $destBranchId);

            // زوّد مخزون الوجهة
            $product->$destCol = (float)$product->$destCol + $qtyBig;
            $product->save();

            // أضف دفعة في الوجهة
            $batch = new \App\Models\StockBatch();
            $batch->product_id = $product->id;
            $batch->branch_id  = $destBranchId;
            $batch->quantity   = $qtyBig;                // كبري
            $batch->price      = (float)$item->cost;     // التكلفة من التحويل
            $batch->created_at = now();
            $batch->updated_at = now();
            $batch->save();

            // سجل حركة منتج
            $log = new \App\Models\ProductLog();
            $log->product_id = $product->id;
            $log->quantity   = $qtyBig;      // كبري
            $log->type       = 100;
            $log->seller_id  = auth('admin')->id();
            $log->branch_id  = $destBranchId;
            $log->save();
        }

        // ====== قيود اليومية + الحركات المالية (مدين/دائن) ======
        // نفترض أن account_id = حساب مخزون المصدر ، account_id_to = حساب مخزون الوجهة
        $sourceAccount = Account::find($transfer->account_id);
        $destAccount   = Account::find($transfer->account_id_to);

        if (!$sourceAccount || !$destAccount) {
            throw new \RuntimeException('تعذر إيجاد حسابات المخزون المرتبطة بالتحويل.');
        }

        $amount      = (float)$transfer->total_amount;
        $today       = now()->toDateString();
        $description = 'تحويل مخزني #' . $transfer->transfer_number;

        // (1) قيد اليومية (الرأس)
        $entry = new JournalEntry();
        $entry->entry_date   = $today;
        $entry->reference    = $transfer->transfer_number;
        $entry->type         = 'transfer';
        $entry->description  = $description;
        $entry->created_by   = auth('admin')->id();
        $entry->branch_id    = (int)$transfer->source_branch_id; // نربطه بفرع المصدر
        $entry->save();

        // (2) تفاصيل — مدين (مخزون الوجهة)
        $detailDebit = new JournalEntryDetail();
        $detailDebit->journal_entry_id = $entry->id;
        $detailDebit->account_id       = $destAccount->id;
        $detailDebit->debit            = $amount;
        $detailDebit->credit           = 0;
        $detailDebit->cost_center_id   = $transfer->destination_branch_id; // إن وُجد مركز تكلفة
        $detailDebit->description      = $description;
        $detailDebit->entry_date       = $today;
        $detailDebit->save();

        // (3) تفاصيل — دائن (مخزون المصدر)
        $detailCredit = new JournalEntryDetail();
        $detailCredit->journal_entry_id = $entry->id;
        $detailCredit->account_id       = $sourceAccount->id;
        $detailCredit->debit            = 0;
        $detailCredit->credit           = $amount;
        $detailCredit->cost_center_id   = $transfer->source_branch_id;
        $detailCredit->description      = $description;
        $detailCredit->entry_date       = $today;
        $detailCredit->save();

        // (4) ترانزاكشن — مدين (لحساب الوجهة)
        $newDestBalance = (float)$destAccount->balance + $amount;

        $tDebit = new Transection();
        $tDebit->tran_type               = 3; // نوع: تحويل
        $tDebit->seller_id               = auth('admin')->id();
        $tDebit->account_id              = $destAccount->id;
        $tDebit->account_id_to           = $sourceAccount->id;
        $tDebit->debit                   = $amount;
        $tDebit->credit                  = 0;
        $tDebit->amount                  = $amount;
        $tDebit->description             = $description;
        $tDebit->date                    = $today;
        $tDebit->balance                 = $newDestBalance;
        $tDebit->branch_id               = $transfer->destination_branch_id;
        $tDebit->journal_entry_detail_id = $detailDebit->id;
        $tDebit->save();

        // (5) ترانزاكشن — دائن (لحساب المصدر)
        $newSourceBalance = (float)$sourceAccount->balance - $amount;

        $tCredit = new Transection();
        $tCredit->tran_type               = 3;
        $tCredit->seller_id               = auth('admin')->id();
        $tCredit->account_id              = $sourceAccount->id;
        $tCredit->account_id_to           = $destAccount->id;
        $tCredit->debit                   = 0;
        $tCredit->credit                  = $amount;
        $tCredit->amount                  = $amount;
        $tCredit->description             = $description;
        $tCredit->date                    = $today;
        $tCredit->balance                 = $newSourceBalance;
        $tCredit->branch_id               = $transfer->source_branch_id;
        $tCredit->journal_entry_detail_id = $detailCredit->id;
        $tCredit->save();

        // (6) تحديث أرصدة الحسابات
        $destAccount->balance   = $newDestBalance;
        $destAccount->total_in  = (float)$destAccount->total_in + $amount;
        $destAccount->save();

        $sourceAccount->balance   = $newSourceBalance;
        $sourceAccount->total_out = (float)$sourceAccount->total_out + $amount;
        $sourceAccount->save();

        // (7) تحديث حالة التحويل
        $transfer->status      = 'approved';
        $transfer->approved_by = auth('admin')->id();
        $transfer->save();

        DB::commit();
        Toastr::success('تم اعتماد التحويل، وإنشاء قيود اليومية وترحيل الحركات (مدين/دائن).');
        return back();

    } catch (\Throwable $e) {
        DB::rollBack();
        Toastr::error($e->getMessage());
        return back();
    }
}



public function getPrice(Request $request)
{
    // التحقق من صحة المدخلات
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity'   => 'required|numeric|min:0.01',
        'unit'       => 'required|in:كبري,صغري'
    ]);

    // جلب بيانات المنتج والكمية المطلوبة
    $product = Product::find($request->product_id);
    $requestedQtyInput = $request->quantity;
    $unit = $request->unit;

    // تحويل الكمية إلى وحدة "كبري" إذا كانت الوحدة المدخلة "صغري"
    $requestedQty = ($unit == 'صغري') 
                    ? $requestedQtyInput / $product->unit_value 
                    : $requestedQtyInput;

    // تحديد معرف الفرع الحالي
    $branchId = auth('admin')->user()->branch_id;

    // استخدام استعلام تجميعي لحساب إجمالي الكمية المتوفرة دون تحميل جميع الصفوف في الذاكرة
    $totalAvailable = DB::table('stock_batches')
                        ->where('product_id', $product->id)
                        ->where('branch_id', $branchId)
                        ->where('quantity', '>', 0)
                        ->sum('quantity');

    if ($totalAvailable < $requestedQty) {
        return response()->json(['error' => 'المخزن نفذ'], 422);
    }

    $remaining = $requestedQty;
    $totalCost = 0;
    $batchesUsed = [];

    // استرجاع دفعات المخزون باستخدام cursor لتجنب تحميل 50 مليون صف دفعة واحدة
    $stockBatches = DB::table('stock_batches')
                      ->where('product_id', $product->id)
                      ->where('branch_id', $branchId)
                      ->where('quantity', '>', 0)
                      ->orderBy('created_at', 'asc')
                      ->cursor();

    // تنفيذ عملية FIFO على دفعات المخزون
    foreach ($stockBatches as $batch) {
        if ($remaining <= 0) {
            break;
        }
        $batchAvailable = $batch->quantity;
        if ($batchAvailable >= $remaining) {
            $used = $remaining;
            $remainingBatch = $batchAvailable - $used;
            $remaining = 0;
        } else {
            $used = $batchAvailable;
            $remainingBatch = 0;
            $remaining -= $batchAvailable;
        }
        $batchCost = $batch->price * $used;
        $totalCost += $batchCost;

        $batchData = [
            'batch_id'           => $batch->id,
            'product_id'         => $product->id,
            'used_quantity_big'  => number_format($used, 2),
            'price'              => $batch->price,
            'cost_big'           => $batchCost,
            'created_at'         => $batch->created_at,
            'remaining_in_batch' => $remainingBatch,
        ];

        if ($unit == 'صغري') {
            $batchData['used_quantity_small'] = $used * $product->unit_value;
            $batchData['cost_small'] = $batchCost / $product->unit_value;
        }

        $batchesUsed[] = $batchData;
    }

    return response()->json([
        'total_cost'               => $totalCost,
        'batches'                  => $batchesUsed,
        'multiple_batches'         => count($batchesUsed) > 1,
        'unit_value'               => $product->unit_value,
        'total_requested_quantity' => $requestedQtyInput,
        'total_available_quantity' => ($unit == 'صغري')
                                      ? $totalAvailable * $product->unit_value
                                      : $totalAvailable,
    ]);
}

}
