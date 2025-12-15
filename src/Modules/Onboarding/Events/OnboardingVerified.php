<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\Onboarding;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\OnboardingResponseDTO;

class OnboardingVerified
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Onboarding $onboarding,
        public OnboardingResponseDTO $response
    ) {
    }
}

