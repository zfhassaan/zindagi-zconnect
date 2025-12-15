<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class AccountVerificationResponseDTO
{
    public function __construct(
        public bool $success,
        public string $responseCode,
        public ?string $merchantType = null,
        public ?string $traceNo = null,
        public ?string $companyName = null,
        public ?string $dateTime = null,
        public ?string $accountStatus = null,
        public ?string $accountTitle = null,
        public ?string $accountType = null,
        public ?string $cnic = null,
        public ?string $isPinSet = null,
        public ?string $mobileNumber = null,
        public ?array $responseDetails = null,
        public ?string $message = null,
        public ?string $errorCode = null
    ) {
    }

    /**
     * Create DTO from API response.
     */
    public static function fromApiResponse(array $response): self
    {
        // Handle success response
        if (isset($response['VerifyAccLinkAccResponse'])) {
            $data = $response['VerifyAccLinkAccResponse'];
            
            return new self(
                success: ($data['ResponseCode'] ?? '') === '00',
                responseCode: $data['ResponseCode'] ?? '',
                merchantType: $data['MerchantType'] ?? null,
                traceNo: $data['TraceNo'] ?? null,
                companyName: $data['CompanyName'] ?? null,
                dateTime: $data['DateTime'] ?? null,
                accountStatus: $data['AccountStatus'] ?? null,
                accountTitle: $data['AccountTitle'] ?? null,
                accountType: $data['AccountType'] ?? null,
                cnic: $data['Cnic'] ?? null,
                isPinSet: $data['IsPinSet'] ?? null,
                mobileNumber: $data['MobileNumber'] ?? null,
                responseDetails: $data['ResponseDetails'] ?? null,
                message: $data['ResponseDetails'][0] ?? null
            );
        }

        // Handle error response
        return new self(
            success: false,
            responseCode: '',
            message: $response['messages'] ?? 'Unknown error',
            errorCode: $response['errorcode'] ?? null
        );
    }

    /**
     * Check if account exists.
     */
    public function accountExists(): bool
    {
        return $this->success && $this->accountStatus === '1';
    }

    /**
     * Check if PIN is set.
     */
    public function isPinSet(): bool
    {
        return $this->isPinSet === '1' || $this->isPinSet === '00';
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
            'account_status' => $this->accountStatus,
            'account_title' => $this->accountTitle,
            'account_type' => $this->accountType,
            'cnic' => $this->cnic,
            'is_pin_set' => $this->isPinSet,
            'mobile_number' => $this->mobileNumber,
            'response_details' => $this->responseDetails,
            'message' => $this->message,
            'error_code' => $this->errorCode,
            'account_exists' => $this->accountExists(),
        ];
    }
}

