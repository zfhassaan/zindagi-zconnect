<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class AgentDeviceChangedRequestDTO
{
    public function __construct(
        public int $dtid,
        public string $pin,
        public string $udid,
        public int $userId,
        public int $userType,
        public int $action,
        public int $encryptionType = 1
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            dtid: (int) ($data['dtid'] ?? $data['DTID'] ?? 0),
            pin: $data['pin'] ?? $data['PIN'] ?? '',
            udid: $data['udid'] ?? $data['UDID'] ?? '',
            userId: (int) ($data['user_id'] ?? $data['UID'] ?? 0),
            userType: (int) ($data['user_type'] ?? $data['USTY'] ?? 0),
            action: (int) ($data['action'] ?? $data['ACTION'] ?? 0),
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

        if ($this->udid === '') {
            throw new InvalidArgumentException('UDID cannot be empty');
        }

        if ($this->userId <= 0) {
            throw new InvalidArgumentException('UID must be greater than zero');
        }

        if ($this->userType <= 0) {
            throw new InvalidArgumentException('USTY must be greater than zero');
        }
    }

    public function toArray(): array
    {
        return [
            'agentDeviceChangedReq' => [
                'DTID' => $this->dtid,
                'PIN' => $this->pin,
                'UDID' => $this->udid,
                'ENCT' => $this->encryptionType,
                'UID' => $this->userId,
                'USTY' => $this->userType,
                'ACTION' => $this->action,
            ],
        ];
    }
}
