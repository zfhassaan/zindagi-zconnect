<?php

declare(strict_types=1);

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentAccountOpeningUpgradeRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentAccountOpeningUpgradeResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;

class AgentAccountOpeningUpgradeServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_successful_agent_account_opening_upgrade(): void
    {
        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logResponse'],
            audit: true,
            client: $this->mockPostClient([
                'accountOpeningAgentL0Res' => [
                    'id' => '99',
                    'errors' => [],
                ],
            ])
        );

        $response = $service->agentAccountOpeningUpgrade($this->requestDto());

        $this->assertInstanceOf(AgentAccountOpeningUpgradeResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('99', $response->id);
    }

    public function test_agent_account_opening_upgrade_with_portal_error(): void
    {
        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logResponse'],
            audit: true,
            client: $this->mockPostClient([
                'accountOpeningAgentL0Res' => [
                    'id' => '-1',
                    'errors' => [
                        [
                            'code' => '9096',
                            'level' => '2',
                            'message' => 'CNIC is expired, please use valid CINC to open account.',
                            'THIRD_PARTY_TRANSACTION_ID' => '',
                            'nadraSessionId' => '',
                        ],
                    ],
                ],
            ])
        );

        $response = $service->agentAccountOpeningUpgrade($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertEquals('9096', $response->errorCode);
        $this->assertStringContainsString('CNIC is expired', $response->message);
    }

    public function test_agent_account_opening_upgrade_with_invalid_json(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->once()->andReturn(new Response(200, [], 'not-json'));

        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logError'],
            audit: false,
            client: $mockClient
        );

        $response = $service->agentAccountOpeningUpgrade($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Invalid response from API', $response->message);
    }

    public function test_agent_account_opening_upgrade_with_network_error(): void
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

        $response = $service->agentAccountOpeningUpgrade($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Failed agent account opening', $response->message);
    }

    private function requestDto(): AgentAccountOpeningUpgradeRequestDTO
    {
        return new AgentAccountOpeningUpgradeRequestDTO(
            dtid: 5,
            pin: '6tBH5Et3C3b9p7Xzr1YVIQ==',
            customerMobile: '03422142169',
            cnic: '4250130646839',
            birthPlace: 'KARACHI SHARKI KARACHI SHARKI',
            customerName: 'AHSAN MEHMOOD',
            motherMaiden: 'SHEHNAZ PARVEEN',
            dateOfBirth: '1992-06-23',
            cnicExpiry: '2022-12-31',
            presentAddress: 'HOUSE NUMBER R-83 MOHALA PAK KOUSAR TOWN MALIR TOUSEE KARACHI SHARKI',
            permanentAddress: 'HOUSE NUMBER R-83 MOHALA PAK KOUSAR TOWN MALIR TOUSEE KARACHI SHARKI',
            accountTitle: 'AHSAN MEHMOOD',
            gender: 'male',
            agentMobile: '03463564149',
            pid: 2510763,
            customerMobileNetwork: 'Telenor',
            customerAccountType: 2
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
                        'agent_account_opening_upgrade' => [
                            'endpoint' => '/api/v1/accountopeningagentl0',
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

        $property = (new \ReflectionClass($service))->getProperty('agentAccountOpeningUpgradeClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        return $service;
    }
}
