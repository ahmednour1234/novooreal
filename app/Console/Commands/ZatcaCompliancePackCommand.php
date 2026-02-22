<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ZatcaEgsUnit;
use App\Models\CompanySetting;
use App\Services\ZatcaApiService;

class ZatcaCompliancePackCommand extends Command
{
    protected $signature = 'zatca:compliance-pack {--egs= : EGS Unit ID} {--from= : Start date (YYYY-MM-DD)} {--to= : End date (YYYY-MM-DD)}';
    protected $description = 'Generate compliance pack for date range';

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

        $fromDate = $this->option('from');
        if (!$fromDate) {
            $fromDate = $this->ask('Start date (YYYY-MM-DD)', date('Y-m-d', strtotime('-30 days')));
        }

        $toDate = $this->option('to');
        if (!$toDate) {
            $toDate = $this->ask('End date (YYYY-MM-DD)', date('Y-m-d'));
        }

        $companySettings = CompanySetting::getSettings();
        if (!$companySettings) {
            $this->error('Company settings not found. Run zatca:setup first.');
            return 1;
        }

        $environment = $companySettings->environment;

        $this->info("Generating compliance pack for {$egsId} from {$fromDate} to {$toDate}...");

        try {
            $result = $this->apiService->generateCompliancePack($egsUnit, $fromDate, $toDate, $environment);

            $this->info("Compliance pack generated successfully!");
            $this->line("Result: " . json_encode($result, JSON_PRETTY_PRINT));

            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to generate compliance pack: " . $e->getMessage());
            return 1;
        }
    }
}
