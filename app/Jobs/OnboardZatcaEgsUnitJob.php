<?php

namespace App\Jobs;

use App\Models\ZatcaEgsUnit;
use App\Services\Zatca\ZatcaOnboardingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OnboardZatcaEgsUnitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ZatcaEgsUnit $egsUnit;
    public string $otp;
    public string $environment;
    public int $tries = 1;
    public string $jobId;

    public function __construct(ZatcaEgsUnit $egsUnit, string $otp, string $environment = 'simulation')
    {
        $this->egsUnit = $egsUnit;
        $this->otp = $otp; // Used immediately, never stored after job completes
        $this->environment = $environment;
        $this->jobId = uniqid('onboard_', true);
        $this->onQueue(config('zatca.queue.queue', 'zatca'));
    }

    public function handle(ZatcaOnboardingService $onboardingService): void
    {
        $this->updateStatus('processing', 'Requesting CSID from ZATCA...');

        try {
            $result = $onboardingService->onboard($this->egsUnit, $this->otp, $this->environment);
            
            // Clear OTP from memory (already used)
            $this->otp = '';
            
            $this->updateStatus('completed', 'EGS unit onboarded successfully', [
                'egs_unit_id' => $this->egsUnit->id,
                'environment' => $this->environment,
            ]);
        } catch (\Exception $e) {
            Log::channel('zatca')->error('Onboarding job failed', [
                'egs_unit_id' => $this->egsUnit->id,
                'error' => $e->getMessage(),
            ]);

            // Clear OTP from memory
            $this->otp = '';
            
            $this->updateStatus('failed', $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        // Clear OTP from memory
        $this->otp = '';
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
