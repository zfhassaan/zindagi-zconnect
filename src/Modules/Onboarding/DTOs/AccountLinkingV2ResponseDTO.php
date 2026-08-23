<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class AccountLinkingV2ResponseDTO
{
    public function __construct(
        public bool $success,
        public string $responseCode,
        public ?string $merchantType = null,
        public ?string $traceNo = null,
        public ?string $companyName = null,
        public ?string $dateTime = null,
        public ?string $accountTitle = null,
        public ?string $accountType = null,
        public ?array $responseDetails = null,
        public ?string $message = null,
        public ?string $errorCode = null,
        public array $originalResponse = []
    ) {
        $this->responseDetails = $responseDetails ?? [];
    }

    public static function fromApiResponse(array $response): self
    {
        if (isset($response['LinkAccountResponse'])) {
            $linkAccountResponse = $response['LinkAccountResponse'];

            $responseCode = (string) ($linkAccountResponse['ResponseCode'] ?? $linkAccountResponse['responseCode'] ?? '01');
            $success = $responseCode === '00';
            $responseDetails = $linkAccountResponse['ResponseDetails'] ?? $linkAccountResponse['responseDetails'] ?? [];

            return new self(
                success: $success,
                responseCode: $responseCode,
                merchantType: $linkAccountResponse['MerchantType'] ?? $linkAccountResponse['merchantType'] ?? null,
                traceNo: $linkAccountResponse['TraceNo'] ?? $linkAccountResponse['traceNo'] ?? null,
                companyName: $linkAccountResponse['CompanyName'] ?? $linkAccountResponse['companyName'] ?? null,
                dateTime: $linkAccountResponse['DateTime'] ?? $linkAccountResponse['dateTime'] ?? null,
                accountTitle: $linkAccountResponse['AccountTitle'] ?? $linkAccountResponse['accountTitle'] ?? null,
                accountType: $linkAccountResponse['AccountType'] ?? $linkAccountResponse['accountType'] ?? null,
                responseDetails: is_array($responseDetails) ? $responseDetails : [$responseDetails],
                message: $success
                    ? (is_array($responseDetails) ? ($responseDetails[0] ?? 'Account linked successfully') : (string) $responseDetails)
                    : (is_array($responseDetails) ? ($responseDetails[0] ?? 'Account linking failed') : (string) $responseDetails),
                originalResponse: $response
            );
        }

        return new self(
            success: false,
            responseCode: '',
            message: $response['messages'] ?? 'Unknown error',
            errorCode: isset($response['errorcode']) ? (string) $response['errorcode'] : null,
            responseDetails: isset($response['messages']) ? [(string) $response['messages']] : [],
            originalResponse: $response
        );
    }

    public static function fromErrorResponse(array $error, ?string $errorCode = null): self
    {
        return new self(
            success: false,
            responseCode: '01',
            message: $error['messages'] ?? $error['message'] ?? 'Account linking failed',
            errorCode: $errorCode ?? (isset($error['errorcode']) ? (string) $error['errorcode'] : null),
            responseDetails: isset($error['messages']) ? [(string) $error['messages']] : []
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'response_code' => $this->responseCode,
            'merchant_type' => $this->merchantType,
            'trace_no' => $this->traceNo,
            'company_name' => $this->companyName,
            'date_time' => $this->dateTime,
            'account_title' => $this->accountTitle,
            'account_type' => $this->accountType,
            'response_details' => $this->responseDetails,
            'message' => $this->message,
            'error_code' => $this->errorCode,
        ];
    }
}
