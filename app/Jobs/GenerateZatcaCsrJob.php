<?php

namespace App\Jobs;

use App\Models\ZatcaEgsUnit;
use App\Services\Zatca\CsrService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateZatcaCsrJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ZatcaEgsUnit $egsUnit;
    public int $tries = 1;
    public string $jobId;

    public function __construct(ZatcaEgsUnit $egsUnit)
    {
        $this->egsUnit = $egsUnit;
        $this->jobId = uniqid('csr_', true);
        $this->onQueue(config('zatca.queue.queue', 'zatca'));
    }

    public function handle(CsrService $csrService): void
    {
        $this->updateStatus('processing', 'Generating CSR...');

        try {
            $result = $csrService->generateCsr($this->egsUnit);
            
            $this->updateStatus('completed', 'CSR generated successfully', [
                'csr_path' => $result['csr_path'],
            ]);
        } catch (\Exception $e) {
            Log::channel('zatca')->error('CSR generation job failed', [
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
