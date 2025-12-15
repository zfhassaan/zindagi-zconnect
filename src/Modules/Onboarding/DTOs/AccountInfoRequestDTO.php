<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class AccountInfoRequestDTO
{
    public function __construct(
        public string $mobileNumber,
        public string $dateTime,
        public string $rrn,
        public string $channelId = 'Lending',
        public string $terminalId = 'Lending'
    ) {
        $this->validate();
    }

    /**
     * Validate DTO data.
     *
     * @throws InvalidArgumentException
     */
    protected function validate(): void
    {
        if (strlen($this->mobileNumber) !== 11) {
            throw new InvalidArgumentException('MobileNumber must be exactly 11 characters');
        }

        if (strlen($this->dateTime) !== 14) {
            throw new InvalidArgumentException('DateTime must be exactly 14 characters (YYYYMMDDHHmmss)');
        }

        if (strlen($this->rrn) !== 14 && strlen($this->rrn) !== 16) {
            throw new InvalidArgumentException('Rrn must be 14 or 16 characters');
        }

        if (empty($this->channelId)) {
            throw new InvalidArgumentException('ChannelId cannot be empty');
        }

        if (empty($this->terminalId)) {
            throw new InvalidArgumentException('TerminalId cannot be empty');
        }
    }

    /**
     * Convert DTO to API request array.
     */
    public function toArray(): array
    {
        return [
            'accountInfoReq' => [
                'MobileNumber' => $this->mobileNumber,
                'DateTime' => $this->dateTime,
                'Rrn' => $this->rrn,
                'ChannelId' => $this->channelId,
                'TerminalId' => $this->terminalId,
            ],
        ];
    }
}
