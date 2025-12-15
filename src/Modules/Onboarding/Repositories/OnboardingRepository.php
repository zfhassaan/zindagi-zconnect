<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories;

use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\Onboarding;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;

class OnboardingRepository implements OnboardingRepositoryInterface
{
    /**
     * Create a new onboarding record.
     */
    public function create(array $data): Onboarding
    {
        return Onboarding::create($data);
    }

    /**
     * Find onboarding by reference ID.
     */
    public function findByReferenceId(string $referenceId): ?Onboarding
    {
        return Onboarding::where('reference_id', $referenceId)->first();
    }

    /**
     * Find onboarding by CNIC.
     */
    public function findByCnic(string $cnic): ?Onboarding
    {
        return Onboarding::where('cnic', $cnic)->latest()->first();
    }

    /**
     * Update onboarding by reference ID.
     */
    public function updateByReferenceId(string $referenceId, array $data): bool
    {
        return Onboarding::where('reference_id', $referenceId)->update($data) > 0;
    }
}

