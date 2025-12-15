<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Services\Contracts;

interface AuditServiceInterface
{
    /**
     * Create an audit log entry.
     *
     * @param string $action
     * @param string $module
     * @param array $data
     * @param string|null $userId
     * @param string|null $referenceId
     * @return void
     */
    public function log(
        string $action,
        string $module,
        array $data = [],
        ?string $userId = null,
        ?string $referenceId = null
    ): void;

    /**
     * Get audit logs.
     *
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLogs(array $filters = [], int $limit = 50, int $offset = 0): array;
}

