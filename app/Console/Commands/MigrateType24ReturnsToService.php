<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Supplier;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;
use App\Models\Transection;
use App\Models\StockBatch;
use App\Models\Customer;

class MigrateType24ReturnsToService extends Command
{
    protected $signature = 'orders:migrate-type24-to-service 
                            {--ids= : optional comma-separated order IDs to limit}
                            {--dry-run : فقط عرض بدون تنفيذ فعلي}';

    protected $description = 'Rollback old type=24 returns and recreate them as service returns (type=7) using cash account from parent payment_id';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        // === 1) تحديد الأوامر المستهدفة ===
        $query = Order::with(['details', 'parent'])
            ->where('type', 24); // الفواتير القديمة (مرتجعات) اللي عايزين نهجّرها

        // لو حابب تطبق على IDs معينة
        if ($ids = $this->option('ids')) {
            $idArray = array_filter(array_map('intval', explode(',', $ids)));
            if (!empty($idArray)) {
                $query->whereIn('id', $idArray);
            }
        }

        // هنا فلترت إن الفاتورة الأصلية نوعها 4 (حسب نظامك)
        $query->whereHas('parent', function ($q) {
            $q->where('type', 4);
        });

        $orders = $query->get();

        if ($orders->isEmpty()) {
            $this->info('لا توجد فواتير type=24 مطابقة للشروط.');
            return Command::SUCCESS;
        }

        $this->info('سيتم معالجة ' . $orders->count() . ' فاتورة مرتجع (type=24).');

        foreach ($orders as $oldReturn) {
            $this->line('------------------------------------');
            $this->info("معالجة مرتجع قديم #{$oldReturn->id} (parent_id = {$oldReturn->parent_id})");

            if (!$oldReturn->parent) {
                $this->warn("⚠️ لا توجد فاتورة أصلية لهذا المرتجع, سيتم تخطيه.");
                continue;
            }

            $parentOrder = $oldReturn->parent; // الفاتورة الأصلية

            // ===== A) فحص حساب الكاش قبل أي حاجة =====
            // الحساب اللي هنرجع عليه الفلوس = account من payment_id
            $cashAccount = null;
            if ($parentOrder->payment_id) {
                $cashAccount = Account::find($parentOrder->payment_id);
            }

            if ($dryRun) {
                $this->comment("DRY RUN:");
                $this->comment(" - parent order #{$parentOrder->id}");
                $this->comment(" - payment_id = " . ($parentOrder->payment_id ?? 'NULL'));
                if ($cashAccount) {
                    $this->comment(" - سيتم استخدام حساب كاش ID={$cashAccount->id} للاستراداد.");
                } else {
                    $this->comment(" - لا يوجد حساب كاش من payment_id => لن يتم تنفيذ الهجرة الفعلية لهذا الطلب.");
                }
                // ما نكمّلش أي تعديل في DRY RUN
                continue;
            }

            // لو مفيش حساب كاش → ما نعملش أي حاجة (لا rollback ولا مرتجع جديد)
            if (!$cashAccount) {
                $this->warn("⚠️ لا يوجد حساب كاش (payment_id فارغ أو لا يشير إلى حساب) للفاتورة الأصلية #{$parentOrder->id}. سيتم تخطي المرتجع القديم #{$oldReturn->id} بدون أي تعديل.");
                continue;
            }

            DB::beginTransaction();
            try {
                $branchId     = $oldReturn->branch_id??1;
                $branchColumn = 'branch_' . $branchId;

                // ========= (A) حفظ بيانات المرتجع القديم =========
                // هنستخدمها في إنشاء مرتجع خدمة جديد بعد عكس القديم
                $lines = [];
                foreach ($oldReturn->details as $detail) {
                    $data = json_decode($detail->product_details, true) ?: [];

                    $lines[] = [
                        'product_id'      => $detail->product_id,
                        'quantity'        => $detail->quantity,
                        'unit'            => $detail->unit,
                        'price'           => $detail->price,
                        'discount'        => $detail->discount_on_product,
                        'tax'             => $detail->tax_amount,
                        'extra_discount'  => $data['extra_discount'] ?? 0,
                        'raw_details'     => $detail, // نخليه موجود لو احتجنا
                    ];
                }

                // ========= (B) عكس تأثير المرتجع القديم =========

                foreach ($oldReturn->details as $detail) {
                    $productId   = $detail->product_id;
                    $qtyInReturn = (float) $detail->quantity;
                    $unit        = (int) $detail->unit;

                    $product = Product::find($productId);
                    if (!$product) {
                        $this->warn("⚠️ المنتج ID={$productId} غير موجود, تخطي تعديل المخزون.");
                        continue;
                    }

                    $pDetails  = json_decode($product->product_details);
                    $unitValue = isset($pDetails->unit_value) ? (float)$pDetails->unit_value : 1;

                    // نفس منطق المرتجع القديم: المرتجع كان بينقص المخزون بـ finalQuantity
                    $finalQuantity = ($unit === 0)
                        ? ($qtyInReturn / ($unitValue ?: 1))
                        : $qtyInReturn;

                    // (B1) تعديل الفاتورة الأصلية: تقليل quantity_returned بما يعادل الكمية
                    $parentDetail = $parentOrder->details
                        ->where('product_id', $productId)
                        ->first();

                    if ($parentDetail) {
                        $oldReturnedBase = (float)($parentDetail->quantity_returned ?? 0);

                        // نرجع اللي كان متخزن
                        $newReturned = $oldReturnedBase - $finalQuantity;
                        if ($newReturned < 0) $newReturned = 0;

                        $parentDetail->quantity_returned = $newReturned;
                        $parentDetail->save();
                    }

                    // (B2) إعادة الكمية للمخزون
                    if (isset($product->$branchColumn)) {
                        $product->$branchColumn += $finalQuantity;
                    } else {
                        $product->quantity += $finalQuantity;
                    }

                    if (!is_null($product->repurchase_count) && $product->repurchase_count > 0) {
                        $product->repurchase_count -= 1;
                    }
                    $product->save();

                    // (B3) تعديل الدُفعات: نضيف الكمية مرة تانية لأقرب دفعات
                    $batches = StockBatch::where('product_id', $productId)
                        ->where('branch_id', $branchId)
                        ->get();

                    $remain = $finalQuantity;
                    foreach ($batches as $batch) {
                        if ($remain <= 0) break;

                        if (isset($batch->$branchColumn)) {
                            $batch->$branchColumn += $remain;
                        } else {
                            $batch->quantity += $remain;
                        }

                        $batch->save();
                        $remain = 0;
                    }
                }

                // (B4) عكس القيود المحاسبية للمرتجع القديم (المخزون / المورد)
                $amount = (float)$oldReturn->order_amount;

                $branch  = Branch::find($branchId);
                $supplier = $oldReturn->supplier_id
                    ? Supplier::find($oldReturn->supplier_id)
                    : null;

                if ($branch && $supplier && $amount > 0) {
                    $inventoryAccount = Account::find($branch->account_stock_Id);
                    $supplierAccount  = Account::find($supplier->account_id);

                    if ($inventoryAccount) {
                        // في المرتجع القديم كنا عاملين -amount على المخزون -> نرجعها +amount
                        $inventoryAccount->balance   += $amount;
                        if (!is_null($inventoryAccount->total_out) && $inventoryAccount->total_out >= $amount) {
                            $inventoryAccount->total_out -= $amount;
                        }
                        $inventoryAccount->save();
                    }

                    if ($supplierAccount) {
                        // كانوا منقصين التزام للمورد -> نرجّعه
                        $supplierAccount->balance   += $amount;
                        if (!is_null($supplierAccount->total_out) && $supplierAccount->total_out >= $amount) {
                            $supplierAccount->total_out -= $amount;
                        }
                        $supplierAccount->save();
                    }
                }

                // (B5) حذف قيود اليومية وترانزاكشن الخاصة بالمرتجع القديم
                $journal = JournalEntry::where('reference', 'PR-' . $oldReturn->id)->first();
                if ($journal) {
                    JournalEntryDetail::where('journal_entry_id', $journal->id)->delete();
                    $journal->delete();
                }

                Transection::where('order_id', $oldReturn->id)->delete();

                // (B6) حذف تفاصيل وأمر المرتجع القديم
                OrderDetail::where('order_id', $oldReturn->id)->delete();
                $oldReturnId = $oldReturn->id;
                $oldReturn->delete();

                $this->info("✔ تم عكس وحذف المرتجع القديم #{$oldReturnId} بنجاح.");

                // ========= (C) إنشاء مرتجع خدمة جديد (type = 7) =========

                // 1) نجيب customer & accounts
                $customer       = Customer::find($parentOrder->user_id);
                $accCustomer    = $customer ? Account::find($customer->account_id) : null;
                $accSalesReturn = Account::find(40); // مردودات خدمات - عدّل ID لو مختلف
                $accTax         = Account::find(28); // ضريبة - عدّل ID لو مختلف

                // 2) الإجماليات من lines
                $totalReturnPrice = $totalReturnDiscount = $totalReturnExtraDiscount = $totalReturnTax = $totalReturnOverall = 0.0;

                foreach ($lines as $l) {
                    $qty  = (float)$l['quantity'];
                    $p    = (float)$l['price'];
                    $disc = (float)$l['discount'];
                    $ex   = (float)$l['extra_discount'];
                    $tax  = (float)$l['tax'];

                    $effUnit = $p - $disc - $ex + $tax;

                    $totalReturnPrice         += $qty * $p;
                    $totalReturnDiscount      += $qty * $disc;
                    $totalReturnExtraDiscount += $qty * $ex;
                    $totalReturnTax           += $qty * $tax;
                    $totalReturnOverall       += $qty * $effUnit;
                }

                if ($totalReturnOverall <= 0) {
                    $this->warn("⚠️ إجمالي المرتجع المحسوب يساوي صفر بعد الهجرة، تم الاكتفاء بعكس المرتجع القديم بدون إنشاء جديد.");
                    DB::commit();
                    continue;
                }

                // 3) إنشاء طلب مرتجع خدمة جديد (type = 7)
                $newOrder = new Order;
                $newOrder->owner_id       = $parentOrder->owner_id;
                $newOrder->user_id        = $parentOrder->user_id;
                $newOrder->parent_id      = $parentOrder->id;
                $newOrder->branch_id      = $parentOrder->branch_id??1;
                $newOrder->cash           = 1;
                $newOrder->type           = 7;            // كود مرتجع خدمة عندك
                $newOrder->order_type     = 'service';
                $newOrder->total_tax      = round($totalReturnTax, 2);
                $newOrder->order_amount   = round($totalReturnOverall, 2);
                $newOrder->extra_discount = round($totalReturnExtraDiscount, 2);
                $newOrder->date           = now()->toDateString();
                $newOrder->note           = $parentOrder->note;
                $newOrder->save();

                // QR بسيط
                $qrcodeData  = url("real/invoicea2/" . $newOrder->id);
                $qrCode      = new \Endroid\QrCode\QrCode($qrcodeData);
                $writer      = new \Endroid\QrCode\Writer\PngWriter();
                $qrcodeImage = $writer->write($qrCode)->getString();
                $qrcodePath  = "qrcodes/order_" . $newOrder->id . ".png";
                \Storage::disk('public')->put($qrcodePath, $qrcodeImage);
                $newOrder->qrcode = $qrcodePath;
                $newOrder->save();

                // 4) اليومية للمرتجع خدمة (كاش فقط)
                $netWithoutTax = $totalReturnOverall - $totalReturnTax;

                $journalRef = 'RET-SVC-MIG-' . now()->format('YmdHis') . '-' . $newOrder->id;

                $journalId = DB::table('journal_entries')->insertGetId([
                    'reference'   => $journalRef,
                    'description' => 'قيد يومية مرتجع خدمة (هجرة) #' . $newOrder->id,
                    'branch_id'   => $newOrder->branch_id??1,
                    'created_by'  => $newOrder->owner_id,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                $linesJE = [];
                $addLine = function ($accountId, $debit, $credit, $memo) use (&$linesJE, $journalId) {
                    if (!$accountId) return;
                    if (round($debit,2) == 0 && round($credit,2) == 0) return;
                    $linesJE[] = [
                        'journal_entry_id' => $journalId,
                        'account_id'       => $accountId,
                        'debit'            => round($debit,2),
                        'credit'           => round($credit,2),
                        'description'      => $memo,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ];
                };

                // Dr Returns + Dr Tax / Cr CashAccount
                if ($netWithoutTax    > 0) $addLine($accSalesReturn?->id, $netWithoutTax,    0, 'مردودات خدمات (صافي) - نقدي [هجرة]');
                if ($totalReturnTax   > 0) $addLine($accTax?->id,        $totalReturnTax,   0, 'ضريبة على مرتجع خدمة - نقدي [هجرة]');
                if ($totalReturnOverall>0) $addLine($cashAccount?->id,   0, $totalReturnOverall, 'صرف نقدي لعميل مقابل مرتجع خدمة [هجرة]');

                if ($linesJE) {
                    DB::table('journal_entries_details')->insert($linesJE);
                }

                // 5) Transactions مبسطة
                $makeTxn = function($mainAcc, $pairAcc, $amount, $isDebitOnMain, $desc, $type, $customerId, $orderId) {
                    if (!$mainAcc || $amount <= 0) return null;
                    $t = new Transection;
                    $t->tran_type      = $type;
                    $t->seller_id      = auth('admin')->id() ?? 1;
                    $t->branch_id      = $mainAcc->branch_id ?? 1;
                    $t->cost_id        = null;
                    $t->account_id     = $mainAcc->id;
                    $t->account_id_to  = $pairAcc ? $pairAcc->id : null;
                    $t->amount         = $amount;
                    $t->description    = $desc;
                    $t->debit          = $isDebitOnMain ? $amount : 0;
                    $t->credit         = $isDebitOnMain ? 0 : $amount;
                    $t->balance        = $isDebitOnMain ? ($mainAcc->balance + $amount) : ($mainAcc->balance - $amount);
                    $t->debit_account  = $isDebitOnMain ? $amount : 0;
                    $t->credit_account = $isDebitOnMain ? 0 : $amount;
                    $t->balance_account= $pairAcc ? ($isDebitOnMain ? ($pairAcc->balance - $amount) : ($pairAcc->balance + $amount)) : 0;
                    $t->date           = now()->format('Y/m/d');
                    $t->customer_id    = $customerId;
                    $t->order_id       = $orderId;
                    $t->save();
                    return $t;
                };

                if ($netWithoutTax > 0)   $makeTxn($cashAccount, $accSalesReturn, $netWithoutTax, false, "صرف نقدي مقابل مردودات خدمة (صافي) [هجرة]", $newOrder->type, $parentOrder->user_id, $newOrder->id);
                if ($totalReturnTax > 0)  $makeTxn($cashAccount, $accTax,         $totalReturnTax, false, "صرف نقدي مقابل ضريبة مرتجع خدمة [هجرة]",      $newOrder->type, $parentOrder->user_id, $newOrder->id);

                if ($cashAccount && $totalReturnOverall > 0) {
                    $cashAccount->balance   -= $totalReturnOverall;
                    $cashAccount->total_out = ($cashAccount->total_out ?? 0) + $totalReturnOverall;
                    $cashAccount->save();
                }

                // 6) تخزين تفاصيل المرتجع الجديد (خدمة)
                foreach ($lines as $l) {
                    OrderDetail::create([
                        'order_id'            => $newOrder->id,
                        'product_id'          => $l['product_id'],
                        'product_details'     => json_encode([
                            'price'          => $l['price'],
                            'discount'       => $l['discount'],
                            'extra_discount' => $l['extra_discount'],
                            'tax'            => $l['tax'],
                            'unit'           => $l['unit'],
                            'return_quantity'=> $l['quantity'],
                        ]),
                        'quantity'            => $l['quantity'],
                        'unit'                => $l['unit'],
                        'price'               => $l['price'],
                        'tax_amount'          => $l['tax'],
                        'discount_on_product' => $l['discount'],
                        'discount_type'       => 'discount_on_product',
                    ]);
                }

                // 7) تحديث quantity_returned في الفاتورة الأصلية بوحدات الأساس
                foreach ($lines as $l) {
                    $od = $parentOrder->details
                        ->where('product_id', $l['product_id'])
                        ->first();

                    if ($od) {
                        $det = json_decode($od->product_details);
                        $uv  = (float)($det->unit_value ?? 1);
                        $baseQty = ($l['unit'] === 0)
                            ? ((float)$l['quantity'] / ($uv ?: 1))
                            : (float)$l['quantity'];

                        $od->quantity_returned = ((float)($od->quantity_returned ?? 0)) + $baseQty;
                        $od->save();
                    }
                }

                DB::commit();

                $this->info("✔ تم إنشاء مرتجع خدمة جديد #{$newOrder->id} بديلًا عن المرتجع القديم #{$oldReturnId}.");

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("❌ خطأ أثناء معالجة المرتجع #{$oldReturn->id}: " . $e->getMessage());
            }
        }

        $this->info('انتهت عملية الهجرة لكل فواتير type=24 المستهدفة.');
        return Command::SUCCESS;
    }
}
