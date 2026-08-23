<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class AgentAccountVerificationRequestDTO
{
    public function __construct(
        public int $dtid,
        public string $customerMobile,
        public string $cnic,
        public int $agentId,
        public int $pid,
        public int $isReceiveCash = 0,
        public int $isHra = 0,
        public int $isUpgrade = 0,
        public string $segmentId = ''
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            dtid: (int) ($data['dtid'] ?? $data['DTID'] ?? 0),
            customerMobile: $data['customer_mobile'] ?? $data['CMOB'] ?? '',
            cnic: $data['cnic'] ?? $data['CNIC'] ?? '',
            agentId: (int) ($data['agent_id'] ?? $data['AGENT_ID'] ?? 0),
            pid: (int) ($data['pid'] ?? $data['PID'] ?? 0),
            isReceiveCash: (int) ($data['is_receive_cash'] ?? $data['IS_RECEIVE_CASH'] ?? 0),
            isHra: (int) ($data['is_hra'] ?? $data['IS_HRA'] ?? 0),
            isUpgrade: (int) ($data['is_upgrade'] ?? $data['IS_UPGRADE'] ?? 0),
            segmentId: (string) ($data['segment_id'] ?? $data['SEGMENT_ID'] ?? ''),
        );
    }

    protected function validate(): void
    {
        if ($this->dtid <= 0) {
            throw new InvalidArgumentException('DTID must be greater than zero');
        }

        if (strlen($this->customerMobile) !== 11) {
            throw new InvalidArgumentException('Customer mobile must be exactly 11 characters');
        }

        if (strlen($this->cnic) !== 13) {
            throw new InvalidArgumentException('CNIC must be exactly 13 characters');
        }

        if ($this->agentId <= 0) {
            throw new InvalidArgumentException('AGENT_ID must be greater than zero');
        }

        if ($this->pid <= 0) {
            throw new InvalidArgumentException('PID must be greater than zero');
        }
    }

    public function toArray(): array
    {
        return [
            'accountVerificationAgentReq' => [
                'DTID' => $this->dtid,
                'CMOB' => $this->customerMobile,
                'CNIC' => $this->cnic,
                'IS_RECEIVE_CASH' => $this->isReceiveCash,
                'IS_HRA' => $this->isHra,
                'IS_UPGRADE' => $this->isUpgrade,
                'AGENT_ID' => $this->agentId,
                'PID' => $this->pid,
                'SEGMENT_ID' => $this->segmentId,
            ],
        ];
    }
}
