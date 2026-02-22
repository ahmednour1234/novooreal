<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\ZatcaEgsUnit;
use App\Models\ZatcaDocument;
use App\Models\CompanySetting;
use App\Services\ZATCAService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SubmitZatcaInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Order $order;
    public ZatcaEgsUnit $egsUnit;
    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(Order $order, ZatcaEgsUnit $egsUnit)
    {
        $this->order = $order;
        $this->egsUnit = $egsUnit;
        $this->onQueue(config('zatca.queue.queue', 'zatca'));
    }

    public function handle(ZATCAService $zatcaService): void
    {
        try {
            $companySettings = CompanySetting::getSettings();
            if (!$companySettings) {
                Log::error('ZATCA: Company settings not found', ['order_id' => $this->order->id]);
                return;
            }

            $environment = $companySettings->environment;

            $result = $zatcaService->submitInvoice($this->order, $this->egsUnit, $environment);

            if (!$result['success']) {
                Log::error('ZATCA: Invoice submission failed', [
                    'order_id' => $this->order->id,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);

                if ($this->attempts() < $this->tries) {
                    $this->release($this->backoff * $this->attempts());
                }
            }
        } catch (\Exception $e) {
            Log::error('ZATCA: Invoice submission job failed', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $zatcaDocument = ZatcaDocument::where('order_id', $this->order->id)->first();
            if ($zatcaDocument) {
                $zatcaDocument->submission_status = 'failed';
                $zatcaDocument->error_message = $e->getMessage();
                $zatcaDocument->retry_count = ($zatcaDocument->retry_count ?? 0) + 1;
                $zatcaDocument->save();
            }

            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff * $this->attempts());
            } else {
                $this->fail($e);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ZATCA: Invoice submission job permanently failed', [
            'order_id' => $this->order->id,
            'error' => $exception->getMessage(),
        ]);

        $zatcaDocument = ZatcaDocument::where('order_id', $this->order->id)->first();
        if ($zatcaDocument) {
            $zatcaDocument->submission_status = 'failed';
            $zatcaDocument->error_message = $exception->getMessage();
            $zatcaDocument->save();
        }
    }
}
