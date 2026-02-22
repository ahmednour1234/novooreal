<?php

namespace App\Services;

class ZatcaQrService
{
    public static function generateTLV(array $data): string
    {
        $tlv = '';

        $tags = [
            1 => 'seller_name',
            2 => 'vat_registration_number',
            3 => 'invoice_timestamp',
            4 => 'invoice_total',
            5 => 'vat_total',
        ];

        foreach ($tags as $tag => $key) {
            if (!isset($data[$key])) {
                continue;
            }

            $value = (string) $data[$key];
            $length = strlen($value);
            $tlv .= sprintf('%02d', $tag);
            $tlv .= sprintf('%02d', $length);
            $tlv .= $value;
        }

        if (isset($data['signature'])) {
            $signature = $data['signature'];
            $length = strlen($signature);
            $tlv .= '06';
            $tlv .= sprintf('%02d', $length);
            $tlv .= $signature;
        }

        return base64_encode($tlv);
    }

    public static function parseTLV(string $encoded): array
    {
        $data = base64_decode($encoded, true);
        if ($data === false) {
            return [];
        }

        $result = [];
        $pos = 0;
        $length = strlen($data);

        while ($pos < $length) {
            if ($pos + 4 > $length) {
                break;
            }

            $tag = substr($data, $pos, 2);
            $pos += 2;
            $len = (int) substr($data, $pos, 2);
            $pos += 2;

            if ($pos + $len > $length) {
                break;
            }

            $value = substr($data, $pos, $len);
            $pos += $len;

            $tagMap = [
                '01' => 'seller_name',
                '02' => 'vat_registration_number',
                '03' => 'invoice_timestamp',
                '04' => 'invoice_total',
                '05' => 'vat_total',
                '06' => 'signature',
            ];

            if (isset($tagMap[$tag])) {
                $result[$tagMap[$tag]] = $value;
            }
        }

        return $result;
    }

    public static function generateQRCode(array $invoiceData, ?string $signature = null): string
    {
        $data = [
            'seller_name' => $invoiceData['seller_name'] ?? '',
            'vat_registration_number' => $invoiceData['vat_registration_number'] ?? '',
            'invoice_timestamp' => $invoiceData['invoice_timestamp'] ?? date('c'),
            'invoice_total' => number_format((float) ($invoiceData['invoice_total'] ?? 0), 2, '.', ''),
            'vat_total' => number_format((float) ($invoiceData['vat_total'] ?? 0), 2, '.', ''),
        ];

        if ($signature) {
            $data['signature'] = $signature;
        }

        return self::generateTLV($data);
    }
}
