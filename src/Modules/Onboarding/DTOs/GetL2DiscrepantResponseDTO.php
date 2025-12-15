<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class GetL2DiscrepantResponseDTO
{
    public function __construct(
        public bool $success,
        public string $responseCode,
        public string $message,
        public ?string $rrn = null,
        public array $originalResponse = []
    ) {}

    /**
     * Create DTO from API response array.
     */
    public static function fromArray(array $data): self
    {
        $response = $data['getL2AccountUpgradeDiscrepantResp'] ?? $data;
        $responseCode = $response['ResponseCode'] ?? '';
        $responseDescription = $response['ResponseDescription'] ?? '';

        return new self(
            success: $responseCode === '00',
            responseCode: $responseCode,
            message: $responseDescription,
            rrn: $response['Rrn'] ?? null,
            originalResponse: $data
        );
    }
}
