<?php

declare(strict_types=1);

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\L2AccountUpgradeRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\L2AccountUpgradeResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;

class L2AccountUpgradeServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_successful_l2_account_upgrade(): void
    {
        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logResponse'],
            audit: true,
            client: $this->mockPostClient([
                'l2AccountUpgradeRes' => [
                    'ResponseCode' => '00',
                    'ResponseDescription' => 'Successful',
                    'Rrn' => '123456789012',
                    'TransactionId' => 'TXN-99',
                    'HashData' => 'abc',
                ],
            ])
        );

        $response = $service->upgradeL2Account($this->requestDto());

        $this->assertInstanceOf(L2AccountUpgradeResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('TXN-99', $response->transactionId);
        $this->assertEquals('abc', $response->hashData);
    }

    public function test_l2_account_upgrade_with_api_error_code(): void
    {
        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logResponse'],
            audit: true,
            client: $this->mockPostClient([
                'l2AccountUpgradeRes' => [
                    'ResponseCode' => '01',
                    'ResponseDescription' => 'Biometric mismatch',
                ],
            ])
        );

        $response = $service->upgradeL2Account($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertEquals('01', $response->responseCode);
        $this->assertEquals('Biometric mismatch', $response->message);
    }

    public function test_l2_account_upgrade_with_invalid_json(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->once()->andReturn(new Response(200, [], 'not-json'));

        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logError'],
            audit: false,
            client: $mockClient
        );

        $response = $service->upgradeL2Account($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Invalid response from API', $response->responseDescription);
    }

    public function test_l2_account_upgrade_with_network_error(): void
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

        $response = $service->upgradeL2Account($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Failed to upgrade L2 account', $response->responseDescription);
    }

    private function requestDto(): L2AccountUpgradeRequestDTO
    {
        return new L2AccountUpgradeRequestDTO(
            mobileNumber: '03001234567',
            dateTime: '20250116174251',
            rrn: '123456789012',
            accountId: '1001',
            cnic: '1234567890123',
            fingerTemplate: 'FINGERPRINT',
            transactionId: 'TXN-99'
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
                        'l2_account_upgrade' => [
                            'endpoint' => '/api/v1/l2Account/l2AccountUpgrade',
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

        $property = (new \ReflectionClass($service))->getProperty('l2AccountUpgradeClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        return $service;
    }
}
