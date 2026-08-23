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
        public ?string $hashData = null,
        public ?string $message = null,
        public ?string $errorCode = null,
        public array $originalResponse = []
    ) {
    }

    /**
     * Create DTO from API response array.
     */
    public static function fromArray(array $data): self
    {
        return self::fromApiResponse($data);
    }

    /**
     * Create DTO from API response.
     */
    public static function fromApiResponse(array $response): self
    {
        $hasWrapper = isset($response['accountInfoRes']) && is_array($response['accountInfoRes']);
        $isGatewayError = isset($response['messages']) || isset($response['errorcode']);

        if ($isGatewayError && ! $hasWrapper) {
            $errorCode = isset($response['errorcode']) ? (string) $response['errorcode'] : null;

            return new self(
                success: false,
                responseCode: $errorCode ?? (string) ($response['ResponseCode'] ?? ''),
                message: is_string($response['messages'] ?? null)
                    ? $response['messages']
                    : ($response['ResponseDescription'] ?? 'Unknown error'),
                errorCode: $errorCode,
                originalResponse: $response
            );
        }

        $data = $hasWrapper ? $response['accountInfoRes'] : $response;
        $responseCode = (string) ($data['ResponseCode'] ?? '');
        $responseDescription = $data['ResponseDescription'] ?? null;

        return new self(
            success: $responseCode === '00',
            responseCode: $responseCode,
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
            responseDescription: $responseDescription,
            hashData: $data['HashData'] ?? null,
            message: $responseDescription,
            errorCode: $responseCode !== '00' && $responseCode !== '' ? $responseCode : null,
            originalResponse: $response
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
            'hash_data' => $this->hashData,
            'message' => $this->message,
            'error_code' => $this->errorCode,
        ];
    }
}
