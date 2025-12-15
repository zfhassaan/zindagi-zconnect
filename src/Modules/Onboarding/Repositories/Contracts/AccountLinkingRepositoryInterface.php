<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts;

use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountLinking;

interface AccountLinkingRepositoryInterface
{
    /**
     * Create a new account linking record.
     */
    public function create(array $data): AccountLinking;

    /**
     * Find account linking by trace number.
     */
    public function findByTraceNo(string $traceNo): ?AccountLinking;

    /**
     * Find account linking by CNIC.
     */
    public function findByCnic(string $cnic): ?AccountLinking;
}

