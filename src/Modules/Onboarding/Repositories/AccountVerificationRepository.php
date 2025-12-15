<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories;

use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountVerification;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;

class AccountVerificationRepository implements AccountVerificationRepositoryInterface
{
    /**
     * Create a new account verification record.
     */
    public function create(array $data): AccountVerification
    {
        return AccountVerification::create($data);
    }

    /**
     * Find account verification by trace number.
     */
    public function findByTraceNo(string $traceNo): ?AccountVerification
    {
        return AccountVerification::where('trace_no', $traceNo)->first();
    }

    /**
     * Find account verification by CNIC.
     */
    public function findByCnic(string $cnic): ?AccountVerification
    {
        return AccountVerification::where('cnic', $cnic)->latest()->first();
    }
}

