<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\ZatcaEgsUnit;
use App\Services\Zatca\ZatcaOrderXmlBuilder;
use App\Services\Zatca\ZatcaSubmitService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TestZatcaSubmissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Order $order;
    public ZatcaEgsUnit $egsUnit;
    public string $environment;
    public int $tries = 1;
    public string $jobId;

    public function __construct(Order $order, ZatcaEgsUnit $egsUnit, string $environment = 'simulation')
    {
        $this->order = $order;
        $this->egsUnit = $egsUnit;
        $this->environment = $environment;
        $this->jobId = uniqid('test_', true);
        $this->onQueue(config('zatca.queue.queue', 'zatca'));
    }

    public function handle(ZatcaOrderXmlBuilder $xmlBuilder, ZatcaSubmitService $submitService): void
    {
        $this->updateStatus('processing', 'Building invoice XML...');

        try {
            // Build XML
            $invoiceType = $xmlBuilder->getInvoiceType($this->order);
            $xml = $xmlBuilder->buildXml($this->order, $this->egsUnit, $invoiceType);
            
            $this->updateStatus('processing', 'Submitting to ZATCA...');

            // Submit invoice
            $result = $submitService->submitInvoice($this->order, $this->egsUnit, $xml, $this->environment);

            if ($result['success']) {
                $this->updateStatus('completed', 'Invoice submitted successfully', [
                    'order_id' => $this->order->id,
                    'zatca_uuid' => $result['zatca_document']->zatca_uuid ?? null,
                    'zatca_long_id' => $result['zatca_document']->zatca_long_id ?? null,
                ]);
            } else {
                $this->updateStatus('failed', $result['error'] ?? 'Submission failed', [
                    'order_id' => $this->order->id,
                    'error' => $result['error'],
                ]);
            }
        } catch (\Exception $e) {
            Log::channel('zatca')->error('Test submission job failed', [
                'order_id' => $this->order->id,
                'egs_unit_id' => $this->egsUnit->id,
                'error' => $e->getMessage(),
            ]);

            $this->updateStatus('failed', $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->updateStatus('failed', $exception->getMessage());
    }

    protected function updateStatus(string $status, string $message, array $data = []): void
    {
        Cache::put("zatca_job_{$this->jobId}", [
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'updated_at' => now()->toIso8601String(),
        ], now()->addHours(1));
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }
}
