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
        public ?string $responseDateTime = null,
        public array $l2Accounts = [],
        public ?string $hashData = null,
        public array $originalResponse = []
    ) {}

    public static function fromArray(array $data): self
    {
        $response = $data['level2AccountsRes'] ?? $data;
        $responseCode = $response['ResponseCode'] ?? '';
        $responseDescription = $response['ResponseDescription'] ?? '';
        
        // Parse L2 accounts with nested details structure
        $l2Accounts = [];
        if (isset($response['L2Accounts']) && is_array($response['L2Accounts'])) {
            $l2Accounts = array_map(function($account) {
                return [
                    'id' => $account['id'] ?? null,
                    'accountId' => $account['accountId'] ?? null,
                    'accountName' => $account['accountName'] ?? null,
                    'description' => $account['description'] ?? null,
                    'details' => isset($account['details']) && is_array($account['details']) 
                        ? array_map(function($detail) {
                            return [
                                'title' => $detail['title'] ?? null,
                                'data' => $detail['data'] ?? [],
                            ];
                        }, $account['details'])
                        : [],
                ];
            }, $response['L2Accounts']);
        }

        return new self(
            success: $responseCode === '00',
            responseCode: $responseCode,
            message: $responseDescription,
            rrn: $response['Rrn'] ?? null,
            responseDateTime: $response['ResponseDateTime'] ?? null,
            l2Accounts: $l2Accounts,
            hashData: $response['HashData'] ?? null,
            originalResponse: $data
        );
    }
}
