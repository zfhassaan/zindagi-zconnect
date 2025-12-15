# Zindagi Z-Connect for JS Bank

A comprehensive Laravel package for integrating with JS Bank's Zindagi Z-Connect API. This package provides scalable, secure, auditable solutions for banking operations.

## Features

- **Onboarding Solution** - Complete customer onboarding workflow with account verification
- **Inquiry Solution** - Account and transaction inquiries (Coming Soon)
- **Payment Solution** - Payment processing and management (Coming Soon)
- **Lending Solution** - Loan application and management (Coming Soon)

## Requirements

- PHP >= 8.1
- Laravel >= 10.0
- Guzzle HTTP Client

## Installation

```bash
composer require zfhassaan/zindagi-zconnect
```

## Configuration

1. Publish the configuration file:

```bash
php artisan vendor:publish --tag=zindagi-zconnect-config
```

2. Add your credentials to `.env`:

```env
ZINDAGI_ZCONNECT_BASE_URL=https://api.jsbank.com/zconnect
ZINDAGI_ZCONNECT_CLIENT_ID=your_client_id
ZINDAGI_ZCONNECT_CLIENT_SECRET=your_client_secret
ZINDAGI_ZCONNECT_API_KEY=your_api_key
ZINDAGI_ZCONNECT_ORGANIZATION_ID=223
ZINDAGI_ZCONNECT_MERCHANT_TYPE=0088
ZINDAGI_ZCONNECT_COMPANY_NAME=NOVA
```

3. Publish and run migrations:

```bash
php artisan vendor:publish --tag=zindagi-zconnect-migrations
php artisan migrate
```

## Usage

### Onboarding Solution

#### Using Facade

```php
use zfhassaan\ZindagiZconnect\Facades\ZindagiZconnect;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\OnboardingRequestDTO;

// Create onboarding request DTO
$dto = OnboardingRequestDTO::fromArray([
    'cnic' => '1234567890123',
    'full_name' => 'John Doe',
    'mobile_number' => '03001234567',
    'email' => 'john@example.com',
    'date_of_birth' => '1990-01-01',
    'address' => '123 Main Street',
    'city' => 'Karachi',
    'country' => 'Pakistan',
]);

// Initiate onboarding
$response = ZindagiZconnect::onboarding()->initiate($dto);

if ($response->success) {
    $referenceId = $response->referenceId;
    // Process success
}

// Verify customer
$verifyResponse = ZindagiZconnect::onboarding()->verify($referenceId, [
    'verification_code' => '123456',
    'otp' => '654321',
]);

// Get status
$statusResponse = ZindagiZconnect::onboarding()->getStatus($referenceId);

// Complete onboarding
$completeResponse = ZindagiZconnect::onboarding()->complete($referenceId, [
    'account_number' => '1234567890',
]);
```

### Account Verification (Part of Onboarding)

#### Verify Account Link

```php
use zfhassaan\ZindagiZconnect\Facades\ZindagiZconnect;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationRequestDTO;

// Create account verification request
$dto = AccountVerificationRequestDTO::fromArray([
    'cnic' => '1234567890123',
    'mobile_no' => '03001234567',
    // Optional - defaults from config:
    // 'merchant_type' => '0088',
    // 'trace_no' => '000009', // Auto-generated if not provided
    // 'date_time' => '20210105201527', // Auto-generated if not provided
    // 'company_name' => 'NOVA',
    // 'reserved1' => '01',
    // 'reserved2' => '01',
    // 'transaction_type' => '02',
]);

// Verify account
$response = ZindagiZconnect::onboarding()->verifyAccount($dto);

if ($response->success && $response->accountExists()) {
    echo "Account exists: " . $response->accountTitle;
    echo "Account Type: " . $response->accountType;
    echo "PIN Set: " . ($response->isPinSet() ? 'Yes' : 'No');
} else {
    echo "Error: " . $response->message;
}
```

#### Account Verification Events

```php
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\AccountVerified;

Event::listen(AccountVerified::class, function ($event) {
    // Handle account verification
    Log::info('Account verified', [
        'trace_no' => $event->verification->trace_no,
        'account_status' => $event->response->accountStatus,
    ]);
});
```

#### Using Dependency Injection

```php
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\Contracts\OnboardingServiceInterface;

class YourController
{
    public function __construct(
        protected OnboardingServiceInterface $onboardingService
    ) {
    }

    public function onboard(Request $request)
    {
        $dto = OnboardingRequestDTO::fromArray($request->all());
        $response = $this->onboardingService->initiate($dto);
        
        return response()->json($response->toArray());
    }
}
```

### Events

The package fires events for onboarding actions:

```php
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\OnboardingInitiated;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\OnboardingVerified;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\OnboardingCompleted;

Event::listen(OnboardingInitiated::class, function ($event) {
    // Handle onboarding initiated
    Log::info('Onboarding initiated', [
        'reference_id' => $event->onboarding->reference_id,
    ]);
});
```

### Validation Helpers

```php
use zfhassaan\ZindagiZconnect\Helpers\ValidationHelper;

// Validate CNIC
if (ValidationHelper::validateCnic('1234567890123')) {
    $formatted = ValidationHelper::formatCnic('1234567890123');
    // Output: 12345-6789012-3
}

// Validate mobile number
if (ValidationHelper::validateMobileNumber('03001234567')) {
    $formatted = ValidationHelper::formatMobileNumber('03001234567');
    // Output: +923001234567
}
```

### Audit Trail

All actions are automatically logged to the audit trail:

```php
use zfhassaan\ZindagiZconnect\Facades\ZindagiZconnect;

// Get audit logs
$logs = ZindagiZconnect::audit()->getLogs([
    'module' => 'onboarding',
    'action' => 'onboarding_initiated',
], limit: 50);
```

## Security Features

- Encrypted API communications
- Request/Response validation
- Comprehensive audit trail logging
- Secure credential management
- Sensitive data masking in logs
- Request signature validation (optional)
- API key validation (optional)

## Logging

The package provides comprehensive logging:

```php
use zfhassaan\ZindagiZconnect\Facades\ZindagiZconnect;

// Log custom messages
ZindagiZconnect::logger()->logInfo('Custom message', ['data' => 'value']);
ZindagiZconnect::logger()->logError('Error occurred', ['error' => 'details']);
```

All API requests and responses are automatically logged (with sensitive data masked by default).

## Architecture

The package follows a modular architecture:

```
src/
├── Modules/
│   ├── Onboarding/          Implemented
│   │   ├── Controllers/
│   │   ├── DTOs/
│   │   ├── Events/
│   │   ├── Models/
│   │   ├── Repositories/
│   │   └── Services/
│   ├── Inquiry/             Coming Soon
│   ├── Payment/             Coming Soon
│   └── Lending/             Coming Soon
├── Services/                 Core services (Auth, HTTP, Logging, Audit)
├── Repositories/             Data access layer
├── Models/                   Eloquent models
├── Middleware/               Security middleware
├── Helpers/                  Utility helpers
└── Exceptions/               Custom exceptions
```

## Testing

Run the test suite:

```bash
composer test
```

Or with PHPUnit:

```bash
vendor/bin/phpunit
```

## Contributing

Contributions are welcome! Please read the contributing guidelines before submitting pull requests.

## License

MIT

## Support

For issues and questions, please open an issue on GitHub.

