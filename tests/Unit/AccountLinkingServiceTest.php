<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingResponseDTO;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountLinking;
use Illuminate\Support\Facades\Event;

class AccountLinkingServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test successful account linking.
     */
    public function test_successful_account_linking(): void
    {
        Event::fake();

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

        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);
        $mockLinking = new AccountLinking();
        $mockAccountLinkingRepo->shouldReceive('create')
            ->once()
            ->andReturn($mockLinking);

        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);

        $successResponse = [
            'LinkAccountResponse' => [
                'MerchantType' => '0088',
                'TraceNo' => '000001',
                'CompanyName' => 'NOVA',
                'DateTime' => '20210105201527',
                'AccountTitle' => 'MUHAMMADARSALANKHAN',
                'AccountType' => 'Level0',
                'ResponseCode' => '00',
                'ResponseDetails' => ['Successful'],
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
                    'client_id' => config('zindagi-zconnect.auth.client_id'),
                    'organization_id' => '223',
                ],
                'modules' => [
                    'onboarding' => [
                        'account_linking' => [
                            'endpoint' => '/api/v2/linkacc-blb',
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
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo
        );

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('accountLinkingClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            traceNo: '000001',
            dateTime: '20210105201527',
            otpPin: '123456'
        );

        $response = $service->linkAccount($dto);

        $this->assertInstanceOf(AccountLinkingResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('MUHAMMADARSALANKHAN', $response->accountTitle);

        Event::assertDispatched(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\AccountLinked::class);
    }

    /**
     * Test account linking with invalid CNIC length.
     */
    public function test_account_linking_with_invalid_cnic_length(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => config('zindagi-zconnect.auth.client_id'),
                    'organization_id' => '223',
                ],
                'modules' => [
                    'onboarding' => [
                        'account_linking' => [
                            'endpoint' => '/api/v2/linkacc-blb',
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
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo
        );

        $dto = new AccountLinkingRequestDTO(
            cnic: '123456789012', // 12 characters instead of 13
            mobileNo: '03001234567'
        );

        $response = $service->linkAccount($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('CNIC must be exactly 13 characters', $response->message);
    }

    /**
     * Test account linking with invalid mobile number length.
     */
    public function test_account_linking_with_invalid_mobile_length(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => config('zindagi-zconnect.auth.client_id'),
                    'organization_id' => '223',
                ],
                'modules' => [
                    'onboarding' => [
                        'account_linking' => [
                            'endpoint' => '/api/v2/linkacc-blb',
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
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo
        );

        $dto = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '0300123456' // 10 characters instead of 11
        );

        $response = $service->linkAccount($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Mobile number must be exactly 11 characters', $response->message);
    }

    /**
     * Test account linking with invalid transaction type length.
     */
    public function test_account_linking_with_invalid_transaction_type_length(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => config('zindagi-zconnect.auth.client_id'),
                    'organization_id' => '223',
                ],
                'modules' => [
                    'onboarding' => [
                        'account_linking' => [
                            'endpoint' => '/api/v2/linkacc-blb',
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
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo
        );

        $dto = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            transactionType: '0' // 1 character instead of 2
        );

        $response = $service->linkAccount($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('TransactionType must be exactly 2 characters', $response->message);
    }

    /**
     * Test account linking with invalid reserved1 length.
     */
    public function test_account_linking_with_invalid_reserved1_length(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => config('zindagi-zconnect.auth.client_id'),
                    'organization_id' => '223',
                ],
                'modules' => [
                    'onboarding' => [
                        'account_linking' => [
                            'endpoint' => '/api/v2/linkacc-blb',
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
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo
        );

        $dto = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            reserved1: '0' // 1 character instead of 2
        );

        $response = $service->linkAccount($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Reserved1 must be exactly 2 characters', $response->message);
    }

    /**
     * Test account linking with API error response.
     */
    public function test_account_linking_with_api_error_response(): void
    {
        Event::fake();

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

        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);
        $mockLinking = new AccountLinking();
        $mockAccountLinkingRepo->shouldReceive('create')
            ->once()
            ->andReturn($mockLinking);

        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);

        $errorResponse = [
            'LinkAccountResponse' => [
                'ResponseCode' => '01',
                'ResponseDetails' => ['Account linking failed'],
            ],
        ];

        $mockClient = Mockery::mock(Client::class);
        $mockResponse = new Response(200, [], json_encode($errorResponse));
        $mockClient->shouldReceive('post')
            ->once()
            ->andReturn($mockResponse);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => config('zindagi-zconnect.auth.client_id'),
                    'organization_id' => '223',
                ],
                'modules' => [
                    'onboarding' => [
                        'account_linking' => [
                            'endpoint' => '/api/v2/linkacc-blb',
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
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo
        );

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('accountLinkingClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            traceNo: '000001',
            dateTime: '20210105201527'
        );

        $response = $service->linkAccount($dto);

        $this->assertFalse($response->success);
        $this->assertEquals('01', $response->responseCode);
    }

    /**
     * Test account linking with HTTP exception.
     */
    public function test_account_linking_with_http_exception(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')
            ->once()
            ->andReturn('test_access_token');

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logRequest')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        $errorResponse = [
            'messages' => 'Bad Request - Invalid Access Token',
            'errorcode' => '4001',
        ];

        $mockClient = Mockery::mock(Client::class);
        $mockGuzzleRequest = new GuzzleRequest('POST', '/test');
        $mockGuzzleResponse = new Response(400, [], json_encode($errorResponse));
        $exception = new RequestException('Bad Request', $mockGuzzleRequest, $mockGuzzleResponse);

        $mockClient->shouldReceive('post')
            ->once()
            ->andThrow($exception);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => config('zindagi-zconnect.auth.client_id'),
                    'organization_id' => '223',
                ],
                'modules' => [
                    'onboarding' => [
                        'account_linking' => [
                            'endpoint' => '/api/v2/linkacc-blb',
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
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo
        );

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('accountLinkingClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            traceNo: '000001',
            dateTime: '20210105201527'
        );

        $response = $service->linkAccount($dto);

        $this->assertFalse($response->success);
        $this->assertEquals('Bad Request - Invalid Access Token', $response->message);
        $this->assertEquals('4001', $response->errorCode);
    }

    /**
     * Test account linking with network exception.
     */
    public function test_account_linking_with_network_exception(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')
            ->once()
            ->andReturn('test_access_token');

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logRequest')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        $mockClient = Mockery::mock(Client::class);
        $mockGuzzleRequest = new GuzzleRequest('POST', '/test');
        $exception = new RequestException('Connection timeout', $mockGuzzleRequest);

        $mockClient->shouldReceive('post')
            ->once()
            ->andThrow($exception);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => config('zindagi-zconnect.auth.client_id'),
                    'organization_id' => '223',
                ],
                'modules' => [
                    'onboarding' => [
                        'account_linking' => [
                            'endpoint' => '/api/v2/linkacc-blb',
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
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo
        );

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('accountLinkingClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            traceNo: '000001',
            dateTime: '20210105201527'
        );

        $response = $service->linkAccount($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Failed to link account', $response->message);
    }

    /**
     * Test account linking with authentication failure.
     */
    public function test_account_linking_with_authentication_failure(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')
            ->once()
            ->andThrow(new \RuntimeException('Authentication failed'));

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => config('zindagi-zconnect.auth.client_id'),
                    'organization_id' => '223',
                ],
                'modules' => [
                    'onboarding' => [
                        'account_linking' => [
                            'endpoint' => '/api/v2/linkacc-blb',
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
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo
        );

        $dto = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            traceNo: '000001',
            dateTime: '20210105201527'
        );

        $response = $service->linkAccount($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Account linking failed', $response->message);
    }

    /**
     * Test account linking with empty CNIC.
     */
    public function test_account_linking_with_empty_cnic(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => config('zindagi-zconnect.auth.client_id'),
                    'organization_id' => '223',
                ],
                'modules' => [
                    'onboarding' => [
                        'account_linking' => [
                            'endpoint' => '/api/v2/linkacc-blb',
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
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo
        );

        $dto = new AccountLinkingRequestDTO(
            cnic: '',
            mobileNo: '03001234567'
        );

        $response = $service->linkAccount($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('CNIC must be exactly 13 characters', $response->message);
    }

    /**
     * Test account linking with empty mobile number.
     */
    public function test_account_linking_with_empty_mobile_number(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => config('zindagi-zconnect.auth.client_id'),
                    'organization_id' => '223',
                ],
                'modules' => [
                    'onboarding' => [
                        'account_linking' => [
                            'endpoint' => '/api/v2/linkacc-blb',
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
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo
        );

        $dto = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: ''
        );

        $response = $service->linkAccount($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Mobile number must be exactly 11 characters', $response->message);
    }

    /**
     * Test account linking without OtpPin.
     */
    public function test_account_linking_without_otp_pin(): void
    {
        Event::fake();

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

        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);
        $mockLinking = new AccountLinking();
        $mockAccountLinkingRepo->shouldReceive('create')
            ->once()
            ->andReturn($mockLinking);

        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);

        $successResponse = [
            'LinkAccountResponse' => [
                'ResponseCode' => '00',
                'ResponseDetails' => ['Successful'],
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
                    'client_id' => config('zindagi-zconnect.auth.client_id'),
                    'organization_id' => '223',
                ],
                'modules' => [
                    'onboarding' => [
                        'account_linking' => [
                            'endpoint' => '/api/v2/linkacc-blb',
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
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo
        );

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('accountLinkingClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            traceNo: '000001',
            dateTime: '20210105201527',
            otpPin: null
        );

        $response = $service->linkAccount($dto);

        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
    }
}

