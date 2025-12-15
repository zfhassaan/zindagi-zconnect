<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class UpdatePmdKycResponseDTO
{
    public function __construct(
        public bool $success,
        public string $responseCode,
        public string $message,
        public ?string $rrn = null,
        public ?string $hashData = null,
        public array $originalResponse = []
    ) {}

    public static function fromArray(array $data): self
    {
        $response = $data['updatePmdAndKycRes'] ?? $data;
        $responseCode = $response['ResponseCode'] ?? '';
        $responseDescription = $response['ResponseDescription'] ?? '';

        return new self(
            success: $responseCode === '00',
            responseCode: $responseCode,
            message: $responseDescription,
            rrn: $response['Rrn'] ?? null,
            hashData: $response['HashData'] ?? null,
            originalResponse: $data
        );
    }
}
