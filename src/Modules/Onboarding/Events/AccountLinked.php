<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountLinking;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingResponseDTO;

class AccountLinked
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public AccountLinking $linking,
        public AccountLinkingResponseDTO $response
    ) {
    }
}

