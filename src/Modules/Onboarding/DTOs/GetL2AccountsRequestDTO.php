<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class GetL2AccountsRequestDTO
{
    public function __construct(
        public string $dateTime,
        public string $rrn,
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

    protected function validate(): void
    {
        if (strlen($this->dateTime) !== 14) {
             throw new InvalidArgumentException('DateTime must be exactly 14 characters (YYYYMMDDHHMMSS)');
        }
        if (empty($this->rrn)) {
            throw new InvalidArgumentException('RRN cannot be empty');
        }
    }

    public function toArray(): array
    {
        return [
            'level2AccountsReq' => [
                'DateTime' => $this->dateTime,
                'Rrn' => $this->rrn,
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
