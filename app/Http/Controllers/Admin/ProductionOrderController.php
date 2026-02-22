<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use App\Models\BillOfMaterial;
use App\Models\Routing;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Admin;
use App\Models\ProductionOrderLog;
use App\Models\StockBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use App\CPU\Helpers;
use App\Models\ProductionOrderExecution;
use App\Models\ProductionOrderExecutionItem;
use Carbon\Carbon;

class ProductionOrderController extends Controller
{
    /**
     * عرض قائمة أوامر الإنتاج مع فلترة حسب الفرع، المُصدر، والحالة.
     */
    public function index(Request $request)
    {
        $query = ProductionOrder::with(['product', 'bom.product', 'routing', 'branch', 'issuer']);

        if ($branch = $request->input('branch_id')) {
            $query->where('branch_id', $branch);
        }
        if ($user = $request->input('issued_by')) {
            $query->where('issued_by', $user);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $orders = $query
            ->paginate(Helpers::pagination_limit())
            ->appends($request->only(['branch_id', 'issued_by', 'status']));

        $branches = Branch::where('active', 1)->get();
        $users    = Admin::all();

        return view('admin-views.production-orders.index', compact('orders', 'branches', 'users'));
    }

    /**
     * إظهار نموذج إضافة أمر إنتاج جديد.
     */
    public function create()
    {
        $products = Product::all();
        $boms     = BillOfMaterial::with('product')->get();
        $routings = Routing::with('bom.product')->get();
        $branches = Branch::where('active', 1)->get();

        return view('admin-views.production-orders.create', compact(
            'products', 'boms', 'routings', 'branches'
        ));
    }

    /**
     * تخزين أمر إنتاج جديد مع تسجيله في السجل.
     */
 public function store(Request $request)
{
    $data = $request->validate([
        'bom_id'     => 'required|exists:bills_of_materials,id',
        'routing_id' => 'required|exists:routings,id',
        'branch_id'  => 'required|exists:branches,id',
        'quantity'   => 'required|numeric|min:0.01',
        'unit'       => 'required|in:0,1',
        'start_date' => 'nullable|date',
        'end_date'   => 'nullable|date',
    ]);

    // أحضر الBOM وربطه بالمنتج
    $bom = BillOfMaterial::findOrFail($data['bom_id']);
    $data['product_id'] = $bom->product_id;

    // من أصدَر الأمر
    $data['issued_by'] = Auth::guard('admin')->id();

    DB::beginTransaction();
    try {
        // إنشاء أمر الإنتاج
        $order = ProductionOrder::create($data);

        // سجل عملية الإنشاء
        ProductionOrderLog::createFromOrder(
            $order,
            Auth::guard('admin')->id(),
            'create',
            $data
        );

        DB::commit();
        Toastr::success('تم إنشاء أمر الإنتاج بنجاح', 'نجاح');
        return redirect()->route('admin.production-orders.index');

    } catch (\Exception $e) {
        DB::rollBack();
        Toastr::error('حدث خطأ أثناء إنشاء الأمر', 'خطأ');
        return back()->withInput();
    }
}

    /**
     * إظهار نموذج تعديل أمر إنتاج.
     */
    public function edit(int $id)
    {
        $order = ProductionOrder::findOrFail($id);

        // منع التعديل بعد بدء التنفيذ
        if (! in_array($order->status, ['draft', 'planned'])) {
            Toastr::error('لا يمكنك تعديل هذا الأمر بعد بدء تنفيذه', 'غير مسموح');
            return redirect()->route('admin.production-orders.index');
        }

        $products = Product::all();
        $boms     = BillOfMaterial::with('product')->get();
        $routings = Routing::with('bom.product')->get();
        $branches = Branch::where('active', 1)->get();

        return view('admin-views.production-orders.edit', compact(
            'order', 'products', 'boms', 'routings', 'branches'
        ));
    }

    /**
     * تحديث أمر إنتاج مع التقيد بقواعد التعديل وتسجيل السجل.
     */
public function update(Request $request, int $id)
{
    $order = ProductionOrder::with([
        'bom.components.componentProduct', // لتحميل المكونات مع بيانات المنتج
        'batches'                          // لتحميل أي حجز سابق
    ])->findOrFail($id);

    // 1) لا يمكن التعديل إلا في حالتي draft أو planned
    if (! in_array($order->status, ['draft','planned'])) {
        Toastr::error('لا يمكنك تعديل هذا الأمر بعد بدء تنفيذه', 'غير مسموح');
        return redirect()->route('admin.production-orders.index');
    }

    // 2) قواعد التحقق تختلف إذا كان الأمر مسبقاً planned أو لا
    $rules = $order->status === 'planned'
        ? [
            'quantity'   => 'required|numeric|min:0.01',
            'unit'       => 'required|in:0,1',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
            'status'     => 'required|in:draft,planned',
        ]
        : [
            'bom_id'     => 'required|exists:bills_of_materials,id',
            'routing_id' => 'required|exists:routings,id',
            'branch_id'  => 'required|exists:branches,id',
            'quantity'   => 'required|numeric|min:0.01',
            'unit'       => 'required|in:0,1',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
            'status'     => 'required|in:draft,planned',
        ];

    $data = $request->validate($rules);

    DB::beginTransaction();
    try {
        $oldStatus = $order->status;

        // 3) إذا غيّرنا الـ BOM، نحدّث الـ product_id تلقائياً
        if (isset($data['bom_id'])) {
            $bom = BillOfMaterial::findOrFail($data['bom_id']);
            $data['product_id'] = $bom->product_id;
        }

        // 4) نحفظ التغييرات العامة
        $order->update($data);

        // 5) إذا انتقلنا من مسودة إلى مخطط → نبدأ حجز مكونات الـ BOM
        if ($oldStatus === 'draft' && $order->status === 'planned') {
            $branchId     = $order->branch_id;
            $branchColumn = 'branch_' . $branchId;

            // كمية المنتج الرئيسي بالوحدة الأساسية
            $mainProduct = $order->bom->product;
            $mainQty     = $order->quantity;
            if ($order->unit == 0) {
                $mainQty = $mainQty / $mainProduct->unit_value;
            }

            // 5.a) نتأكد أولاً من توفر كل المكونات
            foreach ($order->bom->components as $comp) {
                $component = $comp->componentProduct;
                $needed    = $comp->quantity * $mainQty;

                $available = $branchId == 1
                    ? $component->quantity
                    : $component->{$branchColumn};

                if ($available < $needed) {
                    DB::rollBack();
                    Toastr::error(
                        "المكون «{$component->name}» غير كافٍ (مطلوب: {$needed}, متاح: {$available})",
                        'نفاد المخزون'
                    );
                    return back()->withInput();
                }
            }

            // 5.b) نقصّ من المخزون الإجمالي ونحجز من دفعات FIFO
            foreach ($order->bom->components as $comp) {
                $component = $comp->componentProduct;
                $needed    = $comp->quantity * $mainQty;

                // نقص من الإجمالي
                if ($branchId == 1) {
                    $component->decrement('quantity', $needed);
                } else {
                    $component->decrement($branchColumn, $needed);
                }

                // نحجز من دفعات FIFO
                $batches = StockBatch::where('product_id', $component->id)
                    ->where('branch_id', $branchId)
                    ->where('quantity', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($needed <= 0) break;
                    $take = min($batch->quantity, $needed);

                    $batch->decrement('quantity', $take);

                    // سجل الحجز في الجدول الوسيط
                    $order->batches()->attach($batch->id, [
                        'reserved_quantity' => $take,
                    ]);

                    $needed -= $take;
                }
            }
        }

        // 6) سجل عملية التحديث
        ProductionOrderLog::createFromOrder(
            $order,
            Auth::guard('admin')->id(),
            'update',
            $data
        );

        DB::commit();
        Toastr::success('تم تحديث أمر الإنتاج بنجاح', 'نجاح');
        return redirect()->route('admin.production-orders.index');

    } catch (\Exception $e) {
        DB::rollBack();
        Toastr::error('حدث خطأ أثناء التحديث', 'خطأ');
        return back()->withInput();
    }
}


    /**
     * عرض تفاصيل أمر الإنتاج وسجلاته.
     */
public function show(int $id)
{
    $order = ProductionOrder::with([
        'product',
        'bom.product',
        'bom.components.componentProduct',
        'routing',
        'routing.operations.workCenter',
        'branch',
        'issuer'
    ])->findOrFail($id);

    $logs = ProductionOrderLog::with('user')
        ->where('production_order_id', $order->id)
        ->orderBy('created_at','desc')
        ->get();

    return view('admin-views.production-orders.show', compact('order','logs'));
}
 public function show_invoice($id)
    {

    $order = ProductionOrder::with([
        'product',
        'bom.product',
        'bom.components.componentProduct',
        'routing',
        'routing.operations.workCenter',
        'branch',
        'issuer'
    ])->findOrFail($id);

    $logs = ProductionOrderLog::with('user')
        ->where('production_order_id', $order->id)
        ->orderBy('created_at','desc')
        ->get();
        return response()->json([
            'success' => 1,
            'view' => view('admin-views.production-orders.invoice', compact('order','logs'))->render(),
        ]);
    }
    /**
 * إلغاء أمر الإنتاج واسترجاع الكمية المحجوزة (إذا كانت مخططة).
 */
public function cancel(int $id)
{
    // 1) جلب أمر الإنتاج مع الدُفعات المحجوزة لكل مكون
    $order = ProductionOrder::with('batches')->findOrFail($id);

    // 2) نسمح بالإلغاء فقط إذا كانت الحالة draft أو planned
    if (! in_array($order->status, ['draft', 'planned'])) {
        Toastr::error('لا يمكنك إلغاء هذا الأمر بعد بدء تنفيذه أو إتمامه', 'غير مسموح');
        return redirect()->route('admin.production-orders.index');
    }

    DB::beginTransaction();
    try {
        $branchId  = $order->branch_id;
        $branchCol = 'branch_' . $branchId;

        // 3) لكل دفعة مخزون محجوزة (من مكونات الـ BOM)
        foreach ($order->batches as $batch) {
            $reserved = $batch->pivot->reserved_quantity;

            // 3.a) استرجاع الكمية المحجوزة إلى مخزون المنتج الرئيسي
            $product = \App\Models\Product::findOrFail($batch->product_id);
            if ($branchId === 1) {
                $product->increment('quantity', $reserved);
            } else {
                $product->increment($branchCol, $reserved);
            }

            // 3.b) إعادة الكمية إلى الدفعة نفسها
            $batch->increment('quantity', $reserved);

            // 3.c) تفريغ الـ reserved_quantity في الدفعة
            $batch->decrement('reserved_quantity', $reserved);

            // 3.d) حذف الربط بين الأمر وهذه الدفعة
            $order->batches()->detach($batch->id);
        }

        // 4) تغيير حالة الأمر إلى "ملغي"
        $order->update(['status' => 'cancelled']);

        // 5) تسجيل عملية الإلغاء في السجل
        ProductionOrderLog::createFromOrder(
            $order,
            Auth::guard('admin')->id(),
            'cancel',
            ['note' => 'تم إلغاء الأمر واسترجاع كميات المكونات']
        );

        DB::commit();
        Toastr::success('تم إلغاء أمر الإنتاج بنجاح واسترجاع كميات المكونات المحجوزة', 'نجاح');
        return redirect()->route('admin.production-orders.index');
    } catch (\Exception $e) {
        DB::rollBack();
        Toastr::error('حدث خطأ أثناء إلغاء الأمر', 'خطأ');
        return back();
    }
}
public function startProduction(int $id)
{
    $order = ProductionOrder::with('batches')->findOrFail($id);

    // تأكد أن الحالة حالياً مخطط
    if ($order->status !== 'planned') {
        Toastr::error('لا يمكنك بدء هذا الأمر قبل أن يكون مخططاً', 'غير مسموح');
        return redirect()->route('admin.production-orders.index');
    }

    DB::beginTransaction();
    try {
        // 1) تحويل الحالة إلى قيد التنفيذ
        $order->update(['status' => 'in_progress']);

        // 2) تسجيل الحدث في سجل الإنتاج
        ProductionOrderLog::createFromOrder(
            $order,
            Auth::guard('admin')->id(),
            'start',
            []
        );

        // 3) احتساب التكلفة الإجمالية من دفعات المخزون المحجوزة
        //    (الكمية المحجوزة × سعر الوحدة) لكل دفعة ثم جمعها
        $totalPriceAllProducts = $order->batches->sum(function($batch) {
            return $batch->pivot->reserved_quantity * $batch->price;
        });

        // 4) إعداد الحسابات المحاسبية
        $branch = \App\Models\Branch::findOrFail($order->branch_id);
        $accountTo = \App\Models\Account::findOrFail(101);                    // حساب مخزون المواد الأولية
        $accountFrom   = \App\Models\Account::findOrFail($branch->account_stock_Id); // حساب مخزون قيد التنفيذ للفرع

        // 5) إنشاء قيد المعاملة
        $txn = new \App\Models\Transection();
        $txn->tran_type       = $order->type;
        $txn->seller_id       = Auth::guard('admin')->id();
        $txn->branch_id       = $order->branch_id;
        $txn->account_id      = $accountFrom->id;
        $txn->account_id_to   = $accountTo->id;
        $txn->amount          = $totalPriceAllProducts;
        $txn->description     = 'قيد تصنيع: بدء تنفيذ أمر الإنتاج #' . $order->id;
        $txn->debit           = $totalPriceAllProducts;
        $txn->credit          = 0;
        $txn->balance         = $accountFrom->balance - $totalPriceAllProducts;
        $txn->debit_account   = 0;
        $txn->credit_account  = $totalPriceAllProducts;
        $txn->balance_account = $accountTo->balance + $totalPriceAllProducts;
        $txn->date            = now();
        $txn->save();

        // 6) تحديث أرصدة الحسابين
        $accountFrom->decrement('balance', $totalPriceAllProducts);
        $accountFrom->increment('total_out', $totalPriceAllProducts);

        $accountTo->increment('balance', $totalPriceAllProducts);
        $accountTo->increment('total_in', $totalPriceAllProducts);

        DB::commit();
        Toastr::success('تم بدء تنفيذ أمر الإنتاج وحُسِبت التكلفة بنجاح', 'نجاح');
    } catch (\Exception $e) {
        DB::rollBack();
        Toastr::error('حدث خطأ أثناء بدء التنفيذ: ' . $e->getMessage(), 'خطأ');
    }

    return redirect()->route('admin.production-orders.index');
}
      public function showCompleteForm(int $orderId)
    {
        $order = ProductionOrder::with('batches.product')->findOrFail($orderId);

        return view('admin-views.production-orders.finalize', compact('order'));
    }

    // Обработать завершение


public function finalize(Request $request, int $orderId)
{
    // 1) Validation
    $request->validate([
        'produced_quantity'         => ['required','numeric','min:0'],
        'start_time'                => ['required','date'],
        'additional_costs'          => ['nullable','array'],
        'additional_costs.*.desc'   => ['required_with:additional_costs','string'],
        'additional_costs.*.amount' => ['required_with:additional_costs','numeric','min:0'],
        'batches.*.actual_quantity' => ['required','numeric','min:0'],
        'batches.*.waste_quantity'  => ['required','numeric','min:0'],
    ]);

    DB::beginTransaction();

    try {
        // 2) Load order, its product, BOM components and already reserved batches (StockBatch models)
        $order = ProductionOrder::with([
            'product',
            'bom.components.componentProduct',
            'batches'  // eager-load StockBatch + pivot data
        ])->findOrFail($orderId);

        $branchId    = $order->branch_id;
        // determine which column on products table holds this branch's stock
        $branchCol   = $branchId === 1
            ? 'quantity'
            : 'branch_' . $branchId;
        $producedQty = $request->input('produced_quantity');

        // 3) Ensure enough on-hand before reserving
        foreach ($order->bom->components as $comp) {
            $needed    = $comp->quantity * $producedQty;
            $available = $comp->componentProduct->{$branchCol} ?? 0;
            if ($available < $needed) {
                throw new \RuntimeException(
                    "المكون «{$comp->componentProduct->name}» غير كافٍ (مطلوب {$needed}, متاح {$available})"
                );
            }
        }

        // 4) Reserve stock: decrement product, pull FIFO StockBatch, attach pivot
        foreach ($order->bom->components as $comp) {
            $prod   = $comp->componentProduct;
            $needed = $comp->quantity * $producedQty;

            // decrement the product's branch stock
            $prod->decrement($branchCol, $needed);

            // FIFO batches for this product & branch
            $fifo = StockBatch::where('product_id', $prod->id)
                ->where('branch_id', $branchId)
                ->where('quantity', '>', 0)
                ->orderBy('created_at')
                ->get();

            foreach ($fifo as $batch) {
                if ($needed <= 0) break;
                $take = min($batch->quantity, $needed);
                $batch->decrement('quantity', $take);

                // record reservation in pivot
                $order->batches()->attach($batch->id, [
                    'reserved_quantity' => $take,
                    'actual_quantity'   => 0,
                    'waste_quantity'    => 0,
                ]);

                $needed -= $take;
            }
        }

        // reload batches with pivot
        $order->load('batches');

        // 5) Compute elapsed time
        $start = Carbon::parse($request->input('start_time'));
        $end   = Carbon::now();
        $hours = round($end->floatDiffInHours($start), 2);

        // 6) For each reserved batch: validate actual+waste, return remainders, update pivot
        foreach ($order->batches as $batch) {
            $reserved = $batch->pivot->reserved_quantity;
            $actual   = $request->input("batches.{$batch->id}.actual_quantity");
            $waste    = $request->input("batches.{$batch->id}.waste_quantity");

            // find matching componentProduct
            $compProd = $order->bom->components->first(fn($c) =>
                $c->componentProduct->id === $batch->product_id
            )->componentProduct;

            $available = $compProd->{$branchCol} ?? 0;
            if (($actual + $waste) > $available) {
                throw new \RuntimeException(
                    "المجموع ({$actual} + {$waste}) يتجاوز المتاح ({$available}) للمكون «{$compProd->name}»"
                );
            }

            // return any leftover reserved back to stock
            $remainder = $reserved - ($actual + $waste);
            if ($remainder > 0) {
                $compProd->increment($branchCol, $remainder);
                $batch->increment('quantity', $remainder);
            }

            // update pivot with final values
            $order->batches()->updateExistingPivot($batch->id, [
                'actual_quantity' => $actual,
                'waste_quantity'  => $waste,
            ]);
        }

        // 7) Calculate totals & costs
        $totalConsumed      = $order->batches->sum('pivot.actual_quantity');
        $totalWaste         = $order->batches->sum('pivot.waste_quantity');
        $additionalCosts    = $request->input('additional_costs', []);
        $additionalTotal    = collect($additionalCosts)->sum('amount');
        $totalConsumedCost  = $order->batches->reduce(fn($sum, $batch) =>
            $sum + ($batch->pivot->actual_quantity * $batch->unit_price),
        0);
        $unitCostFinal = $producedQty
            ? round($totalConsumedCost / $producedQty, 2)
            : 0;

        // 8) Create execution record
        $execution = ProductionOrderExecution::create([
            'production_order_id'     => $order->id,
            'branch_id'               => $branchId,
            'start_time'              => $start,
            'end_time'                => $end,
            'actual_hours'            => $hours,
            'total_consumed_quantity' => $totalConsumed,
            'waste_quantity'          => $totalWaste,
            'produced_quantity'       => $producedQty,
            'unit_cost'               => $unitCostFinal,
            'additional_costs'        => json_encode($additionalCosts, JSON_UNESCAPED_UNICODE),
            'additional_cost_total'   => $additionalTotal,
            'total_cost'              => round($unitCostFinal * $producedQty + $additionalTotal, 2),
            'executed_by'             => Auth::guard('admin')->id(),
        ]);

        // 9) Create execution items
        foreach ($order->batches as $batch) {
            ProductionOrderExecutionItem::create([
                'execution_id'      => $execution->id,
                'product_id'        => $batch->product_id,
                'reserved_quantity' => $batch->pivot->reserved_quantity,
                'consumed_quantity' => $batch->pivot->actual_quantity,
                'unit_cost'         => $batch->price,
                'reserved_cost'     => $batch->pivot->reserved_quantity * $batch->unit_price,
                'consumed_cost'     => $batch->pivot->actual_quantity  * $batch->unit_price,
                'waste_cost'        => $batch->pivot->waste_quantity     * $batch->unit_price,
            ]);
        }

        // 10) Finalize order record
        $order->update([
            'status'            => 'completed',
            'produced_quantity' => $producedQty,
            'cost_price'        => $unitCostFinal,
        ]);

        // 11) Add produced items into stock
        $prod = $order->product;
        $prod->increment($branchCol, $producedQty);

        StockBatch::create([
            'product_id' => $prod->id,
            'branch_id'  => $branchId,
            'quantity'   => $producedQty,
            'unit_price' => $unitCostFinal,
        ]);

        DB::commit();
        Toastr::success('تم إنهاء أمر الإنتاج وتسجيل التفاصيل بنجاح', 'نجاح');
        return redirect()->route('admin.production-orders.show', $orderId);

    } catch (\Exception $e) {
        DB::rollBack();
        Toastr::error($e->getMessage(), 'خطأ أثناء إنهاء الإنتاج');
        return back()->withInput();
    }
}
}
