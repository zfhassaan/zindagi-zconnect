<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class AgentLoginRequestDTO
{
    public function __construct(
        public string $pin,
        public string $uid,
        public string $appVersion,
        public string $os,
        public string $osVersion,
        public string $model,
        public string $vendor,
        public string $network,
        public string $udid
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            pin: $data['pin'] ?? $data['PIN'] ?? '',
            uid: $data['uid'] ?? $data['UID'] ?? '',
            appVersion: $data['app_version'] ?? $data['APPV'] ?? $data['appVersion'] ?? '',
            os: $data['os'] ?? $data['OS'] ?? '',
            osVersion: $data['os_version'] ?? $data['OSVERSION'] ?? $data['osVersion'] ?? '',
            model: $data['model'] ?? $data['MODEL'] ?? '',
            vendor: $data['vendor'] ?? $data['VENDOR'] ?? '',
            network: $data['network'] ?? $data['NETWORK'] ?? '',
            udid: $data['udid'] ?? $data['UDID'] ?? '',
        );
    }

    protected function validate(): void
    {
        if ($this->pin === '') {
            throw new InvalidArgumentException('PIN cannot be empty');
        }

        if ($this->uid === '') {
            throw new InvalidArgumentException('UID cannot be empty');
        }

        if ($this->udid === '') {
            throw new InvalidArgumentException('UDID cannot be empty');
        }
    }

    public function toArray(): array
    {
        return [
            'loginAgentReq' => [
                'PIN' => $this->pin,
                'UID' => $this->uid,
                'APPV' => $this->appVersion,
                'OS' => $this->os,
                'OSVERSION' => $this->osVersion,
                'MODEL' => $this->model,
                'VENDOR' => $this->vendor,
                'NETWORK' => $this->network,
                'UDID' => $this->udid,
            ],
        ];
    }
}
