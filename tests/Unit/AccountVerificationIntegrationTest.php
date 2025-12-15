<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationRequestDTO;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountVerification;

class AccountVerificationIntegrationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test organization ID default value when not configured.
     */
    public function test_organization_id_default_value(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')
            ->once()
            ->andReturn('test_access_token');

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->zeroOrMoreTimes();
        $mockLoggingService->shouldReceive('logRequest')
            ->once()
            ->with(
                Mockery::any(),
                Mockery::any(),
                Mockery::on(function ($headers) {
                    return isset($headers['organizationId']) && $headers['organizationId'] === '223';
                })
            );
        $mockLoggingService->shouldReceive('logResponse')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockAuditService->shouldReceive('log')->once();

        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockVerification = new AccountVerification();
        $mockAccountVerificationRepo->shouldReceive('create')
            ->once()
            ->andReturn($mockVerification);

        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);

        $successResponse = [
            'VerifyAccLinkAccResponse' => [
                'ResponseCode' => '00',
                'AccountStatus' => '1',
            ],
        ];

        $mockClient = Mockery::mock(Client::class);
        $mockResponse = new Response(200, [], json_encode($successResponse));
        $mockClient->shouldReceive('post')
            ->once()
            ->with(
                Mockery::any(),
                Mockery::on(function ($options) {
                    return isset($options['headers']['organizationId']) 
                        && $options['headers']['organizationId'] === '223';
                })
            )
            ->andReturn($mockResponse);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://test.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => config('zindagi-zconnect.auth.client_id'),
                    // organization_id not set - should default to 223
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
            Mockery::mock(AccountLinkingRepositoryInterface::class),
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
        
        $this->assertInstanceOf(\zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationResponseDTO::class, $response);
        $this->assertTrue($response->success);
    }

    /**
     * Test custom organization ID from config.
     */
    public function test_custom_organization_id_from_config(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')
            ->once()
            ->andReturn('test_access_token');

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->zeroOrMoreTimes();
        $mockLoggingService->shouldReceive('logRequest')
            ->once()
            ->with(
                Mockery::any(),
                Mockery::any(),
                Mockery::on(function ($headers) {
                    return isset($headers['organizationId']) && $headers['organizationId'] === '999';
                })
            );
        $mockLoggingService->shouldReceive('logResponse')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockAuditService->shouldReceive('log')->once();

        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockVerification = new AccountVerification();
        $mockAccountVerificationRepo->shouldReceive('create')
            ->once()
            ->andReturn($mockVerification);

        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);

        $successResponse = [
            'VerifyAccLinkAccResponse' => [
                'ResponseCode' => '00',
                'AccountStatus' => '1',
            ],
        ];

        $mockClient = Mockery::mock(Client::class);
        $mockResponse = new Response(200, [], json_encode($successResponse));
        $mockClient->shouldReceive('post')
            ->once()
            ->with(
                Mockery::any(),
                Mockery::on(function ($options) {
                    return isset($options['headers']['organizationId']) 
                        && $options['headers']['organizationId'] === '999';
                })
            )
            ->andReturn($mockResponse);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://test.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => config('zindagi-zconnect.auth.client_id'),
                    'organization_id' => '999',
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
            Mockery::mock(AccountLinkingRepositoryInterface::class),
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
        
        $this->assertInstanceOf(\zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationResponseDTO::class, $response);
        $this->assertTrue($response->success);
    }

    /**
     * Test request headers are correctly set.
     */
    public function test_request_headers_correctly_set(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')
            ->once()
            ->andReturn('test_access_token_123');

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->zeroOrMoreTimes();
        $mockLoggingService->shouldReceive('logRequest')
            ->once()
            ->with(
                Mockery::any(),
                Mockery::any(),
                Mockery::on(function ($headers) {
                    return $headers['Accept'] === 'application/json'
                        && $headers['Content-Type'] === 'application/json'
                        && $headers['clientId'] === 'test_client_id'
                        && $headers['clientSecret'] === 'test_access_token_123'
                        && $headers['organizationId'] === '223';
                })
            );
        $mockLoggingService->shouldReceive('logResponse')->once();
        $mockLoggingService->shouldReceive('logError')->zeroOrMoreTimes();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockAuditService->shouldReceive('log')->once();

        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockVerification = new AccountVerification();
        $mockAccountVerificationRepo->shouldReceive('create')
            ->once()
            ->andReturn($mockVerification);

        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);

        $successResponse = [
            'VerifyAccLinkAccResponse' => [
                'ResponseCode' => '00',
                'AccountStatus' => '1',
                'ResponseDetails' => ['Account verified successfully'],
                'IsPinSet' => '00',
            ],
        ];

        $mockClient = Mockery::mock(Client::class);
        $mockResponse = new Response(200, [], json_encode($successResponse));
        $mockClient->shouldReceive('post')
            ->once()
            ->with(
                Mockery::any(),
                Mockery::on(function ($options) {
                    $headers = $options['headers'];
                    return $headers['Accept'] === 'application/json'
                        && $headers['Content-Type'] === 'application/json'
                        && $headers['clientId'] === 'test_client_id'
                        && $headers['clientSecret'] === 'test_access_token_123'
                        && $headers['organizationId'] === '223';
                })
            )
            ->andReturn($mockResponse);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://test.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => 'test_client_id',
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
            Mockery::mock(AccountLinkingRepositoryInterface::class),
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
        
        $this->assertInstanceOf(\zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationResponseDTO::class, $response);
        $this->assertTrue($response->success);
    }

    /**
     * Test request body format matches API specification.
     */
    public function test_request_body_format_matches_api_specification(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')
            ->once()
            ->andReturn('test_access_token');

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->zeroOrMoreTimes();
        $mockLoggingService->shouldReceive('logRequest')
            ->once()
            ->with(
                Mockery::any(),
                Mockery::on(function ($body) {
                    return isset($body['VerifyAccLinkAccRequest'])
                        && $body['VerifyAccLinkAccRequest']['MerchantType'] === '0088'
                        && $body['VerifyAccLinkAccRequest']['TraceNo'] === '000009'
                        && $body['VerifyAccLinkAccRequest']['CNIC'] === '1234567890123'
                        && $body['VerifyAccLinkAccRequest']['MobileNo'] === '03001234567'
                        && $body['VerifyAccLinkAccRequest']['DateTime'] === '20210105201527'
                        && $body['VerifyAccLinkAccRequest']['CompanyName'] === 'NOVA'
                        && $body['VerifyAccLinkAccRequest']['Reserved1'] === '01'
                        && $body['VerifyAccLinkAccRequest']['Reserved2'] === '01'
                        && $body['VerifyAccLinkAccRequest']['TransactionType'] === '02';
                }),
                Mockery::any()
            );
        $mockLoggingService->shouldReceive('logResponse')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockAuditService->shouldReceive('log')->once();

        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockVerification = new AccountVerification();
        $mockAccountVerificationRepo->shouldReceive('create')
            ->once()
            ->andReturn($mockVerification);

        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);

        $successResponse = [
            'VerifyAccLinkAccResponse' => [
                'ResponseCode' => '00',
                'AccountStatus' => '1',
            ],
        ];

        $mockClient = Mockery::mock(Client::class);
        $mockResponse = new Response(200, [], json_encode($successResponse));
        $mockClient->shouldReceive('post')
            ->once()
            ->with(
                Mockery::any(),
                Mockery::on(function ($options) {
                    $body = $options['json'];
                    return isset($body['VerifyAccLinkAccRequest'])
                        && $body['VerifyAccLinkAccRequest']['MerchantType'] === '0088'
                        && $body['VerifyAccLinkAccRequest']['TraceNo'] === '000009'
                        && $body['VerifyAccLinkAccRequest']['CNIC'] === '1234567890123'
                        && $body['VerifyAccLinkAccRequest']['MobileNo'] === '03001234567'
                        && $body['VerifyAccLinkAccRequest']['DateTime'] === '20210105201527'
                        && $body['VerifyAccLinkAccRequest']['CompanyName'] === 'NOVA'
                        && $body['VerifyAccLinkAccRequest']['Reserved1'] === '01'
                        && $body['VerifyAccLinkAccRequest']['Reserved2'] === '01'
                        && $body['VerifyAccLinkAccRequest']['TransactionType'] === '02';
                })
            )
            ->andReturn($mockResponse);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://test.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => 'test_client_id',
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
            Mockery::mock(AccountLinkingRepositoryInterface::class),
            Mockery::mock(\zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface::class)
        );

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('accountVerificationClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            merchantType: '0088',
            traceNo: '000009',
            dateTime: '20210105201527',
            companyName: 'NOVA',
            reserved1: '01',
            reserved2: '01',
            transactionType: '02'
        );

        $service->verifyAccount($dto);
        $this->assertTrue(true);
    }
}

