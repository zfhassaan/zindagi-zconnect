<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountUpgradeRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountUpgradeResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;

class AccountUpgradeServiceTest extends TestCase
{
    /**
     * Test successful account upgrade.
     */
    public function test_successful_account_upgrade(): void
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
            'UpgradeAcc' => [
                'ProcessingCode' => 'UpgradeAccount',
                'MerchantType' => '0088',
                'TraceNo' => '123456',
                'CompanyName' => 'NOVA',
                'DateTime' => '20211022111230',
                'ResponseCode' => '00',
                'ResponseDetails' => ['Successful'],
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
                        'account_upgrade' => [
                            'endpoint' => '/api/v2/upgradeaccount',
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
        $property = $reflection->getProperty('accountUpgradeClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountUpgradeRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            fingerTemplate: 'test_template',
            traceNo: '123456',
            dateTime: '20211022111230',
            terminalId: '12345678',
            mpin: '1234'
        );

        $response = $service->upgradeAccount($dto);

        $this->assertInstanceOf(AccountUpgradeResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('UpgradeAccount', $response->processingCode);
    }
}
