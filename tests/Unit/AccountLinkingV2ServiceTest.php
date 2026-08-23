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
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingV2RequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingV2ResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\AccountLinked;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountLinking;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;

class AccountLinkingV2ServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_successful_account_linking_v2(): void
    {
        Event::fake();

        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logResponse'],
            audit: true,
            persist: true,
            client: $this->mockPostClient([
                'LinkAccountResponse' => [
                    'MerchantType' => '0088',
                    'TraceNo' => '000001',
                    'CompanyName' => 'NOVA',
                    'DateTime' => '20210105201527',
                    'AccountTitle' => 'JOHN DOE',
                    'AccountType' => 'Level0',
                    'ResponseCode' => '00',
                    'ResponseDetails' => ['Successful'],
                ],
            ])
        );

        $response = $service->linkAccountV2($this->requestDto());

        $this->assertInstanceOf(AccountLinkingV2ResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('JOHN DOE', $response->accountTitle);

        Event::assertDispatched(AccountLinked::class);
    }

    public function test_account_linking_v2_with_api_error_code(): void
    {
        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logResponse'],
            audit: true,
            persist: true,
            client: $this->mockPostClient([
                'LinkAccountResponse' => [
                    'ResponseCode' => '01',
                    'ResponseDetails' => ['Invalid OTP'],
                ],
            ])
        );

        $response = $service->linkAccountV2($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertEquals('01', $response->responseCode);
        $this->assertEquals('Invalid OTP', $response->message);
    }

    public function test_account_linking_v2_with_invalid_json(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->once()->andReturn(new Response(200, [], 'not-json'));

        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logError'],
            audit: false,
            persist: false,
            client: $mockClient
        );

        $response = $service->linkAccountV2($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Invalid response from API', $response->message);
    }

    public function test_account_linking_v2_with_network_error(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->once()->andThrow(new RequestException(
            'Connection timeout',
            Mockery::mock(RequestInterface::class)
        ));

        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logError'],
            audit: false,
            persist: false,
            client: $mockClient
        );

        $response = $service->linkAccountV2($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Failed to link account v2', $response->message);
    }

    public function test_account_linking_v2_validation_error(): void
    {
        $service = $this->makeService(
            logging: ['logInfo', 'logError'],
            audit: false,
            persist: false,
            authenticate: false,
            client: Mockery::mock(Client::class)
        );

        $dto = new AccountLinkingV2RequestDTO(
            cnic: '123456789012',
            mobileNo: '03001234567',
            mPin: 'encrypted-mpin',
            confirmMpin: 'encrypted-mpin',
            traceNo: '000001',
            dateTime: '20210105201527'
        );

        $response = $service->linkAccountV2($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('CNIC must be exactly 13 characters', $response->message);
    }

    private function requestDto(): AccountLinkingV2RequestDTO
    {
        return new AccountLinkingV2RequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            mPin: 'encrypted-mpin',
            confirmMpin: 'encrypted-mpin',
            traceNo: '000001',
            dateTime: '20210105201527',
            transactionType: '01',
            reserved1: '01'
        );
    }

    private function mockPostClient(array $payload): Client
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->once()->andReturn(new Response(200, [], json_encode($payload)));

        return $mockClient;
    }

    private function makeService(array $logging, bool $audit, bool $persist, Client $client, bool $authenticate = true): OnboardingService
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        if ($authenticate) {
            $mockAuthService->shouldReceive('authenticate')->once()->andReturn('test_access_token');
        }

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        foreach ($logging as $method) {
            $mockLoggingService->shouldReceive($method)->once();
        }

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        if ($audit) {
            $mockAuditService->shouldReceive('log')->once();
        }

        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);
        if ($persist) {
            $mockAccountLinkingRepo->shouldReceive('create')->once()->andReturn(new AccountLinking());
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
                        'account_linking_v2' => [
                            'endpoint' => '/api/v2/acountlinking',
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
            $mockAccountLinkingRepo,
            Mockery::mock(AccountOpeningRepositoryInterface::class)
        );

        $property = (new \ReflectionClass($service))->getProperty('accountLinkingV2Client');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        return $service;
    }
}
