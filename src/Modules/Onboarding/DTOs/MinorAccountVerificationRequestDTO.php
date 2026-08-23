<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class MinorAccountVerificationRequestDTO
{
    public function __construct(
        public string $rrn,
        public string $dateTime,
        public string $cnic,
        public string $issuanceDate,
        public string $mobileNumber,
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
            rrn: $data['rrn'] ?? $data['RRN'] ?? '',
            dateTime: $data['date_time'] ?? $data['DateTime'] ?? '',
            cnic: $data['cnic'] ?? $data['Cnic'] ?? '',
            issuanceDate: $data['issuance_date'] ?? $data['IssuanceDate'] ?? '',
            mobileNumber: $data['mobile_number'] ?? $data['MobileNumber'] ?? '',
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
        if (empty($this->rrn)) {
            throw new InvalidArgumentException('RRN cannot be empty');
        }

        if (empty($this->cnic)) {
            throw new InvalidArgumentException('CNIC cannot be empty');
        }

        if (empty($this->mobileNumber)) {
             throw new InvalidArgumentException('Mobile Number cannot be empty');
        }
    }

    public function toArray(): array
    {
        return [
            'minorAccountVerifyReq' => [
                'RRN' => $this->rrn,
                'DateTime' => $this->dateTime,
                'Cnic' => $this->cnic,
                'IssuanceDate' => $this->issuanceDate,
                'MobileNumber' => $this->mobileNumber,
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
