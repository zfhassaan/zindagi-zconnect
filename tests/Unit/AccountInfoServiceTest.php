<?php

declare(strict_types=1);

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountInfoRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountInfoResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;

class AccountInfoServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_successful_get_account_info(): void
    {
        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logResponse'],
            audit: true,
            client: $this->mockPostClient([
                'accountInfoRes' => [
                    'ResponseCode' => '00',
                    'ResponseDescription' => 'Successful',
                    'ResponseDateTime' => '20250116174253',
                    'DateOfBirth' => '19900101',
                    'AccountLevelCode' => 'L1',
                    'Email' => 'user@example.com',
                    'Cnic' => '1234567890123',
                    'Segment' => 'RETAIL',
                    'Rrn' => '12345678901234',
                    'AccountNumber' => '001122334455',
                    'AccountNatureCode' => 'SAV',
                    'AccountTitle' => 'JOHN DOE',
                    'AccountStatusCode' => 'A',
                    'RegistrationTypeCode' => '01',
                    'HashData' => 'abc123hash',
                ],
            ])
        );

        $response = $service->getAccountInfo($this->requestDto());

        $this->assertInstanceOf(AccountInfoResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('JOHN DOE', $response->accountTitle);
        $this->assertEquals('001122334455', $response->accountNumber);
        $this->assertEquals('1234567890123', $response->cnic);
        $this->assertEquals('abc123hash', $response->hashData);
    }

    public function test_get_account_info_with_api_error_code(): void
    {
        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logResponse'],
            audit: true,
            client: $this->mockPostClient([
                'accountInfoRes' => [
                    'ResponseCode' => '01',
                    'ResponseDescription' => 'Account not found',
                    'Rrn' => '12345678901234',
                ],
            ])
        );

        $response = $service->getAccountInfo($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertEquals('01', $response->responseCode);
        $this->assertEquals('Account not found', $response->message);
    }

    public function test_get_account_info_with_invalid_json(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->once()->andReturn(new Response(200, [], 'not-json'));

        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logError'],
            audit: false,
            client: $mockClient
        );

        $response = $service->getAccountInfo($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Invalid response from API', $response->message);
    }

    public function test_get_account_info_with_network_error(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->once()->andThrow(new RequestException(
            'Connection timeout',
            Mockery::mock(RequestInterface::class)
        ));

        $service = $this->makeService(
            logging: ['logInfo', 'logRequest', 'logError'],
            audit: false,
            client: $mockClient
        );

        $response = $service->getAccountInfo($this->requestDto());

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Failed to get account info', $response->message);
    }

    private function requestDto(): AccountInfoRequestDTO
    {
        return new AccountInfoRequestDTO(
            mobileNumber: '03343118436',
            dateTime: '20250116174251',
            rrn: '12345678901234'
        );
    }

    private function mockPostClient(array $payload): Client
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->once()->andReturn(new Response(200, [], json_encode($payload)));

        return $mockClient;
    }

    private function makeService(array $logging, bool $audit, Client $client): OnboardingService
    {
        $mockAuthService = Mockery::mock(AuthenticationServiceInterface::class);
        $mockAuthService->shouldReceive('authenticate')->once()->andReturn('test_access_token');

        $mockLoggingService = Mockery::mock(LoggingServiceInterface::class);
        foreach ($logging as $method) {
            $mockLoggingService->shouldReceive($method)->once();
        }

        $mockAuditService = Mockery::mock(AuditServiceInterface::class);
        if ($audit) {
            $mockAuditService->shouldReceive('log')->once();
        }

        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => 'test_id',
                    'organization_id' => '223',
                ],
                'modules' => [
                    'onboarding' => [
                        'get_account_info' => [
                            'endpoint' => '/api/v1/accountInfo',
                        ],
                        'timeout' => 60,
                    ],
                ],
                'security' => ['verify_ssl' => true],
            ],
        ]);

        $service = new OnboardingService(
            Mockery::mock(HttpClientInterface::class),
            $mockAuthService,
            $mockLoggingService,
            $mockAuditService,
            Mockery::mock(OnboardingRepositoryInterface::class),
            Mockery::mock(AccountVerificationRepositoryInterface::class),
            Mockery::mock(AccountLinkingRepositoryInterface::class),
            Mockery::mock(AccountOpeningRepositoryInterface::class)
        );

        $property = (new \ReflectionClass($service))->getProperty('accountInfoClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        return $service;
    }
}
