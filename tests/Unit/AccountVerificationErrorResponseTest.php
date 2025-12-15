<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\OnboardingService;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationRequestDTO;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountVerification;

class AccountVerificationErrorResponseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_error_code_4001_invalid_access_token(): void
    {
        $this->assertErrorResponse('4001', 'Bad Request - Invalid Access Token');
    }

    public function test_error_code_4002_invalid_request_payload(): void
    {
        $this->assertErrorResponse('4002', 'Bad Request - Invalid Request Payload');
    }

    public function test_error_code_4003_invalid_authorization_header(): void
    {
        $this->assertErrorResponse('4003', 'Bad Request - Invalid Authorization Header');
    }

    public function test_error_code_4004_something_went_wrong(): void
    {
        $this->assertErrorResponse('4004', 'Something Went Wrong');
    }

    public function test_error_code_4005_record_not_found(): void
    {
        $this->assertErrorResponse('4005', 'Record Not Found');
    }

    public function test_error_code_4006_invalid_client_id_secret(): void
    {
        $this->assertErrorResponse('4006', 'Invalid Client Id/Secret');
    }

    public function test_error_code_4007_invalid_access_token(): void
    {
        $this->assertErrorResponse('4007', 'Bad Request - Invalid Access Token');
    }

    protected function assertErrorResponse(string $errorCode, string $errorMessage): void
    {
        $auth = Mockery::mock(AuthenticationServiceInterface::class);
        $auth->shouldReceive('authenticate')->once()->andReturn('token');

        $logger = Mockery::mock(LoggingServiceInterface::class);
        $logger->shouldReceive('logInfo')->once();
        $logger->shouldReceive('logRequest')->once();
        $logger->shouldReceive('logError')->once();

        $audit = Mockery::mock(AuditServiceInterface::class);
        $onboardingRepo = Mockery::mock(OnboardingRepositoryInterface::class);
        $verificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $linkingRepo = Mockery::mock(AccountLinkingRepositoryInterface::class);
        $openingRepo = Mockery::mock(AccountOpeningRepositoryInterface::class);

        $client = Mockery::mock(Client::class);
        $exception = new RequestException(
            $errorMessage,
            new GuzzleRequest('POST', '/'),
            new Response(400, [], json_encode([
                'messages' => $errorMessage,
                'errorcode' => $errorCode,
            ]))
        );

        $client->shouldReceive('post')->once()->andThrow($exception);

        $this->configure();

        $service = $this->makeService(
            $auth,
            $logger,
            $audit,
            $onboardingRepo,
            $verificationRepo,
            $linkingRepo,
            $openingRepo
        );

        $this->injectClient($service, $client);

        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            traceNo: '000009',
            dateTime: '20210105201527'
        );

        $response = $service->verifyAccount($dto);

        $this->assertFalse($response->success);
        $this->assertEquals($errorMessage, $response->message);
        $this->assertEquals($errorCode, $response->errorCode);
    }

    public function test_response_missing_verify_response_key(): void
    {
        $auth = Mockery::mock(AuthenticationServiceInterface::class);
        $auth->shouldReceive('authenticate')->once()->andReturn('token');

        $logger = Mockery::mock(LoggingServiceInterface::class);
        $logger->shouldReceive('logInfo')->once();
        $logger->shouldReceive('logRequest')->once();
        $logger->shouldReceive('logResponse')->once();

        $audit = Mockery::mock(AuditServiceInterface::class);
        $audit->shouldReceive('log')->once();

        $verificationRepo = Mockery::mock(AccountVerificationRepositoryInterface::class);
        $verificationRepo->shouldReceive('create')->once()->andReturn(new AccountVerification());

        $service = $this->makeService(
            $auth,
            $logger,
            $audit,
            Mockery::mock(OnboardingRepositoryInterface::class),
            $verificationRepo,
            Mockery::mock(AccountLinkingRepositoryInterface::class),
            Mockery::mock(AccountOpeningRepositoryInterface::class)
        );

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('post')->once()->andReturn(
            new Response(200, [], json_encode(['foo' => ['ResponseCode' => '00']]))
        );

        $this->injectClient($service, $client);

        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567'
        );

        $response = $service->verifyAccount($dto);

        $this->assertFalse($response->success);
        $this->assertEquals('Unknown error', $response->message);
    }

    private function configure(): void
    {
        config([
            'zindagi-zconnect' => [
                'api' => ['base_url' => 'https://z-sandbox.jsbl.com/zconnect'],
                'auth' => [
                    'client_id' => config('zindagi-zconnect.auth.client_id'),
                    'organization_id' => '223'],
                'modules' => [
                    'onboarding' => [
                        'account_verification' => ['endpoint' => '/api/v2/verifyacclinkacc-blb'],
                        'timeout' => 60,
                    ],
                ],
                'security' => ['verify_ssl' => true],
            ],
        ]);
    }

    private function makeService(
        AuthenticationServiceInterface $auth,
        LoggingServiceInterface $logger,
        AuditServiceInterface $audit,
        OnboardingRepositoryInterface $onboardingRepo,
        AccountVerificationRepositoryInterface $verificationRepo,
        AccountLinkingRepositoryInterface $linkingRepo,
        AccountOpeningRepositoryInterface $openingRepo
    ): OnboardingService {
        return new OnboardingService(
            Mockery::mock(\zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface::class),
            $auth,
            $logger,
            $audit,
            $onboardingRepo,
            $verificationRepo,
            $linkingRepo,
            $openingRepo
        );
    }

    private function injectClient(OnboardingService $service, Client $client): void
    {
        $ref = new \ReflectionProperty($service, 'accountVerificationClient');
        $ref->setAccessible(true);
        $ref->setValue($service, $client);
    }
}
