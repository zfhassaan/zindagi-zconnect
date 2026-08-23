<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class AgentOtpVerificationRequestDTO
{
    public function __construct(
        public int $dtid,
        public string $pin,
        public string $newPin,
        public string $confirmPin,
        public int $encryptionType = 1
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            dtid: (int) ($data['dtid'] ?? $data['DTID'] ?? 0),
            pin: $data['pin'] ?? $data['PIN'] ?? '',
            newPin: $data['new_pin'] ?? $data['NPIN'] ?? '',
            confirmPin: $data['confirm_pin'] ?? $data['CPIN'] ?? '',
            encryptionType: (int) ($data['encryption_type'] ?? $data['ENCT'] ?? 1),
        );
    }

    protected function validate(): void
    {
        if ($this->dtid <= 0) {
            throw new InvalidArgumentException('DTID must be greater than zero');
        }

        if ($this->pin === '') {
            throw new InvalidArgumentException('PIN cannot be empty');
        }

        if ($this->newPin === '') {
            throw new InvalidArgumentException('NPIN cannot be empty');
        }

        if ($this->confirmPin === '') {
            throw new InvalidArgumentException('CPIN cannot be empty');
        }
    }

    public function toArray(): array
    {
        return [
            'agentOtpVerificationReq' => [
                'DTID' => $this->dtid,
                'PIN' => $this->pin,
                'NPIN' => $this->newPin,
                'CPIN' => $this->confirmPin,
                'ENCT' => $this->encryptionType,
            ],
        ];
    }
}
