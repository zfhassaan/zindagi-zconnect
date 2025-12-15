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
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;

class AccountLinkingErrorResponseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test error code 4001 - Invalid Access Token.
     */
    public function test_error_code_4001_invalid_access_token(): void
    {
        $this->testErrorResponse('4001', 'Bad Request - Invalid Access Token');
    }

    /**
     * Test error code 4002 - Invalid Request Payload.
     */
    public function test_error_code_4002_invalid_request_payload(): void
    {
        $this->testErrorResponse('4002', 'Bad Request - Invalid Request Payload');
    }

    /**
     * Test error code 4003 - Invalid Authorization Header.
     */
    public function test_error_code_4003_invalid_authorization_header(): void
    {
        $this->testErrorResponse('4003', 'Bad Request - Invalid Authorization Header');
    }

    /**
     * Test error code 4004 - Something Went Wrong.
     */
    public function test_error_code_4004_something_went_wrong(): void
    {
        $this->testErrorResponse('4004', 'Something Went Wrong');
    }

    /**
     * Test error code 4005 - Record Not Found.
     */
    public function test_error_code_4005_record_not_found(): void
    {
        $this->testErrorResponse('4005', 'Record Not Found');
    }

    /**
     * Test error code 4006 - Invalid Client Id/Secret.
     */
    public function test_error_code_4006_invalid_client_id_secret(): void
    {
        $this->testErrorResponse('4006', 'Invalid Client Id/Secret');
    }

    /**
     * Test error code 4007 - Invalid Access Token.
     */
    public function test_error_code_4007_invalid_access_token(): void
    {
        $this->testErrorResponse('4007', 'Bad Request - Invalid Access Token');
    }

    /**
     * Helper method to test error responses.
     */
    protected function testErrorResponse(string $errorCode, string $errorMessage): void
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
            'messages' => $errorMessage,
            'errorcode' => $errorCode,
        ];

        $mockClient = Mockery::mock(Client::class);
        $mockGuzzleRequest = new GuzzleRequest('POST', '/test');
        $mockGuzzleResponse = new Response(400, [], json_encode($errorResponse));
        $exception = new RequestException($errorMessage, $mockGuzzleRequest, $mockGuzzleResponse);

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
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
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
        $this->assertEquals($errorMessage, $response->message);
        $this->assertEquals($errorCode, $response->errorCode);
    }

    /**
     * Test response with missing LinkAccountResponse key.
     */
    public function test_response_missing_link_response_key(): void
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
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);
        $mockLinking = new \zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountLinking();
        $mockAccountLinkingRepo->shouldReceive('create')
            ->once()
            ->andReturn($mockLinking);

        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);

        $invalidResponse = [
            'SomeOtherKey' => [
                'ResponseCode' => '00',
            ],
        ];

        $mockClient = Mockery::mock(Client::class);
        $mockResponse = new Response(200, [], json_encode($invalidResponse));
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
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
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
        $this->assertEquals('Unknown error', $response->message);
    }

    /**
     * Test response with invalid JSON.
     */
    public function test_response_with_invalid_json(): void
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
        $mockResponse = new Response(200, [], 'Invalid JSON response');
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
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
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
        $this->assertStringContainsString('Account linking failed', $response->message);
    }

    /**
     * Test response with empty response body.
     */
    public function test_response_with_empty_body(): void
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
        $mockResponse = new Response(200, [], '');
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
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
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
        $this->assertStringContainsString('Account linking failed', $response->message);
    }

    /**
     * Test boundary conditions for CNIC length.
     */
    public function test_boundary_conditions_cnic_length(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->twice();
        $mockLoggingService->shouldReceive('logError')->twice();

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
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
        );

        // Test with 12 characters (one less)
        $dto1 = new AccountLinkingRequestDTO(
            cnic: '123456789012', // 12 chars
            mobileNo: '03001234567'
        );
        $response1 = $service->linkAccount($dto1);
        $this->assertFalse($response1->success);

        // Test with 14 characters (one more)
        $dto2 = new AccountLinkingRequestDTO(
            cnic: '12345678901234', // 14 chars
            mobileNo: '03001234567'
        );
        $response2 = $service->linkAccount($dto2);
        $this->assertFalse($response2->success);
    }

    /**
     * Test boundary conditions for mobile number length.
     */
    public function test_boundary_conditions_mobile_length(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->twice();
        $mockLoggingService->shouldReceive('logError')->twice();

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
            $mockAccountLinkingRepo,
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
        );

        // Test with 10 characters (one less)
        $dto1 = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '0300123456' // 10 chars
        );
        $response1 = $service->linkAccount($dto1);
        $this->assertFalse($response1->success);

        // Test with 12 characters (one more)
        $dto2 = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '030012345678' // 12 chars
        );
        $response2 = $service->linkAccount($dto2);
        $this->assertFalse($response2->success);
    }
}

