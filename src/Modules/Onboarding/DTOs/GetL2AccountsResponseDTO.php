<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class GetL2AccountsResponseDTO
{
    public function __construct(
        public bool $success,
        public string $responseCode,
        public string $message,
        public ?string $rrn = null,
        public array $l2Accounts = [],
        public ?string $hashData = null,
        public array $originalResponse = []
    ) {}

    public static function fromArray(array $data): self
    {
        $response = $data['level2AccountsRes'] ?? $data;
        $responseCode = $response['ResponseCode'] ?? '';
        $responseDescription = $response['ResponseDescription'] ?? '';

        return new self(
            success: $responseCode === '00',
            responseCode: $responseCode,
            message: $responseDescription,
            rrn: $response['Rrn'] ?? null,
            l2Accounts: $response['L2Accounts'] ?? [],
            hashData: $response['HashData'] ?? null,
            originalResponse: $data
        );
    }
}
