<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class AccountStatementV2ResponseDTO
{
    public function __construct(
        public bool $success,
        public string $responseCode,
        public string $responseDescription,
        public ?string $hashData = null,
        public ?string $responseDateTime = null,
        public array $closingBalanceStatement = [],
        public array $digiWalletStatement = [],
        public ?string $rrn = null,
        public array $originalResponse = []
    ) {}

    public static function fromArray(array $data): self
    {
        // Handle if data is wrapped in 'AccountStatementRes' or flattened
        $root = $data['AccountStatementRes'] ?? $data;

        $responseCode = $root['ResponseCode'] ?? $root['responseCode'] ?? '';
        $responseDescription = $root['ResponseDescription'] ?? $root['responseDescription'] ?? '';
        
        $closingBalance = $root['ClosingBalanceStatement'] ?? [];
        $digiWallet = $root['DigiWalletStatement'] ?? [];

        return new self(
            success: $responseCode === '00',
            responseCode: $responseCode,
            responseDescription: $responseDescription,
            hashData: $root['HashData'] ?? null,
            responseDateTime: $root['ResponseDateTime'] ?? null,
            closingBalanceStatement: $closingBalance,
            digiWalletStatement: $digiWallet,
            rrn: $root['Rrn'] ?? null,
            originalResponse: $data
        );
    }
}
