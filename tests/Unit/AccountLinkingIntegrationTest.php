<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingRequestDTO;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountLinking;

class AccountLinkingIntegrationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
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
        $mockLoggingService->shouldReceive('logRequest')
            ->once()
            ->with(
                Mockery::any(),
                Mockery::any(),
                Mockery::on(function ($headers) {
                    return $headers['Accept'] === 'application/json'
                        && $headers['Content-Type'] === 'application/json'
                        && $headers['clientId'] === config('zindagi-zconnect.auth.client_id')
                        && $headers['clientSecret'] === 'test_access_token_123'
                        && $headers['organizationId'] === '223';
                })
            );
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
                'AccountTitle' => 'Test Account',
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
                        && $headers['clientId'] === config('zindagi-zconnect.auth.client_id')
                        && $headers['clientSecret'] === 'test_access_token_123'
                        && $headers['organizationId'] === '223';
                })
            )
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
        
        $this->assertInstanceOf(\zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingResponseDTO::class, $response);
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
        $mockLoggingService->shouldReceive('logRequest')->once();
        $mockLoggingService->shouldReceive('logResponse')->once();
        $mockLoggingService->shouldReceive('logError')->zeroOrMoreTimes();

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
                'AccountTitle' => 'Test Account',
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
                    return isset($body['LinkAccountRequest'])
                        && $body['LinkAccountRequest']['MerchantType'] === '0088'
                        && $body['LinkAccountRequest']['TraceNo'] === '000001'
                        && $body['LinkAccountRequest']['Cnic'] === '1234567890123'
                        && $body['LinkAccountRequest']['MobileNo'] === '03001234567'
                        && $body['LinkAccountRequest']['DateTime'] === '20210105201527'
                        && $body['LinkAccountRequest']['CompanyName'] === 'NOVA'
                        && $body['LinkAccountRequest']['Reserved1'] === '02'
                        && $body['LinkAccountRequest']['TransactionType'] === '01'
                        && isset($body['LinkAccountRequest']['OtpPin'])
                        && $body['LinkAccountRequest']['OtpPin'] === '123456';
                })
            )
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
            merchantType: '0088',
            traceNo: '000001',
            dateTime: '20210105201527',
            companyName: 'NOVA',
            transactionType: '01',
            reserved1: '02',
            otpPin: '123456'
        );

        $response = $service->linkAccount($dto);
        
        $this->assertInstanceOf(\zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingResponseDTO::class, $response);
        $this->assertTrue($response->success);
    }

    /**
     * Test request body without OtpPin.
     */
    public function test_request_body_without_otp_pin(): void
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
        $mockLinking = new AccountLinking();
        $mockAccountLinkingRepo->shouldReceive('create')
            ->once()
            ->andReturn($mockLinking);

        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);

        $successResponse = [
            'LinkAccountResponse' => [
                'ResponseCode' => '00',
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
                    return isset($body['LinkAccountRequest'])
                        && !isset($body['LinkAccountRequest']['OtpPin']);
                })
            )
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

        $service->linkAccount($dto);
    }
}

