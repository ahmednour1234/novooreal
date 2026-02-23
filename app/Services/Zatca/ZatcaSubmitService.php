<?php

namespace App\Services\Zatca;

use App\Models\Order;
use App\Models\ZatcaEgsUnit;
use App\Models\ZatcaDocument;
use App\Models\CompanySetting;
use App\Services\ZatcaApiService;
use App\Services\ZatcaKeyService;
use Illuminate\Support\Facades\Log;

class ZatcaSubmitService
{
    protected ZatcaApiService $apiService;
    protected ZatcaKeyService $keyService;

    public function __construct(ZatcaApiService $apiService, ZatcaKeyService $keyService)
    {
        $this->apiService = $apiService;
        $this->keyService = $keyService;
    }

    /**
     * Submit invoice to ZATCA
     * 
     * @param Order $order
     * @param ZatcaEgsUnit $egsUnit
     * @param string $xmlContent Unsigned XML
     * @param string $environment 'simulation' or 'production'
     * @return array
     */
    public function submitInvoice(Order $order, ZatcaEgsUnit $egsUnit, string $xmlContent, string $environment = 'simulation'): array
    {
        $companySettings = CompanySetting::getSettings();
        if (!$companySettings) {
            throw new \RuntimeException('Company settings not found');
        }

        // Sign XML
        $signature = $this->keyService->signData($xmlContent, $egsUnit);
        
        $dom = new \DOMDocument();
        $dom->loadXML($xmlContent);
        $signatureElement = $dom->createElement('Signature', $signature);
        $dom->documentElement->appendChild($signatureElement);
        $signedXml = $dom->saveXML();

        // Submit to ZATCA
        $result = $this->apiService->submitInvoice($signedXml, $egsUnit, $environment);

        // Save or update ZatcaDocument
        $zatcaDocument = ZatcaDocument::where('order_id', $order->id)->first();
        if (!$zatcaDocument) {
            $zatcaDocument = new ZatcaDocument();
            $zatcaDocument->order_id = $order->id;
            $zatcaDocument->egs_unit_id = $egsUnit->id;
            $zatcaDocument->invoice_uuid = $order->uuid;
            $zatcaDocument->invoice_number = $order->invoice_number;
            
            $standardTypes = config('zatca.invoice_types.standard', [4, 12]);
            $zatcaDocument->invoice_type = in_array($order->type, $standardTypes) ? 'standard' : 'simplified';
        }

        $zatcaDocument->xml_content = $xmlContent;
        $zatcaDocument->signed_xml = $signedXml;

        if ($result['success']) {
            $zatcaDocument->submission_status = 'success';
            $zatcaDocument->zatca_uuid = $result['data']['uuid'] ?? null;
            $zatcaDocument->zatca_long_id = $result['data']['longId'] ?? $result['data']['long_id'] ?? null;
            $zatcaDocument->submitted_at = now();
            $zatcaDocument->error_message = null;
            $zatcaDocument->response_json = $result['data'] ?? null;
            
            $order->zatca_submitted = true;
            $order->zatca_submitted_at = now();
            $order->save();

            Log::channel('zatca')->info('Invoice submitted successfully', [
                'order_id' => $order->id,
                'egs_unit_id' => $egsUnit->id,
                'zatca_uuid' => $zatcaDocument->zatca_uuid,
            ]);
        } else {
            $zatcaDocument->submission_status = 'failed';
            $zatcaDocument->error_message = $result['error'] ?? 'Unknown error';
            $zatcaDocument->retry_count = ($zatcaDocument->retry_count ?? 0) + 1;
            $zatcaDocument->response_json = $result['data'] ?? null;

            Log::channel('zatca')->error('Invoice submission failed', [
                'order_id' => $order->id,
                'egs_unit_id' => $egsUnit->id,
                'error' => $zatcaDocument->error_message,
            ]);
        }

        $zatcaDocument->save();

        return [
            'success' => $result['success'],
            'data' => $result['data'] ?? null,
            'error' => $result['error'] ?? null,
            'zatca_document' => $zatcaDocument,
        ];
    }
}
