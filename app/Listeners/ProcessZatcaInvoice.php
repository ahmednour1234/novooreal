<?php

namespace App\Listeners;

use App\Events\InvoiceFinalized;
use App\Jobs\SubmitZatcaInvoiceJob;
use App\Models\CompanySetting;
use App\Models\ZatcaEgsUnit;
use Illuminate\Support\Facades\Log;

class ProcessZatcaInvoice
{
    public function handle(InvoiceFinalized $event): void
    {
        $order = $event->order;

        $companySettings = CompanySetting::getSettings();
        if (!$companySettings) {
            return;
        }

        // Only submit in production environment
        // In simulation, only submit via manual "Run Auto Test" button
        if ($companySettings->environment !== 'production') {
            Log::info('ZATCA: Skipping automatic submission in simulation mode', [
                'order_id' => $order->id,
                'environment' => $companySettings->environment,
            ]);
            return;
        }

        $invoiceTypes = config('zatca.invoice_types', []);
        $standardTypes = $invoiceTypes['standard'] ?? [4, 12];
        $simplifiedTypes = $invoiceTypes['simplified'] ?? [1];

        if (!in_array($order->type, array_merge($standardTypes, $simplifiedTypes))) {
            return;
        }

        $egsUnit = $this->getEgsUnitForOrder($order);
        if (!$egsUnit || !$egsUnit->isOnboarded()) {
            Log::warning('ZATCA: EGS unit not found or not onboarded for order', [
                'order_id' => $order->id,
                'egs_unit_id' => $egsUnit?->id,
            ]);
            return;
        }

        SubmitZatcaInvoiceJob::dispatch($order, $egsUnit)
            ->onQueue(config('zatca.queue.queue', 'zatca'));
    }

    protected function getEgsUnitForOrder($order): ?ZatcaEgsUnit
    {
        if ($order->branch_id) {
            $egsUnit = ZatcaEgsUnit::where('branch_id', $order->branch_id)
                ->where('status', 'active')
                ->first();

            if ($egsUnit) {
                return $egsUnit;
            }
        }

        return ZatcaEgsUnit::where('status', 'active')->first();
    }
}
