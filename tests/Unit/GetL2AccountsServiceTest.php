<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\GetL2AccountsRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\GetL2AccountsResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;

class GetL2AccountsServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test successful get L2 accounts with full response structure.
     */
    public function test_successful_get_l2_accounts_with_complete_data(): void
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

        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);
        $mockAccountOpeningRepo = Mockery::mock(AccountOpeningRepositoryInterface::class);

        $successResponse = [
            'level2AccountsRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'ResponseDateTime' => '2024111045235146',
                'Rrn' => '229830310784',
                'HashData' => 'b7e7d7332f7866d1d1509b6b9cb784384ad19d7588c9e1522ceffcd630ca72b7',
                'L2Accounts' => [
                    [
                        'id' => '1',
                        'accountId' => '1001',
                        'accountName' => 'ULTRA',
                        'description' => 'Transaction limits up to PKR 1 million and international payments.',
                        'details' => [
                            [
                                'title' => 'Features and benifits',
                                'data' => [
                                    'Unlimite account limits',
                                    'Currency PK',
                                    'International transactions',
                                    'Invest more in mutual funds and stocks',
                                    'Instant account opening',
                                ],
                            ],
                            [
                                'title' => 'What you will need',
                                'data' => [
                                    'Your valid CNIC - you\'ll need to scan it',
                                    'Your sim registered in your name',
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => '2',
                        'accountId' => '1002',
                        'accountName' => 'ULTRA SIGNATURE',
                        'description' => 'Full fledged bank account with enhanced limits.',
                        'details' => [
                            [
                                'title' => 'Features and benifits',
                                'data' => [
                                    'Enhanced transaction limits',
                                    'Premium banking features',
                                ],
                            ],
                        ],
                    ],
                ],
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
                        'get_l2_accounts' => [
                            'endpoint' => '/api/v1/level2Accounts',
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
        $property = $reflection->getProperty('getL2AccountsClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '229830310784',
            channelId: 'NOVA',
            terminalId: 'NOVA'
        );

        $response = $service->getL2Accounts($dto);

        $this->assertInstanceOf(GetL2AccountsResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('Success', $response->message);
        $this->assertEquals('229830310784', $response->rrn);
        $this->assertEquals('2024111045235146', $response->responseDateTime);
        $this->assertNotNull($response->hashData);
        $this->assertIsArray($response->l2Accounts);
        $this->assertCount(2, $response->l2Accounts);
        
        // Validate first account structure
        $firstAccount = $response->l2Accounts[0];
        $this->assertEquals('1', $firstAccount['id']);
        $this->assertEquals('1001', $firstAccount['accountId']);
        $this->assertEquals('ULTRA', $firstAccount['accountName']);
        $this->assertArrayHasKey('description', $firstAccount);
        $this->assertArrayHasKey('details', $firstAccount);
        $this->assertCount(2, $firstAccount['details']);
        
        // Validate account details structure
        $this->assertEquals('Features and benifits', $firstAccount['details'][0]['title']);
        $this->assertIsArray($firstAccount['details'][0]['data']);
        $this->assertCount(5, $firstAccount['details'][0]['data']);
    }

    /**
     * Test successful get L2 accounts with minimal response.
     */
    public function test_successful_get_l2_accounts_minimal_response(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')->once()->andReturn('test_token');

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logRequest')->once();
        $mockLoggingService->shouldReceive('logResponse')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);
        $mockAccountOpeningRepo = Mockery::mock(AccountOpeningRepositoryInterface::class);

        $minimalResponse = [
            'level2AccountsRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'Rrn' => '123456789012',
                'L2Accounts' => [],
            ],
        ];

        $mockClient = Mockery::mock(Client::class);
        $mockResponse = new Response(200, [], json_encode($minimalResponse));
        $mockClient->shouldReceive('post')->once()->andReturn($mockResponse);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => ['client_id' => 'test_id', 'organization_id' => '223'],
                'modules' => [
                    'onboarding' => [
                        'get_l2_accounts' => ['endpoint' => '/api/v1/level2Accounts'],
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
        $property = $reflection->getProperty('getL2AccountsClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '123456789012'
        );

        $response = $service->getL2Accounts($dto);

        $this->assertInstanceOf(GetL2AccountsResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEmpty($response->l2Accounts);
    }

    /**
     * Test failed get L2 accounts with error response code.
     */
    public function test_failed_get_l2_accounts_with_error_code(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')->once()->andReturn('test_token');

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logRequest')->once();
        $mockLoggingService->shouldReceive('logResponse')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);
        $mockAccountOpeningRepo = Mockery::mock(AccountOpeningRepositoryInterface::class);

        $errorResponse = [
            'level2AccountsRes' => [
                'ResponseCode' => '01',
                'ResponseDescription' => 'Invalid Request',
                'Rrn' => '123456789012',
            ],
        ];

        $mockClient = Mockery::mock(Client::class);
        $mockResponse = new Response(200, [], json_encode($errorResponse));
        $mockClient->shouldReceive('post')->once()->andReturn($mockResponse);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => ['client_id' => 'test_id', 'organization_id' => '223'],
                'modules' => [
                    'onboarding' => [
                        'get_l2_accounts' => ['endpoint' => '/api/v1/level2Accounts'],
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
        $property = $reflection->getProperty('getL2AccountsClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '123456789012'
        );

        $response = $service->getL2Accounts($dto);

        $this->assertInstanceOf(GetL2AccountsResponseDTO::class, $response);
        $this->assertFalse($response->success);
        $this->assertEquals('01', $response->responseCode);
        $this->assertEquals('Invalid Request', $response->message);
    }

    /**
     * Test get L2 accounts with network error.
     */
    public function test_get_l2_accounts_with_network_error(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')->once()->andReturn('test_token');

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logRequest')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);
        $mockAccountOpeningRepo = Mockery::mock(AccountOpeningRepositoryInterface::class);

        $mockClient = Mockery::mock(Client::class);
        $mockRequest = Mockery::mock(RequestInterface::class);
        $exception = new RequestException(
            'Connection timeout',
            $mockRequest
        );
        $mockClient->shouldReceive('post')->once()->andThrow($exception);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => ['client_id' => 'test_id', 'organization_id' => '223'],
                'modules' => [
                    'onboarding' => [
                        'get_l2_accounts' => ['endpoint' => '/api/v1/level2Accounts'],
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
        $property = $reflection->getProperty('getL2AccountsClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '123456789012'
        );

        $response = $service->getL2Accounts($dto);

        $this->assertInstanceOf(GetL2AccountsResponseDTO::class, $response);
        $this->assertFalse($response->success);
        $this->assertStringContainsString('Failed to get L2 accounts', $response->message);
    }

    /**
     * Test get L2 accounts with invalid JSON response.
     */
    public function test_get_l2_accounts_with_invalid_json_response(): void
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')->once()->andReturn('test_token');

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        $mockLoggingService->shouldReceive('logInfo')->once();
        $mockLoggingService->shouldReceive('logRequest')->once();
        $mockLoggingService->shouldReceive('logError')->once();

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        $mockOnboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $mockAccountVerificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $mockAccountLinkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);
        $mockAccountOpeningRepo = Mockery::mock(AccountOpeningRepositoryInterface::class);

        $mockClient = Mockery::mock(Client::class);
        $mockResponse = new Response(200, [], 'Invalid JSON');
        $mockClient->shouldReceive('post')->once()->andReturn($mockResponse);

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => ['client_id' => 'test_id', 'organization_id' => '223'],
                'modules' => [
                    'onboarding' => [
                        'get_l2_accounts' => ['endpoint' => '/api/v1/level2Accounts'],
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
        $property = $reflection->getProperty('getL2AccountsClient');
        $property->setAccessible(true);
        $property->setValue($service, $mockClient);

        $dto = new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '123456789012'
        );

        $response = $service->getL2Accounts($dto);

        $this->assertInstanceOf(GetL2AccountsResponseDTO::class, $response);
        $this->assertFalse($response->success);
        $this->assertStringContainsString('Invalid response from API', $response->message);
    }

    /**
     * Test DTO validation for invalid DateTime length.
     */
    public function test_dto_validation_invalid_datetime_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('DateTime must be exactly 16 characters');

        new GetL2AccountsRequestDTO(
            dateTime: '20241110',  // Too short
            rrn: '123456789012'
        );
    }

    /**
     * Test DTO validation for empty RRN.
     */
    public function test_dto_validation_empty_rrn(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('RRN cannot be empty');

        new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: ''
        );
    }

    /**
     * Test DTO validation for invalid RRN length.
     */
    public function test_dto_validation_invalid_rrn_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('RRN must be exactly 12 characters');

        new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '12345'  // Too short
        );
    }

    /**
     * Test DTO validation for empty ChannelId.
     */
    public function test_dto_validation_empty_channel_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ChannelId cannot be empty');

        new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '123456789012',
            channelId: ''
        );
    }

    /**
     * Test DTO validation for empty TerminalId.
     */
    public function test_dto_validation_empty_terminal_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('TerminalId cannot be empty');

        new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '123456789012',
            channelId: 'NOVA',
            terminalId: ''
        );
    }

    /**
     * Test request DTO toArray method.
     */
    public function test_request_dto_to_array(): void
    {
        $dto = new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '229830310784',
            channelId: 'NOVA',
            terminalId: 'NOVA'
        );

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('level2AccountsReq', $array);
        $this->assertEquals('2024111045235146', $array['level2AccountsReq']['DateTime']);
        $this->assertEquals('229830310784', $array['level2AccountsReq']['Rrn']);
        $this->assertEquals('NOVA', $array['level2AccountsReq']['ChannelId']);
        $this->assertEquals('NOVA', $array['level2AccountsReq']['TerminalId']);
        $this->assertEquals('', $array['level2AccountsReq']['Reserved1']);
    }

    /**
     * Test response DTO fromArray method with complete data.
     */
    public function test_response_dto_from_array_complete(): void
    {
        $responseData = [
            'level2AccountsRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'ResponseDateTime' => '2024111045235146',
                'Rrn' => '229830310784',
                'HashData' => 'test_hash',
                'L2Accounts' => [
                    [
                        'id' => '1',
                        'accountId' => '1001',
                        'accountName' => 'ULTRA',
                        'description' => 'Premium account',
                        'details' => [
                            [
                                'title' => 'Features',
                                'data' => ['Feature 1', 'Feature 2'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $dto = GetL2AccountsResponseDTO::fromArray($responseData);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Success', $dto->message);
        $this->assertEquals('229830310784', $dto->rrn);
        $this->assertEquals('2024111045235146', $dto->responseDateTime);
        $this->assertEquals('test_hash', $dto->hashData);
        $this->assertCount(1, $dto->l2Accounts);
        $this->assertEquals('ULTRA', $dto->l2Accounts[0]['accountName']);
        $this->assertCount(1, $dto->l2Accounts[0]['details']);
    }
}
