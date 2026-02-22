<?php

return [
    'environment' => env('ZATCA_ENVIRONMENT', 'simulation'),

    'api' => [
        'simulation' => [
            'base_url' => env('ZATCA_SIMULATION_BASE_URL', 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal'),
            'validate_endpoint' => '/invoices/validate',
            'reporting_endpoint' => '/invoices/reporting/single',
            'compliance_csid_endpoint' => '/compliance',
            'production_csid_endpoint' => '/production/csid',
            'compliance_pack_endpoint' => '/compliance/pack',
        ],
        'production' => [
            'base_url' => env('ZATCA_PRODUCTION_BASE_URL', 'https://gw-fatoora.zatca.gov.sa/e-invoicing/core'),
            'validate_endpoint' => '/invoices/validate',
            'reporting_endpoint' => '/invoices/reporting/single',
            'compliance_csid_endpoint' => '/compliance',
            'production_csid_endpoint' => '/production/csid',
            'compliance_pack_endpoint' => '/compliance/pack',
        ],
    ],

    'storage' => [
        'disk' => env('ZATCA_STORAGE_DISK', 'local'),
        'keys_path' => 'zatca/keys',
        'certificates_path' => 'zatca/certificates',
    ],

    'keys' => [
        'algorithm' => 'RSA',
        'key_size' => 2048,
        'encrypt_private_key' => true,
    ],

    'retry' => [
        'max_attempts' => 3,
        'delay_seconds' => 5,
        'exponential_backoff' => true,
    ],

    'queue' => [
        'connection' => env('ZATCA_QUEUE_CONNECTION', 'database'),
        'queue' => env('ZATCA_QUEUE_NAME', 'zatca'),
    ],

    'invoice_types' => [
        'standard' => [4, 12],
        'simplified' => [1],
    ],

    'logging' => [
        'mask_sensitive_data' => true,
        'log_requests' => true,
        'log_responses' => true,
    ],
];
