<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\ZatcaEgsUnit;
use App\Models\CompanySetting;
use App\Services\ZATCAService;

class ZatcaSubmitInvoiceCommand extends Command
{
    protected $signature = 'zatca:submit {--invoice_id= : Order/Invoice ID}';
    protected $description = 'Manually submit an invoice to ZATCA';

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

        $companySettings = CompanySetting::getSettings();
        if (!$companySettings) {
            $this->error('Company settings not found. Run zatca:setup first.');
            return 1;
        }

        $egsUnit = ZatcaEgsUnit::where('status', 'active')->first();
        if (!$egsUnit) {
            $this->error('No active EGS unit found. Run zatca:setup first.');
            return 1;
        }

        $environment = $companySettings->environment;

        $this->info("Submitting invoice {$invoiceId} to ZATCA ({$environment})...");

        try {
            $result = $this->zatcaService->submitInvoice($order, $egsUnit, $environment);

            if ($result['success']) {
                $this->info("Invoice submitted successfully!");
                $this->line("ZATCA UUID: " . ($result['data']['uuid'] ?? 'N/A'));
                return 0;
            } else {
                $this->error("Invoice submission failed: " . ($result['error'] ?? 'Unknown error'));
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Failed to submit invoice: " . $e->getMessage());
            return 1;
        }
    }
}
