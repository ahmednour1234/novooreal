<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ZatcaEgsUnit;
use App\Models\CompanySetting;
use App\Services\ZatcaKeyService;
use Illuminate\Support\Facades\Storage;

class ZatcaCsrCommand extends Command
{
    protected $signature = 'zatca:csr {--egs= : EGS Unit ID} {--env=simulation : Environment}';
    protected $description = 'Generate CSR for an EGS unit';

    protected ZatcaKeyService $keyService;

    public function __construct(ZatcaKeyService $keyService)
    {
        parent::__construct();
        $this->keyService = $keyService;
    }

    public function handle(): int
    {
        $egsId = $this->option('egs');
        if (!$egsId) {
            $egsId = $this->ask('Enter EGS Unit ID');
        }

        $egsUnit = ZatcaEgsUnit::where('egs_id', $egsId)->first();
        if (!$egsUnit) {
            $this->error("EGS unit {$egsId} not found");
            return 1;
        }

        $companySettings = CompanySetting::getSettings();
        if (!$companySettings) {
            $this->error('Company settings not found. Run zatca:setup first.');
            return 1;
        }

        if (!$egsUnit->private_key_path) {
            $this->info("Generating key pair for {$egsId}...");
            $keys = $this->keyService->generateKeyPair($egsId);
            $egsUnit->private_key_path = $keys['private_key_path'];
            $egsUnit->public_key_path = $keys['public_key_path'];
            $egsUnit->save();
        }

        $this->info("Generating CSR for {$egsId}...");

        try {
            $companyInfo = [
                'company_name_en' => $companySettings->company_name_en,
            ];
            $csr = $this->keyService->generateCSR($egsUnit, $companyInfo);

            $disk = config('zatca.storage.disk', 'local');
            $certificatesPath = config('zatca.storage.certificates_path', 'zatca/certificates');
            $csrPath = $certificatesPath . '/' . $egsUnit->egs_id . '/csr.pem';
            Storage::disk($disk)->put($csrPath, $csr);
            $egsUnit->csr_path = $csrPath;
            $egsUnit->save();

            $this->info("CSR generated successfully!");
            $this->line("CSR Path: {$csrPath}");
            $this->newLine();
            $this->line("CSR Content:");
            $this->line($csr);

            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to generate CSR: " . $e->getMessage());
            return 1;
        }
    }
}
