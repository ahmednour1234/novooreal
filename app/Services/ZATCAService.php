<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\ZatcaEgsUnit;
use App\Models\ZatcaDocument;
use App\Models\CompanySetting;

class ZATCAService
{
    protected ZatcaKeyService $keyService;
    protected ZatcaXmlService $xmlService;
    protected ZatcaApiService $apiService;
    protected ZatcaQrService $qrService;

    public function __construct(
        ZatcaKeyService $keyService,
        ZatcaXmlService $xmlService,
        ZatcaApiService $apiService,
        ZatcaQrService $qrService
    ) {
        $this->keyService = $keyService;
        $this->xmlService = $xmlService;
        $this->apiService = $apiService;
        $this->qrService = $qrService;
    }

    public static function generateUUID(): string
    {
        return (string) Str::uuid();
    }

    public static function generateInvoiceNumber(int $counter): string
    {
        return 'INV-' . str_pad($counter, 6, '0', STR_PAD_LEFT);
    }

    public static function calculateHash(array $invoiceData): string
    {
        $dataString = json_encode($invoiceData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return hash('sha256', $dataString);
    }

    public static function generateZATCAQRCode(array $invoiceData): string
    {
        $qrService = new ZatcaQrService();
        return $qrService::generateQRCode($invoiceData);
    }

    public static function getNextInvoiceCounter(?int $companyId = null): int
    {
        $query = DB::table('orders')
            ->whereNotNull('invoice_counter');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $maxCounter = $query->max('invoice_counter');

        return ($maxCounter ?? 0) + 1;
    }

    public static function getPreviousInvoiceHash(?int $companyId = null): ?string
    {
        $query = DB::table('orders')
            ->whereNotNull('previous_invoice_hash')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $lastOrder = $query->first();

        return $lastOrder->previous_invoice_hash ?? null;
    }

    public static function validateZATCACompliance($order): array
    {
        $errors = [];

        if (empty($order->uuid)) {
            $errors[] = 'UUID is required';
        }

        if (empty($order->invoice_number)) {
            $errors[] = 'Invoice number is required';
        }

        if (empty($order->invoice_counter)) {
            $errors[] = 'Invoice counter is required';
        }

        if (empty($order->currency_code)) {
            $errors[] = 'Currency code is required';
        }

        if ($order->currency_code !== 'SAR') {
            $errors[] = 'Currency code must be SAR for ZATCA compliance';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    public function generateQRCodeTLV(Order $order, ?string $signature = null): string
    {
        $companySettings = CompanySetting::getSettings();
        if (!$companySettings) {
            throw new \RuntimeException('Company settings not found');
        }

        $invoiceData = [
            'seller_name' => $companySettings->company_name_ar,
            'vat_registration_number' => $companySettings->vat_tin,
            'invoice_timestamp' => $order->created_at->toIso8601String(),
            'invoice_total' => $order->order_amount,
            'vat_total' => $order->total_tax ?? 0,
        ];

        return $this->qrService::generateQRCode($invoiceData, $signature);
    }

    public function validateInvoice(Order $order): array
    {
        return self::validateZATCACompliance($order);
    }

    public function buildInvoiceXML(Order $order, ZatcaEgsUnit $egsUnit, string $invoiceType = 'standard'): string
    {
        return $this->xmlService->buildInvoiceXML($order, $egsUnit, $invoiceType);
    }

    public function signXML(string $xml, ZatcaEgsUnit $egsUnit): string
    {
        $signature = $this->keyService->signData($xml, $egsUnit);

        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $signatureElement = $dom->createElement('Signature', $signature);
        $dom->documentElement->appendChild($signatureElement);

        return $dom->saveXML();
    }

    public function submitInvoice(Order $order, ZatcaEgsUnit $egsUnit, string $environment = 'simulation'): array
    {
        $invoiceType = $this->getInvoiceType($order->type);
        $xml = $this->buildInvoiceXML($order, $egsUnit, $invoiceType);
        $signedXml = $this->signXML($xml, $egsUnit);

        $result = $this->apiService->submitInvoice($signedXml, $egsUnit, $environment);

        if ($result['success']) {
            $zatcaDocument = ZatcaDocument::where('order_id', $order->id)->first();
            if (!$zatcaDocument) {
                $zatcaDocument = new ZatcaDocument();
                $zatcaDocument->order_id = $order->id;
                $zatcaDocument->egs_unit_id = $egsUnit->id;
                $zatcaDocument->invoice_uuid = $order->uuid;
                $zatcaDocument->invoice_number = $order->invoice_number;
                $zatcaDocument->invoice_type = $invoiceType;
            }

            $zatcaDocument->xml_content = $xml;
            $zatcaDocument->signed_xml = $signedXml;
            $zatcaDocument->submission_status = 'success';
            $zatcaDocument->zatca_uuid = $result['data']['uuid'] ?? null;
            $zatcaDocument->zatca_long_id = $result['data']['longId'] ?? null;
            $zatcaDocument->submitted_at = now();
            $zatcaDocument->save();

            $order->zatca_submitted = true;
            $order->zatca_submitted_at = now();
            $order->save();
        } else {
            $zatcaDocument = ZatcaDocument::where('order_id', $order->id)->first();
            if (!$zatcaDocument) {
                $zatcaDocument = new ZatcaDocument();
                $zatcaDocument->order_id = $order->id;
                $zatcaDocument->egs_unit_id = $egsUnit->id;
                $zatcaDocument->invoice_uuid = $order->uuid;
                $zatcaDocument->invoice_number = $order->invoice_number;
                $zatcaDocument->invoice_type = $invoiceType;
            }

            $zatcaDocument->xml_content = $xml;
            $zatcaDocument->signed_xml = $signedXml;
            $zatcaDocument->submission_status = 'failed';
            $zatcaDocument->error_message = $result['error'] ?? 'Unknown error';
            $zatcaDocument->retry_count = ($zatcaDocument->retry_count ?? 0) + 1;
            $zatcaDocument->save();
        }

        return $result;
    }

    public function requestComplianceCSID(ZatcaEgsUnit $egsUnit, string $csr, string $otp): array
    {
        return $this->apiService->requestCSID($egsUnit, $csr, $otp, 'simulation');
    }

    public function requestProductionCSID(ZatcaEgsUnit $egsUnit, string $csr, string $otp): array
    {
        return $this->apiService->requestCSID($egsUnit, $csr, $otp, 'production');
    }

    public function generateCompliancePack(ZatcaEgsUnit $egsUnit, string $fromDate, string $toDate, string $environment = 'simulation'): array
    {
        return $this->apiService->generateCompliancePack($egsUnit, $fromDate, $toDate, $environment);
    }

    protected function getInvoiceType(int $orderType): string
    {
        $standardTypes = config('zatca.invoice_types.standard', [4, 12]);
        return in_array($orderType, $standardTypes) ? 'standard' : 'simplified';
    }
}
