<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class AgentBillPaymentInquiryResponseDTO
{
    public function __construct(
        public bool $success,
        public ?string $id = null,
        public ?string $customerMobile = null,
        public ?string $consumer = null,
        public ?string $productName = null,
        public ?string $billAmount = null,
        public ?string $billAmountFormatted = null,
        public ?string $lateBillAmount = null,
        public ?string $lateBillAmountFormatted = null,
        public ?string $billPaid = null,
        public ?string $isOverdue = null,
        public ?string $dueDate = null,
        public ?string $dueDateFormatted = null,
        public ?string $cnic = null,
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
        $hasWrapper = isset($response['agentBillPaymentInquiryRes'])
            && is_array($response['agentBillPaymentInquiryRes']);
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

        $data = $hasWrapper ? $response['agentBillPaymentInquiryRes'] : $response;
        $id = isset($data['id']) ? (string) $data['id'] : null;

        return new self(
            success: $id !== null && $id !== '' && $id !== '-1',
            id: $id,
            customerMobile: $data['CMOB'] ?? null,
            consumer: $data['CONSUMER'] ?? null,
            productName: $data['PNAME'] ?? null,
            billAmount: isset($data['BAMT']) ? (string) $data['BAMT'] : null,
            billAmountFormatted: $data['BAMTF'] ?? null,
            lateBillAmount: isset($data['LBAMT']) ? (string) $data['LBAMT'] : null,
            lateBillAmountFormatted: $data['LBAMTF'] ?? null,
            billPaid: isset($data['BPAID']) ? (string) $data['BPAID'] : null,
            isOverdue: isset($data['ISOVERDUE']) ? (string) $data['ISOVERDUE'] : null,
            dueDate: $data['DUEDATE'] ?? null,
            dueDateFormatted: $data['DUEDATEF'] ?? null,
            cnic: $data['CNIC'] ?? null,
            message: $data['PNAME'] ?? null,
            originalResponse: $response
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'id' => $this->id,
            'customer_mobile' => $this->customerMobile,
            'consumer' => $this->consumer,
            'product_name' => $this->productName,
            'bill_amount' => $this->billAmount,
            'bill_amount_formatted' => $this->billAmountFormatted,
            'late_bill_amount' => $this->lateBillAmount,
            'late_bill_amount_formatted' => $this->lateBillAmountFormatted,
            'bill_paid' => $this->billPaid,
            'is_overdue' => $this->isOverdue,
            'due_date' => $this->dueDate,
            'due_date_formatted' => $this->dueDateFormatted,
            'cnic' => $this->cnic,
            'message' => $this->message,
            'error_code' => $this->errorCode,
        ];
    }
}
