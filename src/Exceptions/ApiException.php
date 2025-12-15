<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Exceptions;

class ApiException extends ZindagiZconnectException
{
    protected int $statusCode;
    protected ?array $responseData = null;

    public function __construct(
        string $message = 'API request failed',
        string $module = 'api',
        ?string $referenceId = null,
        int $statusCode = 500,
        ?array $responseData = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $module, $referenceId, $code, $previous);
        $this->statusCode = $statusCode;
        $this->responseData = $responseData;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseData(): ?array
    {
        return $this->responseData;
    }
}

