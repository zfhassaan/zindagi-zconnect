<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories;

use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountOpening;

class AccountOpeningRepository implements AccountOpeningRepositoryInterface
{
    public function create(array $data): AccountOpening
    {
        return AccountOpening::create($data);
    }

    public function findByTraceNo(string $traceNo): ?AccountOpening
    {
        return AccountOpening::where('trace_no', $traceNo)->first();
    }

    public function findByCnic(string $cnic): ?AccountOpening
    {
        return AccountOpening::where('cnic', $cnic)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }
}

