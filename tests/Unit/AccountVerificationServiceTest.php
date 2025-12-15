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
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationResponseDTO;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountVerification;
use Illuminate\Support\Facades\Event;

class AccountVerificationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test successful account verification.
     */
    public function test_successful_account_verification(): void
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

        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockVerification = new AccountVerification();
        $mockAccountVerificationRepo->shouldReceive('create')
            ->once()
            ->andReturn($mockVerification);

        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        $successResponse = [
            'VerifyAccLinkAccResponse' => [
                'MerchantType' => '0088',
                'TraceNo' => '000009',
                'CompanyName' => 'NOVA',
                'DateTime' => '20210105201527',
                'AccountStatus' => '1',
                'AccountTitle' => 'MUHAMMAD ARSALAN KHAN',
                'AccountType' => 'L0',
                'Cnic' => '1234567890123',
                'IsPinSet' => '0',
                'MobileNumber' => '03001234567',
                'ResponseCode' => '00',
                'ResponseDetails' => ['Account exists'],
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
                        'account_verification' => [
                            'endpoint' => '/api/v2/verifyacclinkacc-blb',
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
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
        );

        // Use reflection to set the client
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('accountVerificationClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            traceNo: '000009',
            dateTime: '20210105201527'
        );

        $response = $service->verifyAccount($dto);

        $this->assertInstanceOf(AccountVerificationResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
        $this->assertTrue($response->accountExists());
        $this->assertEquals('MUHAMMAD ARSALAN KHAN', $response->accountTitle);

        Event::assertDispatched(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\AccountVerified::class);
    }

    /**
     * Test account verification with invalid CNIC length.
     */
    public function test_account_verification_with_invalid_cnic_length(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        $service = new OnboardingService(
            Mockery::mock(\zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface::class),
            $mockAuthService,
            $mockLoggingService,
            $mockAuditService,
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
        );

        $dto = new AccountVerificationRequestDTO(
            cnic: '123456789012', // 12 characters instead of 13
            mobileNo: '03001234567'
        );

        $response = $service->verifyAccount($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('CNIC must be exactly 13 characters', $response->message);
    }

    /**
     * Test account verification with invalid mobile number length.
     */
    public function test_account_verification_with_invalid_mobile_length(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        $service = new OnboardingService(
            Mockery::mock(\zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface::class),
            $mockAuthService,
            $mockLoggingService,
            $mockAuditService,
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
        );

        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '0300123456' // 10 characters instead of 11
        );

        $response = $service->verifyAccount($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Mobile number must be exactly 11 characters', $response->message);
    }

    /**
     * Test account verification with invalid merchant type length.
     */
    public function test_account_verification_with_invalid_merchant_type_length(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        $service = new OnboardingService(
            Mockery::mock(\zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface::class),
            $mockAuthService,
            $mockLoggingService,
            $mockAuditService,
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
        );

        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            merchantType: '088' // 3 characters instead of 4
        );

        $response = $service->verifyAccount($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('MerchantType must be exactly 4 characters', $response->message);
    }

    /**
     * Test account verification with invalid trace number length.
     */
    public function test_account_verification_with_invalid_trace_no_length(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        $service = new OnboardingService(
            Mockery::mock(\zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface::class),
            $mockAuthService,
            $mockLoggingService,
            $mockAuditService,
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
        );

        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            traceNo: '00009' // 5 characters instead of 6
        );

        $response = $service->verifyAccount($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('TraceNo must be exactly 6 characters', $response->message);
    }

    /**
     * Test account verification with invalid date time length.
     */
    public function test_account_verification_with_invalid_date_time_length(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        $service = new OnboardingService(
            Mockery::mock(\zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface::class),
            $mockAuthService,
            $mockLoggingService,
            $mockAuditService,
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
        );

        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            dateTime: '2021010520152' // 13 characters instead of 14
        );

        $response = $service->verifyAccount($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('DateTime must be exactly 14 characters', $response->message);
    }

    /**
     * Test account verification with invalid company name length.
     */
    public function test_account_verification_with_invalid_company_name_length(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        $service = new OnboardingService(
            Mockery::mock(\zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface::class),
            $mockAuthService,
            $mockLoggingService,
            $mockAuditService,
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
        );

        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            companyName: 'NOV' // 3 characters instead of 4
        );

        $response = $service->verifyAccount($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('CompanyName must be exactly 4 characters', $response->message);
    }

    /**
     * Test account verification with API error response.
     */
    public function test_account_verification_with_api_error_response(): void
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

        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockVerification = new AccountVerification();
        $mockAccountVerificationRepo->shouldReceive('create')
            ->once()
            ->andReturn($mockVerification);

        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        $errorResponse = [
            'VerifyAccLinkAccResponse' => [
                'ResponseCode' => '01',
                'ResponseDetails' => ['Account not found'],
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
                        'account_verification' => [
                            'endpoint' => '/api/v2/verifyacclinkacc-blb',
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
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
        );

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('accountVerificationClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            traceNo: '000009',
            dateTime: '20210105201527'
        );

        $response = $service->verifyAccount($dto);

        $this->assertFalse($response->success);
        $this->assertEquals('01', $response->responseCode);
    }

    /**
     * Test account verification with HTTP exception.
     */
    public function test_account_verification_with_http_exception(): void
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
                        'account_verification' => [
                            'endpoint' => '/api/v2/verifyacclinkacc-blb',
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
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
        );

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('accountVerificationClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            traceNo: '000009',
            dateTime: '20210105201527'
        );

        $response = $service->verifyAccount($dto);

        $this->assertFalse($response->success);
        $this->assertEquals('Bad Request - Invalid Access Token', $response->message);
        $this->assertEquals('4001', $response->errorCode);
    }

    /**
     * Test account verification with network exception.
     */
    public function test_account_verification_with_network_exception(): void
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
                        'account_verification' => [
                            'endpoint' => '/api/v2/verifyacclinkacc-blb',
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
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
        );

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('accountVerificationClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            traceNo: '000009',
            dateTime: '20210105201527'
        );

        $response = $service->verifyAccount($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Failed to verify account', $response->message);
    }

    /**
     * Test account verification with authentication failure.
     */
    public function test_account_verification_with_authentication_failure(): void
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

        $service = new OnboardingService(
            Mockery::mock(\zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface::class),
            $mockAuthService,
            $mockLoggingService,
            $mockAuditService,
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
        );

        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            traceNo: '000009',
            dateTime: '20210105201527'
        );

        $response = $service->verifyAccount($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Account verification failed', $response->message);
    }

    /**
     * Test account verification with empty CNIC.
     */
    public function test_account_verification_with_empty_cnic(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        $service = new OnboardingService(
            Mockery::mock(\zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface::class),
            $mockAuthService,
            $mockLoggingService,
            $mockAuditService,
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
        );

        $dto = new AccountVerificationRequestDTO(
            cnic: '',
            mobileNo: '03001234567'
        );

        $response = $service->verifyAccount($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('CNIC must be exactly 13 characters', $response->message);
    }

    /**
     * Test account verification with empty mobile number.
     */
    public function test_account_verification_with_empty_mobile_number(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        $service = new OnboardingService(
            Mockery::mock(\zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface::class),
            $mockAuthService,
            $mockLoggingService,
            $mockAuditService,
            $mockOnboardingRepo,
            $mockAccountVerificationRepo,
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
        );

        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: ''
        );

        $response = $service->verifyAccount($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Mobile number must be exactly 11 characters', $response->message);
    }

    /**
     * Test account verification with account status 0 (not exists).
     */
    public function test_account_verification_with_account_status_zero(): void
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

        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockVerification = new AccountVerification();
        $mockAccountVerificationRepo->shouldReceive('create')
            ->once()
            ->andReturn($mockVerification);

        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);

        $responseData = [
            'VerifyAccLinkAccResponse' => [
                'MerchantType' => '0088',
                'TraceNo' => '000009',
                'CompanyName' => 'NOVA',
                'DateTime' => '20210105201527',
                'AccountStatus' => '0',
                'ResponseCode' => '00',
                'ResponseDetails' => ['Account does not exist'],
            ],
        ];

        $mockClient = Mockery::mock(Client::class);
        $mockResponse = new Response(200, [], json_encode($responseData));
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
                        'account_verification' => [
                            'endpoint' => '/api/v2/verifyacclinkacc-blb',
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
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
        );

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('accountVerificationClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            traceNo: '000009',
            dateTime: '20210105201527'
        );

        $response = $service->verifyAccount($dto);

        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
        $this->assertFalse($response->accountExists());
        $this->assertEquals('0', $response->accountStatus);
    }
}

