<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountVerification;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationResponseDTO;

class AccountVerified
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public AccountVerification $verification,
        public AccountVerificationResponseDTO $response
    ) {
    }
}

