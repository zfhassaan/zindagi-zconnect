<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class AccountInfoResponseDTO
{
    public function __construct(
        public bool $success,
        public string $responseCode,
        public ?string $dateOfBirth = null,
        public ?string $responseDateTime = null,
        public ?string $accountLevelCode = null,
        public ?string $email = null,
        public ?string $cnic = null,
        public ?string $segment = null,
        public ?string $rrn = null,
        public ?string $accountNumber = null,
        public ?string $accountNatureCode = null,
        public ?string $accountTitle = null,
        public ?string $accountStatusCode = null,
        public ?string $registrationTypeCode = null,
        public ?string $responseDescription = null,
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
        if (isset($response['accountInfoRes'])) {
            $data = $response['accountInfoRes'];
            
            return new self(
                success: ($data['ResponseCode'] ?? '') === '00',
                responseCode: $data['ResponseCode'] ?? '',
                dateOfBirth: $data['DateOfBirth'] ?? null,
                responseDateTime: $data['ResponseDateTime'] ?? null,
                accountLevelCode: $data['AccountLevelCode'] ?? null,
                email: $data['Email'] ?? null,
                cnic: $data['Cnic'] ?? null,
                segment: $data['Segment'] ?? null,
                rrn: $data['Rrn'] ?? null,
                accountNumber: $data['AccountNumber'] ?? null,
                accountNatureCode: $data['AccountNatureCode'] ?? null,
                accountTitle: $data['AccountTitle'] ?? null,
                accountStatusCode: $data['AccountStatusCode'] ?? null,
                registrationTypeCode: $data['RegistrationTypeCode'] ?? null,
                responseDescription: $data['ResponseDescription'] ?? null,
                message: $data['ResponseDescription'] ?? null
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
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'response_code' => $this->responseCode,
            'date_of_birth' => $this->dateOfBirth,
            'response_date_time' => $this->responseDateTime,
            'account_level_code' => $this->accountLevelCode,
            'email' => $this->email,
            'cnic' => $this->cnic,
            'segment' => $this->segment,
            'rrn' => $this->rrn,
            'account_number' => $this->accountNumber,
            'account_nature_code' => $this->accountNatureCode,
            'account_title' => $this->accountTitle,
            'account_status_code' => $this->accountStatusCode,
            'registration_type_code' => $this->registrationTypeCode,
            'response_description' => $this->responseDescription,
            'message' => $this->message,
            'error_code' => $this->errorCode,
        ];
    }
}

