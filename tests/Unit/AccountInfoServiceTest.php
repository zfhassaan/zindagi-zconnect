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
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountInfoRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountInfoResponseDTO;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;

class AccountInfoServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test successful account info retrieval.
     */
    public function test_successful_account_info(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')
            ->once()
            ->andReturn('test_access_token');

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logRequest')->once();
        $mockLoggingService->shouldReceive('logResponse')->once();
        $mockLoggingService->shouldReceive('logError')->zeroOrMoreTimes();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockAuditService->shouldReceive('log')->once();

        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);
        $mockAccountOpeningRepo = Mockery::mock(AccountOpeningRepositoryInterface::class);

        $successResponse = [
            'accountInfoRes' => [
                'DateOfBirth' => '1992-12-31 00:00:00.0',
                'ResponseDateTime' => '20241297129031',
                'ResponseCode' => '00',
                'AccountLevelCode' => 'Level 1',
                'Email' => '[email protected]',
                'Cnic' => '3740522428798',
                'Segment' => 'NOVA',
                'Rrn' => '20230112332423',
                'AccountNumber' => '03165392185',
                'AccountNatureCode' => 'CURRENT',
                'AccountTitle' => 'NAZAHAT FATIMA',
                'AccountStatusCode' => 'IN-ACTIVE',
                'RegistrationTypeCode' => 'Customer',
                'ResponseDescription' => 'Successful',
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
                        'account_info' => [
                            'endpoint' => '/api/v1/accountInfo',
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
            $mockAccountOpeningRepo
        );

        // Use reflection to set the client
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('accountInfoClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountInfoRequestDTO(
            mobileNumber: '03165392185',
            dateTime: '20241297129031',
            rrn: '20230112332423'
        );

        $response = $service->getAccountInfo($dto);

        $this->assertInstanceOf(AccountInfoResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('NAZAHAT FATIMA', $response->accountTitle);
        $this->assertEquals('3740522428798', $response->cnic);
        $this->assertEquals('[email protected]', $response->email);
        $this->assertEquals('Level 1', $response->accountLevelCode);
        $this->assertEquals('CURRENT', $response->accountNatureCode);
        $this->assertEquals('IN-ACTIVE', $response->accountStatusCode);
    }

    /**
     * Test account info with invalid mobile number length.
     */
    public function test_account_info_with_invalid_mobile_number_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('MobileNumber must be exactly 11 characters');

        new AccountInfoRequestDTO(
            mobileNumber: '0316539218', // 10 characters instead of 11
            dateTime: '20241297129031',
            rrn: '20230112332423'
        );
    }

    /**
     * Test account info with invalid date time length.
     */
    public function test_account_info_with_invalid_date_time_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DateTime must be exactly 14 characters');

        new AccountInfoRequestDTO(
            mobileNumber: '03165392185',
            dateTime: '2024129712903', // 13 characters instead of 14
            rrn: '20230112332423'
        );
    }

    /**
     * Test account info with invalid RRN length.
     */
    public function test_account_info_with_invalid_rrn_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rrn must be 14 or 16 characters');

        new AccountInfoRequestDTO(
            mobileNumber: '03165392185',
            dateTime: '20241297129031',
            rrn: '2023011233242' // 13 characters instead of 14 or 16
        );
    }

    /**
     * Test account info with API error response.
     */
    public function test_account_info_with_api_error_response(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')
            ->once()
            ->andReturn('test_access_token');

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logRequest')->once();
        $mockLoggingService->shouldReceive('logResponse')->once();
        $mockLoggingService->shouldReceive('logError')->zeroOrMoreTimes();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockAuditService->shouldReceive('log')->once();

        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);
        $mockAccountOpeningRepo = Mockery::mock(AccountOpeningRepositoryInterface::class);

        $errorResponse = [
            'accountInfoRes' => [
                'ResponseCode' => '01',
                'ResponseDescription' => 'Record Not Found',
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
                        'account_info' => [
                            'endpoint' => '/api/v1/accountInfo',
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
            $mockAccountOpeningRepo
        );

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('accountInfoClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountInfoRequestDTO(
            mobileNumber: '03165392185',
            dateTime: '20241297129031',
            rrn: '20230112332423'
        );

        $response = $service->getAccountInfo($dto);

        $this->assertFalse($response->success);
        $this->assertEquals('01', $response->responseCode);
    }

    /**
     * Test account info with HTTP exception - Bad Request.
     */
    public function test_account_info_with_http_exception_bad_request(): void
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
        $mockAccountOpeningRepo = Mockery::mock(AccountOpeningRepositoryInterface::class);

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
                        'account_info' => [
                            'endpoint' => '/api/v1/accountInfo',
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
            $mockAccountOpeningRepo
        );

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('accountInfoClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountInfoRequestDTO(
            mobileNumber: '03165392185',
            dateTime: '20241297129031',
            rrn: '20230112332423'
        );

        $response = $service->getAccountInfo($dto);

        $this->assertFalse($response->success);
        $this->assertEquals('Bad Request - Invalid Access Token', $response->message);
        $this->assertEquals('4001', $response->errorCode);
    }

    /**
     * Test account info with HTTP exception - Record Not Found.
     */
    public function test_account_info_with_http_exception_record_not_found(): void
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
        $mockAccountOpeningRepo = Mockery::mock(AccountOpeningRepositoryInterface::class);

        $errorResponse = [
            'messages' => 'Record Not Found',
            'errorcode' => '4005',
        ];

        $mockClient = Mockery::mock(Client::class);
        $mockGuzzleRequest = new GuzzleRequest('POST', '/test');
        $mockGuzzleResponse = new Response(404, [], json_encode($errorResponse));
        $exception = new RequestException('Not Found', $mockGuzzleRequest, $mockGuzzleResponse);

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
                        'account_info' => [
                            'endpoint' => '/api/v1/accountInfo',
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
            $mockAccountOpeningRepo
        );

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('accountInfoClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountInfoRequestDTO(
            mobileNumber: '03165392185',
            dateTime: '20241297129031',
            rrn: '20230112332423'
        );

        $response = $service->getAccountInfo($dto);

        $this->assertFalse($response->success);
        $this->assertEquals('Record Not Found', $response->message);
        $this->assertEquals('4005', $response->errorCode);
    }

    /**
     * Test account info with network exception.
     */
    public function test_account_info_with_network_exception(): void
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
        $mockAccountOpeningRepo = Mockery::mock(AccountOpeningRepositoryInterface::class);

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
                        'account_info' => [
                            'endpoint' => '/api/v1/accountInfo',
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
            $mockAccountOpeningRepo
        );

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('accountInfoClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountInfoRequestDTO(
            mobileNumber: '03165392185',
            dateTime: '20241297129031',
            rrn: '20230112332423'
        );

        $response = $service->getAccountInfo($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Failed to get account info', $response->message);
    }

    /**
     * Test account info with invalid JSON response.
     */
    public function test_account_info_with_invalid_json_response(): void
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
        $mockAccountOpeningRepo = Mockery::mock(AccountOpeningRepositoryInterface::class);

        $mockClient = Mockery::mock(Client::class);
        $mockResponse = new Response(200, [], 'Invalid JSON Response');
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
                        'account_info' => [
                            'endpoint' => '/api/v1/accountInfo',
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
            $mockAccountOpeningRepo
        );

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('accountInfoClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountInfoRequestDTO(
            mobileNumber: '03165392185',
            dateTime: '20241297129031',
            rrn: '20230112332423'
        );

        $response = $service->getAccountInfo($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Invalid response from API', $response->message);
    }

    /**
     * Test account info with authentication failure.
     */
    public function test_account_info_with_authentication_failure(): void
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
        $mockAccountOpeningRepo = Mockery::mock(AccountOpeningRepositoryInterface::class);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => config('zindagi-zconnect.auth.client_id'),
                    'organization_id' => '223',
                ],
                'modules' => [
                    'onboarding' => [
                        'account_info' => [
                            'endpoint' => '/api/v1/accountInfo',
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
            $mockAccountOpeningRepo
        );

        $dto = new AccountInfoRequestDTO(
            mobileNumber: '03165392185',
            dateTime: '20241297129031',
            rrn: '20230112332423'
        );

        $response = $service->getAccountInfo($dto);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Failed to get account info', $response->message);
    }

    /**
     * Test account info with 16-character RRN.
     */
    public function test_account_info_with_16_character_rrn(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')
            ->once()
            ->andReturn('test_access_token');

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logRequest')->once();
        $mockLoggingService->shouldReceive('logResponse')->once();
        $mockLoggingService->shouldReceive('logError')->zeroOrMoreTimes();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockAuditService->shouldReceive('log')->once();

        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);
        $mockAccountOpeningRepo = Mockery::mock(AccountOpeningRepositoryInterface::class);

        $successResponse = [
            'accountInfoRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Successful',
                'AccountNumber' => '03165392185',
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
                        'account_info' => [
                            'endpoint' => '/api/v1/accountInfo',
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
            $mockAccountOpeningRepo
        );

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('accountInfoClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        // Test with 16-character RRN
        $dto = new AccountInfoRequestDTO(
            mobileNumber: '03165392185',
            dateTime: '20241297129031',
            rrn: '2023011233242312' // 16 characters
        );

        $response = $service->getAccountInfo($dto);

        $this->assertInstanceOf(AccountInfoResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
    }
}