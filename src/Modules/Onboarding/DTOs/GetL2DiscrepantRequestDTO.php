<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class GetL2DiscrepantRequestDTO
{
    public function __construct(
        public string $mobileNo,
        public string $cnic,
        public string $dateTime,
        public string $rrn,
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

    /**
     * Validate the DTO data.
     *
     * @throws InvalidArgumentException
     */
    protected function validate(): void
    {
        if (strlen($this->mobileNo) !== 11) {
            throw new InvalidArgumentException('Mobile number must be exactly 11 characters');
        }

        if (strlen($this->cnic) !== 13) {
            throw new InvalidArgumentException('CNIC must be exactly 13 characters');
        }

        if (strlen($this->dateTime) !== 14) {
             throw new InvalidArgumentException('DateTime must be exactly 14 characters (YYYYMMDDHHMMSS)');
        }

        if (empty($this->rrn)) {
            throw new InvalidArgumentException('RRN cannot be empty');
        }
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return [
            'getL2AccountUpgradeDiscrepantReq' => [
                'MobileNo' => $this->mobileNo,
                'Cnic' => $this->cnic,
                'DateTime' => $this->dateTime,
                'Rrn' => $this->rrn,
                'ChannelId' => $this->channelId,
                'TerminalId' => $this->terminalId,
                'Reserved1' => $this->reserved1,
                'Reserved2' => $this->reserved2,
                'Reserved3' => $this->reserved3,
                'Reserved4' => $this->reserved4,
                'Reserved5' => $this->reserved5,
            ],
        ];
    }
}
