<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Zindagi Z-Connect API endpoints and credentials
    |
    */

    'api' => [
        'base_url' => env('ZINDAGI_ZCONNECT_BASE_URL', 'https://z-sandbox.jsbl.com/zconnect'),
        'timeout' => env('ZINDAGI_ZCONNECT_TIMEOUT', 30),
        'retry_attempts' => env('ZINDAGI_ZCONNECT_RETRY_ATTEMPTS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    |
    | API credentials for authentication
    |
    */

    'auth' => [
        'client_id' => env('ZINDAGI_ZCONNECT_CLIENT_ID'),
        'client_secret' => env('ZINDAGI_ZCONNECT_CLIENT_SECRET'),
        'api_key' => env('ZINDAGI_ZCONNECT_API_KEY'),
        'organization_id' => env('ZINDAGI_ZCONNECT_ORGANIZATION_ID'),
        'token_cache_ttl' => env('ZINDAGI_ZCONNECT_TOKEN_CACHE_TTL', 3600), // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for logging and audit trails
    |
    */

    'logging' => [
        'enabled' => env('ZINDAGI_ZCONNECT_LOGGING_ENABLED', true),
        'channel' => env('ZINDAGI_ZCONNECT_LOG_CHANNEL', 'zindagi'),
        'days' => env('ZINDAGI_ZCONNECT_LOG_DAYS', 14),
        'level' => env('ZINDAGI_ZCONNECT_LOG_LEVEL', 'debug'),
        'log_requests' => env('ZINDAGI_ZCONNECT_LOG_REQUESTS', true),
        'log_responses' => env('ZINDAGI_ZCONNECT_LOG_RESPONSES', true),
        'log_sensitive_data' => env('ZINDAGI_ZCONNECT_LOG_SENSITIVE_DATA', false),
        'sensitive_fields' => [
            'password',
            'pin',
            'cvv',
            'card_number',
            'account_number',
            'cnic',
            'mobile_number',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Trail Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for audit trail functionality
    |
    */

    'audit' => [
        'enabled' => env('ZINDAGI_ZCONNECT_AUDIT_ENABLED', true),
        'table' => 'zindagi_zconnect_audit_logs',
        'retention_days' => env('ZINDAGI_ZCONNECT_AUDIT_RETENTION_DAYS', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security settings for API communications
    |
    */

    'security' => [
        'encrypt_requests' => env('ZINDAGI_ZCONNECT_ENCRYPT_REQUESTS', true),
        'encrypt_responses' => env('ZINDAGI_ZCONNECT_ENCRYPT_RESPONSES', true),
        'verify_ssl' => env('ZINDAGI_ZCONNECT_VERIFY_SSL', true),
        'request_signing' => env('ZINDAGI_ZCONNECT_REQUEST_SIGNING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for individual modules
    |
    */

    'modules' => [
        'onboarding' => [
            'enabled' => env('ZINDAGI_ZCONNECT_ONBOARDING_ENABLED', true),
            'endpoint' => '/onboarding',
            'timeout' => env('ZINDAGI_ZCONNECT_ONBOARDING_TIMEOUT', 60),
            'account_verification' => [
                'endpoint' => '/api/v2/verifyacclinkacc-blb',
                'merchant_type' => env('ZINDAGI_ZCONNECT_MERCHANT_TYPE', '0088'),
                'company_name' => env('ZINDAGI_ZCONNECT_COMPANY_NAME', 'NOVA'),
                'transaction_type' => env('ZINDAGI_ZCONNECT_TRANSACTION_TYPE', '02'),
            ],
            'account_linking' => [
                'endpoint' => '/api/v2/linkacc-blb',
                'merchant_type' => env('ZINDAGI_ZCONNECT_MERCHANT_TYPE', '0088'),
                'company_name' => env('ZINDAGI_ZCONNECT_COMPANY_NAME', 'NOVA'),
                'transaction_type' => env('ZINDAGI_ZCONNECT_ACCOUNT_LINKING_TRANSACTION_TYPE', '01'),
                'reserved1' => env('ZINDAGI_ZCONNECT_ACCOUNT_LINKING_RESERVED1', '02'),
            ],
            'account_opening' => [
                'endpoint' => '/api/v2/accountopening-blb',
                'merchant_type' => env('ZINDAGI_ZCONNECT_MERCHANT_TYPE', '0088'),
                'company_name' => env('ZINDAGI_ZCONNECT_COMPANY_NAME', 'NOVA'),
            ],
            'account_opening_l1' => [
                'endpoint' => '/api/v2/accountopeningl1-blb2',
                'merchant_type' => env('ZINDAGI_ZCONNECT_MERCHANT_TYPE', '0088'),
                'company_name' => env('ZINDAGI_ZCONNECT_COMPANY_NAME', 'NOVA'),
            ],
            'account_upgrade' => [
                'endpoint' => '/api/v2/upgradeaccount',
                'merchant_type' => env('ZINDAGI_ZCONNECT_MERCHANT_TYPE', '0088'),
                'company_name' => env('ZINDAGI_ZCONNECT_COMPANY_NAME', 'NOVA'),
            ],
            'l2_account_fields' => [
                'endpoint' => '/api/v1/l2Account/l2AccountFields',
            ],
            'update_pmd_kyc' => [
                'endpoint' => '/api/v1/updatePmdAndKyc',
            ],
            'minor_account_opening' => [
                 'endpoint' => '/api/v1/M0AccountOpening',
            ],
            'minor_account_verification' => [
                 'endpoint' => '/api/v1/M0AccountVerification',
            ],
            'upgrade_minor_account' => [
                 'endpoint' => '/api/v1/UpgradeMinorAccount',
            ],
            'get_l2_discrepant_data' => [
                'endpoint' => '/api/v1/getL2AccountUpgradeDiscrepant',
            ],
            'l2_account_upgrade_discrepant' => [
                'endpoint' => '/api/v1/l2AccountUpgradeDiscrepant',
            ],
            'get_l2_accounts' => [
                'endpoint' => '/api/v1/level2Accounts',
            ],
            'l2_account_status' => [
                'endpoint' => '/api/v1/l2Account/l2AccountStatus',
            ],
            'level2_account_mother' => [
                'endpoint' => '/api/v1/l2Account/motherName',
            ],
            'get_account_info' => [
                'endpoint' => '/api/v1/accountInfo'
            ]
        ],
        'inquiry' => [
            'enabled' => env('ZINDAGI_ZCONNECT_INQUIRY_ENABLED', true),
            'endpoint' => '/inquiry',
            'timeout' => env('ZINDAGI_ZCONNECT_INQUIRY_TIMEOUT', 30),
        ],
        'payment' => [
            'enabled' => env('ZINDAGI_ZCONNECT_PAYMENT_ENABLED', true),
            'endpoint' => '/payment',
            'timeout' => env('ZINDAGI_ZCONNECT_PAYMENT_TIMEOUT', 45),
        ],
        'lending' => [
            'enabled' => env('ZINDAGI_ZCONNECT_LENDING_ENABLED', true),
            'endpoint' => '/lending',
            'timeout' => env('ZINDAGI_ZCONNECT_LENDING_TIMEOUT', 60),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for API responses and tokens
    |
    */

    'cache' => [
        'enabled' => env('ZINDAGI_ZCONNECT_CACHE_ENABLED', true),
        'prefix' => 'zindagi_zconnect',
        'default_ttl' => env('ZINDAGI_ZCONNECT_CACHE_TTL', 300), // 5 minutes
    ],
];
