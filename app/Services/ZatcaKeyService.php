<?php

namespace App\Services;

use App\Models\ZatcaEgsUnit;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ZatcaKeyService
{
    public function generateKeyPair(string $egsId): array
    {
        $config = [
            'digest_alg' => 'sha256',
            'private_key_bits' => config('zatca.keys.key_size', 2048),
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $resource = openssl_pkey_new($config);
        if ($resource === false) {
            throw new \RuntimeException('Failed to generate key pair: ' . openssl_error_string());
        }

        openssl_pkey_export($resource, $privateKey);
        $publicKeyDetails = openssl_pkey_get_details($resource);
        $publicKey = $publicKeyDetails['key'];

        $disk = config('zatca.storage.disk', 'local');
        $keysPath = config('zatca.storage.keys_path', 'zatca/keys');
        $egsPath = $keysPath . '/' . $egsId;

        Storage::disk($disk)->makeDirectory($egsPath);

        $privateKeyPath = $egsPath . '/private_key.pem';
        $publicKeyPath = $egsPath . '/public_key.pem';

        if (config('zatca.keys.encrypt_private_key', true)) {
            $encrypted = encrypt($privateKey);
            Storage::disk($disk)->put($privateKeyPath, $encrypted);
        } else {
            Storage::disk($disk)->put($privateKeyPath, $privateKey);
        }

        Storage::disk($disk)->put($publicKeyPath, $publicKey);

        return [
            'private_key' => $privateKey,
            'public_key' => $publicKey,
            'private_key_path' => $privateKeyPath,
            'public_key_path' => $publicKeyPath,
        ];
    }

    public function generateCSR(ZatcaEgsUnit $egsUnit, array $companyInfo): string
    {
        $privateKeyPath = $egsUnit->private_key_path;
        if (!$privateKeyPath) {
            throw new \RuntimeException('Private key not found for EGS unit: ' . $egsUnit->egs_id);
        }

        $disk = config('zatca.storage.disk', 'local');
        $privateKeyContent = Storage::disk($disk)->get($privateKeyPath);

        if (config('zatca.keys.encrypt_private_key', true)) {
            $privateKeyContent = decrypt($privateKeyContent);
        }

        $privateKey = openssl_pkey_get_private($privateKeyContent);
        if ($privateKey === false) {
            throw new \RuntimeException('Failed to load private key: ' . openssl_error_string());
        }

        $dn = [
            'countryName' => 'SA',
            'organizationName' => $companyInfo['company_name_en'] ?? '',
            'organizationalUnitName' => $egsUnit->name,
            'commonName' => $egsUnit->egs_id,
        ];

        $csr = openssl_csr_new($dn, $privateKey, [
            'digest_alg' => 'sha256',
        ]);

        if ($csr === false) {
            throw new \RuntimeException('Failed to generate CSR: ' . openssl_error_string());
        }

        openssl_csr_export($csr, $csrOut);
        openssl_free_key($privateKey);

        $certificatesPath = config('zatca.storage.certificates_path', 'zatca/certificates');
        $egsPath = $certificatesPath . '/' . $egsUnit->egs_id;
        Storage::disk($disk)->makeDirectory($egsPath);

        $csrPath = $egsPath . '/csr.pem';
        Storage::disk($disk)->put($csrPath, $csrOut);

        return $csrOut;
    }

    public function signData(string $data, ZatcaEgsUnit $egsUnit): string
    {
        $privateKeyPath = $egsUnit->private_key_path;
        if (!$privateKeyPath) {
            throw new \RuntimeException('Private key not found for EGS unit: ' . $egsUnit->egs_id);
        }

        $disk = config('zatca.storage.disk', 'local');
        $privateKeyContent = Storage::disk($disk)->get($privateKeyPath);

        if (config('zatca.keys.encrypt_private_key', true)) {
            $privateKeyContent = decrypt($privateKeyContent);
        }

        $privateKey = openssl_pkey_get_private($privateKeyContent);
        if ($privateKey === false) {
            throw new \RuntimeException('Failed to load private key: ' . openssl_error_string());
        }

        $signature = '';
        $success = openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($privateKey);

        if (!$success) {
            throw new \RuntimeException('Failed to sign data: ' . openssl_error_string());
        }

        return base64_encode($signature);
    }
}
