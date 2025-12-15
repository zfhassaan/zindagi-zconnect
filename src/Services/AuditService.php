<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Services;

use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Repositories\Contracts\AuditLogRepositoryInterface;

class AuditService implements AuditServiceInterface
{
    protected bool $enabled;

    public function __construct(
        protected AuditLogRepositoryInterface $repository
    ) {
        $this->enabled = config('zindagi-zconnect.audit.enabled', true);
    }

    /**
     * Create an audit log entry.
     */
    public function log(
        string $action,
        string $module,
        array $data = [],
        ?string $userId = null,
        ?string $referenceId = null
    ): void {
        if (!$this->enabled) {
            return;
        }

        $this->repository->create([
            'action' => $action,
            'module' => $module,
            'data' => $data,
            'user_id' => $userId ?? auth()->id(),
            'reference_id' => $referenceId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Get audit logs.
     */
    public function getLogs(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        return $this->repository->getLogs($filters, $limit, $offset);
    }
}

