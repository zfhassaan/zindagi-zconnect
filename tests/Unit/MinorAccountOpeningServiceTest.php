<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\MinorAccountOpeningRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\MinorAccountOpeningResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;

class MinorAccountOpeningServiceTest extends TestCase
{
    /**
     * Test successful minor account opening.
     */
    public function test_successful_minor_account_opening(): void
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
            'minorAccountOpeningRes' => [
                'responseCode' => '00', // Assuming 00 is success
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
                        'minor_account_opening' => [
                             'endpoint' => '/api/v1/M0AccountOpening',
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
        $property = $reflection->getProperty('minorAccountOpeningClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new MinorAccountOpeningRequestDTO(
            rrn: '1255822445001',
            dateTime: '11172022',
            accountTitle: 'Ahsan',
            cnic: '3520243953533',
            issuanceDate: '2020-08-12',
            mobileNumber: '03200460403',
            motherMaidenName: 'Nusrat',
            fatherName: 'Javed',
            placeOfBirth: 'Lahore',
            dateOfBirth: '1994-09-30',
            address: 'Gulberg 3 lahore',
            nicExpiry: '2025-03-30',
            parentCnicPic: '',
            snicPic: '',
            minorCustomerPic: '',
            fatherMotherMobileNumber: '03734642041',
            fatherCnic: '3570730079593',
            fatherCnicIssuanceDate: '2020-08-25',
            fatherCnicExpiryDate: '2025-03-30',
            motherCnic: '3520130109590',
            email: 'test@example.com'
        );

        $response = $service->minorAccountOpening($dto);

        $this->assertInstanceOf(MinorAccountOpeningResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('Successful', $response->responseDescription);
    }
    
    /**
     * Test failure response.
     */
    public function test_failure_minor_account_opening(): void
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
        $mockAuditService->shouldReceive('log')->once(); // It logs even on failure response if it's 200 OK but application error? 
        // Logic says: logs if response is 200. Yes.
        
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);
        $mockAccountOpeningRepo = Mockery::mock(AccountOpeningRepositoryInterface::class);

        $failureResponse = [
            'minorAccountOpeningRes' => [
                'responseCode' => '24',
                'responseDescription' => 'CNIC is already in use',
                'hashData' => 'some_hash',
            ],
        ];

        $mockClient = Mockery::mock(Client::class);
        $mockResponse = new Response(200, [], json_encode($failureResponse));
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
                        'minor_account_opening' => [
                             'endpoint' => '/api/v1/M0AccountOpening',
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
        $property = $reflection->getProperty('minorAccountOpeningClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);
        
        $dto = new MinorAccountOpeningRequestDTO(
            rrn: '1255822445001',
            dateTime: '11172022',
            accountTitle: 'Ahsan',
            cnic: '3520243953533',
            issuanceDate: '2020-08-12',
            mobileNumber: '03200460403',
            motherMaidenName: 'Nusrat',
            fatherName: 'Javed',
            placeOfBirth: 'Lahore',
            dateOfBirth: '1994-09-30',
            address: 'Gulberg 3 lahore',
            nicExpiry: '2025-03-30',
            parentCnicPic: '',
            snicPic: '',
            minorCustomerPic: '',
            fatherMotherMobileNumber: '03734642041',
            fatherCnic: '3570730079593',
            fatherCnicIssuanceDate: '2020-08-25',
            fatherCnicExpiryDate: '2025-03-30',
            motherCnic: '3520130109590',
            email: 'test@example.com'
        );

        $response = $service->minorAccountOpening($dto);
        
        $this->assertFalse($response->success);
        $this->assertEquals('24', $response->responseCode);
    }
}
