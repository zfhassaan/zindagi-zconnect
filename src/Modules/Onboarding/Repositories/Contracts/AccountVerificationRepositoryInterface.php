<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts;

use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountVerification;

interface AccountVerificationRepositoryInterface
{
    /**
     * Create a new account verification record.
     *
     * @param array $data
     * @return AccountVerification
     */
    public function create(array $data): AccountVerification;

    /**
     * Find account verification by trace number.
     *
     * @param string $traceNo
     * @return AccountVerification|null
     */
    public function findByTraceNo(string $traceNo): ?AccountVerification;

    /**
     * Find account verification by CNIC.
     *
     * @param string $cnic
     * @return AccountVerification|null
     */
    public function findByCnic(string $cnic): ?AccountVerification;
}

