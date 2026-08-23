<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class AgentCashDepositInquiryResponseDTO
{
    public function __construct(
        public bool $success,
        public ?string $id = null,
        public ?string $customerMobile = null,
        public ?string $totalAmount = null,
        public ?string $transactionAmount = null,
        public ?string $cnic = null,
        public ?string $commissionAmount = null,
        public ?string $thirdPartyAmountFormatted = null,
        public ?string $totalAmountFormatted = null,
        public ?string $transactionAmountFormatted = null,
        public ?string $name = null,
        public ?string $commissionAmountFormatted = null,
        public ?string $thirdPartyAmount = null,
        public ?string $message = null,
        public ?string $errorCode = null,
        public array $originalResponse = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return self::fromApiResponse($data);
    }

    public static function fromApiResponse(array $response): self
    {
        $hasWrapper = isset($response['agentCashDepositInquiryRes'])
            && is_array($response['agentCashDepositInquiryRes']);
        $isGatewayError = isset($response['messages']) || isset($response['errorcode']);

        if ($isGatewayError && ! $hasWrapper) {
            $errorCode = isset($response['errorcode']) ? (string) $response['errorcode'] : null;
            $message = is_string($response['messages'] ?? null)
                ? $response['messages']
                : 'Unknown error';

            return new self(
                success: false,
                message: $message,
                errorCode: $errorCode,
                originalResponse: $response
            );
        }

        $data = $hasWrapper ? $response['agentCashDepositInquiryRes'] : $response;
        $id = isset($data['id']) ? (string) $data['id'] : null;

        return new self(
            success: $id !== null && $id !== '' && $id !== '-1',
            id: $id,
            customerMobile: $data['CMOB'] ?? null,
            totalAmount: isset($data['TAMT']) ? (string) $data['TAMT'] : null,
            transactionAmount: isset($data['TXAM']) ? (string) $data['TXAM'] : null,
            cnic: $data['CNIC'] ?? null,
            commissionAmount: isset($data['CAMT']) ? (string) $data['CAMT'] : null,
            thirdPartyAmountFormatted: $data['TPAMF'] ?? null,
            totalAmountFormatted: $data['TAMTF'] ?? null,
            transactionAmountFormatted: $data['TXAMF'] ?? null,
            name: $data['NAME'] ?? null,
            commissionAmountFormatted: $data['CAMTF'] ?? null,
            thirdPartyAmount: isset($data['TPAM']) ? (string) $data['TPAM'] : null,
            message: $data['NAME'] ?? null,
            originalResponse: $response
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'id' => $this->id,
            'customer_mobile' => $this->customerMobile,
            'total_amount' => $this->totalAmount,
            'transaction_amount' => $this->transactionAmount,
            'cnic' => $this->cnic,
            'commission_amount' => $this->commissionAmount,
            'third_party_amount_formatted' => $this->thirdPartyAmountFormatted,
            'total_amount_formatted' => $this->totalAmountFormatted,
            'transaction_amount_formatted' => $this->transactionAmountFormatted,
            'name' => $this->name,
            'commission_amount_formatted' => $this->commissionAmountFormatted,
            'third_party_amount' => $this->thirdPartyAmount,
            'message' => $this->message,
            'error_code' => $this->errorCode,
        ];
    }
}
