<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Exceptions;

class AuthenticationException extends ZindagiZconnectException
{
    public function __construct(
        string $message = 'Authentication failed',
        ?string $referenceId = null,
        int $code = 401,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 'authentication', $referenceId, $code, $previous);
    }
}

