<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class L2AccountFieldsResponseDTO
{
    public function __construct(
        public bool $success,
        public string $responseCode,
        public string $message,
        public ?string $mobileNumber = null,
        public ?string $rrn = null,
        public array $originalResponse = []
    ) {}

    /**
     * Create DTO from API response array.
     */
    public static function fromArray(array $data): self
    {
        // Adjusting based on typical JSBL structure which might wrap in 'l2AccountFieldsResp' or return flat
        $response = $data['l2AccountFieldsResp'] ?? $data; 
        
        $responseCode = $response['ResponseCode'] ?? '';
        $responseDetails = $response['ResponseDescription'] ?? $response['ResponseDetails'] ?? '';
        
        return new self(
            success: $responseCode === '00',
            responseCode: $responseCode,
            message: $responseDetails,
            mobileNumber: $response['MobileNumber'] ?? null,
            rrn: $response['Rrn'] ?? null,
            originalResponse: $data
        );
    }
}
