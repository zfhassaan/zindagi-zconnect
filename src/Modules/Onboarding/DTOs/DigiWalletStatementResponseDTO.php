<?php

declare(strict_types=1);

namespace Zindagi\ZConnect\Modules\Onboarding\DTOs;

class DigiWalletStatementResponseDTO
{
    public function __construct(
        public string $responseCode,
        public string $responseDescription,
        public ?string $hashData,
        public ?string $responseDateTime,
        public ?string $rrn,
        public array $closingBalanceStatement,
        public array $transactions,
    ) {}

    public static function fromApiResponse(array $data): self
    {
        $res = $data['AccountStatementRes'] ?? [];

        return new self(
            responseCode: $res['ResponseCode'] ?? '',
            responseDescription: $res['ResponseDescription'] ?? '',
            hashData: $res['HashData'] ?? null,
            responseDateTime: $res['ResponseDateTime'] ?? null,
            rrn: $res['Rrn'] ?? null,
            closingBalanceStatement: $res['ClosingBalanceStatement'] ?? [],
            transactions: $res['DigiWalletStatement'] ?? [],
        );
    }

    public function isSuccessful(): bool
    {
        return $this->responseCode === '00';
    }
}
