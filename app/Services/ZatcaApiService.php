<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\ZatcaEgsUnit;
use App\Models\ZatcaAuditLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZatcaApiService
{
    protected function getBaseUrl(string $environment): string
    {
        $config = config("zatca.api.{$environment}");
        return $config['base_url'] ?? '';
    }

    protected function getEndpoint(string $environment, string $endpoint): string
    {
        $config = config("zatca.api.{$environment}");
        $baseUrl = $config['base_url'] ?? '';
        $path = $config[$endpoint] ?? '';

        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    public function requestCSID(ZatcaEgsUnit $egsUnit, string $csr, string $otp, string $environment = 'simulation'): array
    {
        $endpoint = $environment === 'production' 
            ? $this->getEndpoint($environment, 'production_csid_endpoint')
            : $this->getEndpoint($environment, 'compliance_csid_endpoint');

        $requestData = [
            'csr' => $csr,
            'otp' => $otp,
        ];

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, $requestData);

            $responseData = $response->json();
            $status = $response->successful() ? 'success' : 'failed';

            ZatcaAuditLog::log('csid_request', [
                'egs_unit_id' => $egsUnit->id,
                'request' => $this->maskSensitiveData($requestData),
                'response' => $this->maskSensitiveData($responseData ?? []),
                'status' => $status,
                'error' => $response->successful() ? null : ($responseData['message'] ?? 'Unknown error'),
            ]);

            if (!$response->successful()) {
                throw new \RuntimeException($responseData['message'] ?? 'Failed to request CSID');
            }

            return $responseData;
        } catch (\Exception $e) {
            Log::error('ZATCA CSID request failed', [
                'egs_unit_id' => $egsUnit->id,
                'error' => $e->getMessage(),
            ]);

            ZatcaAuditLog::log('csid_request', [
                'egs_unit_id' => $egsUnit->id,
                'request' => $this->maskSensitiveData($requestData),
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function validateInvoice(string $signedXml, string $environment = 'simulation'): array
    {
        $endpoint = $this->getEndpoint($environment, 'validate_endpoint');

        $requestData = [
            'invoice' => base64_encode($signedXml),
        ];

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, $requestData);

            $responseData = $response->json();

            return [
                'valid' => $response->successful(),
                'data' => $responseData,
            ];
        } catch (\Exception $e) {
            Log::error('ZATCA invoice validation failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'valid' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function submitInvoice(string $signedXml, ZatcaEgsUnit $egsUnit, string $environment = 'simulation'): array
    {
        $endpoint = $this->getEndpoint($environment, 'reporting_endpoint');

        $companySettings = CompanySetting::getSettings();
        if (!$companySettings) {
            throw new \RuntimeException('Company settings not found');
        }

        $csid = $egsUnit->getActiveCsid();
        if (!$csid) {
            throw new \RuntimeException('CSID not found for EGS unit');
        }

        $requestData = [
            'invoice' => base64_encode($signedXml),
            'csid' => $csid,
        ];

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, $requestData);

            $responseData = $response->json();
            $status = $response->successful() ? 'success' : 'failed';

            return [
                'success' => $response->successful(),
                'data' => $responseData,
                'status' => $status,
            ];
        } catch (\Exception $e) {
            Log::error('ZATCA invoice submission failed', [
                'egs_unit_id' => $egsUnit->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'failed',
            ];
        }
    }

    public function generateCompliancePack(ZatcaEgsUnit $egsUnit, string $fromDate, string $toDate, string $environment = 'simulation'): array
    {
        $endpoint = $this->getEndpoint($environment, 'compliance_pack_endpoint');

        $companySettings = CompanySetting::getSettings();
        if (!$companySettings) {
            throw new \RuntimeException('Company settings not found');
        }

        $csid = $egsUnit->getActiveCsid();
        if (!$csid) {
            throw new \RuntimeException('CSID not found for EGS unit');
        }

        $requestData = [
            'csid' => $csid,
            'from' => $fromDate,
            'to' => $toDate,
        ];

        try {
            $response = Http::timeout(120)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, $requestData);

            $responseData = $response->json();
            $status = $response->successful() ? 'success' : 'failed';

            ZatcaAuditLog::log('compliance_pack', [
                'egs_unit_id' => $egsUnit->id,
                'request' => $this->maskSensitiveData($requestData),
                'response' => $this->maskSensitiveData($responseData ?? []),
                'status' => $status,
                'error' => $response->successful() ? null : ($responseData['message'] ?? 'Unknown error'),
            ]);

            if (!$response->successful()) {
                throw new \RuntimeException($responseData['message'] ?? 'Failed to generate compliance pack');
            }

            return $responseData;
        } catch (\Exception $e) {
            Log::error('ZATCA compliance pack generation failed', [
                'egs_unit_id' => $egsUnit->id,
                'error' => $e->getMessage(),
            ]);

            ZatcaAuditLog::log('compliance_pack', [
                'egs_unit_id' => $egsUnit->id,
                'request' => $this->maskSensitiveData($requestData),
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function maskSensitiveData(array $data): array
    {
        if (!config('zatca.logging.mask_sensitive_data', true)) {
            return $data;
        }

        $sensitiveKeys = ['csid', 'otp', 'csr', 'private_key', 'signature'];
        $masked = $data;

        foreach ($sensitiveKeys as $key) {
            if (isset($masked[$key])) {
                $value = $masked[$key];
                if (is_string($value) && strlen($value) > 8) {
                    $masked[$key] = substr($value, 0, 4) . '***' . substr($value, -4);
                } else {
                    $masked[$key] = '***';
                }
            }
        }

        return $masked;
    }
}
