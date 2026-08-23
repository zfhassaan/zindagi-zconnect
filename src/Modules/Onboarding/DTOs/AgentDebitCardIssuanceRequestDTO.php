<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class AgentDebitCardIssuanceRequestDTO
{
    public function __construct(
        public string $cnic,
        public string $customerMobile,
        public string $cardDescription,
        public string $mailingAddress,
        public int $dtid,
        public int $appId = 1
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            cnic: $data['cnic'] ?? $data['CNIC'] ?? '',
            customerMobile: $data['customer_mobile'] ?? $data['CMOB'] ?? '',
            cardDescription: $data['card_description'] ?? $data['CARD_DESCRIPTION'] ?? '',
            mailingAddress: $data['mailing_address'] ?? $data['MAILING_ADDRESS'] ?? '',
            dtid: (int) ($data['dtid'] ?? $data['DTID'] ?? 0),
            appId: (int) ($data['app_id'] ?? $data['APPID'] ?? 1),
        );
    }

    protected function validate(): void
    {
        if (strlen($this->cnic) !== 13) {
            throw new InvalidArgumentException('CNIC must be exactly 13 characters');
        }

        if (strlen($this->customerMobile) !== 11) {
            throw new InvalidArgumentException('Customer mobile must be exactly 11 characters');
        }

        if ($this->cardDescription === '') {
            throw new InvalidArgumentException('Card description cannot be empty');
        }

        if ($this->mailingAddress === '') {
            throw new InvalidArgumentException('Mailing address cannot be empty');
        }

        if ($this->dtid <= 0) {
            throw new InvalidArgumentException('DTID must be greater than zero');
        }
    }

    public function toArray(): array
    {
        return [
            'agentDebitCardIssuanceReq' => [
                'CNIC' => $this->cnic,
                'CMOB' => $this->customerMobile,
                'CARD_DESCRIPTION' => $this->cardDescription,
                'MAILING_ADDRESS' => $this->mailingAddress,
                'APPID' => $this->appId,
                'DTID' => $this->dtid,
            ],
        ];
    }
}
