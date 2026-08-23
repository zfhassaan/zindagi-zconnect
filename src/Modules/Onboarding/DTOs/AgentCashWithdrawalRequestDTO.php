<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class AgentCashWithdrawalRequestDTO
{
    public function __construct(
        public int $dtid,
        public int $pid,
        public string $pin,
        public string $customerMobile,
        public string $agentMobile,
        public string $cnic,
        public int|float $transactionAmount,
        public int|float $commissionAmount,
        public int|float $thirdPartyAmount,
        public int|float $totalAmount,
        public int $encryptionType = 1,
        public string $otpPin = '',
        public string $transactionId = '',
        public int $isOtpRequired = 1
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            dtid: (int) ($data['dtid'] ?? $data['DTID'] ?? 0),
            pid: (int) ($data['pid'] ?? $data['PID'] ?? 0),
            pin: $data['pin'] ?? $data['PIN'] ?? '',
            customerMobile: $data['customer_mobile'] ?? $data['CMOB'] ?? '',
            agentMobile: $data['agent_mobile'] ?? $data['AMOB'] ?? '',
            cnic: $data['cnic'] ?? $data['CNIC'] ?? '',
            transactionAmount: $data['transaction_amount'] ?? $data['TXAM'] ?? 0,
            commissionAmount: $data['commission_amount'] ?? $data['CAMT'] ?? 0,
            thirdPartyAmount: $data['third_party_amount'] ?? $data['TPAM'] ?? 0,
            totalAmount: $data['total_amount'] ?? $data['TAMT'] ?? 0,
            encryptionType: (int) ($data['encryption_type'] ?? $data['ENCT'] ?? 1),
            otpPin: $data['otp_pin'] ?? $data['OTPIN'] ?? '',
            transactionId: $data['transaction_id'] ?? $data['TXID'] ?? '',
            isOtpRequired: (int) ($data['is_otp_required'] ?? $data['IS_OTP_REQ'] ?? 1),
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

        if ($this->pin === '') {
            throw new InvalidArgumentException('PIN cannot be empty');
        }

        if (strlen($this->customerMobile) !== 11) {
            throw new InvalidArgumentException('Customer mobile must be exactly 11 characters');
        }

        if (strlen($this->agentMobile) !== 11) {
            throw new InvalidArgumentException('Agent mobile must be exactly 11 characters');
        }

        if (strlen($this->cnic) !== 13) {
            throw new InvalidArgumentException('CNIC must be exactly 13 characters');
        }

        if ($this->transactionAmount <= 0) {
            throw new InvalidArgumentException('Transaction amount must be greater than zero');
        }

        if ($this->totalAmount <= 0) {
            throw new InvalidArgumentException('Total amount must be greater than zero');
        }

        if ($this->isOtpRequired === 1 && $this->otpPin === '') {
            throw new InvalidArgumentException('OTPIN cannot be empty when IS_OTP_REQ is 1');
        }
    }

    public function toArray(): array
    {
        return [
            // Portal wrapper typo preserved: WithDrawl
            'agentCashdWithDrawlReq' => [
                'DTID' => $this->dtid,
                'PID' => $this->pid,
                'PIN' => $this->pin,
                'ENCT' => $this->encryptionType,
                'CMOB' => $this->customerMobile,
                'CNIC' => $this->cnic,
                'AMOB' => $this->agentMobile,
                'TXAM' => $this->transactionAmount,
                'CAMT' => $this->commissionAmount,
                'TPAM' => $this->thirdPartyAmount,
                'TAMT' => $this->totalAmount,
                'OTPIN' => $this->otpPin,
                'TXID' => $this->transactionId,
                'IS_OTP_REQ' => $this->isOtpRequired,
            ],
        ];
    }
}
