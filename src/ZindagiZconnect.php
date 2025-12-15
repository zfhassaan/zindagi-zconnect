<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect;

use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\Contracts\OnboardingServiceInterface;

class ZindagiZconnect
{
    /**
     * Create a new ZindagiZconnect instance.
     */
    public function __construct(
        protected AuthenticationServiceInterface $authenticationService,
        protected HttpClientInterface $httpClient,
        protected LoggingServiceInterface $loggingService,
        protected AuditServiceInterface $auditService,
        protected OnboardingServiceInterface $onboardingService
    ) {
    }

    /**
     * Get the onboarding service instance.
     */
    public function onboarding(): OnboardingServiceInterface
    {
        return $this->onboardingService;
    }

    /**
     * Get the authentication service instance.
     */
    public function auth(): AuthenticationServiceInterface
    {
        return $this->authenticationService;
    }

    /**
     * Get the HTTP client instance.
     */
    public function http(): HttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Get the logging service instance.
     */
    public function logger(): LoggingServiceInterface
    {
        return $this->loggingService;
    }

    /**
     * Get the audit service instance.
     */
    public function audit(): AuditServiceInterface
    {
        return $this->auditService;
    }
}

