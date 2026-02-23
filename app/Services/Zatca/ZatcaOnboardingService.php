<?php

namespace App\Services\Zatca;

use App\Models\ZatcaEgsUnit;
use App\Models\CompanySetting;
use App\Services\ZatcaApiService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ZatcaOnboardingService
{
    protected ZatcaApiService $apiService;

    public function __construct(ZatcaApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Onboard EGS unit with OTP (simulation or production)
     * 
     * @param ZatcaEgsUnit $egsUnit
     * @param string $otp OTP from ZATCA portal (never stored)
     * @param string $environment 'simulation' or 'production'
     * @return array
     */
    public function onboard(ZatcaEgsUnit $egsUnit, string $otp, string $environment = 'simulation'): array
    {
        if (!$egsUnit->csr_path) {
            throw new \RuntimeException('CSR not found. Please generate CSR first.');
        }

        $companySettings = CompanySetting::getSettings();
        if (!$companySettings) {
            throw new \RuntimeException('Company settings not found.');
        }

        // Get CSR content
        $disk = config('zatca.storage.disk', 'local');
        $csrContent = Storage::disk($disk)->get($egsUnit->csr_path);

        Log::channel('zatca')->info('Requesting CSID for EGS unit', [
            'egs_unit_id' => $egsUnit->id,
            'egs_id' => $egsUnit->egs_id,
            'environment' => $environment,
        ]);

        try {
            // Request CSID from ZATCA API
            $result = $this->apiService->requestCSID($egsUnit, $csrContent, $otp, $environment);

            // Extract CSID from response
            $csid = $result['requestedSecurityToken'] ?? $result['securityToken'] ?? null;
            
            if (!$csid) {
                throw new \RuntimeException('CSID not found in response: ' . json_encode($result));
            }

            // Store CSID encrypted (handled by model mutator)
            if ($environment === 'production') {
                $egsUnit->production_csid = $csid;
            } else {
                $egsUnit->compliance_csid = $csid;
            }

            $egsUnit->status = 'active';
            $egsUnit->onboarded_at = now();
            $egsUnit->save();

            Log::channel('zatca')->info('EGS unit onboarded successfully', [
                'egs_unit_id' => $egsUnit->id,
                'egs_id' => $egsUnit->egs_id,
                'environment' => $environment,
            ]);

            return [
                'success' => true,
                'message' => 'EGS unit onboarded successfully',
                'egs_unit' => $egsUnit->fresh(),
            ];
        } catch (\Exception $e) {
            Log::channel('zatca')->error('Onboarding failed', [
                'egs_unit_id' => $egsUnit->id,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Onboarding failed: ' . $e->getMessage());
        }
    }

    /**
     * Check if EGS unit is onboarded for given environment
     */
    public function isOnboarded(ZatcaEgsUnit $egsUnit, string $environment = 'simulation'): bool
    {
        if ($environment === 'production') {
            return !empty($egsUnit->production_csid);
        }
        return !empty($egsUnit->compliance_csid);
    }
}
