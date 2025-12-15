<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountUpgradeResponseDTO;

class AccountUpgraded
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public array $requestData,
        public AccountUpgradeResponseDTO $response
    ) {
    }
}
