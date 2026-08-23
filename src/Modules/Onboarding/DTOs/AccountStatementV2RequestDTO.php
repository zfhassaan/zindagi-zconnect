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

    /**
     * Create DTO from snake_case or API field names.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            transmissionDatetime: $data['transmission_datetime'] ?? $data['TransmissionDatetime'] ?? '',
            systemsTraceAuditNumber: $data['systems_trace_audit_number'] ?? $data['SystemsTraceAuditNumber'] ?? '',
            timeLocalTransaction: $data['time_local_transaction'] ?? $data['TimeLocalTransaction'] ?? '',
            dateLocalTransaction: $data['date_local_transaction'] ?? $data['DateLocalTransaction'] ?? '',
            accountNumber: $data['account_number'] ?? $data['AccountNumber'] ?? '',
            fromDate: $data['from_date'] ?? $data['FromDate'] ?? '',
            toDate: $data['to_date'] ?? $data['ToDate'] ?? '',
            merchantType: $data['merchant_type'] ?? $data['MerchantType'] ?? '',
        );
    }

    protected function validate(): void
    {
        if (empty($this->transmissionDatetime)) {
            throw new InvalidArgumentException('Transmission Datetime cannot be empty');
        }

        if (empty($this->systemsTraceAuditNumber)) {
            throw new InvalidArgumentException('Systems Trace Audit Number cannot be empty');
        }

        if (empty($this->timeLocalTransaction)) {
            throw new InvalidArgumentException('Time Local Transaction cannot be empty');
        }

        if (empty($this->dateLocalTransaction)) {
            throw new InvalidArgumentException('Date Local Transaction cannot be empty');
        }

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
