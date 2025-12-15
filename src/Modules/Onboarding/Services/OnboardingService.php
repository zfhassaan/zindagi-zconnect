<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Services;

use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\Contracts\OnboardingServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\OnboardingRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\OnboardingResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingResponseDTO;
use zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\OnboardingInitiated;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\OnboardingVerified;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\OnboardingCompleted;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\AccountVerified;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\AccountLinked;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Event;

class OnboardingService implements OnboardingServiceInterface
{
    protected string $endpoint;
    protected Client $accountVerificationClient;
    protected string $accountVerificationEndpoint;
    protected Client $accountLinkingClient;
    protected string $accountLinkingEndpoint;

    public function __construct(
        protected HttpClientInterface $httpClient,
        protected AuthenticationServiceInterface $authService,
        protected LoggingServiceInterface $loggingService,
        protected AuditServiceInterface $auditService,
        protected OnboardingRepositoryInterface $repository,
        protected AccountVerificationRepositoryInterface $accountVerificationRepository,
        protected AccountLinkingRepositoryInterface $accountLinkingRepository
    ) {
        $this->endpoint = config('zindagi-zconnect.modules.onboarding.endpoint', '/onboarding');
        
        // Setup account verification client
        $config = config('zindagi-zconnect', []);
        $accountVerificationConfig = $config['modules']['onboarding']['account_verification'] ?? [];
        
        $baseUrl = $config['api']['base_url'] ?? 'https://z-sandbox.jsbl.com/zconnect';
        $this->accountVerificationEndpoint = $accountVerificationConfig['endpoint'] ?? '/api/v2/verifyacclinkacc-blb';
        
        $this->accountVerificationClient = new Client([
            'base_uri' => $baseUrl,
            'timeout' => $config['modules']['onboarding']['timeout'] ?? 60,
            'verify' => $config['security']['verify_ssl'] ?? true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        // Setup account linking client
        $accountLinkingConfig = $config['modules']['onboarding']['account_linking'] ?? [];
        $this->accountLinkingEndpoint = $accountLinkingConfig['endpoint'] ?? '/api/v2/linkacc-blb';
        
        $this->accountLinkingClient = new Client([
            'base_uri' => $baseUrl,
            'timeout' => $config['modules']['onboarding']['timeout'] ?? 60,
            'verify' => $config['security']['verify_ssl'] ?? true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Initiate customer onboarding.
     */
    public function initiate(OnboardingRequestDTO $dto): OnboardingResponseDTO
    {
        try {
            $this->loggingService->logInfo('Initiating customer onboarding', [
                'cnic' => $dto->cnic,
                'email' => $dto->email,
            ]);

            // Validate DTO
            $this->validateOnboardingRequest($dto);

            // Make API request
            $response = $this->httpClient->post($this->endpoint . '/initiate', $dto->toArray());
            $responseData = json_decode($response->getBody()->getContents(), true);

            $responseDTO = OnboardingResponseDTO::fromApiResponse($responseData);

            if ($responseDTO->success && $responseDTO->referenceId) {
                // Store in database
                $onboarding = $this->repository->create([
                    'reference_id' => $responseDTO->referenceId,
                    'cnic' => $dto->cnic,
                    'full_name' => $dto->fullName,
                    'mobile_number' => $dto->mobileNumber,
                    'email' => $dto->email,
                    'status' => 'initiated',
                    'request_data' => $dto->toArray(),
                    'response_data' => $responseData,
                ]);

                // Audit log
                $this->auditService->log(
                    'onboarding_initiated',
                    'onboarding',
                    $dto->toArray(),
                    auth()->id(),
                    $responseDTO->referenceId
                );

                // Fire event
                Event::dispatch(new OnboardingInitiated($onboarding, $responseDTO));
            }

            return $responseDTO;
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'Failed to initiate onboarding',
                ['cnic' => $dto->cnic],
                $e
            );

            return new OnboardingResponseDTO(
                success: false,
                status: 'failed',
                message: 'Failed to initiate onboarding: ' . $e->getMessage(),
                errors: ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Verify customer information.
     */
    public function verify(string $referenceId, array $verificationData): OnboardingResponseDTO
    {
        try {
            $this->loggingService->logInfo('Verifying customer onboarding', [
                'reference_id' => $referenceId,
            ]);

            $response = $this->httpClient->post(
                $this->endpoint . '/verify',
                array_merge(['reference_id' => $referenceId], $verificationData)
            );

            $responseData = json_decode($response->getBody()->getContents(), true);
            $responseDTO = OnboardingResponseDTO::fromApiResponse($responseData);

            if ($responseDTO->success) {
                $this->repository->updateByReferenceId($referenceId, [
                    'status' => 'verified',
                    'verification_data' => $verificationData,
                    'response_data' => $responseData,
                ]);

                $onboarding = $this->repository->findByReferenceId($referenceId);

                $this->auditService->log(
                    'onboarding_verified',
                    'onboarding',
                    $verificationData,
                    auth()->id(),
                    $referenceId
                );

                Event::dispatch(new OnboardingVerified($onboarding, $responseDTO));
            }

            return $responseDTO;
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'Failed to verify onboarding',
                ['reference_id' => $referenceId],
                $e
            );

            return new OnboardingResponseDTO(
                success: false,
                status: 'failed',
                message: 'Failed to verify onboarding: ' . $e->getMessage(),
                errors: ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Get onboarding status.
     */
    public function getStatus(string $referenceId): OnboardingResponseDTO
    {
        try {
            $response = $this->httpClient->get($this->endpoint . '/status/' . $referenceId);
            $responseData = json_decode($response->getBody()->getContents(), true);

            return OnboardingResponseDTO::fromApiResponse($responseData);
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'Failed to get onboarding status',
                ['reference_id' => $referenceId],
                $e
            );

            return new OnboardingResponseDTO(
                success: false,
                status: 'failed',
                message: 'Failed to get onboarding status: ' . $e->getMessage(),
                errors: ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Complete onboarding process.
     */
    public function complete(string $referenceId, array $completionData): OnboardingResponseDTO
    {
        try {
            $this->loggingService->logInfo('Completing customer onboarding', [
                'reference_id' => $referenceId,
            ]);

            $response = $this->httpClient->post(
                $this->endpoint . '/complete',
                array_merge(['reference_id' => $referenceId], $completionData)
            );

            $responseData = json_decode($response->getBody()->getContents(), true);
            $responseDTO = OnboardingResponseDTO::fromApiResponse($responseData);

            if ($responseDTO->success) {
                $this->repository->updateByReferenceId($referenceId, [
                    'status' => 'completed',
                    'completion_data' => $completionData,
                    'response_data' => $responseData,
                    'completed_at' => now(),
                ]);

                $onboarding = $this->repository->findByReferenceId($referenceId);

                $this->auditService->log(
                    'onboarding_completed',
                    'onboarding',
                    $completionData,
                    auth()->id(),
                    $referenceId
                );

                Event::dispatch(new OnboardingCompleted($onboarding, $responseDTO));
            }

            return $responseDTO;
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'Failed to complete onboarding',
                ['reference_id' => $referenceId],
                $e
            );

            return new OnboardingResponseDTO(
                success: false,
                status: 'failed',
                message: 'Failed to complete onboarding: ' . $e->getMessage(),
                errors: ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Verify account link with CNIC and mobile number.
     */
    public function verifyAccount(AccountVerificationRequestDTO $dto): AccountVerificationResponseDTO
    {
        try {
            $this->loggingService->logInfo('Initiating account verification', [
                'cnic' => $dto->cnic,
                'mobile_no' => $dto->mobileNo,
                'trace_no' => $dto->traceNo,
            ]);

            // Validate DTO
            $this->validateVerificationRequest($dto);

            // Get authentication token
            $token = $this->authService->authenticate();
            $config = config('zindagi-zconnect');

            // Prepare headers
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'clientId' => $config['auth']['client_id'],
                'clientSecret' => $token,
                'organizationId' => $config['auth']['organization_id'] ?? '223',
            ];

            // Prepare request body
            $requestBody = $dto->toApiRequest();

            // Log request
            $this->loggingService->logRequest($this->accountVerificationEndpoint, $requestBody, $headers);

            // Make API request
            $response = $this->accountVerificationClient->post($this->accountVerificationEndpoint, [
                'headers' => $headers,
                'json' => $requestBody,
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            // Log response
            $this->loggingService->logResponse(
                $this->accountVerificationEndpoint,
                $responseData,
                $response->getStatusCode()
            );

            $responseDTO = AccountVerificationResponseDTO::fromApiResponse($responseData);

            // Store in database
            $verification = $this->accountVerificationRepository->create([
                'trace_no' => $dto->traceNo,
                'cnic' => $dto->cnic,
                'mobile_no' => $dto->mobileNo,
                'merchant_type' => $dto->merchantType,
                'request_data' => $dto->toArray(),
                'response_data' => $responseData,
                'response_code' => $responseDTO->responseCode,
                'account_status' => $responseDTO->accountStatus,
                'account_title' => $responseDTO->accountTitle,
                'account_type' => $responseDTO->accountType,
                'is_pin_set' => $responseDTO->isPinSet,
                'success' => $responseDTO->success,
            ]);

            // Audit log
            $this->auditService->log(
                'account_verification',
                'onboarding',
                $dto->toArray(),
                auth()->id(),
                $dto->traceNo
            );

            // Fire event
            Event::dispatch(new AccountVerified($verification, $responseDTO));

            return $responseDTO;
        } catch (GuzzleException $e) {
            $this->loggingService->logError(
                'Failed to verify account',
                [
                    'cnic' => $dto->cnic,
                    'mobile_no' => $dto->mobileNo,
                    'trace_no' => $dto->traceNo,
                ],
                $e
            );

            // Try to parse error response
            $errorResponse = null;
            if ($e->hasResponse()) {
                $errorBody = $e->getResponse()->getBody()->getContents();
                $errorResponse = json_decode($errorBody, true);
            }

            if ($errorResponse) {
                return AccountVerificationResponseDTO::fromApiResponse($errorResponse);
            }

            return new AccountVerificationResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to verify account: ' . $e->getMessage(),
                errorCode: (string) $e->getCode()
            );
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'Account verification error',
                [
                    'cnic' => $dto->cnic,
                    'mobile_no' => $dto->mobileNo,
                ],
                $e
            );

            return new AccountVerificationResponseDTO(
                success: false,
                responseCode: '',
                message: 'Account verification failed: ' . $e->getMessage(),
                errorCode: (string) $e->getCode()
            );
        }
    }

    /**
     * Link account with CNIC and mobile number.
     */
    public function linkAccount(AccountLinkingRequestDTO $dto): AccountLinkingResponseDTO
    {
        try {
            $this->loggingService->logInfo('Initiating account linking', [
                'cnic' => $dto->cnic,
                'mobile_no' => $dto->mobileNo,
                'trace_no' => $dto->traceNo,
            ]);

            // Validate DTO
            $this->validateLinkingRequest($dto);

            // Get authentication token
            $token = $this->authService->authenticate();
            $config = config('zindagi-zconnect');

            // Prepare headers
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'clientId' => $config['auth']['client_id'],
                'clientSecret' => $token,
                'organizationId' => $config['auth']['organization_id'] ?? '223',
            ];

            // Prepare request body
            $requestBody = $dto->toApiRequest();

            // Log request
            $this->loggingService->logRequest($this->accountLinkingEndpoint, $requestBody, $headers);

            // Make API request
            $response = $this->accountLinkingClient->post($this->accountLinkingEndpoint, [
                'headers' => $headers,
                'json' => $requestBody,
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            // Handle null or invalid JSON
            if (!is_array($responseData)) {
                $this->loggingService->logError(
                    'Invalid response from account linking API',
                    ['response_body' => $responseBody],
                    new \RuntimeException('Invalid JSON response')
                );
                
                return new AccountLinkingResponseDTO(
                    success: false,
                    responseCode: '',
                    message: 'Account linking failed: Invalid response from API'
                );
            }

            // Log response
            $this->loggingService->logResponse(
                $this->accountLinkingEndpoint,
                $responseData,
                $response->getStatusCode()
            );

            $responseDTO = AccountLinkingResponseDTO::fromApiResponse($responseData);

            // Store in database
            $linking = $this->accountLinkingRepository->create([
                'trace_no' => $dto->traceNo,
                'cnic' => $dto->cnic,
                'mobile_no' => $dto->mobileNo,
                'merchant_type' => $dto->merchantType,
                'request_data' => $dto->toArray(),
                'response_data' => $responseData,
                'response_code' => $responseDTO->responseCode,
                'account_title' => $responseDTO->accountTitle,
                'account_type' => $responseDTO->accountType,
                'otp_pin' => $dto->otpPin,
                'success' => $responseDTO->success,
            ]);

            // Audit log
            $this->auditService->log(
                'account_linking',
                'onboarding',
                $dto->toArray(),
                auth()->id(),
                $dto->traceNo
            );

            // Fire event
            Event::dispatch(new AccountLinked($linking, $responseDTO));

            return $responseDTO;
        } catch (GuzzleException $e) {
            $this->loggingService->logError(
                'Failed to link account',
                [
                    'cnic' => $dto->cnic,
                    'mobile_no' => $dto->mobileNo,
                    'trace_no' => $dto->traceNo,
                ],
                $e
            );

            // Try to parse error response
            $errorResponse = null;
            if ($e->hasResponse()) {
                $errorBody = $e->getResponse()->getBody()->getContents();
                $errorResponse = json_decode($errorBody, true);
            }

            if ($errorResponse) {
                return AccountLinkingResponseDTO::fromApiResponse($errorResponse);
            }

            return new AccountLinkingResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to link account: ' . $e->getMessage(),
                errorCode: (string) $e->getCode()
            );
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'Account linking error',
                [
                    'cnic' => $dto->cnic,
                    'mobile_no' => $dto->mobileNo,
                ],
                $e
            );

            return new AccountLinkingResponseDTO(
                success: false,
                responseCode: '',
                message: 'Account linking failed: ' . $e->getMessage(),
                errorCode: (string) $e->getCode()
            );
        }
    }

    /**
     * Validate verification request.
     */
    protected function validateVerificationRequest(AccountVerificationRequestDTO $dto): void
    {
        if (empty($dto->cnic) || strlen($dto->cnic) !== 13) {
            throw new \InvalidArgumentException('CNIC must be exactly 13 characters');
        }

        if (empty($dto->mobileNo) || strlen($dto->mobileNo) !== 11) {
            throw new \InvalidArgumentException('Mobile number must be exactly 11 characters');
        }

        if (empty($dto->merchantType) || strlen($dto->merchantType) !== 4) {
            throw new \InvalidArgumentException('MerchantType must be exactly 4 characters');
        }

        if (empty($dto->traceNo) || strlen($dto->traceNo) !== 6) {
            throw new \InvalidArgumentException('TraceNo must be exactly 6 characters');
        }

        if (empty($dto->dateTime) || strlen($dto->dateTime) !== 14) {
            throw new \InvalidArgumentException('DateTime must be exactly 14 characters (YYYYMMDDHHmmss)');
        }

        if (empty($dto->companyName) || strlen($dto->companyName) !== 4) {
            throw new \InvalidArgumentException('CompanyName must be exactly 4 characters');
        }
    }

    /**
     * Validate linking request.
     */
    protected function validateLinkingRequest(AccountLinkingRequestDTO $dto): void
    {
        if (empty($dto->cnic) || strlen($dto->cnic) !== 13) {
            throw new \InvalidArgumentException('CNIC must be exactly 13 characters');
        }

        if (empty($dto->mobileNo) || strlen($dto->mobileNo) !== 11) {
            throw new \InvalidArgumentException('Mobile number must be exactly 11 characters');
        }

        if (empty($dto->merchantType) || strlen($dto->merchantType) !== 4) {
            throw new \InvalidArgumentException('MerchantType must be exactly 4 characters');
        }

        if (empty($dto->traceNo) || strlen($dto->traceNo) !== 6) {
            throw new \InvalidArgumentException('TraceNo must be exactly 6 characters');
        }

        if (empty($dto->dateTime) || strlen($dto->dateTime) !== 14) {
            throw new \InvalidArgumentException('DateTime must be exactly 14 characters (YYYYMMDDHHmmss)');
        }

        if (empty($dto->companyName) || strlen($dto->companyName) !== 4) {
            throw new \InvalidArgumentException('CompanyName must be exactly 4 characters');
        }

        if (empty($dto->transactionType) || strlen($dto->transactionType) !== 2) {
            throw new \InvalidArgumentException('TransactionType must be exactly 2 characters');
        }

        if (empty($dto->reserved1) || strlen($dto->reserved1) !== 2) {
            throw new \InvalidArgumentException('Reserved1 must be exactly 2 characters');
        }
    }

    /**
     * Validate onboarding request.
     */
    protected function validateOnboardingRequest(OnboardingRequestDTO $dto): void
    {
        if (empty($dto->cnic)) {
            throw new \InvalidArgumentException('CNIC is required');
        }

        if (empty($dto->fullName)) {
            throw new \InvalidArgumentException('Full name is required');
        }

        if (empty($dto->mobileNumber)) {
            throw new \InvalidArgumentException('Mobile number is required');
        }

        if (empty($dto->email) || !filter_var($dto->email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Valid email is required');
        }

        if (empty($dto->dateOfBirth)) {
            throw new \InvalidArgumentException('Date of birth is required');
        }
    }
}

