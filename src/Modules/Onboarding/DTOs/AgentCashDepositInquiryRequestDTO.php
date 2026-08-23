<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class AgentCashDepositInquiryRequestDTO
{
    public function __construct(
        public int $dtid,
        public int $pid,
        public string $agentMobile,
        public string $customerMobile,
        public int|float $transactionAmount
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
    }

    public function toArray(): array
    {
        return [
            'agentCashDepositInquiryReq' => [
                'DTID' => $this->dtid,
                'PID' => $this->pid,
                'AMOB' => $this->agentMobile,
                'CMOB' => $this->customerMobile,
                'TXAM' => $this->transactionAmount,
            ],
        ];
    }
}
