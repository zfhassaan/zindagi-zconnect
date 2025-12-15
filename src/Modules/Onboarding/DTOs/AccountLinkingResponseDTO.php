<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class AccountLinkingResponseDTO
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
        public ?string $errorCode = null
    ) {
        $this->responseDetails = $responseDetails ?? [];
    }

    /**
     * Create DTO from API response.
     */
    public static function fromApiResponse(array $response): self
    {
        // Handle success response
        if (isset($response['LinkAccountResponse'])) {
            $linkAccountResponse = $response['LinkAccountResponse'];
            
            $responseCode = $linkAccountResponse['ResponseCode'] ?? '01';
            $success = $responseCode === '00';

            return new self(
                success: $success,
                responseCode: $responseCode,
                merchantType: $linkAccountResponse['MerchantType'] ?? null,
                traceNo: $linkAccountResponse['TraceNo'] ?? null,
                companyName: $linkAccountResponse['CompanyName'] ?? null,
                dateTime: $linkAccountResponse['DateTime'] ?? null,
                accountTitle: $linkAccountResponse['AccountTitle'] ?? null,
                accountType: $linkAccountResponse['AccountType'] ?? null,
                responseDetails: $linkAccountResponse['ResponseDetails'] ?? [],
                message: $success 
                    ? ($linkAccountResponse['ResponseDetails'][0] ?? 'Account linked successfully')
                    : ($linkAccountResponse['ResponseDetails'][0] ?? 'Account linking failed')
            );
        }

        // Handle error response
        return new self(
            success: false,
            responseCode: '',
            message: $response['messages'] ?? 'Unknown error',
            errorCode: $response['errorcode'] ?? null,
            responseDetails: isset($response['messages']) ? [$response['messages']] : []
        );
    }

    /**
     * Create DTO from error response.
     */
    public static function fromErrorResponse(array $error, ?string $errorCode = null): self
    {
        return new self(
            success: false,
            responseCode: '01',
            message: $error['messages'] ?? $error['message'] ?? 'Account linking failed',
            errorCode: $errorCode ?? $error['errorcode'] ?? null,
            responseDetails: isset($error['messages']) ? [$error['messages']] : []
        );
    }

    /**
     * Convert to array.
     */
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

