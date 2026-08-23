<?php

declare(strict_types=1);

namespace Tests\Unit;


use Orchestra\Testbench\TestCase;
use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountStatementV2RequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountStatementV2ResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;

class AccountStatementV2ServiceTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return ['zfhassaan\ZindagiZconnect\Providers\ZindagiZconnectServiceProvider'];
    }

    /**
     * Test successful account statement v2 fetch.
     */
    public function test_successful_account_statement_v2_fetch(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')
            ->once()
            ->andReturn('test_access_token');

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logRequest')->once();
        $mockLoggingService->shouldReceive('logResponse')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockAuditService->shouldReceive('log')->once();

        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);
        $mockAccountOpeningRepo = Mockery::mock(AccountOpeningRepositoryInterface::class);


        $successResponse = [
            'AccountStatementRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Successful',
                'ClosingBalanceStatement' => [
                    [
                        'dateTime' => '20250116174251',
                        'mobileNumber' => '03343118436',
                        'dayEndBalance' => '5000'
                    ]
                ],
                'DigiWalletStatement' => [
                    [
                        'transactionAmount' => 100,
                        'transactionType' => 'Debit',
                        'mobileNumber' => '03343118436'
                    ]
                ],
                'HashData' => 'some-hash',
                'ResponseDateTime' => '20250116174253',
                'Rrn' => '123456789'
            ],
        ];

        $mockClient = Mockery::mock(Client::class);
        $mockResponse = new Response(200, [], json_encode($successResponse));
        $mockClient->shouldReceive('post')
            ->once()
            ->andReturn($mockResponse);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => 'test_id',
                    'organization_id' => '223',
                ],
                'modules' => [
                    'onboarding' => [
                        'account_statement_v2' => [
                             'endpoint' => '/api/v2/digiWalletStatement',
                        ]
                    ],
                ],
                'security' => ['verify_ssl' => true],
            ],
        ]);

        $service = new OnboardingService(
            Mockery::mock(\zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface::class),
            $mockAuthService,
            $mockLoggingService,
            $mockAuditService,
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo,
            $mockAccountOpeningRepo
        );

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('accountStatementV2Client');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountStatementV2RequestDTO(
            transmissionDatetime: '0116174253',
            systemsTraceAuditNumber: '396583',
            timeLocalTransaction: '054253',
            dateLocalTransaction: '20250116174251',
            accountNumber: '03343118436',
            fromDate: '12/16/2025',
            toDate: '01/16/2025'
        );

        $response = $service->accountStatementV2($dto);

        $this->assertInstanceOf(AccountStatementV2ResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('Successful', $response->responseDescription);
        $this->assertIsArray($response->closingBalanceStatement);
        $this->assertIsArray($response->digiWalletStatement);
        $this->assertCount(1, $response->closingBalanceStatement);
        $this->assertCount(1, $response->digiWalletStatement);
        $this->assertEquals('5000', $response->closingBalanceStatement[0]['dayEndBalance']);
        $this->assertEquals('some-hash', $response->hashData);
        $this->assertEquals('123456789', $response->rrn);
    }

    public function test_account_statement_v2_with_api_error_code(): void
    {
        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logResponse'],
            audit: true,
            client: $this->mockPostClient([
                'AccountStatementRes' => [
                    'ResponseCode' => '01',
                    'ResponseDescription' => 'No transactions found',
                    'Rrn' => '123456789',
                ],
            ])
        );

        $response = $service->accountStatementV2($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertEquals('01', $response->responseCode);
        $this->assertEquals('No transactions found', $response->responseDescription);
    }

    public function test_account_statement_v2_with_invalid_json(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->once()->andReturn(new Response(200, [], 'not-json'));

        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logError'],
            audit: false,
            client: $mockClient
        );

        $response = $service->accountStatementV2($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Invalid response from API', $response->responseDescription);
    }

    public function test_account_statement_v2_with_network_error(): void
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

        $response = $service->accountStatementV2($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Failed to get account statement V2', $response->responseDescription);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function requestDto(): AccountStatementV2RequestDTO
    {
        return new AccountStatementV2RequestDTO(
            transmissionDatetime: '0116174253',
            systemsTraceAuditNumber: '396583',
            timeLocalTransaction: '054253',
            dateLocalTransaction: '20250116174251',
            accountNumber: '03343118436',
            fromDate: '12/16/2025',
            toDate: '01/16/2025'
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
                        'account_statement_v2' => [
                            'endpoint' => '/api/v2/digiWalletStatement',
                        ],
                        'timeout' => 60,
                    ],
                ],
                'security' => ['verify_ssl' => true],
            ],
        ]);

        $service = new OnboardingService(
            Mockery::mock(\zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface::class),
            $mockAuthService,
            $mockLoggingService,
            $mockAuditService,
            Mockery::mock(OnboardingRepositoryInterface::class),
            Mockery::mock(AccountVerificationRepositoryInterface::class),
            Mockery::mock(AccountLinkingRepositoryInterface::class),
            Mockery::mock(AccountOpeningRepositoryInterface::class)
        );

        $property = (new \ReflectionClass($service))->getProperty('accountStatementV2Client');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        return $service;
    }
}
