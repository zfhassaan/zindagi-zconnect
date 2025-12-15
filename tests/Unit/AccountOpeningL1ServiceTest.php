<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountOpeningL1RequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountOpeningL1ResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountOpening;

class AccountOpeningL1ServiceTest extends TestCase
{
    /**
     * Test successful account opening L1.
     */
    public function test_successful_account_opening_l1(): void
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
        $mockOpening = new AccountOpening();
        $mockAccountOpeningRepo->shouldReceive('create')
            ->once()
            ->andReturn($mockOpening);

        $successResponse = [
            'AccOpenL1Response' => [
                'processingCode' => 'AccountOpening',
                'MerchantType' => '0088',
                'TraceNo' => '706055',
                'CompanyName' => 'NOVA',
                'DateTime' => '20211022111230',
                'ResponseCode' => '00',
                'ResponseDetails' => ['Successful'],
                'citizenNo' => '5350403468539',
                'consumerName' => 'Test Data',
                'accountTitle' => 'Test Data',
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
                        'account_opening_l1' => [
                            'endpoint' => '/api/v2/accountopeningl1-blb2',
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
        $property = $reflection->getProperty('accountOpeningL1Client');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountOpeningL1RequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            fingerTemplate: 'test_template',
            traceNo: '123456',
            dateTime: '20211022111230',
            cnicIssuanceDate: '20191028',
            mobileNetwork: 'UFONE',
            emailId: 'test@example.com'
        );

        $response = $service->openAccountL1($dto);

        $this->assertInstanceOf(AccountOpeningL1ResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('Test Data', $response->accountTitle);
    }
}
