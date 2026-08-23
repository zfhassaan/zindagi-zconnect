<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class AgentCashWithdrawalInquiryRequestDTO
{
    public function __construct(
        public int $dtid,
        public int $pid,
        public string $agentMobile,
        public string $customerMobile,
        public int|float $transactionAmount,
        public int $appId = 1,
        public string $cnic = '',
        public string $paymentMode = '',
        public int $isOtpRequired = 1,
        public int $hraLinkedRequest = 1
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            dtid: (int) ($data['dtid'] ?? $data['DTID'] ?? 0),
            pid: (int) ($data['pid'] ?? $data['PID'] ?? 0),
            agentMobile: $data['agent_mobile'] ?? $data['AMOB'] ?? '',
            customerMobile: $data['customer_mobile'] ?? $data['CMOB'] ?? '',
            transactionAmount: $data['transaction_amount'] ?? $data['TXAM'] ?? 0,
            appId: (int) ($data['app_id'] ?? $data['APPID'] ?? 1),
            cnic: (string) ($data['cnic'] ?? $data['CNIC'] ?? ''),
            paymentMode: (string) ($data['payment_mode'] ?? $data['PAYMENT_MODE'] ?? ''),
            isOtpRequired: (int) ($data['is_otp_required'] ?? $data['IS_OTP_REQ'] ?? 1),
            hraLinkedRequest: (int) ($data['hra_linked_request'] ?? $data['HRA_LINKED_REQUEST'] ?? 1),
        );
    }

    protected function validate(): void
    {
        if ($this->dtid <= 0) {
            throw new InvalidArgumentException('DTID must be greater than zero');
        }

        if ($this->pid <= 0) {
            throw new InvalidArgumentException('PID must be greater than zero');
        }

        if (strlen($this->agentMobile) !== 11) {
            throw new InvalidArgumentException('Agent mobile must be exactly 11 characters');
        }

        if (strlen($this->customerMobile) !== 11) {
            throw new InvalidArgumentException('Customer mobile must be exactly 11 characters');
        }

        if ($this->transactionAmount <= 0) {
            throw new InvalidArgumentException('Transaction amount must be greater than zero');
        }

        if ($this->cnic !== '' && strlen($this->cnic) !== 13) {
            throw new InvalidArgumentException('CNIC must be empty or exactly 13 characters');
        }
    }

    public function toArray(): array
    {
        // Portal wrapper typos preserved: Cashd / WithDrawl / Iinquiry
        return [
            'agentCashdWithDrawlIinquiryReq' => [
                'DTID' => $this->dtid,
                'APPID' => $this->appId,
                'PID' => $this->pid,
                'AMOB' => $this->agentMobile,
                'CMOB' => $this->customerMobile,
                'TXAM' => $this->transactionAmount,
                'CNIC' => $this->cnic,
                'PAYMENT_MODE' => $this->paymentMode,
                'IS_OTP_REQ' => $this->isOtpRequired,
                'HRA_LINKED_REQUEST' => $this->hraLinkedRequest,
            ],
        ];
    }
}
