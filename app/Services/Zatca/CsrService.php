<?php

namespace App\Services\Zatca;

use App\Models\ZatcaEgsUnit;
use App\Models\CompanySetting;
use App\Services\ZatcaKeyService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CsrService
{
    protected ZatcaKeyService $keyService;

    public function __construct(ZatcaKeyService $keyService)
    {
        $this->keyService = $keyService;
    }

    /**
     * Generate key pair and CSR for an EGS unit
     */
    public function generateCsr(ZatcaEgsUnit $egsUnit): array
    {
        $companySettings = CompanySetting::getSettings();
        if (!$companySettings) {
            throw new \RuntimeException('Company settings not found. Please configure company information first.');
        }

        // Generate key pair if not exists
        if (!$egsUnit->private_key_path) {
            Log::channel('zatca')->info('Generating key pair for EGS unit', [
                'egs_unit_id' => $egsUnit->id,
                'egs_id' => $egsUnit->egs_id,
            ]);

            $keys = $this->keyService->generateKeyPair($egsUnit->egs_id);
            $egsUnit->private_key_path = $keys['private_key_path'];
            $egsUnit->public_key_path = $keys['public_key_path'];
            $egsUnit->save();
        }

        // Generate CSR
        Log::channel('zatca')->info('Generating CSR for EGS unit', [
            'egs_unit_id' => $egsUnit->id,
            'egs_id' => $egsUnit->egs_id,
        ]);

        $companyInfo = [
            'company_name_en' => $companySettings->company_name_en,
        ];

        $csr = $this->keyService->generateCSR($egsUnit, $companyInfo);

        // Store CSR path
        $disk = config('zatca.storage.disk', 'local');
        $certificatesPath = config('zatca.storage.certificates_path', 'zatca/certificates');
        $csrPath = $certificatesPath . '/' . $egsUnit->egs_id . '/csr.pem';
        
        Storage::disk($disk)->put($csrPath, $csr);
        $egsUnit->csr_path = $csrPath;
        $egsUnit->save();

        Log::channel('zatca')->info('CSR generated successfully', [
            'egs_unit_id' => $egsUnit->id,
            'csr_path' => $csrPath,
        ]);

        return [
            'success' => true,
            'csr' => $csr,
            'csr_path' => $csrPath,
            'egs_unit' => $egsUnit->fresh(),
        ];
    }

    /**
     * Get CSR content for an EGS unit
     */
    public function getCsrContent(ZatcaEgsUnit $egsUnit): ?string
    {
        if (!$egsUnit->csr_path) {
            return null;
        }

        $disk = config('zatca.storage.disk', 'local');
        return Storage::disk($disk)->get($egsUnit->csr_path);
    }
}
