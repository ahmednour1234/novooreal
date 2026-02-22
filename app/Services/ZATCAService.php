<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ZATCAService
{
    /**
     * Generate a unique UUID for invoice
     *
     * @return string
     */
    public static function generateUUID(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Generate formatted invoice number
     *
     * @param int $counter
     * @return string
     */
    public static function generateInvoiceNumber(int $counter): string
    {
        return 'INV-' . str_pad($counter, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate hash for invoice chain validation
     *
     * @param array $invoiceData
     * @return string
     */
    public static function calculateHash(array $invoiceData): string
    {
        $dataString = json_encode($invoiceData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return hash('sha256', $dataString);
    }

    /**
     * Generate ZATCA-compliant QR code data
     *
     * @param array $invoiceData
     * @return string
     */
    public static function generateZATCAQRCode(array $invoiceData): string
    {
        $qrData = [
            'seller_name' => $invoiceData['seller_name'] ?? '',
            'vat_registration_number' => $invoiceData['vat_registration_number'] ?? '',
            'invoice_date' => $invoiceData['invoice_date'] ?? '',
            'invoice_total' => $invoiceData['invoice_total'] ?? 0,
            'vat_total' => $invoiceData['vat_total'] ?? 0,
        ];

        return base64_encode(json_encode($qrData, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Get next invoice counter for company
     *
     * @param int|null $companyId
     * @return int
     */
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

    /**
     * Get previous invoice hash for chain validation
     *
     * @param int|null $companyId
     * @return string|null
     */
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

    /**
     * Validate ZATCA compliance for order
     *
     * @param \App\Models\Order $order
     * @return array
     */
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
}
