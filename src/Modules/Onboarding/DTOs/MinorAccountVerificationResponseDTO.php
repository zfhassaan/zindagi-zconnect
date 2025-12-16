<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class MinorAccountVerificationResponseDTO
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
        $response = $data['minorAccountVerifyRes'] ?? $data;
        $responseCode = $response['responseCode'] ?? '';
        $responseDescription = $response['responseDescription'] ?? '';

        return new self(
            success: $responseCode === '00',
            responseCode: $responseCode,
            responseDescription: $responseDescription,
            hashData: $response['hashData'] ?? null,
            originalResponse: $data
        );
    }
}
