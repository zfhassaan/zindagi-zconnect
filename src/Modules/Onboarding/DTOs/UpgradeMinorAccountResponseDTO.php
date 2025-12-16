<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class UpgradeMinorAccountResponseDTO
{
    public function __construct(
        public bool $success,
        public string $responseCode,
        public string $responseDescription,
        public ?string $rrn = null,
        public ?string $hashData = null,
        public array $originalResponse = []
    ) {}

    public static function fromArray(array $data): self
    {
        // Assuming response structure similar to others since it's missing in docs
        // Usually response key would be 'upgradeMinorAccountRes'
        $response = $data['upgradeMinorAccountRes'] ?? $data;
        $responseCode = $response['ResponseCode'] ?? $response['responseCode'] ?? ''; // Try both casing
        $responseDescription = $response['ResponseDescription'] ?? $response['responseDescription'] ?? '';

        return new self(
            success: $responseCode === '00',
            responseCode: $responseCode,
            responseDescription: $responseDescription,
            rrn: $response['Rrn'] ?? null,
            hashData: $response['HashData'] ?? $response['hashData'] ?? null,
            originalResponse: $data
        );
    }
}
