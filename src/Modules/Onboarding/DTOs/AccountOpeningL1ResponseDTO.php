<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class AccountOpeningL1ResponseDTO
{
    public function __construct(
        public bool $success,
        public string $responseCode,
        public string $message,
        public ?string $processingCode = null,
        public ?string $merchantType = null,
        public ?string $traceNo = null,
        public ?string $companyName = null,
        public ?string $dateTime = null,
        public ?string $citizenNo = null,
        public ?string $consumerName = null,
        public ?string $accountTitle = null,
        public ?string $birthPlace = null,
        public ?string $presentAddress = null,
        public ?string $dateOfBirth = null,
        public array $originalResponse = []
    ) {}

    /**
     * Create DTO from API response array.
     */
    public static function fromArray(array $data): self
    {
        $response = $data['AccOpenL1Response'] ?? [];
        $responseCode = $response['ResponseCode'] ?? '';
        $responseDetails = $response['ResponseDetails'] ?? [];
        $message = is_array($responseDetails) ? implode(', ', $responseDetails) : (string) $responseDetails;

        return new self(
            success: $responseCode === '00',
            responseCode: $responseCode,
            message: $message,
            processingCode: $response['processingCode'] ?? null,
            merchantType: $response['MerchantType'] ?? null,
            traceNo: $response['TraceNo'] ?? null,
            companyName: $response['CompanyName'] ?? null,
            dateTime: $response['DateTime'] ?? null,
            citizenNo: $response['citizenNo'] ?? null,
            consumerName: $response['consumerName'] ?? null,
            accountTitle: $response['accountTitle'] ?? null,
            birthPlace: $response['birthPlace'] ?? null,
            presentAddress: $response['presentAddress'] ?? null,
            dateOfBirth: $response['dateOfBirth'] ?? null,
            originalResponse: $data
        );
    }
}
