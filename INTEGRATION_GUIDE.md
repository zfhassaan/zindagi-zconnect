# New Module Integration Guide

This guide outlines the steps to integrate a new API module (or feature) into the `zindagi-zconnect` package. We will follow the pattern used for the `Account Statement V2` integration.

## Overview

Integration involves 5 key steps:

1.  **Configuration**: Define API endpoints.
2.  **DTOs**: Create Request and Response Data Transfer Objects.
3.  **Interface**: Define the contract.
4.  **Implementation**: Add logic to the Service.
5.  **Testing**: Verify functionality.

---

## Step 1: Configuration

Add your new module's endpoint configuration in `config/zindagi-zconnect.php` under the `modules` array.

```php
// config/zindagi-zconnect.php

'modules' => [
    'onboarding' => [
        // ... existing configs
        'your_new_feature' => [
             'endpoint' => '/api/v2/newFeatureEndpoint',
        ],
    ],
]
```

## Step 2: Create DTOs

Create **Request** and **Response** DTOs in `src/Modules/{Module}/DTOs/`.

### Request DTO (`YourFeatureRequestDTO.php`)

Must include:

- Properties mapping to the API request fields.
- `validate()` method to ensure required fields exist.
- `toArray()` method to format data for the API.

```php
public function toArray(): array
{
    return [
        'NewFeatureReq' => [
            'Field1' => $this->field1,
            'Field2' => $this->field2,
        ],
    ];
}
```

### Response DTO (`YourFeatureResponseDTO.php`)

Must include:

- `fromApiResponse(array $data)` static method to map the raw JSON response to the DTO.

## Step 3: Update Service Interface

Add the new method signature to the relevant interface, e.g., `src/Modules/Onboarding/Services/Contracts/OnboardingServiceInterface.php`.

```php
public function yourNewFeature(YourFeatureRequestDTO $dto): YourFeatureResponseDTO;
```

## Step 4: Implement Service Method

Update `src/Modules/Onboarding/Services/OnboardingService.php`.

1.  **Initialize Client**: Add a new `Client` property and initialize it in `__construct` using the config from Step 1.
2.  **Implement Method**:
    - **Log** the initiation.
    - **Validate** the DTO.
    - **Call API** using `httpClient` or the specific Guzzle client.
    - **Parse Response** into the Response DTO.
    - **Log** the result.
    - **Return** the DTO.

```php
public function yourNewFeature(YourFeatureRequestDTO $dto): YourFeatureResponseDTO
{
    try {
        $this->loggingService->logInfo('Initiating new feature', ['data' => $dto->field1]);
        $dto->validate();

        // API Call
        $response = $this->yourFeatureClient->post($this->yourFeatureEndpoint, ['json' => $dto->toArray()]);

        return YourFeatureResponseDTO::fromApiResponse(json_decode($response->getBody(), true));
    } catch (\Exception $e) {
        // Handle errors & Log
    }
}
```

## Step 5: Testing

Create a unit test in `tests/Unit/`.

- Mock the API response.
- Verify the Service correctly maps the Request and Response DTOs.
- Ensure the Service handles success and failure cases appropriately.

```bash
./vendor/bin/phpunit tests/Unit/YourFeatureServiceTest.php
```
