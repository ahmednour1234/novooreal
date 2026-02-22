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

class RollbackType24Returns extends Command
{
    /**
     * اسم الكومانده في أرتيزان
     */
    protected $signature = 'orders:rollback-returns-24 {--dry-run : فقط عرض بدون تنفيذ فعلي}';

    protected $description = 'Rollback all purchase return orders (type=24) and reverse their stock & accounting effects';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        // هجيب كل أوامر المرتجع type = 24
        $orders = Order::with(['details'])
            ->where('type', 24)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('لا توجد فواتير مرتجع من نوع 24.');
            return Command::SUCCESS;
        }

        $this->info('تم العثور على ' . $orders->count() . ' فاتورة مرتجع (type=24).');

        foreach ($orders as $order) {
            $this->line('---------------------------------------------');
            $this->info('معالجة المرتجع رقم: ' . $order->id);

            if ($dryRun) {
                $this->comment('DRY RUN: لن يتم أي تعديل فعلي في قاعدة البيانات لهذا الطلب.');
                continue;
            }

            DB::beginTransaction();
            try {
                $branchId     = $order->branch_id;
                $supplierId   = $order->supplier_id;
                $parentId     = $order->parent_id;   // الفاتورة الأصلية
                $amount       = (float) $order->order_amount;
                $totalTax     = (float) $order->total_tax;
                $branchColumn = 'branch_' . $branchId;

                // -----------------------------------------
                // 1) إعادة كميات المرتجع إلى الفاتورة الأصلية
                //    وإنقاص quantity_returned
                // -----------------------------------------
                $parentOrder = null;
                if ($parentId) {
                    $parentOrder = Order::with('details')->find($parentId);
                }

                foreach ($order->details as $detail) {

                    $productId   = $detail->product_id;
                    $qtyInReturn = (float) $detail->quantity;
                    $unit        = (int) $detail->unit;   // نفس اللي سجلته في المرتجع

                    // هجيب المنتج عشان أطلع unit_value من product_details
                    $product = Product::find($productId);
                    if (!$product) {
                        $this->warn("⚠️ المنتج ID={$productId} غير موجود, تخطّي هذا السطر.");
                        continue;
                    }

                    $productDetails = json_decode($product->product_details);
                    $unitValue      = isset($productDetails->unit_value) ? (float) $productDetails->unit_value : 1;

                    // نفس المنطق اللي كنت بتستخدمه في المرتجع الأصلي
                    $finalQuantity = ($unit == 0)
                        ? ($qtyInReturn / ($unitValue ?: 1))
                        : $qtyInReturn;

                    // 1-A) تعديل الفاتورة الأصلية: تقليل quantity_returned
                    if ($parentOrder) {
                        $parentDetail = $parentOrder->details
                            ->where('product_id', $productId)
                            ->first();

                        if ($parentDetail) {
                            $oldReturned = (float) ($parentDetail->quantity_returned ?? 0);
                            $newReturned = $oldReturned - $finalQuantity;
                            if ($newReturned < 0) {
                                $newReturned = 0; // بس أمان
                            }
                            $parentDetail->quantity_returned = $newReturned;
                            $parentDetail->save();
                        }
                    }

                    // 1-B) إعادة الكمية للمخزون (المنتج)
                    if (isset($product->$branchColumn)) {
                        $product->$branchColumn += $finalQuantity;
                    } else {
                        $product->quantity += $finalQuantity;
                    }

                    // تقليل عداد الـ repurchase_count لو أنت مستخدمه كعدد مرات المرتجع
                    if (! is_null($product->repurchase_count) && $product->repurchase_count > 0) {
                        $product->repurchase_count -= 1;
                    }

                    $product->save();

                    // 1-C) تعديل مخزون الدُفعات StockBatch (هنعيد الكمية ببساطة لأول دفعة/دفعات للفرع)
                    $stockBatches = StockBatch::where('product_id', $productId)
                        ->where('branch_id', $branchId)
                        ->get();

                    $remain = $finalQuantity;
                    foreach ($stockBatches as $batch) {
                        if ($remain <= 0) {
                            break;
                        }

                        // هنا هنجمع كل الكمية في أول دفعات (ببساطة)
                        if (isset($batch->$branchColumn)) {
                            $batch->$branchColumn += $remain;
                        } else {
                            $batch->quantity += $remain;
                        }

                        $batch->save();
                        $remain = 0; // وزعناها كلها على أول دفعة, لو حابب تقسمها على أكثر من دفعة عدّل المنطق
                    }

                    $this->info("✔ تم إعادة كمية المنتج #{$productId} = {$finalQuantity} للمخزون + تعديل الفاتورة الأصلية.");
                }

                // -----------------------------------------
                // 2) تعديل الحسابات (المورد + المخزون)
                //    عكس اللي حصل في المرتجع:
                //    في المرتجع:
                //      - inventoryAccount->balance -= amount
                //      - supplierAccount->balance -= amount
                //    هنا هنعمل العكس:
                //      +inventoryAccount->balance += amount
                //      +supplierAccount->balance += amount
                // -----------------------------------------

                $branch   = Branch::find($branchId);
                $supplier = Supplier::find($supplierId);

                if ($branch && $supplier) {
                    $inventoryAccount = Account::find($branch->account_stock_Id);
                    $supplierAccount  = Account::find($supplier->account_id);

                    if ($inventoryAccount && $amount > 0) {
                        $inventoryAccount->balance   += $amount;
                        // لو كنت مستخدم total_out في المرتجع هتنقصه هنا
                        if (! is_null($inventoryAccount->total_out) && $inventoryAccount->total_out >= $amount) {
                            $inventoryAccount->total_out -= $amount;
                        }
                        $inventoryAccount->save();
                        $this->info("✔ تم تعديل حساب المخزون (ID={$inventoryAccount->id}) بمقدار +{$amount}");
                    }

                    if ($supplierAccount && $amount > 0) {
                        $supplierAccount->balance   += $amount;
                        if (! is_null($supplierAccount->total_out) && $supplierAccount->total_out >= $amount) {
                            $supplierAccount->total_out -= $amount;
                        }
                        $supplierAccount->save();
                        $this->info("✔ تم تعديل حساب المورد (ID={$supplierAccount->id}) بمقدار +{$amount}");
                    }
                } else {
                    $this->warn('⚠️ لم يتم العثور على الفرع أو المورد لهذا الطلب، تخطي تعديل الحسابات لهذا الطلب.');
                }

                // -----------------------------------------
                // 3) حذف قيود اليومية الخاصة بالمرتجع
                //    كنا عاملين reference = PR-{$order->id}
                // -----------------------------------------
                $journal = JournalEntry::where('reference', 'PR-' . $order->id)->first();

                if ($journal) {
                    JournalEntryDetail::where('journal_entry_id', $journal->id)->delete();
                    $journal->delete();
                    $this->info("✔ تم حذف قيد اليومية (JournalEntry) الخاص بالمرتجع رقم {$order->id}");
                }

                // -----------------------------------------
                // 4) حذف معاملات الترانزاكشن المرتبطة بالمرتجع
                // -----------------------------------------
                Transection::where('order_id', $order->id)->delete();
                $this->info('✔ تم حذف معاملات Transection المرتبطة بالمرتجع.');

                // -----------------------------------------
                // 5) حذف تفاصيل المرتجع ثم حذف أمر المرتجع نفسه
                // -----------------------------------------
                OrderDetail::where('order_id', $order->id)->delete();
                $order->delete();

                $this->info('✔ تم حذف أمر المرتجع بالكامل وإرجاع النظام لوضعه السابق لهذا الأمر.');

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("❌ خطأ أثناء معالجة الطلب رقم {$order->id}: " . $e->getMessage());
            }
        }

        $this->info('انتهت عملية Rollback لكل فواتير type=24.');
        return Command::SUCCESS;
    }
}
