<?php

declare(strict_types=1);

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashDepositInquiryRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashDepositInquiryResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;

class AgentCashDepositInquiryServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_successful_agent_cash_deposit_inquiry(): void
    {
        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logResponse'],
            audit: true,
            client: $this->mockPostClient([
                'agentCashDepositInquiryRes' => [
                    'CMOB' => '03377805512',
                    'TAMT' => '50100.0',
                    'TXAM' => '50000.0',
                    'CNIC' => '6110180873549',
                    'CAMT' => '86.21',
                    'id' => '78',
                    'NAME' => 'SAOUD NASEER',
                ],
            ])
        );

        $response = $service->agentCashDepositInquiry($this->requestDto());

        $this->assertInstanceOf(AgentCashDepositInquiryResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('78', $response->id);
        $this->assertEquals('SAOUD NASEER', $response->name);
        $this->assertEquals('50100.0', $response->totalAmount);
    }

    public function test_agent_cash_deposit_inquiry_gateway_error(): void
    {
        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logResponse'],
            audit: true,
            client: $this->mockPostClient([
                'messages' => 'Record Not Found',
                'errorcode' => '4005',
            ])
        );

        $response = $service->agentCashDepositInquiry($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertEquals('4005', $response->errorCode);
    }

    public function test_agent_cash_deposit_inquiry_with_invalid_json(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->once()->andReturn(new Response(200, [], 'not-json'));

        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logError'],
            audit: false,
            client: $mockClient
        );

        $response = $service->agentCashDepositInquiry($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Invalid response from API', $response->message);
    }

    public function test_agent_cash_deposit_inquiry_with_network_error(): void
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

        $response = $service->agentCashDepositInquiry($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Failed agent cash deposit inquiry', $response->message);
    }

    private function requestDto(): AgentCashDepositInquiryRequestDTO
    {
        return new AgentCashDepositInquiryRequestDTO(
            dtid: 5,
            pid: 50002,
            agentMobile: '03215013511',
            customerMobile: '03377805512',
            transactionAmount: 50000
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
                        'agent_cash_deposit_inquiry' => [
                            'endpoint' => '/api/v1/agentcashdepositinquiry',
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

        $property = (new \ReflectionClass($service))->getProperty('agentCashDepositInquiryClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        return $service;
    }
}
