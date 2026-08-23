<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class L2AccountUpgradeRequestDTO
{
    public function __construct(
        public string $mobileNumber,
        public string $dateTime,
        public string $rrn,
        public string $accountId,
        public string $cnic,
        public string $fingerTemplate,
        public string $fingerIndex = '1',
        public string $templateType = '0',
        public string $transactionId = '',
        public string $termCondition = '',
        public string $channelId = 'NOVA',
        public string $terminalId = 'NOVA',
        public string $reserved1 = '',
        public string $reserved2 = '',
        public string $reserved3 = '',
        public string $reserved4 = '',
        public string $reserved5 = '',
        public string $reserved6 = '',
        public string $reserved7 = '',
        public string $reserved8 = '',
        public string $reserved9 = '',
        public string $reserved10 = ''
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            mobileNumber: $data['mobile_number'] ?? $data['MobileNumber'] ?? '',
            dateTime: $data['date_time'] ?? $data['DateTime'] ?? '',
            rrn: $data['rrn'] ?? $data['Rrn'] ?? $data['RRN'] ?? '',
            accountId: $data['account_id'] ?? $data['AccountID'] ?? $data['AccountId'] ?? '',
            cnic: $data['cnic'] ?? $data['Cnic'] ?? $data['CNIC'] ?? '',
            fingerTemplate: $data['finger_template'] ?? $data['FingerTemplate'] ?? '',
            fingerIndex: $data['finger_index'] ?? $data['FingerIndex'] ?? '1',
            templateType: $data['template_type'] ?? $data['TemplateType'] ?? '0',
            transactionId: $data['transaction_id'] ?? $data['TransactionId'] ?? '',
            termCondition: $data['term_condition'] ?? $data['TermCondition'] ?? '',
            channelId: $data['channel_id'] ?? $data['ChannelId'] ?? 'NOVA',
            terminalId: $data['terminal_id'] ?? $data['TerminalId'] ?? 'NOVA',
            reserved1: $data['reserved1'] ?? $data['Reserved1'] ?? '',
            reserved2: $data['reserved2'] ?? $data['Reserved2'] ?? '',
            reserved3: $data['reserved3'] ?? $data['Reserved3'] ?? '',
            reserved4: $data['reserved4'] ?? $data['Reserved4'] ?? '',
            reserved5: $data['reserved5'] ?? $data['Reserved5'] ?? '',
            reserved6: $data['reserved6'] ?? $data['Reserved6'] ?? '',
            reserved7: $data['reserved7'] ?? $data['Reserved7'] ?? '',
            reserved8: $data['reserved8'] ?? $data['Reserved8'] ?? '',
            reserved9: $data['reserved9'] ?? $data['Reserved9'] ?? '',
            reserved10: $data['reserved10'] ?? $data['Reserved10'] ?? '',
        );
    }

    protected function validate(): void
    {
        if (strlen($this->mobileNumber) !== 11) {
            throw new InvalidArgumentException('Mobile number must be exactly 11 characters');
        }

        if (strlen($this->dateTime) !== 14) {
            throw new InvalidArgumentException('DateTime must be exactly 14 characters (YYYYMMDDHHMMSS)');
        }

        if (empty($this->rrn)) {
            throw new InvalidArgumentException('RRN cannot be empty');
        }

        if (empty($this->accountId)) {
            throw new InvalidArgumentException('Account ID cannot be empty');
        }

        if (strlen($this->cnic) !== 13) {
            throw new InvalidArgumentException('CNIC must be exactly 13 characters');
        }

        if (empty($this->fingerTemplate)) {
            throw new InvalidArgumentException('Finger template cannot be empty');
        }
    }

    public function toArray(): array
    {
        return [
            'l2AccountUpgradeReq' => [
                'MobileNumber' => $this->mobileNumber,
                'DateTime' => $this->dateTime,
                'Rrn' => $this->rrn,
                'AccountID' => $this->accountId,
                'Cnic' => $this->cnic,
                'FingerIndex' => $this->fingerIndex,
                'FingerTemplate' => $this->fingerTemplate,
                'TemplateType' => $this->templateType,
                'TransactionId' => $this->transactionId,
                'TermCondition' => $this->termCondition,
                'ChannelId' => $this->channelId,
                'TerminalId' => $this->terminalId,
                'Reserved1' => $this->reserved1,
                'Reserved2' => $this->reserved2,
                'Reserved3' => $this->reserved3,
                'Reserved4' => $this->reserved4,
                'Reserved5' => $this->reserved5,
                'Reserved6' => $this->reserved6,
                'Reserved7' => $this->reserved7,
                'Reserved8' => $this->reserved8,
                'Reserved9' => $this->reserved9,
                'Reserved10' => $this->reserved10,
            ],
        ];
    }
}
