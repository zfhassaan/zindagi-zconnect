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
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\AccountOpeningRepository;
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
        // Load migrations (needed for both console and testing)
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            // Publish config file
            $this->publishes([
                __DIR__ . '/../../config/zindagi-zconnect.php' => config_path('zindagi-zconnect.php'),
            ], 'zindagi-zconnect-config');

            // Publish migrations
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'zindagi-zconnect-migrations');
        }

        $this->configureLogging();
    }

    /**
     * Configure the custom logging channel for the package.
     */
    protected function configureLogging(): void
    {
        $config = $this->app['config'];

        if (! $config->has('logging.channels.zindagi')) {
            $config->set('logging.channels.zindagi', [
                'driver' => 'daily',
                'path' => storage_path('logs/zindagi/zindagi.log'),
                'level' => $this->app['config']->get('zindagi-zconnect.logging.level', 'debug'),
                'days' => $this->app['config']->get('zindagi-zconnect.logging.days', 14),
            ]);
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
        $this->app->singleton(AccountOpeningRepositoryInterface::class, AccountOpeningRepository::class);

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

