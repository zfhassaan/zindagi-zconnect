<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Repositories;

use zfhassaan\ZindagiZconnect\Models\AuditLog;
use zfhassaan\ZindagiZconnect\Repositories\Contracts\AuditLogRepositoryInterface;

class AuditLogRepository implements AuditLogRepositoryInterface
{
    /**
     * Create a new audit log entry.
     */
    public function create(array $data)
    {
        return AuditLog::create($data);
    }

    /**
     * Get audit logs with filters.
     */
    public function getLogs(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $query = AuditLog::query();

        if (isset($filters['module'])) {
            $query->where('module', $filters['module']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['reference_id'])) {
            $query->where('reference_id', $filters['reference_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->toArray();
    }
}

