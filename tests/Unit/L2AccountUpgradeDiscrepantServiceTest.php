<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\L2AccountUpgradeDiscrepantRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\L2AccountUpgradeDiscrepantResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;

class L2AccountUpgradeDiscrepantServiceTest extends TestCase
{
    /**
     * Test successful submission of L2 account upgrade discrepant data.
     */
    public function test_successful_submit_l2_account_upgrade_discrepant_data(): void
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
            'l2AccountUpgradeDiscrepantRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Successful',
                'Rrn' => '000000770011',
                'ResponseDateTime' => '20220729171717',
                'HashData' => 'some_hash',
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
                        'l2_account_upgrade_discrepant' => [ // Changed key to match what should be expected
                            'endpoint' => '/api/v1/l2AccountUpgradeDiscrepant',
                        ],
                        'timeout' => 60,
                        'get_l2_discrepant_data' => [ // Old key also needs to be present if code uses it? No, code uses separate setup.
                             'endpoint' => '/api/v1/getL2AccountUpgradeDiscrepant',
                        ]
                    ],
                ],
                'security' => ['verify_ssl' => true],
            ],
        ]);
        
        // Note: constructor uses 'get_l2_discrepant_data' key for this client erroneously in current code.
        // We will fix the code to use 'l2_account_upgrade_discrepant' key, so test uses the correct key.

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
        $property = $reflection->getProperty('l2AccountUpgradeDiscrepantClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new L2AccountUpgradeDiscrepantRequestDTO(
            mobileNumber: '03200460403',
            dateTime: '20220729171717',
            rrn: '000000770011',
            cnic: '3660238069587',
            consumerName: 'HAMZA',
            fatherHusbandName: 'KHALID',
            purposeOfAccount: 'BUSINESS',
            sourceOfIncome: 'JOB',
            expectedMonthlyTurnover: '400000',
            birthPlace: 'LAHORE',
            motherMaiden: 'KIRA',
            emailAddress: 'test@example.com',
            mailingAddress: 'ISLAMABAD',
            permanentAddress: 'ISLAMABAD',
            city: 'Islamabad',
            area: 'Gulberg Greens',
            houseNumber: '35/69'
        );

        $response = $service->submitL2AccountUpgradeDiscrepant($dto);

        $this->assertInstanceOf(L2AccountUpgradeDiscrepantResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('Successful', $response->message);
        $this->assertEquals('000000770011', $response->rrn);
        $this->assertEquals('some_hash', $response->hashData);
    }

    /**
     * Test failure when submitting L2 account upgrade discrepant data.
     */
    public function test_failure_submit_l2_account_upgrade_discrepant_data(): void
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
        $mockAuditService->shouldReceive('log')->never();

        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);
        $mockAccountOpeningRepo = Mockery::mock(AccountOpeningRepositoryInterface::class);

        $errorResponse = [
            'ResponseCode' => '11',
            'ResponseDescription' => 'Error occurred',
             'l2AccountUpgradeDiscrepantRes' => [ // Sometimes wrapper is missing in errors, but usually top level fields or nested. DTO handles both.
                'ResponseCode' => '11',
             ]
        ];
        
        // Simulating error response structure
         $errorResponse = [
                'ResponseCode' => '11',
                'ResponseDescription' => 'Error occurred',
        ];

        $mockClient = Mockery::mock(Client::class);
        // Assuming 200 OK but logic error, or 400.
        // Let's assume 200 but error code in body for business logic error, or exception for HTTP error.
        // Service code handles GuzzleException. Let's throw one.
        
        $mockClient->shouldReceive('post')
            ->once()
            ->andThrow(new \Exception('Network Error'));

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
        $property = $reflection->getProperty('l2AccountUpgradeDiscrepantClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new L2AccountUpgradeDiscrepantRequestDTO(
            mobileNumber: '03200460403',
            dateTime: '20220729171717',
            rrn: '000000770011',
            cnic: '3660238069587',
            consumerName: 'HAMZA',
            fatherHusbandName: 'KHALID',
            purposeOfAccount: 'BUSINESS',
            sourceOfIncome: 'JOB',
            expectedMonthlyTurnover: '400000',
            birthPlace: 'LAHORE',
            motherMaiden: 'KIRA',
            emailAddress: 'test@example.com',
            mailingAddress: 'ISLAMABAD',
            permanentAddress: 'ISLAMABAD',
            city: 'Islamabad',
            area: 'Gulberg Greens',
            houseNumber: '35/69'
        );

        $response = $service->submitL2AccountUpgradeDiscrepant($dto);

        $this->assertInstanceOf(L2AccountUpgradeDiscrepantResponseDTO::class, $response);
        $this->assertFalse($response->success);
        $this->assertStringContainsString('Network Error', $response->message);
    }
}
