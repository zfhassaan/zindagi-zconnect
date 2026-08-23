<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use Carbon\Carbon;

class AccountLinkingV2RequestDTO
{
    public function __construct(
        public string $cnic,
        public string $mobileNo,
        public string $mPin,
        public string $confirmMpin,
        public ?string $merchantType = null,
        public ?string $traceNo = null,
        public ?string $dateTime = null,
        public ?string $companyName = null,
        public ?string $transactionType = null,
        public ?string $reserved1 = null,
        public ?string $otpPin = null
    ) {
        $config = config('zindagi-zconnect.modules.onboarding.account_linking_v2', []);

        $this->merchantType = $merchantType ?? $config['merchant_type'] ?? '0088';
        $this->traceNo = $traceNo ?? $this->generateTraceNo();
        $this->dateTime = $dateTime ?? Carbon::now()->format('YmdHis');
        $this->companyName = $companyName ?? $config['company_name'] ?? 'NOVA';
        $this->transactionType = $transactionType ?? $config['transaction_type'] ?? '01';
        $this->reserved1 = $reserved1 ?? $config['reserved1'] ?? '01';
    }

    public static function fromArray(array $data): self
    {
        return new self(
            cnic: $data['cnic'] ?? $data['CNIC'] ?? $data['Cnic'] ?? '',
            mobileNo: $data['mobile_no'] ?? $data['MobileNo'] ?? $data['mobile_number'] ?? '',
            mPin: $data['m_pin'] ?? $data['mPin'] ?? $data['MPin'] ?? '',
            confirmMpin: $data['confirm_mpin'] ?? $data['confirmMpin'] ?? $data['ConfirmMpin'] ?? '',
            merchantType: $data['merchant_type'] ?? $data['MerchantType'] ?? null,
            traceNo: $data['trace_no'] ?? $data['TraceNo'] ?? null,
            dateTime: $data['date_time'] ?? $data['DateTime'] ?? null,
            companyName: $data['company_name'] ?? $data['CompanyName'] ?? null,
            transactionType: $data['transaction_type'] ?? $data['TransactionType'] ?? null,
            reserved1: $data['reserved1'] ?? $data['Reserved1'] ?? null,
            otpPin: $data['otp_pin'] ?? $data['OtpPin'] ?? null
        );
    }

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
                'mPin' => $this->mPin,
                'confirmMpin' => $this->confirmMpin,
            ],
        ];

        if ($this->otpPin !== null && $this->otpPin !== '') {
            $request['LinkAccountRequest']['OtpPin'] = $this->otpPin;
        }

        return $request;
    }

    public function toArray(): array
    {
        return [
            'cnic' => $this->cnic,
            'mobile_no' => $this->mobileNo,
            'm_pin' => $this->mPin,
            'confirm_mpin' => $this->confirmMpin,
            'merchant_type' => $this->merchantType,
            'trace_no' => $this->traceNo,
            'date_time' => $this->dateTime,
            'company_name' => $this->companyName,
            'transaction_type' => $this->transactionType,
            'reserved1' => $this->reserved1,
            'otp_pin' => $this->otpPin,
        ];
    }

    protected function generateTraceNo(): string
    {
        return str_pad((string) mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    }
}
