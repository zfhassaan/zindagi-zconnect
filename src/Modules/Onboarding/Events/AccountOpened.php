<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountOpening;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountOpeningResponseDTO;

class AccountOpened
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public AccountOpening $accountOpening,
        public AccountOpeningResponseDTO $response
    ) {
    }
}

