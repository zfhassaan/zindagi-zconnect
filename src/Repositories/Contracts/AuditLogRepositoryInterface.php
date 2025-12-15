<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Repositories\Contracts;

interface AuditLogRepositoryInterface
{
    /**
     * Create a new audit log entry.
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Get audit logs with filters.
     *
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLogs(array $filters = [], int $limit = 50, int $offset = 0): array;
}

