<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Services\Contracts;

interface AuthenticationServiceInterface
{
    /**
     * Authenticate and get access token.
     *
     * @return string
     */
    public function authenticate(): string;

    /**
     * Get cached access token.
     *
     * @return string|null
     */
    public function getToken(): ?string;

    /**
     * Refresh the access token.
     *
     * @return string
     */
    public function refreshToken(): string;

    /**
     * Check if token is valid.
     *
     * @return bool
     */
    public function isTokenValid(): bool;
}

