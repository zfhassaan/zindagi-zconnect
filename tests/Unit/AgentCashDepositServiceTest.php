<?php

declare(strict_types=1);

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashDepositRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashDepositResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;

class AgentCashDepositServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_successful_agent_cash_deposit(): void
    {
        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logResponse'],
            audit: true,
            client: $this->mockPostClient([
                'agentCashDepositRes' => [
                    'id' => '99',
                    'errors' => [],
                ],
            ])
        );

        $response = $service->agentCashDeposit($this->requestDto());

        $this->assertInstanceOf(AgentCashDepositResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('99', $response->id);
    }

    public function test_agent_cash_deposit_with_insufficient_balance_error(): void
    {
        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logResponse'],
            audit: true,
            client: $this->mockPostClient([
                'agentCashDepositRes' => [
                    'id' => '-1',
                    'errors' => [
                        [
                            'code' => '9001',
                            'level' => '2',
                            'message' => 'Transaction cannot be processed due to insufficient balance.',
                            'THIRD_PARTY_TRANSACTION_ID' => '',
                            'nadraSessionId' => '',
                        ],
                    ],
                ],
            ])
        );

        $response = $service->agentCashDeposit($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertEquals('9001', $response->errorCode);
        $this->assertStringContainsString('insufficient balance', $response->message);
    }

    public function test_agent_cash_deposit_with_invalid_json(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->once()->andReturn(new Response(200, [], 'not-json'));

        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logError'],
            audit: false,
            client: $mockClient
        );

        $response = $service->agentCashDeposit($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Invalid response from API', $response->message);
    }

    public function test_agent_cash_deposit_with_network_error(): void
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

        $response = $service->agentCashDeposit($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Failed agent cash deposit', $response->message);
    }

    private function requestDto(): AgentCashDepositRequestDTO
    {
        return new AgentCashDepositRequestDTO(
            dtid: 5,
            pid: 50002,
            pin: '6tBH5Et3C3b9p7Xzr1YVIQ==',
            customerMobile: '03377805512',
            agentMobile: '03215013511',
            cnic: '3740528738129',
            transactionAmount: 50000,
            commissionAmount: 200,
            thirdPartyAmount: 0,
            totalAmount: 50000
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
                        'agent_cash_deposit' => [
                            'endpoint' => '/api/v1/agentcashdeposit',
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

        $property = (new \ReflectionClass($service))->getProperty('agentCashDepositClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        return $service;
    }
}
