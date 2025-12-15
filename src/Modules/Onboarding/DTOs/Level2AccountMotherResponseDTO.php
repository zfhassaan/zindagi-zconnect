<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class Level2AccountMotherResponseDTO
{
    public function __construct(
        public bool $success,
        public string $responseCode,
        public string $message,
        public ?string $responseDateTime = null,
        public array $motherNameList = [],
        public array $originalResponse = []
    ) {}

    /**
     * Create DTO from API response array.
     */
    public static function fromArray(array $data): self
    {
        $response = $data['level2AccountMotherRes'] ?? $data;
        $responseCode = $response['responseCode'] ?? '';
        $responseDescription = $response['responseDescription'] ?? '';

        // Parse mother name list
        $motherNameList = [];
        if (isset($response['motherNameList']) && is_array($response['motherNameList'])) {
            $motherNameList = array_map(function($item) {
                return [
                    'id' => $item['id'] ?? null,
                    'motherName' => $item['motherName'] ?? null,
                ];
            }, $response['motherNameList']);
        }

        return new self(
            success: $responseCode === '00',
            responseCode: $responseCode,
            message: $responseDescription,
            responseDateTime: $response['responseDateTime'] ?? null,
            motherNameList: $motherNameList,
            originalResponse: $data
        );
    }
}
