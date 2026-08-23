<?php

declare(strict_types=1);

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentLoginRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentLoginResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;

class AgentLoginServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_successful_agent_login(): void
    {
        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logResponse'],
            audit: true,
            client: $this->mockPostClient([
                'loginAgentRes' => [
                    'id' => '33',
                    'ATYPE' => '3',
                    'AMOB' => '03324779796',
                    'FNAME' => 'AMEN',
                    'LNAME' => 'AMEN',
                    'AGENT_AREA_NAME' => 'Punjab',
                    'TSTR' => 'Dear Agent! Welcome to Branchless Banking',
                    'CNIC' => '3380213794351',
                ],
            ])
        );

        $response = $service->agentLogin($this->requestDto());

        $this->assertInstanceOf(AgentLoginResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('33', $response->id);
        $this->assertEquals('03324779796', $response->agentMobile);
        $this->assertEquals('Punjab', $response->agentAreaName);
    }

    public function test_agent_login_gateway_error(): void
    {
        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logResponse'],
            audit: true,
            client: $this->mockPostClient([
                'messages' => 'Record Not Found',
                'errorcode' => '4005',
            ])
        );

        $response = $service->agentLogin($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertEquals('4005', $response->errorCode);
        $this->assertEquals('Record Not Found', $response->message);
    }

    public function test_agent_login_with_invalid_json(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->once()->andReturn(new Response(200, [], 'not-json'));

        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logError'],
            audit: false,
            client: $mockClient
        );

        $response = $service->agentLogin($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Invalid response from API', $response->message);
    }

    public function test_agent_login_with_network_error(): void
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

        $response = $service->agentLogin($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Failed agent login', $response->message);
    }

    private function requestDto(): AgentLoginRequestDTO
    {
        return new AgentLoginRequestDTO(
            pin: '6tBH5Et3C3b9p7Xzr1YVIQ==',
            uid: '1063857',
            appVersion: '2.0.10.43',
            os: 'Android',
            osVersion: '11',
            model: 'SM-A505F',
            vendor: 'samsung',
            network: 'Ufone',
            udid: 'b227ee26ac146393'
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
                        'agent_login' => [
                            'endpoint' => '/api/v1/loginagentmate',
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

        $property = (new \ReflectionClass($service))->getProperty('agentLoginClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        return $service;
    }
}
