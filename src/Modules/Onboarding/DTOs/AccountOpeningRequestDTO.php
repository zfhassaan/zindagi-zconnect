<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use Carbon\Carbon;

class AccountOpeningRequestDTO
{
    public function __construct(
        public string $cnic,
        public string $mobileNo,
        public string $emailId,
        public string $cnicIssuanceDate,
        public string $mobileNetwork,
        public ?string $merchantType = null,
        public ?string $traceNo = null,
        public ?string $dateTime = null,
        public ?string $companyName = null
    ) {
        // Set defaults from config if not provided
        $config = config('zindagi-zconnect.modules.onboarding.account_opening', []);
        
        $this->merchantType = $merchantType ?? $config['merchant_type'] ?? '0088';
        $this->traceNo = $traceNo ?? $this->generateTraceNo();
        $this->dateTime = $dateTime ?? Carbon::now()->format('YmdHis');
        $this->companyName = $companyName ?? $config['company_name'] ?? 'NOVA';
    }

    /**
     * Create DTO from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            cnic: $data['cnic'] ?? $data['CNIC'] ?? $data['Cnic'] ?? '',
            mobileNo: $data['mobile_no'] ?? $data['MobileNo'] ?? $data['mobile_number'] ?? '',
            emailId: $data['email_id'] ?? $data['EmailId'] ?? $data['email'] ?? '',
            cnicIssuanceDate: $data['cnic_issuance_date'] ?? $data['CnicIssuanceDate'] ?? '',
            mobileNetwork: $data['mobile_network'] ?? $data['MobileNetwork'] ?? '',
            merchantType: $data['merchant_type'] ?? $data['MerchantType'] ?? null,
            traceNo: $data['trace_no'] ?? $data['TraceNo'] ?? null,
            dateTime: $data['date_time'] ?? $data['DateTime'] ?? null,
            companyName: $data['company_name'] ?? $data['CompanyName'] ?? null
        );
    }

    /**
     * Convert to API request format.
     */
    public function toApiRequest(): array
    {
        return [
            'AccountOpeningRequest' => [
                'MerchantType' => $this->merchantType,
                'TraceNo' => $this->traceNo,
                'CNIC' => $this->cnic,
                'MobileNo' => $this->mobileNo,
                'CompanyName' => $this->companyName,
                'DateTime' => $this->dateTime,
                'CnicIssuanceDate' => $this->cnicIssuanceDate,
                'MobileNetwork' => $this->mobileNetwork,
                'EmailId' => $this->emailId,
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
            'email_id' => $this->emailId,
            'cnic_issuance_date' => $this->cnicIssuanceDate,
            'mobile_network' => $this->mobileNetwork,
            'merchant_type' => $this->merchantType,
            'trace_no' => $this->traceNo,
            'date_time' => $this->dateTime,
            'company_name' => $this->companyName,
        ];
    }

    /**
     * Generate trace number.
     */
    private function generateTraceNo(): string
    {
        return str_pad((string) (time() % 1000000), 6, '0', STR_PAD_LEFT);
    }
}

