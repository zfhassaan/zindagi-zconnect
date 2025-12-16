<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class MinorAccountOpeningResponseDTO
{
    public function __construct(
        public bool $success,
        public string $responseCode,
        public string $responseDescription,
        public ?string $hashData = null,
        public array $originalResponse = []
    ) {}

    public static function fromArray(array $data): self
    {
        $response = $data['minorAccountOpeningRes'] ?? $data;
        $responseCode = $response['responseCode'] ?? '';
        $responseDescription = $response['responseDescription'] ?? '';

        return new self(
            success: $responseCode === '00', // Assuming '00' is success, need to verify if validation failure '24' logic implies '00' is success. Standard ISO 8583 '00' is success.
            responseCode: $responseCode,
            responseDescription: $responseDescription,
            hashData: $response['hashData'] ?? null,
            originalResponse: $data
        );
    }
}
