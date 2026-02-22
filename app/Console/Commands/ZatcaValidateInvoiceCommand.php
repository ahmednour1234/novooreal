<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\ZATCAService;

class ZatcaValidateInvoiceCommand extends Command
{
    protected $signature = 'zatca:validate-invoice {--invoice_id= : Order/Invoice ID}';
    protected $description = 'Validate an invoice against ZATCA rules';

    protected ZATCAService $zatcaService;

    public function __construct(ZATCAService $zatcaService)
    {
        parent::__construct();
        $this->zatcaService = $zatcaService;
    }

    public function handle(): int
    {
        $invoiceId = $this->option('invoice_id');
        if (!$invoiceId) {
            $invoiceId = $this->ask('Enter Invoice/Order ID');
        }

        $order = Order::find($invoiceId);
        if (!$order) {
            $this->error("Invoice {$invoiceId} not found");
            return 1;
        }

        $this->info("Validating invoice {$invoiceId}...");

        $validation = $this->zatcaService->validateInvoice($order);

        if ($validation['valid']) {
            $this->info("Invoice is valid!");
            return 0;
        } else {
            $this->error("Invoice validation failed:");
            foreach ($validation['errors'] as $error) {
                $this->line("  - {$error}");
            }
            return 1;
        }
    }
}
