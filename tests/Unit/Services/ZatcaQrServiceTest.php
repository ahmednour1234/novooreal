<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ZatcaQrService;

class ZatcaQrServiceTest extends TestCase
{
    public function test_generate_tlv_encodes_data_correctly(): void
    {
        $data = [
            'seller_name' => 'Test Company',
            'vat_registration_number' => '123456789012345',
            'invoice_timestamp' => '2024-01-01T12:00:00Z',
            'invoice_total' => '100.00',
            'vat_total' => '15.00',
        ];

        $tlv = ZatcaQrService::generateTLV($data);

        $this->assertNotEmpty($tlv);
        $this->assertIsString($tlv);
    }

    public function test_parse_tlv_decodes_data_correctly(): void
    {
        $data = [
            'seller_name' => 'Test Company',
            'vat_registration_number' => '123456789012345',
            'invoice_timestamp' => '2024-01-01T12:00:00Z',
            'invoice_total' => '100.00',
            'vat_total' => '15.00',
        ];

        $encoded = ZatcaQrService::generateTLV($data);
        $decoded = ZatcaQrService::parseTLV($encoded);

        $this->assertEquals($data['seller_name'], $decoded['seller_name']);
        $this->assertEquals($data['vat_registration_number'], $decoded['vat_registration_number']);
        $this->assertEquals($data['invoice_total'], $decoded['invoice_total']);
        $this->assertEquals($data['vat_total'], $decoded['vat_total']);
    }

    public function test_generate_qr_code_includes_signature(): void
    {
        $invoiceData = [
            'seller_name' => 'Test Company',
            'vat_registration_number' => '123456789012345',
            'invoice_timestamp' => '2024-01-01T12:00:00Z',
            'invoice_total' => 100.00,
            'vat_total' => 15.00,
        ];

        $signature = 'test_signature_12345';
        $qrCode = ZatcaQrService::generateQRCode($invoiceData, $signature);

        $this->assertNotEmpty($qrCode);
        $decoded = ZatcaQrService::parseTLV($qrCode);
        $this->assertEquals($signature, $decoded['signature'] ?? null);
    }

    public function test_generate_qr_code_without_signature(): void
    {
        $invoiceData = [
            'seller_name' => 'Test Company',
            'vat_registration_number' => '123456789012345',
            'invoice_timestamp' => '2024-01-01T12:00:00Z',
            'invoice_total' => 100.00,
            'vat_total' => 15.00,
        ];

        $qrCode = ZatcaQrService::generateQRCode($invoiceData);

        $this->assertNotEmpty($qrCode);
        $decoded = ZatcaQrService::parseTLV($qrCode);
        $this->assertArrayNotHasKey('signature', $decoded);
    }
}
