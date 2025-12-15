<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\AccountVerified;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountVerification;

class AccountVerificationEventTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test AccountVerified event is dispatched on successful verification.
     */
    public function test_account_verified_event_dispatched(): void
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
        $mockVerification = new AccountVerification([
            'trace_no' => '000009',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
        ]);
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

        $service->verifyAccount($dto);

        Event::assertDispatched(AccountVerified::class, function ($event) {
            return $event->verification instanceof AccountVerification
                && $event->response instanceof AccountVerificationResponseDTO;
        });
    }

    /**
     * Test AccountVerified event is not dispatched on failure.
     */
    public function test_account_verified_event_not_dispatched_on_failure(): void
    {
        Event::fake();

        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);

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

        $dto = new AccountVerificationRequestDTO(
            cnic: '123456789012', // Invalid length
            mobileNo: '03001234567'
        );

        $service->verifyAccount($dto);

        Event::assertNotDispatched(AccountVerified::class);
    }

    /**
     * Test audit log is created on successful verification.
     */
    public function test_audit_log_created_on_success(): void
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
        $mockAuditService->shouldReceive('log')
            ->once()
            ->with(
                'account_verification',
                'onboarding',
                Mockery::type('array'),
                null,
                '000009'
            );

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
}

