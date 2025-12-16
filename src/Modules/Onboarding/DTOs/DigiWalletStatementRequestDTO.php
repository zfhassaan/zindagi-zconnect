<?php

declare(strict_types=1);

namespace Zindagi\ZConnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class DigiWalletStatementRequestDTO
{
    public function __construct(
        public string $transmissionDatetime,
        public string $systemsTraceAuditNumber,
        public string $timeLocalTransaction,
        public string $dateLocalTransaction,
        public string $accountNumber,
        public string $fromDate,
        public string $toDate,
        public string $merchantType = '',
    ) {}

    public function validate(): void
    {
        foreach ([
                     'transmissionDatetime',
                     'systemsTraceAuditNumber',
                     'timeLocalTransaction',
                     'dateLocalTransaction',
                     'accountNumber',
                     'fromDate',
                     'toDate',
                 ] as $field) {
            if (empty($this->{$field})) {
                throw new InvalidArgumentException("{$field} is required");
            }
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
