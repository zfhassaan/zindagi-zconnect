<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\MinorAccountVerificationRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\MinorAccountVerificationResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;

class MinorAccountVerificationServiceTest extends TestCase
{
    /**
     * Test successful minor account verification.
     */
    public function test_successful_minor_account_verification(): void
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
            'minorAccountVerifyRes' => [
                'responseCode' => '00',
                'responseDescription' => 'Successful',
                'hashData' => 'some_hash',
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
                        'minor_account_verification' => [
                             'endpoint' => '/api/v1/M0AccountVerification',
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
        $property = $reflection->getProperty('minorAccountVerificationClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new MinorAccountVerificationRequestDTO(
            rrn: '1255822445001',
            dateTime: '11172022',
            cnic: '3520243953533',
            issuanceDate: '2020-08-12',
            mobileNumber: '03200460403'
        );

        $response = $service->minorAccountVerification($dto);

        $this->assertInstanceOf(MinorAccountVerificationResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('Successful', $response->responseDescription);
        $this->assertEquals('some_hash', $response->hashData);
    }

    public function test_failure_minor_account_verification(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->once()->andReturn(new Response(200, [], json_encode([
            'minorAccountVerifyRes' => [
                'responseCode' => '24',
                'responseDescription' => 'CNIC is already in use',
                'hashData' => 'some_hash',
            ],
        ])));

        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')->once()->andReturn('test_access_token');

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logRequest')->once();
        $mockLoggingService->shouldReceive('logResponse')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockAuditService->shouldReceive('log')->once();

        $service = $this->makeService($mockAuthService, $mockLoggingService, $mockAuditService, $mockClient);
        $response = $service->minorAccountVerification($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertEquals('24', $response->responseCode);
        $this->assertEquals('CNIC is already in use', $response->responseDescription);
    }

    public function test_minor_account_verification_with_invalid_json(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->once()->andReturn(new Response(200, [], 'not-json'));

        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')->once()->andReturn('test_access_token');

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logRequest')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $service = $this->makeService($mockAuthService, $mockLoggingService, Mockery::mock(AuditServiceInterface::class), $mockClient);
        $response = $service->minorAccountVerification($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Invalid response from API', $response->responseDescription);
    }

    public function test_minor_account_verification_with_network_error(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->once()->andThrow(new RequestException(
            'Connection timeout',
            Mockery::mock(RequestInterface::class)
        ));

        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')->once()->andReturn('test_access_token');

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logRequest')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $service = $this->makeService($mockAuthService, $mockLoggingService, Mockery::mock(AuditServiceInterface::class), $mockClient);
        $response = $service->minorAccountVerification($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Failed to verify minor account', $response->responseDescription);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function requestDto(): MinorAccountVerificationRequestDTO
    {
        return new MinorAccountVerificationRequestDTO(
            rrn: '1255822445001',
            dateTime: '11172022',
            cnic: '3520243953533',
            issuanceDate: '2020-08-12',
            mobileNumber: '03200460403'
        );
    }

    private function makeService($auth, $logging, $audit, Client $client): OnboardingService
    {
        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => 'test_id',
                    'organization_id' => '223',
                ],
                'modules' => [
                    'onboarding' => [
                        'minor_account_verification' => [
                            'endpoint' => '/api/v1/M0AccountVerification',
                        ],
                    ],
                ],
                'security' => ['verify_ssl' => true],
            ],
        ]);

        $service = new OnboardingService(
            Mockery::mock(\zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface::class),
            $auth,
            $logging,
            $audit,
            Mockery::mock(OnboardingRepositoryInterface::class),
            Mockery::mock(AccountVerificationRepositoryInterface::class),
            Mockery::mock(AccountLinkingRepositoryInterface::class),
            Mockery::mock(AccountOpeningRepositoryInterface::class)
        );

        $property = (new \ReflectionClass($service))->getProperty('minorAccountVerificationClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        return $service;
    }
}
