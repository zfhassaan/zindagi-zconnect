<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class AccountStatementV2RequestDTO
{
    public function __construct(
        public string $transmissionDatetime,
        public string $systemsTraceAuditNumber,
        public string $timeLocalTransaction,
        public string $dateLocalTransaction,
        public string $accountNumber,
        public string $fromDate,
        public string $toDate,
        public string $merchantType = ''
    ) {
        $this->validate();
    }

    protected function validate(): void
    {
        if (empty($this->accountNumber)) {
            throw new InvalidArgumentException('Account Number cannot be empty');
        }
        if (empty($this->fromDate)) {
            throw new InvalidArgumentException('From Date cannot be empty');
        }
        if (empty($this->toDate)) {
            throw new InvalidArgumentException('To Date cannot be empty');
        }
    }

    public function toArray(): array
    {
        return [
            'AccountStatementReq' => [
                'TransmissionDatetime' => $this->transmissionDatetime,
                'SystemsTraceAuditNumber' => $this->systemsTraceAuditNumber,
                'TimeLocalTransaction' => $this->timeLocalTransaction,
                'DateLocalTransaction' => $this->dateLocalTransaction,
                'MerchantType' => $this->merchantType,
                'AccountNumber' => $this->accountNumber,
                'FromDate' => $this->fromDate,
                'ToDate' => $this->toDate,
            ],
        ];
    }
}
