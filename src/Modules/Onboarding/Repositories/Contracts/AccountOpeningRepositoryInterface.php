<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts;

use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountOpening;

interface AccountOpeningRepositoryInterface
{
    /**
     * Create a new account opening record.
     */
    public function create(array $data): AccountOpening;

    /**
     * Find account opening by trace number.
     */
    public function findByTraceNo(string $traceNo): ?AccountOpening;

    /**
     * Find account opening by CNIC.
     */
    public function findByCnic(string $cnic): ?AccountOpening;
}

