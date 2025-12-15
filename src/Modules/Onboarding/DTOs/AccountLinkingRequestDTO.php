<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use Carbon\Carbon;

class AccountLinkingRequestDTO
{
    public function __construct(
        public string $cnic,
        public string $mobileNo,
        public ?string $merchantType = null,
        public ?string $traceNo = null,
        public ?string $dateTime = null,
        public ?string $companyName = null,
        public ?string $transactionType = null,
        public ?string $reserved1 = null,
        public ?string $otpPin = null
    ) {
        // Set defaults from config if not provided
        $config = config('zindagi-zconnect.modules.onboarding.account_linking', []);
        
        $this->merchantType = $merchantType ?? $config['merchant_type'] ?? '0088';
        $this->traceNo = $traceNo ?? $this->generateTraceNo();
        $this->dateTime = $dateTime ?? Carbon::now()->format('YmdHis');
        $this->companyName = $companyName ?? $config['company_name'] ?? 'NOVA';
        $this->transactionType = $transactionType ?? $config['transaction_type'] ?? '01';
        $this->reserved1 = $reserved1 ?? $config['reserved1'] ?? '02';
    }

    /**
     * Create DTO from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            cnic: $data['cnic'] ?? $data['CNIC'] ?? $data['Cnic'] ?? '',
            mobileNo: $data['mobile_no'] ?? $data['MobileNo'] ?? $data['mobile_number'] ?? '',
            merchantType: $data['merchant_type'] ?? $data['MerchantType'] ?? null,
            traceNo: $data['trace_no'] ?? $data['TraceNo'] ?? null,
            dateTime: $data['date_time'] ?? $data['DateTime'] ?? null,
            companyName: $data['company_name'] ?? $data['CompanyName'] ?? null,
            transactionType: $data['transaction_type'] ?? $data['TransactionType'] ?? null,
            reserved1: $data['reserved1'] ?? $data['Reserved1'] ?? null,
            otpPin: $data['otp_pin'] ?? $data['OtpPin'] ?? null
        );
    }

    /**
     * Convert to API request format.
     */
    public function toApiRequest(): array
    {
        $request = [
            'LinkAccountRequest' => [
                'MerchantType' => $this->merchantType,
                'TraceNo' => $this->traceNo,
                'CompanyName' => $this->companyName,
                'DateTime' => $this->dateTime,
                'TransactionType' => $this->transactionType,
                'MobileNo' => $this->mobileNo,
                'Cnic' => $this->cnic,
                'Reserved1' => $this->reserved1,
            ],
        ];

        // OtpPin is optional, only include if provided
        if ($this->otpPin !== null && $this->otpPin !== '') {
            $request['LinkAccountRequest']['OtpPin'] = $this->otpPin;
        }

        return $request;
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
            'transaction_type' => $this->transactionType,
            'reserved1' => $this->reserved1,
            'otp_pin' => $this->otpPin,
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

