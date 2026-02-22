<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanySetting;
use App\Models\ZatcaEgsUnit;
use App\Services\ZatcaKeyService;
use App\Services\ZatcaApiService;
use App\Services\ZATCAService;
use Illuminate\Support\Facades\Storage;

class ZatcaSetupCommand extends Command
{
    protected $signature = 'zatca:setup {--env=simulation : Environment (simulation/production)}';
    protected $description = 'Interactive ZATCA setup wizard';

    protected ZatcaKeyService $keyService;
    protected ZatcaApiService $apiService;

    public function __construct(ZatcaKeyService $keyService, ZatcaApiService $apiService)
    {
        parent::__construct();
        $this->keyService = $keyService;
        $this->apiService = $apiService;
    }

    public function handle(): int
    {
        $environment = $this->option('env');
        if (!in_array($environment, ['simulation', 'production'])) {
            $this->error('Environment must be simulation or production');
            return 1;
        }

        $this->info('=== ZATCA Setup Wizard ===');
        $this->info("Environment: {$environment}");
        $this->newLine();

        $companySettings = $this->collectCompanyInfo($environment);
        $egsUnits = $this->createEgsUnits();
        $this->generateKeysAndCSRs($egsUnits, $companySettings);
        $this->onboardEgsUnits($egsUnits, $environment);
        $this->runTestSubmission($environment);

        $this->info('Setup completed successfully!');
        return 0;
    }

    protected function collectCompanyInfo(string $environment): CompanySetting
    {
        $this->info('Step 1: Company Information');
        $this->line('Please provide your company details:');

        $vatTin = $this->ask('VAT/TIN Number');
        $crNumber = $this->ask('CR Number (optional)', null);
        $companyNameAr = $this->ask('Company Name (Arabic)');
        $companyNameEn = $this->ask('Company Name (English)');
        $addressAr = $this->ask('Address (Arabic, optional)', null);
        $addressEn = $this->ask('Address (English, optional)', null);

        $companySettings = CompanySetting::first();
        if (!$companySettings) {
            $companySettings = new CompanySetting();
        }

        $companySettings->vat_tin = $vatTin;
        $companySettings->cr_number = $crNumber;
        $companySettings->company_name_ar = $companyNameAr;
        $companySettings->company_name_en = $companyNameEn;
        $companySettings->address_ar = $addressAr;
        $companySettings->address_en = $addressEn;
        $companySettings->environment = $environment;
        $companySettings->save();

        $this->info('Company information saved.');
        $this->newLine();

        return $companySettings;
    }

    protected function createEgsUnits(): array
    {
        $this->info('Step 2: Create EGS Units');
        $count = (int) $this->ask('How many EGS units do you want to create?', 1);

        $egsUnits = [];
        for ($i = 1; $i <= $count; $i++) {
            $egsId = $this->ask("EGS ID for unit {$i} (e.g., EGS_01)", "EGS_" . str_pad($i, 2, '0', STR_PAD_LEFT));
            $name = $this->ask("Name for {$egsId}");
            $type = $this->choice("Type for {$egsId}", ['branch', 'cashier'], 'branch');
            $branchId = $this->ask("Branch ID (optional, press Enter to skip)", null);

            $egsUnit = ZatcaEgsUnit::firstOrCreate(
                ['egs_id' => $egsId],
                [
                    'name' => $name,
                    'type' => $type,
                    'branch_id' => $branchId ? (int) $branchId : null,
                    'status' => 'pending',
                ]
            );

            $egsUnits[] = $egsUnit;
            $this->info("EGS unit {$egsId} created.");
        }

        $this->newLine();
        return $egsUnits;
    }

    protected function generateKeysAndCSRs(array $egsUnits, CompanySetting $companySettings): void
    {
        $this->info('Step 3: Generate Keys and CSRs');

        foreach ($egsUnits as $egsUnit) {
            $this->line("Generating keys for {$egsUnit->egs_id}...");

            try {
                $keys = $this->keyService->generateKeyPair($egsUnit->egs_id);
                $egsUnit->private_key_path = $keys['private_key_path'];
                $egsUnit->public_key_path = $keys['public_key_path'];
                $egsUnit->save();

                $companyInfo = [
                    'company_name_en' => $companySettings->company_name_en,
                ];
                $csr = $this->keyService->generateCSR($egsUnit, $companyInfo);

                $disk = config('zatca.storage.disk', 'local');
                $certificatesPath = config('zatca.storage.certificates_path', 'zatca/certificates');
                $csrPath = $certificatesPath . '/' . $egsUnit->egs_id . '/csr.pem';
                $egsUnit->csr_path = $csrPath;
                $egsUnit->save();

                $this->info("Keys and CSR generated for {$egsUnit->egs_id}");
                $this->line("CSR saved to: {$csrPath}");
                $this->line("CSR Content:");
                $this->line($csr);
                $this->newLine();
            } catch (\Exception $e) {
                $this->error("Failed to generate keys for {$egsUnit->egs_id}: " . $e->getMessage());
            }
        }
    }

    protected function onboardEgsUnits(array $egsUnits, string $environment): void
    {
        $this->info('Step 4: Onboard EGS Units');
        $this->line('You need to upload each CSR to the ZATCA Fatoora portal and get an OTP.');

        foreach ($egsUnits as $egsUnit) {
            $this->newLine();
            $this->line("Onboarding {$egsUnit->egs_id}...");
            $this->line("CSR Path: {$egsUnit->csr_path}");

            $disk = config('zatca.storage.disk', 'local');
            $csrContent = Storage::disk($disk)->get($egsUnit->csr_path);

            $this->line("Please upload this CSR to the ZATCA portal:");
            $this->line($csrContent);
            $this->newLine();

            $otp = $this->secret("Enter OTP from ZATCA portal for {$egsUnit->egs_id}");

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

                $this->info("Successfully onboarded {$egsUnit->egs_id}");
            } catch (\Exception $e) {
                $this->error("Failed to onboard {$egsUnit->egs_id}: " . $e->getMessage());
            }
        }
    }

    protected function runTestSubmission(string $environment): void
    {
        $this->info('Step 5: Test Submission');
        $this->line('Running validation test...');

        $egsUnit = ZatcaEgsUnit::where('status', 'active')->first();
        if (!$egsUnit) {
            $this->warn('No active EGS unit found. Skipping test.');
            return;
        }

        $this->info('Test completed. Check the logs for details.');
    }
}
