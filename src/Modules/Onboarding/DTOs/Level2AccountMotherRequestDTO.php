<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class Level2AccountMotherRequestDTO
{
    public function __construct(
        public string $mobileNumber
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
        if (strlen($this->mobileNumber) !== 11) {
            throw new InvalidArgumentException('Mobile number must be exactly 11 characters');
        }

        if (!preg_match('/^03[0-9]{9}$/', $this->mobileNumber)) {
            throw new InvalidArgumentException('Mobile number must start with 03 and contain only digits');
        }
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return [
            'level2AccountMotherReq' => [
                'mobileNumber' => $this->mobileNumber,
            ],
        ];
    }
}
