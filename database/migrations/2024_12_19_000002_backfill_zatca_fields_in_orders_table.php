<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\ZATCAService;

class BackfillZatcaFieldsInOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get all orders without UUIDs, grouped by company_id for proper counter assignment
        $companies = DB::table('orders')
            ->select('company_id')
            ->distinct()
            ->get();

        foreach ($companies as $company) {
            $companyId = $company->company_id;
            
            // Get orders for this company ordered by created_at
            $orders = DB::table('orders')
                ->where(function($query) use ($companyId) {
                    if ($companyId) {
                        $query->where('company_id', $companyId);
                    } else {
                        $query->whereNull('company_id');
                    }
                })
                ->whereNull('uuid')
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $counter = 1;
            $previousHash = null;

            foreach ($orders as $order) {
                // Generate UUID if not exists
                $uuid = (string) Str::uuid();
                
                // Generate invoice number
                $invoiceNumber = ZATCAService::generateInvoiceNumber($counter);
                
                // Set currency code to SAR if empty
                $currencyCode = $order->currency_code ?? 'SAR';
                
                // Update the order
                DB::table('orders')
                    ->where('id', $order->id)
                    ->update([
                        'uuid' => $uuid,
                        'invoice_number' => $invoiceNumber,
                        'invoice_counter' => $counter,
                        'previous_invoice_hash' => $previousHash,
                        'currency_code' => $currencyCode,
                        'zatca_submitted' => false,
                        'updated_at' => now(),
                    ]);

                // Calculate hash for next iteration
                $invoiceData = [
                    'uuid' => $uuid,
                    'invoice_number' => $invoiceNumber,
                    'invoice_counter' => $counter,
                    'order_amount' => $order->order_amount ?? 0,
                    'total_tax' => $order->total_tax ?? 0,
                    'created_at' => $order->created_at,
                ];
                $previousHash = ZATCAService::calculateHash($invoiceData);
                
                $counter++;
            }
        }

        // Also handle orders that might not have company_id set
        $ordersWithoutCompany = DB::table('orders')
            ->whereNull('company_id')
            ->whereNull('uuid')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($ordersWithoutCompany->count() > 0) {
            $counter = DB::table('orders')
                ->whereNotNull('invoice_counter')
                ->max('invoice_counter') ?? 0;
            $counter++;
            
            $previousHash = DB::table('orders')
                ->whereNotNull('previous_invoice_hash')
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->value('previous_invoice_hash');

            foreach ($ordersWithoutCompany as $order) {
                $uuid = (string) Str::uuid();
                $invoiceNumber = ZATCAService::generateInvoiceNumber($counter);
                
                DB::table('orders')
                    ->where('id', $order->id)
                    ->update([
                        'uuid' => $uuid,
                        'invoice_number' => $invoiceNumber,
                        'invoice_counter' => $counter,
                        'previous_invoice_hash' => $previousHash,
                        'currency_code' => 'SAR',
                        'zatca_submitted' => false,
                        'updated_at' => now(),
                    ]);

                $invoiceData = [
                    'uuid' => $uuid,
                    'invoice_number' => $invoiceNumber,
                    'invoice_counter' => $counter,
                    'order_amount' => $order->order_amount ?? 0,
                    'total_tax' => $order->total_tax ?? 0,
                    'created_at' => $order->created_at,
                ];
                $previousHash = ZATCAService::calculateHash($invoiceData);
                
                $counter++;
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Optionally clear ZATCA fields (but keep UUIDs for data integrity)
        DB::table('orders')->update([
            'invoice_number' => null,
            'invoice_counter' => null,
            'previous_invoice_hash' => null,
            'zatca_submitted' => false,
            'zatca_submitted_at' => null,
            'zatca_qr_code' => null,
        ]);
    }
}
