<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class AgentBillPaymentRequestDTO
{
    public function __construct(
        public int $dtid,
        public int $pid,
        public string $pin,
        public string $customerMobile,
        public string $agentMobile,
        public string $consumer,
        public int $paymentType,
        public int $billAccountId,
        public int|float $transactionAmount,
        public int $encryptionType = 1
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            dtid: (int) ($data['dtid'] ?? $data['DTID'] ?? 0),
            pid: (int) ($data['pid'] ?? $data['PID'] ?? 0),
            pin: $data['pin'] ?? $data['PIN'] ?? '',
            customerMobile: $data['customer_mobile'] ?? $data['CMOB'] ?? '',
            agentMobile: $data['agent_mobile'] ?? $data['AMOB'] ?? '',
            consumer: $data['consumer'] ?? $data['CONSUMER'] ?? '',
            paymentType: (int) ($data['payment_type'] ?? $data['PMTTYPE'] ?? 0),
            billAccountId: (int) ($data['bill_account_id'] ?? $data['BAID'] ?? 0),
            transactionAmount: $data['transaction_amount'] ?? $data['TXAM'] ?? 0,
            encryptionType: (int) ($data['encryption_type'] ?? $data['ENCT'] ?? 1),
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

        if ($this->pin === '') {
            throw new InvalidArgumentException('PIN cannot be empty');
        }

        if (strlen($this->customerMobile) !== 11) {
            throw new InvalidArgumentException('Customer mobile must be exactly 11 characters');
        }

        if (strlen($this->agentMobile) !== 11) {
            throw new InvalidArgumentException('Agent mobile must be exactly 11 characters');
        }

        if ($this->consumer === '') {
            throw new InvalidArgumentException('Consumer cannot be empty');
        }

        if ($this->paymentType <= 0) {
            throw new InvalidArgumentException('Payment type must be greater than zero');
        }

        if ($this->billAccountId <= 0) {
            throw new InvalidArgumentException('Bill account ID must be greater than zero');
        }

        if ($this->transactionAmount <= 0) {
            throw new InvalidArgumentException('Transaction amount must be greater than zero');
        }
    }

    public function toArray(): array
    {
        return [
            'agentBillPaymentReq' => [
                'DTID' => $this->dtid,
                'PID' => $this->pid,
                'PIN' => $this->pin,
                'ENCT' => $this->encryptionType,
                'CMOB' => $this->customerMobile,
                'AMOB' => $this->agentMobile,
                'CONSUMER' => $this->consumer,
                'PMTTYPE' => $this->paymentType,
                'BAID' => $this->billAccountId,
                'TXAM' => $this->transactionAmount,
            ],
        ];
    }
}
