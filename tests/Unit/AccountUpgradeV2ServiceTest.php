<?php

declare(strict_types=1);

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;
use Mockery;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountUpgradeV2RequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountUpgradeV2ResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\AccountUpgraded;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;

class AccountUpgradeV2ServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_successful_account_upgrade_v2(): void
    {
        Event::fake();

        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logResponse'],
            audit: true,
            client: $this->mockPostClient([
                'UpgradeAcc' => [
                    'responseCode' => '00',
                    'responseDescription' => 'Successful',
                    'token' => 'abc-token',
                    'coolingOffStatus' => false,
                ],
            ])
        );

        $response = $service->upgradeAccountV2($this->requestDto());

        $this->assertInstanceOf(AccountUpgradeV2ResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('abc-token', $response->token);

        Event::assertDispatched(AccountUpgraded::class);
    }

    public function test_account_upgrade_v2_with_api_error_code(): void
    {
        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logResponse'],
            audit: true,
            client: $this->mockPostClient([
                'UpgradeAcc' => [
                    'responseCode' => '100',
                    'responseDescription' => 'Biometric verification succeeded, but account upgrade failed. Please contact support.',
                    'token' => '',
                    'coolingOffStatus' => false,
                ],
            ])
        );

        $response = $service->upgradeAccountV2($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertEquals('100', $response->responseCode);
        $this->assertStringContainsString('account upgrade failed', $response->message);
    }

    public function test_account_upgrade_v2_with_invalid_json(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->once()->andReturn(new Response(200, [], 'not-json'));

        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logError'],
            audit: false,
            client: $mockClient
        );

        $response = $service->upgradeAccountV2($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Invalid response from API', $response->responseDescription);
    }

    public function test_account_upgrade_v2_with_network_error(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->once()->andThrow(new RequestException(
            'Connection timeout',
            Mockery::mock(RequestInterface::class)
        ));

        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logError'],
            audit: false,
            client: $mockClient
        );

        $response = $service->upgradeAccountV2($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Failed to upgrade account v2', $response->responseDescription);
    }

    private function requestDto(): AccountUpgradeV2RequestDTO
    {
        return new AccountUpgradeV2RequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            traceNo: '211045',
            dateTime: '20210105201527',
            terminalId: 'NOVA',
            fingerprints: [
                ['index' => '2', 'template' => 'template-a'],
            ],
            latitude: '33.6844',
            longitude: '73.0479',
            udid: 'efc2b31481cd070a'
        );
    }

    private function mockPostClient(array $payload): Client
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->once()->andReturn(new Response(200, [], json_encode($payload)));

        return $mockClient;
    }

    private function makeService(array $logging, bool $audit, Client $client): OnboardingService
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')->once()->andReturn('test_access_token');

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        foreach ($logging as $method) {
            $mockLoggingService->shouldReceive($method)->once();
        }

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        if ($audit) {
            $mockAuditService->shouldReceive('log')->once();
        }

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => 'test_id',
                    'organization_id' => '223',
                ],
                'modules' => [
                    'onboarding' => [
                        'account_upgrade_v2' => [
                            'endpoint' => '/api/v3/upgradeaccount',
                        ],
                        'timeout' => 60,
                    ],
                ],
                'security' => ['verify_ssl' => true],
            ],
        ]);

        $service = new OnboardingService(
            Mockery::mock(HttpClientInterface::class),
            $mockAuthService,
            $mockLoggingService,
            $mockAuditService,
            Mockery::mock(OnboardingRepositoryInterface::class),
            Mockery::mock(AccountVerificationRepositoryInterface::class),
            Mockery::mock(AccountLinkingRepositoryInterface::class),
            Mockery::mock(AccountOpeningRepositoryInterface::class)
        );

        $property = (new \ReflectionClass($service))->getProperty('accountUpgradeV2Client');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        return $service;
    }
}
