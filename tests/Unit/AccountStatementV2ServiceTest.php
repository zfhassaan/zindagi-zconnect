<?php

declare(strict_types=1);

namespace Tests\Unit;


use Orchestra\Testbench\TestCase;
use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountStatementV2RequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountStatementV2ResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;

class AccountStatementV2ServiceTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return ['zfhassaan\ZindagiZconnect\Providers\ZindagiZconnectServiceProvider'];
    }

    /**
     * Test successful account statement v2 fetch.
     */
    public function test_successful_account_statement_v2_fetch(): void
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
            'AccountStatementRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Successful',
                'ClosingBalanceStatement' => [
                    [
                        'dateTime' => '20250116174251',
                        'mobileNumber' => '03343118436',
                        'dayEndBalance' => '5000'
                    ]
                ],
                'DigiWalletStatement' => [
                    [
                        'transactionAmount' => 100,
                        'transactionType' => 'Debit',
                        'mobileNumber' => '03343118436'
                    ]
                ],
                'HashData' => 'some-hash',
                'ResponseDateTime' => '20250116174253',
                'Rrn' => '123456789'
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
                        'account_statement_v2' => [
                             'endpoint' => '/api/v2/digiWalletStatement',
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
        $property = $reflection->getProperty('accountStatementV2Client');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new AccountStatementV2RequestDTO(
            transmissionDatetime: '0116174253',
            systemsTraceAuditNumber: '396583',
            timeLocalTransaction: '054253',
            dateLocalTransaction: '20250116174251',
            accountNumber: '03343118436',
            fromDate: '12/16/2025',
            toDate: '01/16/2025'
        );

        $response = $service->accountStatementV2($dto);

        $this->assertInstanceOf(AccountStatementV2ResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('Successful', $response->responseDescription);
        $this->assertIsArray($response->closingBalanceStatement);
        $this->assertIsArray($response->digiWalletStatement);
        $this->assertCount(1, $response->closingBalanceStatement);
        $this->assertCount(1, $response->digiWalletStatement);
        $this->assertEquals('5000', $response->closingBalanceStatement[0]['dayEndBalance']);
    }
}
