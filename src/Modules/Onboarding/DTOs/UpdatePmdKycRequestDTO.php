<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class UpdatePmdKycRequestDTO
{
    public function __construct(
        public string $mobileNumber,
        public string $dateTime,
        public string $rrn,
        public string $accountId,
        public string $motherName,
        public string $placeOfBirth,
        public string $pmd = 'true',
        public string $kyc = 'true',
        public string $channelId = 'NOVA',
        public string $terminalId = 'NOVA',
        public string $reserved1 = '0',
        public string $reserved2 = '',
        public string $reserved3 = '',
        public string $reserved4 = '',
        public string $reserved5 = ''
    ) {
        $this->validate();
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
    }

    public function toArray(): array
    {
        return [
            'updatePmdAndKycReq' => [
                'MobileNumber' => $this->mobileNumber,
                'DateTime' => $this->dateTime,
                'Rrn' => $this->rrn,
                'ChannelId' => $this->channelId,
                'TerminalId' => $this->terminalId,
                'AccountID' => $this->accountId,
                'PMD' => $this->pmd,
                'KYC' => $this->kyc,
                'MotherName' => $this->motherName,
                'PlaceOfBirth' => $this->placeOfBirth,
                'Reserved1' => $this->reserved1,
                'Reserved2' => $this->reserved2,
                'Reserved3' => $this->reserved3,
                'Reserved4' => $this->reserved4,
                'Reserved5' => $this->reserved5,
            ],
        ];
    }
}
