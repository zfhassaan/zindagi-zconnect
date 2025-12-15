<?php

declare(strict_types=1);

namespace Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use zfhassaan\ZindagiZconnect\Providers\ZindagiZconnectServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Load and run migrations
        $this->loadMigrationsFrom(__DIR__ . '/../src/database/migrations');
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            ZindagiZconnectServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Setup zindagi-zconnect config
        $app['config']->set('zindagi-zconnect', [
            'api' => [
                'base_url' => 'https://api.test.jsbank.com/zconnect',
                'timeout' => 30,
                'retry_attempts' => 3,
            ],
            'auth' => [
                'client_id' => 'test_client_id',
                'client_secret' => 'test_client_secret',
                'api_key' => 'test_api_key',
                'token_cache_ttl' => 3600,
            ],
            'logging' => [
                'enabled' => true,
                'channel' => 'daily',
                'log_requests' => true,
                'log_responses' => true,
                'log_sensitive_data' => false,
            ],
            'audit' => [
                'enabled' => true,
                'table' => 'zindagi_zconnect_audit_logs',
            ],
            'modules' => [
                'onboarding' => [
                    'enabled' => true,
                    'endpoint' => '/onboarding',
                    'timeout' => 60,
                    'account_verification' => [
                        'endpoint' => '/api/v2/verifyacclinkacc-blb',
                        'merchant_type' => '0088',
                        'company_name' => 'NOVA',
                        'transaction_type' => '02',
                    ],
                    'account_linking' => [
                        'endpoint' => '/api/v2/linkacc-blb',
                        'merchant_type' => '0088',
                        'company_name' => 'NOVA',
                        'transaction_type' => '01',
                        'reserved1' => '02',
                    ],
                ],
            ],
            'security' => [
                'verify_ssl' => true,
            ],
        ]);
    }
}

