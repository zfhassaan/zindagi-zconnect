<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Services\Contracts;

interface LoggingServiceInterface
{
    /**
     * Log an API request.
     *
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     * @return void
     */
    public function logRequest(string $endpoint, array $data = [], array $headers = []): void;

    /**
     * Log an API response.
     *
     * @param string $endpoint
     * @param mixed $response
     * @param int $statusCode
     * @return void
     */
    public function logResponse(string $endpoint, $response, int $statusCode): void;

    /**
     * Log an error.
     *
     * @param string $message
     * @param array $context
     * @param \Throwable|null $exception
     * @return void
     */
    public function logError(string $message, array $context = [], ?\Throwable $exception = null): void;

    /**
     * Log an info message.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function logInfo(string $message, array $context = []): void;
}

