<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Providers;

use Illuminate\Support\ServiceProvider;
use zfhassaan\ZindagiZconnect\ZindagiZconnect;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\AuditService;
use zfhassaan\ZindagiZconnect\Services\AuthenticationService;
use zfhassaan\ZindagiZconnect\Services\HttpClientService;
use zfhassaan\ZindagiZconnect\Services\LoggingService;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\Contracts\OnboardingServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\OnboardingRepository;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\AccountVerificationRepository;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\AccountLinkingRepository;
use zfhassaan\ZindagiZconnect\Repositories\Contracts\AuditLogRepositoryInterface;
use zfhassaan\ZindagiZconnect\Repositories\AuditLogRepository;

class ZindagiZconnectServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish config file
            $this->publishes([
                __DIR__ . '/../../config/zindagi-zconnect.php' => config_path('zindagi-zconnect.php'),
            ], 'zindagi-zconnect-config');

            // Publish migrations
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'zindagi-zconnect-migrations');

            // Load migrations
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../../config/zindagi-zconnect.php', 'zindagi-zconnect');

        // Register core services
        $this->app->singleton(HttpClientInterface::class, HttpClientService::class);
        $this->app->singleton(AuthenticationServiceInterface::class, AuthenticationService::class);
        $this->app->singleton(LoggingServiceInterface::class, LoggingService::class);
        $this->app->singleton(AuditServiceInterface::class, AuditService::class);

        // Register repositories
        $this->app->singleton(AuditLogRepositoryInterface::class, AuditLogRepository::class);
        $this->app->singleton(OnboardingRepositoryInterface::class, OnboardingRepository::class);
        $this->app->singleton(AccountVerificationRepositoryInterface::class, AccountVerificationRepository::class);
        $this->app->singleton(AccountLinkingRepositoryInterface::class, AccountLinkingRepository::class);

        // Register module services
        $this->app->singleton(OnboardingServiceInterface::class, OnboardingService::class);

        // Register the main ZindagiZconnect class
        $this->app->singleton('zindagi-zconnect', function ($app) {
            return new ZindagiZconnect(
                $app->make(AuthenticationServiceInterface::class),
                $app->make(HttpClientInterface::class),
                $app->make(LoggingServiceInterface::class),
                $app->make(AuditServiceInterface::class),
                $app->make(OnboardingServiceInterface::class)
            );
        });
    }
}

