<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts;

use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\Onboarding;

interface OnboardingRepositoryInterface
{
    /**
     * Create a new onboarding record.
     *
     * @param array $data
     * @return Onboarding
     */
    public function create(array $data): Onboarding;

    /**
     * Find onboarding by reference ID.
     *
     * @param string $referenceId
     * @return Onboarding|null
     */
    public function findByReferenceId(string $referenceId): ?Onboarding;

    /**
     * Find onboarding by CNIC.
     *
     * @param string $cnic
     * @return Onboarding|null
     */
    public function findByCnic(string $cnic): ?Onboarding;

    /**
     * Update onboarding by reference ID.
     *
     * @param string $referenceId
     * @param array $data
     * @return bool
     */
    public function updateByReferenceId(string $referenceId, array $data): bool;
}

