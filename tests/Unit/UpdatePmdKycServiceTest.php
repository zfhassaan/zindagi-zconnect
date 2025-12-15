<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\UpdatePmdKycRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\UpdatePmdKycResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;

class UpdatePmdKycServiceTest extends TestCase
{
    /**
     * Test successful update PMD and KYC.
     */
    public function test_successful_update_pmd_kyc(): void
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
            'updatePmdAndKycRes' => [
                'Rrn' => '123456789200',
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'ResponseDateTime' => '20221229181818',
                'HashData' => 'hash_string'
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
                        'update_pmd_kyc' => [
                            'endpoint' => '/api/v1/updatePmdAndKyc',
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
        $property = $reflection->getProperty('updatePmdKycClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new UpdatePmdKycRequestDTO(
            mobileNumber: '03008214443',
            dateTime: '20221229181818',
            rrn: '123456789200',
            accountId: '1002',
            motherName: 'ABC',
            placeOfBirth: 'LAHORE'
        );

        $response = $service->updatePmdAndKyc($dto);

        $this->assertInstanceOf(UpdatePmdKycResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('Success', $response->message);
        $this->assertEquals('123456789200', $response->rrn);
        $this->assertEquals('hash_string', $response->hashData);
    }
}
