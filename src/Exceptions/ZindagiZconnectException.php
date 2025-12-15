<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Exceptions;

use Exception;

class ZindagiZconnectException extends Exception
{
    protected string $module;
    protected ?string $referenceId = null;

    public function __construct(
        string $message = '',
        string $module = 'general',
        ?string $referenceId = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->module = $module;
        $this->referenceId = $referenceId;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function getReferenceId(): ?string
    {
        return $this->referenceId;
    }
}

