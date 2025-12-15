<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use Carbon\Carbon;

class AccountVerificationRequestDTO
{
    public function __construct(
        public string $cnic,
        public string $mobileNo,
        public ?string $merchantType = null,
        public ?string $traceNo = null,
        public ?string $dateTime = null,
        public ?string $companyName = null,
        public ?string $reserved1 = null,
        public ?string $reserved2 = null,
        public ?string $transactionType = null
    ) {
        // Set defaults from config if not provided
        $config = config('zindagi-zconnect.modules.onboarding.account_verification', []);
        
        $this->merchantType = $merchantType ?? $config['merchant_type'] ?? '0088';
        $this->traceNo = $traceNo ?? $this->generateTraceNo();
        $this->dateTime = $dateTime ?? Carbon::now()->format('YmdHis');
        $this->companyName = $companyName ?? $config['company_name'] ?? 'NOVA';
        $this->reserved1 = $reserved1 ?? '01';
        $this->reserved2 = $reserved2 ?? '01';
        $this->transactionType = $transactionType ?? $config['transaction_type'] ?? '02';
    }

    /**
     * Create DTO from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            cnic: $data['cnic'] ?? $data['CNIC'] ?? '',
            mobileNo: $data['mobile_no'] ?? $data['MobileNo'] ?? $data['mobile_number'] ?? '',
            merchantType: $data['merchant_type'] ?? $data['MerchantType'] ?? null,
            traceNo: $data['trace_no'] ?? $data['TraceNo'] ?? null,
            dateTime: $data['date_time'] ?? $data['DateTime'] ?? null,
            companyName: $data['company_name'] ?? $data['CompanyName'] ?? null,
            reserved1: $data['reserved1'] ?? $data['Reserved1'] ?? null,
            reserved2: $data['reserved2'] ?? $data['Reserved2'] ?? null,
            transactionType: $data['transaction_type'] ?? $data['TransactionType'] ?? null
        );
    }

    /**
     * Convert to API request format.
     */
    public function toApiRequest(): array
    {
        return [
            'VerifyAccLinkAccRequest' => [
                'MerchantType' => $this->merchantType,
                'TraceNo' => $this->traceNo,
                'CNIC' => $this->cnic,
                'MobileNo' => $this->mobileNo,
                'DateTime' => $this->dateTime,
                'CompanyName' => $this->companyName,
                'Reserved1' => $this->reserved1,
                'Reserved2' => $this->reserved2,
                'TransactionType' => $this->transactionType,
            ],
        ];
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'cnic' => $this->cnic,
            'mobile_no' => $this->mobileNo,
            'merchant_type' => $this->merchantType,
            'trace_no' => $this->traceNo,
            'date_time' => $this->dateTime,
            'company_name' => $this->companyName,
            'reserved1' => $this->reserved1,
            'reserved2' => $this->reserved2,
            'transaction_type' => $this->transactionType,
        ];
    }

    /**
     * Generate a 6-digit trace number.
     */
    protected function generateTraceNo(): string
    {
        return str_pad((string) mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    }
}

