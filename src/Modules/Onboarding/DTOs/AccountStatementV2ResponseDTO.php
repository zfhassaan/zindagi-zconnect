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
        public ?string $message = null,
        public ?string $errorCode = null,
        public array $originalResponse = []
    ) {
        $this->message = $message ?? $this->responseDescription;
    }

    public static function fromArray(array $data): self
    {
        return self::fromApiResponse($data);
    }

    public static function fromApiResponse(array $response): self
    {
        $hasWrapper = isset($response['AccountStatementRes']) && is_array($response['AccountStatementRes']);
        $isGatewayError = isset($response['messages']) || isset($response['errorcode']);

        if ($isGatewayError && ! $hasWrapper) {
            $errorCode = isset($response['errorcode']) ? (string) $response['errorcode'] : null;
            $message = is_string($response['messages'] ?? null)
                ? $response['messages']
                : ($response['ResponseDescription'] ?? 'Unknown error');

            return new self(
                success: false,
                responseCode: $errorCode ?? (string) ($response['ResponseCode'] ?? ''),
                responseDescription: $message,
                message: $message,
                errorCode: $errorCode,
                originalResponse: $response
            );
        }

        $root = $hasWrapper ? $response['AccountStatementRes'] : $response;
        $responseCode = (string) ($root['ResponseCode'] ?? $root['responseCode'] ?? '');
        $responseDescription = (string) ($root['ResponseDescription'] ?? $root['responseDescription'] ?? '');

        $closingBalance = $root['ClosingBalanceStatement'] ?? [];
        $digiWallet = $root['DigiWalletStatement'] ?? [];

        return new self(
            success: $responseCode === '00',
            responseCode: $responseCode,
            responseDescription: $responseDescription,
            hashData: $root['HashData'] ?? null,
            responseDateTime: $root['ResponseDateTime'] ?? null,
            closingBalanceStatement: is_array($closingBalance) ? $closingBalance : [],
            digiWalletStatement: is_array($digiWallet) ? $digiWallet : [],
            rrn: $root['Rrn'] ?? null,
            message: $responseDescription,
            errorCode: $responseCode !== '00' && $responseCode !== '' ? $responseCode : null,
            originalResponse: $response
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'response_code' => $this->responseCode,
            'response_description' => $this->responseDescription,
            'hash_data' => $this->hashData,
            'response_date_time' => $this->responseDateTime,
            'closing_balance_statement' => $this->closingBalanceStatement,
            'digi_wallet_statement' => $this->digiWalletStatement,
            'rrn' => $this->rrn,
            'message' => $this->message,
            'error_code' => $this->errorCode,
        ];
    }
}
