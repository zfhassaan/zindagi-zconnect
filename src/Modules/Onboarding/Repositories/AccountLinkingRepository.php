<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories;

use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountLinking;

class AccountLinkingRepository implements AccountLinkingRepositoryInterface
{
    public function create(array $data): AccountLinking
    {
        return AccountLinking::create($data);
    }

    public function findByTraceNo(string $traceNo): ?AccountLinking
    {
        return AccountLinking::where('trace_no', $traceNo)->first();
    }

    public function findByCnic(string $cnic): ?AccountLinking
    {
        return AccountLinking::where('cnic', $cnic)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }
}

