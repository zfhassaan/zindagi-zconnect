<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class L2AccountStatusRequestDTO
{
    public function __construct(
        public string $dateTime,
        public string $rrn,
        public string $mobileNo,
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

    /**
     * Validate the DTO data.
     *
     * @throws InvalidArgumentException
     */
    protected function validate(): void
    {
        if (strlen($this->dateTime) !== 12) {
             throw new InvalidArgumentException('DateTime must be exactly 12 characters');
        }

        if (strlen($this->rrn) !== 16) {
            throw new InvalidArgumentException('RRN must be exactly 16 characters');
        }

        if (strlen($this->mobileNo) !== 11) {
            throw new InvalidArgumentException('Mobile number must be exactly 11 characters');
        }

        if (empty($this->channelId)) {
            throw new InvalidArgumentException('ChannelId cannot be empty');
        }

        if (empty($this->terminalId)) {
            throw new InvalidArgumentException('TerminalId cannot be empty');
        }
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return [
            'l2AccountStatusReq' => [
                'DateTime' => $this->dateTime,
                'Rrn' => $this->rrn,
                'MobileNo' => $this->mobileNo,
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
