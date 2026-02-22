<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ZatcaEgsUnit;
use App\Services\ZatcaApiService;
use Illuminate\Support\Facades\Storage;

class ZatcaOnboardCommand extends Command
{
    protected $signature = 'zatca:onboard {--egs= : EGS Unit ID} {--env=simulation : Environment} {--otp= : OTP from ZATCA portal}';
    protected $description = 'Onboard an EGS unit with ZATCA using OTP';

    protected ZatcaApiService $apiService;

    public function __construct(ZatcaApiService $apiService)
    {
        parent::__construct();
        $this->apiService = $apiService;
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

        if (!$egsUnit->csr_path) {
            $this->error("CSR not found for {$egsId}. Run zatca:csr first.");
            return 1;
        }

        $environment = $this->option('env');
        $otp = $this->option('otp');
        if (!$otp) {
            $otp = $this->secret("Enter OTP from ZATCA portal for {$egsId}");
        }

        $disk = config('zatca.storage.disk', 'local');
        $csrContent = Storage::disk($disk)->get($egsUnit->csr_path);

        $this->info("Requesting CSID for {$egsId}...");

        try {
            $result = $this->apiService->requestCSID($egsUnit, $csrContent, $otp, $environment);

            if ($environment === 'simulation') {
                $egsUnit->compliance_csid = $result['requestedSecurityToken'] ?? null;
            } else {
                $egsUnit->production_csid = $result['requestedSecurityToken'] ?? null;
            }

            $egsUnit->status = 'active';
            $egsUnit->onboarded_at = now();
            $egsUnit->save();

            $this->info("Successfully onboarded {$egsId}!");
            $this->line("CSID received and stored.");

            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to onboard {$egsId}: " . $e->getMessage());
            return 1;
        }
    }
}
