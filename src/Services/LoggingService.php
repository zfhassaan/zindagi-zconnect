<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Services;

use Illuminate\Support\Facades\Log;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;

class LoggingService implements LoggingServiceInterface
{
    protected string $channel;
    protected bool $logRequests;
    protected bool $logResponses;
    protected bool $logSensitiveData;
    protected array $sensitiveFields;

    public function __construct()
    {
        $config = config('zindagi-zconnect.logging', []);
        $this->channel = $config['channel'] ?? 'daily';
        $this->logRequests = $config['log_requests'] ?? true;
        $this->logResponses = $config['log_responses'] ?? true;
        $this->logSensitiveData = $config['log_sensitive_data'] ?? false;
        $this->sensitiveFields = $config['sensitive_fields'] ?? [];
    }

    /**
     * Log an API request.
     */
    public function logRequest(string $endpoint, array $data = [], array $headers = []): void
    {
        if (!$this->logRequests) {
            return;
        }

        $logData = [
            'endpoint' => $endpoint,
            'data' => $this->sanitizeData($data),
            'headers' => $this->sanitizeHeaders($headers),
        ];

        Log::channel($this->channel)->info('Zindagi Z-Connect API Request', $logData);
    }

    /**
     * Log an API response.
     */
    public function logResponse(string $endpoint, $response, int $statusCode): void
    {
        if (!$this->logResponses) {
            return;
        }

        $logData = [
            'endpoint' => $endpoint,
            'status_code' => $statusCode,
            'response' => $this->sanitizeData(is_array($response) ? $response : ['raw' => $response]),
        ];

        $level = $statusCode >= 400 ? 'error' : 'info';
        Log::channel($this->channel)->$level('Zindagi Z-Connect API Response', $logData);
    }

    /**
     * Log an error.
     */
    public function logError(string $message, array $context = [], ?\Throwable $exception = null): void
    {
        $logData = array_merge([
            'message' => $message,
        ], $this->sanitizeData($context));

        if ($exception) {
            $logData['exception'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        Log::channel($this->channel)->error('Zindagi Z-Connect Error', $logData);
    }

    /**
     * Log an info message.
     */
    public function logInfo(string $message, array $context = []): void
    {
        Log::channel($this->channel)->info('Zindagi Z-Connect: ' . $message, $this->sanitizeData($context));
    }

    /**
     * Sanitize data by masking sensitive fields.
     */
    protected function sanitizeData(array $data): array
    {
        if ($this->logSensitiveData) {
            return $data;
        }

        return $this->maskSensitiveFields($data);
    }

    /**
     * Mask sensitive fields in data.
     */
    protected function maskSensitiveFields(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $this->sensitiveFields)) {
                $data[$key] = $this->maskValue($value);
            } elseif (is_array($value)) {
                $data[$key] = $this->maskSensitiveFields($value);
            }
        }

        return $data;
    }

    /**
     * Mask a sensitive value.
     */
    protected function maskValue($value): string
    {
        if (!is_string($value)) {
            $value = (string) $value;
        }

        $length = strlen($value);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, 2) . str_repeat('*', $length - 4) . substr($value, -2);
    }

    /**
     * Sanitize headers by removing sensitive information.
     */
    protected function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'x-api-key', 'x-auth-token'];
        
        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), $sensitiveHeaders)) {
                $headers[$key] = '***MASKED***';
            }
        }

        return $headers;
    }
}

