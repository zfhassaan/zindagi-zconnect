<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountLinking;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingV2ResponseDTO;

class AccountLinked
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public AccountLinking $linking,
        public AccountLinkingResponseDTO|AccountLinkingV2ResponseDTO $response
    ) {
    }
}

