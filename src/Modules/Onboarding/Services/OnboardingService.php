<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Services;

use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\Contracts\OnboardingServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\MinorAccountOpeningRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\MinorAccountOpeningResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\MinorAccountVerificationRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\MinorAccountVerificationResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\UpgradeMinorAccountRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\UpgradeMinorAccountResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountStatementV2RequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountStatementV2ResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\OnboardingRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\OnboardingResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountOpeningRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountOpeningResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountOpeningL1RequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountOpeningL1ResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountUpgradeRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountUpgradeResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\L2AccountFieldsRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\L2AccountFieldsResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\UpdatePmdKycRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\UpdatePmdKycResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\GetL2DiscrepantRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\GetL2DiscrepantResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\GetL2AccountsRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\GetL2AccountsResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\L2AccountUpgradeDiscrepantRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\L2AccountUpgradeDiscrepantResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\L2AccountStatusRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\L2AccountStatusResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\Level2AccountMotherRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\Level2AccountMotherResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountInfoRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountInfoResponseDTO;
use zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\OnboardingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountVerificationRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountLinkingRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\Contracts\AccountOpeningRepositoryInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\OnboardingInitiated;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\OnboardingVerified;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\OnboardingCompleted;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\AccountVerified;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\AccountLinked;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\AccountOpened;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\AccountOpenedL1;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Events\AccountUpgraded;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Event;

class OnboardingService implements OnboardingServiceInterface
{
    protected string $endpoint;
    protected Client $minorAccountOpeningClient;
    protected string $minorAccountOpeningEndpoint;
    protected Client $minorAccountVerificationClient;
    protected string $minorAccountVerificationEndpoint;
    protected Client $upgradeMinorAccountClient;
    protected string $upgradeMinorAccountEndpoint;
    protected Client $accountStatementV2Client;
    protected string $accountStatementV2Endpoint;
    protected Client $accountVerificationClient;
    protected string $accountVerificationEndpoint;
    protected Client $accountLinkingClient;
    protected string $accountLinkingEndpoint;
    protected Client $accountOpeningClient;
    protected string $accountOpeningEndpoint;
    protected Client $accountOpeningL1Client;
    protected string $accountOpeningL1Endpoint;
    protected Client $accountUpgradeClient;
    protected string $accountUpgradeEndpoint;
    protected Client $l2AccountFieldsClient;
    protected string $l2AccountFieldsEndpoint;
    protected Client $updatePmdKycClient;
    protected string $updatePmdKycEndpoint;
    protected Client $getL2DiscrepantClient;
    protected string $getL2DiscrepantEndpoint;
    protected Client $getL2AccountsClient;
    protected string $getL2AccountsEndpoint;
    protected Client $l2AccountUpgradeDiscrepantClient;
    protected string $l2AccountUpgradeDiscrepantEndpoint;
    protected Client $l2AccountStatusClient;
    protected string $l2AccountStatusEndpoint;
    protected Client $level2AccountMotherClient;
    protected string $level2AccountMotherEndpoint;
    protected Client $accountInfoClient;
    protected string $accountInfoEndpoint;


    public function __construct(
        protected HttpClientInterface $httpClient,
        protected AuthenticationServiceInterface $authService,
        protected LoggingServiceInterface $loggingService,
        protected AuditServiceInterface $auditService,
        protected OnboardingRepositoryInterface $repository,
        protected AccountVerificationRepositoryInterface $accountVerificationRepository,
        protected AccountLinkingRepositoryInterface $accountLinkingRepository,
        protected AccountOpeningRepositoryInterface $accountOpeningRepository
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

        // Setup account opening client
        $accountOpeningConfig = $config['modules']['onboarding']['account_opening'] ?? [];
        $this->accountOpeningEndpoint = $accountOpeningConfig['endpoint'] ?? '/api/v2/accountopening-blb';

        $this->accountOpeningClient = new Client([
            'base_uri' => $baseUrl,
            'timeout' => $config['modules']['onboarding']['timeout'] ?? 60,
            'verify' => $config['security']['verify_ssl'] ?? true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        // Setup account opening L1 client
        $accountOpeningL1Config = $config['modules']['onboarding']['account_opening_l1'] ?? [];
        $this->accountOpeningL1Endpoint = $accountOpeningL1Config['endpoint'] ?? '/api/v2/accountopeningl1-blb2';

        $this->accountOpeningL1Client = new Client([
            'base_uri' => $baseUrl,
            'timeout' => $config['modules']['onboarding']['timeout'] ?? 60,
            'verify' => $config['security']['verify_ssl'] ?? true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        // Setup account upgrade client
        $accountUpgradeConfig = $config['modules']['onboarding']['account_upgrade'] ?? [];
        $this->accountUpgradeEndpoint = $accountUpgradeConfig['endpoint'] ?? '/api/v2/upgradeaccount';

        $this->accountUpgradeClient = new Client([
            'base_uri' => $baseUrl,
            'timeout' => $config['modules']['onboarding']['timeout'] ?? 60,
            'verify' => $config['security']['verify_ssl'] ?? true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        // Setup L2 account fields client
        $l2AccountFieldsConfig = $config['modules']['onboarding']['l2_account_fields'] ?? [];
        $this->l2AccountFieldsEndpoint = $l2AccountFieldsConfig['endpoint'] ?? '/api/v1/l2Account/l2AccountFields';

        $this->l2AccountFieldsClient = new Client([
            'base_uri' => $baseUrl,
            'timeout' => $config['modules']['onboarding']['timeout'] ?? 60,
            'verify' => $config['security']['verify_ssl'] ?? true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        // Setup Update PMD KYC client
        $updatePmdKycConfig = $config['modules']['onboarding']['update_pmd_kyc'] ?? [];
        $this->updatePmdKycEndpoint = $updatePmdKycConfig['endpoint'] ?? '/api/v1/updatePmdAndKyc';

        $this->updatePmdKycClient = new Client([
            'base_uri' => $baseUrl,
            'timeout' => $config['modules']['onboarding']['timeout'] ?? 60,
            'verify' => $config['security']['verify_ssl'] ?? true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        // Setup Get L2 Discrepant client
        $getL2DiscrepantConfig = $config['modules']['onboarding']['get_l2_discrepant_data'] ?? [];
        $this->getL2DiscrepantEndpoint = $getL2DiscrepantConfig['endpoint'] ?? '/api/v1/getL2AccountUpgradeDiscrepant';

        $this->getL2DiscrepantClient = new Client([
            'base_uri' => $baseUrl,
            'timeout' => $config['modules']['onboarding']['timeout'] ?? 60,
            'verify' => $config['security']['verify_ssl'] ?? true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        // Setup Get L2 Accounts client
        $getL2AccountsConfig = $config['modules']['onboarding']['get_l2_accounts'] ?? [];
        $this->getL2AccountsEndpoint = $getL2AccountsConfig['endpoint'] ?? '/api/v1/getL2Accounts';

        $this->getL2AccountsClient = new Client([
            'base_uri' => $baseUrl,
            'timeout' => $config['modules']['onboarding']['timeout'] ?? 60,
            'verify' => $config['security']['verify_ssl'] ?? true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        // Setup L2 Account Upgrade Discrepant client
        $l2AccountUpgradeDiscrepantConfig = $config['modules']['onboarding']['l2_account_upgrade_discrepant'] ?? [];
        $this->l2AccountUpgradeDiscrepantEndpoint = $l2AccountUpgradeDiscrepantConfig['endpoint'] ?? '/api/v1/l2AccountUpgradeDiscrepant';

        $this->l2AccountUpgradeDiscrepantClient = new Client([
            'base_uri' => $baseUrl,
            'timeout' => $config['modules']['onboarding']['timeout'] ?? 60,
            'verify' => $config['security']['verify_ssl'] ?? true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        // Setup L2 Account Status client
        $l2AccountStatusConfig = $config['modules']['onboarding']['l2_account_status'] ?? [];
        $this->l2AccountStatusEndpoint = $l2AccountStatusConfig['endpoint'] ?? '/api/v1/l2Account/l2AccountStatus';

        $this->l2AccountStatusClient = new Client([
            'base_uri' => $baseUrl,
            'timeout' => $config['modules']['onboarding']['timeout'] ?? 60,
            'verify' => $config['security']['verify_ssl'] ?? true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        // Setup Level 2 Account Mother client
        $level2AccountMotherConfig = $config['modules']['onboarding']['level2_account_mother'] ?? [];
        $this->level2AccountMotherEndpoint = $level2AccountMotherConfig['endpoint'] ?? '/api/v1/level2AccountMother';

        $this->level2AccountMotherClient = new Client([
            'base_uri' => $baseUrl,
            'timeout' => $config['modules']['onboarding']['timeout'] ?? 60,
            'verify' => $config['security']['verify_ssl'] ?? true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        // Setup Account Info client
        $accountInfoConfig = $config['modules']['onboarding']['account_info'] ?? [];
        $this->accountInfoEndpoint = $accountInfoConfig['endpoint'] ?? '/api/v1/accountInfo';

        $this->accountInfoClient = new Client([
            'base_uri' => $baseUrl,
            'timeout' => $config['modules']['onboarding']['timeout'] ?? 60,
            'verify' => $config['security']['verify_ssl'] ?? true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        // Setup Minor Account Opening client
        $minorAccountOpeningConfig = $config['modules']['onboarding']['minor_account_opening'] ?? [];
        $this->minorAccountOpeningEndpoint = $minorAccountOpeningConfig['endpoint'] ?? '/api/v1/M0AccountOpening';

        $this->minorAccountOpeningClient = new Client([
            'base_uri' => $baseUrl,
            'timeout' => $config['modules']['onboarding']['timeout'] ?? 60,
            'verify' => $config['security']['verify_ssl'] ?? true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        // Setup Minor Account Verification client
        $minorAccountVerificationConfig = $config['modules']['onboarding']['minor_account_verification'] ?? [];
        $this->minorAccountVerificationEndpoint = $minorAccountVerificationConfig['endpoint'] ?? '/api/v1/M0AccountVerification';

        $this->minorAccountVerificationClient = new Client([
            'base_uri' => $baseUrl,
            'timeout' => $config['modules']['onboarding']['timeout'] ?? 60,
            'verify' => $config['security']['verify_ssl'] ?? true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        // Setup Upgrade Minor Account client
        $upgradeMinorAccountConfig = $config['modules']['onboarding']['upgrade_minor_account'] ?? [];
        $this->upgradeMinorAccountEndpoint = $upgradeMinorAccountConfig['endpoint'] ?? '/api/v1/UpgradeMinorAccount';

        $this->upgradeMinorAccountClient = new Client([
            'base_uri' => $baseUrl,
            'timeout' => $config['modules']['onboarding']['timeout'] ?? 60,
            'verify' => $config['security']['verify_ssl'] ?? true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        // Setup Account Statement V2 client
        $accountStatementV2Config = $config['modules']['onboarding']['account_statement_v2'] ?? [];
        $this->accountStatementV2Endpoint = $accountStatementV2Config['endpoint'] ?? '/api/v2/digiWalletStatement';

        $this->accountStatementV2Client = new Client([
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


            if (empty($config['auth']['client_id'])) {
                throw new \RuntimeException('Missing client_id configuration');
            }

            // Prepare headers
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'clientId' => $config['auth']['client_id'] ?? '',
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

            // Handle null or invalid JSON
            if (!is_array($responseData)) {
                $this->loggingService->logError(
                    'Invalid response from account verification API',
                    ['response_body' => $responseBody],
                    new \RuntimeException('Invalid JSON response')
                );


                return new AccountVerificationResponseDTO(
                    success: false,
                    responseCode: '',
                    message: 'Account verification failed: Invalid response from API'
                );
            }

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
                return new AccountVerificationResponseDTO(
                    success: false,
                    responseCode: '',
                    message: $errorResponse['messages'] ?? 'Account verification failed',
                    errorCode: (string) ($errorResponse['errorcode'] ?? '')
                );
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
                return new AccountLinkingResponseDTO(
                    success: false,
                    responseCode: '',
                    message: $errorResponse['messages'] ?? 'Account linking failed',
                    errorCode: (string) ($errorResponse['errorcode'] ?? '')
                );
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
     * Open account with customer information.
     */
    public function openAccount(AccountOpeningRequestDTO $dto): AccountOpeningResponseDTO
    {
        try {
            $this->loggingService->logInfo('Initiating account opening', [
                'cnic' => $dto->cnic,
                'mobile_no' => $dto->mobileNo,
                'email_id' => $dto->emailId,
                'trace_no' => $dto->traceNo,
            ]);

            // Validate DTO
            $this->validateOpeningRequest($dto);

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
            $this->loggingService->logRequest($this->accountOpeningEndpoint, $requestBody, $headers);

            // Make API request
            $response = $this->accountOpeningClient->post($this->accountOpeningEndpoint, [
                'headers' => $headers,
                'json' => $requestBody,
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            // Handle null or invalid JSON
            if (!is_array($responseData)) {
                $this->loggingService->logError(
                    'Invalid response from account opening API',
                    ['response_body' => $responseBody],
                    new \RuntimeException('Invalid JSON response')
                );

                return new AccountOpeningResponseDTO(
                    success: false,
                    responseCode: '',
                    message: 'Account opening failed: Invalid response from API'
                );
            }

            // Log response
            $this->loggingService->logResponse(
                $this->accountOpeningEndpoint,
                $responseData,
                $response->getStatusCode()
            );

            $responseDTO = AccountOpeningResponseDTO::fromApiResponse($responseData);

            // Store in database
            $opening = $this->accountOpeningRepository->create([
                'trace_no' => $dto->traceNo,
                'cnic' => $dto->cnic,
                'mobile_no' => $dto->mobileNo,
                'email_id' => $dto->emailId,
                'cnic_issuance_date' => $dto->cnicIssuanceDate,
                'mobile_network' => $dto->mobileNetwork,
                'merchant_type' => $dto->merchantType,
                'request_data' => $dto->toArray(),
                'response_data' => $responseData,
                'response_code' => $responseDTO->responseCode,
                'success' => $responseDTO->success,
            ]);

            // Audit log
            $this->auditService->log(
                'account_opening',
                'onboarding',
                $dto->toArray(),
                auth()->id(),
                $dto->traceNo
            );

            // Fire event
            Event::dispatch(new AccountOpened($opening, $responseDTO));

            return $responseDTO;
        } catch (GuzzleException $e) {
            $this->loggingService->logError(
                'Failed to open account',
                [
                    'cnic' => $dto->cnic,
                    'mobile_no' => $dto->mobileNo,
                    'email_id' => $dto->emailId,
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
                return new AccountOpeningResponseDTO(
                    success: false,
                    responseCode: '',
                    message: $errorResponse['messages'] ?? 'Account opening failed',
                    errorCode: (string) ($errorResponse['errorcode'] ?? '')
                );
            }


            return new AccountOpeningResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to open account: ' . $e->getMessage(),
                errorCode: (string) $e->getCode()
            );
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'Account opening error',
                [
                    'cnic' => $dto->cnic,
                    'mobile_no' => $dto->mobileNo,
                    'email_id' => $dto->emailId,
                ],
                $e
            );

            return new AccountOpeningResponseDTO(
                success: false,
                responseCode: '',
                message: 'Account opening failed: ' . $e->getMessage(),
                errorCode: (string) $e->getCode()
            );
        }
    }

    /**
     * Validate opening request.
     */
    protected function validateOpeningRequest(AccountOpeningRequestDTO $dto): void
    {
        if (empty($dto->cnic) || strlen($dto->cnic) !== 13) {
            throw new \InvalidArgumentException('CNIC must be exactly 13 characters');
        }

        if (empty($dto->mobileNo) || strlen($dto->mobileNo) !== 11) {
            throw new \InvalidArgumentException('Mobile number must be exactly 11 characters');
        }

        if (empty($dto->emailId) || strlen($dto->emailId) > 25) {
            throw new \InvalidArgumentException('EmailId must be maximum 25 characters');
        }

        if (empty($dto->cnicIssuanceDate) || strlen($dto->cnicIssuanceDate) !== 8) {
            throw new \InvalidArgumentException('CnicIssuanceDate must be exactly 8 characters (YYYYMMDD)');
        }

        if (empty($dto->mobileNetwork) || strlen($dto->mobileNetwork) !== 5) {
            throw new \InvalidArgumentException('MobileNetwork must be exactly 5 characters');
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
     * Open L1 account with customer information.
     */
    public function openAccountL1(AccountOpeningL1RequestDTO $dto): AccountOpeningL1ResponseDTO
    {
        try {
            $this->loggingService->logInfo('Initiating L1 account opening', [
                'cnic' => $dto->cnic,
                'mobile_no' => $dto->mobileNo,
                'email_id' => $dto->emailId,
                'trace_no' => $dto->traceNo,
            ]);

            // Validate DTO
            $this->validateOpeningL1Request($dto);

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
            $requestBody = $dto->toArray();

            // Log request
            $this->loggingService->logRequest($this->accountOpeningL1Endpoint, $requestBody, $headers);

            // Make API request
            $response = $this->accountOpeningL1Client->post($this->accountOpeningL1Endpoint, [
                'headers' => $headers,
                'json' => $requestBody,
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            // Handle null or invalid JSON
            if (!is_array($responseData)) {
                $this->loggingService->logError(
                    'Invalid response from L1 account opening API',
                    ['response_body' => $responseBody],
                    new \RuntimeException('Invalid JSON response')
                );

                return new AccountOpeningL1ResponseDTO(
                    success: false,
                    responseCode: '',
                    message: 'L1 Account opening failed: Invalid response from API'
                );
            }

            // Log response
            $this->loggingService->logResponse(
                $this->accountOpeningL1Endpoint,
                $responseData,
                $response->getStatusCode()
            );

            $responseDTO = AccountOpeningL1ResponseDTO::fromArray($responseData);

            // Store in database
            $opening = $this->accountOpeningRepository->create([
                'trace_no' => $dto->traceNo,
                'cnic' => $dto->cnic,
                'mobile_no' => $dto->mobileNo,
                'email_id' => $dto->emailId,
                'cnic_issuance_date' => $dto->cnicIssuanceDate,
                'mobile_network' => $dto->mobileNetwork,
                'merchant_type' => $dto->merchantType,
                'request_data' => $dto->toArray(),
                'response_data' => $responseData,
                'response_code' => $responseDTO->responseCode,
                'success' => $responseDTO->success,
            ]);

            // Audit log
            $this->auditService->log(
                'account_opening_l1',
                'onboarding',
                $dto->toArray(),
                (string) (auth()->id() ?? 'system'),
                $dto->traceNo
            );

            // Fire event
            Event::dispatch(new AccountOpenedL1($opening, $responseDTO));

            return $responseDTO;
        } catch (GuzzleException $e) {
            $this->loggingService->logError(
                'Failed to open L1 account',
                [
                    'cnic' => $dto->cnic,
                    'mobile_no' => $dto->mobileNo,
                    'email_id' => $dto->emailId,
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
                return new AccountOpeningL1ResponseDTO(
                    success: false,
                    responseCode: '',
                    message: $errorResponse['messages'] ?? 'L1 Account opening failed',
                );
            }

            return new AccountOpeningL1ResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to open L1 account: ' . $e->getMessage(),
            );
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'L1 Account opening error',
                [
                    'cnic' => $dto->cnic,
                    'mobile_no' => $dto->mobileNo,
                    'email_id' => $dto->emailId,
                ],
                $e
            );

            return new AccountOpeningL1ResponseDTO(
                success: false,
                responseCode: '',
                message: 'L1 Account opening failed: ' . $e->getMessage(),
            );
        }
    }

    protected function validateOpeningL1Request(AccountOpeningL1RequestDTO $dto): void
    {
        if (empty($dto->fingerTemplate)) {
             throw new \InvalidArgumentException('Finger template cannot be empty');
        }
    }

    /**
     * Upgrade existing account.
     */
    public function upgradeAccount(AccountUpgradeRequestDTO $dto): AccountUpgradeResponseDTO
    {
        try {
            $this->loggingService->logInfo('Initiating account upgrade', [
                'cnic' => $dto->cnic,
                'mobile_no' => $dto->mobileNo,
                'trace_no' => $dto->traceNo,
            ]);

            // Validate DTO
            $this->validateUpgradeRequest($dto);

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
            $requestBody = $dto->toArray();

            // Log request
            $this->loggingService->logRequest($this->accountUpgradeEndpoint, $requestBody, $headers);

            // Make API request
            $response = $this->accountUpgradeClient->post($this->accountUpgradeEndpoint, [
                'headers' => $headers,
                'json' => $requestBody,
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            // Handle null or invalid JSON
            if (!is_array($responseData)) {
                $this->loggingService->logError(
                    'Invalid response from account upgrade API',
                    ['response_body' => $responseBody],
                    new \RuntimeException('Invalid JSON response')
                );

                return new AccountUpgradeResponseDTO(
                    success: false,
                    responseCode: '',
                    message: 'Account upgrade failed: Invalid response from API'
                );
            }

            // Log response
            $this->loggingService->logResponse(
                $this->accountUpgradeEndpoint,
                $responseData,
                $response->getStatusCode()
            );

            $responseDTO = AccountUpgradeResponseDTO::fromArray($responseData);

            // Audit log
            $this->auditService->log(
                'account_upgrade',
                'onboarding',
                $dto->toArray(),
                (string) (auth()->id() ?? 'system'),
                $dto->traceNo
            );

            // Fire event
            Event::dispatch(new AccountUpgraded($requestBody, $responseDTO));

            return $responseDTO;
        } catch (GuzzleException $e) {
            $this->loggingService->logError(
                'Failed to upgrade account',
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
                return new AccountUpgradeResponseDTO(
                    success: false,
                    responseCode: '',
                    message: $errorResponse['messages'] ?? 'Account upgrade failed',
                );
            }

            return new AccountUpgradeResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to upgrade account: ' . $e->getMessage(),
            );
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'Account upgrade error',
                [
                    'cnic' => $dto->cnic,
                    'mobile_no' => $dto->mobileNo,
                ],
                $e
            );

            return new AccountUpgradeResponseDTO(
                success: false,
                responseCode: '',
                message: 'Account upgrade failed: ' . $e->getMessage(),
            );
        }
    }

    protected function validateUpgradeRequest(AccountUpgradeRequestDTO $dto): void
    {
         // DTO does most validation
         if (empty($dto->fingerTemplate)) {
             throw new \InvalidArgumentException('Finger template cannot be empty');
        }
    }

    /**
     * Get L2 account fields.
     */
    public function getL2AccountFields(L2AccountFieldsRequestDTO $dto): L2AccountFieldsResponseDTO
    {
        try {
            $this->loggingService->logInfo('Getting L2 account fields', [
                'mobile_number' => $dto->mobileNumber,
                'rrn' => $dto->rrn,
            ]);

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
            $requestBody = $dto->toArray();

            // Log request
            $this->loggingService->logRequest($this->l2AccountFieldsEndpoint, $requestBody, $headers);

            // Make API request
            $response = $this->l2AccountFieldsClient->post($this->l2AccountFieldsEndpoint, [
                'headers' => $headers,
                'json' => $requestBody,
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            // Handle null or invalid JSON
            if (!is_array($responseData)) {
                $this->loggingService->logError(
                    'Invalid response from L2 account fields API',
                    ['response_body' => $responseBody],
                    new \RuntimeException('Invalid JSON response')
                );

                return new L2AccountFieldsResponseDTO(
                    success: false,
                    responseCode: '',
                    message: 'Get L2 account fields failed: Invalid response from API'
                );
            }

            // Log response
            $this->loggingService->logResponse(
                $this->l2AccountFieldsEndpoint,
                $responseData,
                $response->getStatusCode()
            );

            return L2AccountFieldsResponseDTO::fromArray($responseData);
        } catch (GuzzleException $e) {
            $this->loggingService->logError(
                'Failed to get L2 account fields',
                [
                    'mobile_number' => $dto->mobileNumber,
                    'rrn' => $dto->rrn,
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
                return new L2AccountFieldsResponseDTO(
                    success: false,
                    responseCode: '',
                    message: $errorResponse['ResponseDescription'] ?? $errorResponse['messages'] ?? 'Failed to get L2 account fields',
                );
            }

            return new L2AccountFieldsResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to get L2 account fields: ' . $e->getMessage(),
            );
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'Get L2 account fields error',
                [
                    'mobile_number' => $dto->mobileNumber,
                ],
                $e
            );

            return new L2AccountFieldsResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to get L2 account fields: ' . $e->getMessage(),
            );
        }
    }

    /**
     * Update PMD and KYC.
     */
    public function updatePmdAndKyc(UpdatePmdKycRequestDTO $dto): UpdatePmdKycResponseDTO
    {
        try {
            $this->loggingService->logInfo('Updating PMD and KYC', [
                'mobile_number' => $dto->mobileNumber,
                'account_id' => $dto->accountId,
                'rrn' => $dto->rrn,
            ]);

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
            $requestBody = $dto->toArray();

            // Log request
            $this->loggingService->logRequest($this->updatePmdKycEndpoint, $requestBody, $headers);

            // Make API request
            $response = $this->updatePmdKycClient->post($this->updatePmdKycEndpoint, [
                'headers' => $headers,
                'json' => $requestBody,
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            // Handle null or invalid JSON
            if (!is_array($responseData)) {
                $this->loggingService->logError(
                    'Invalid response from Update PMD KYC API',
                    ['response_body' => $responseBody],
                    new \RuntimeException('Invalid JSON response')
                );

                return new UpdatePmdKycResponseDTO(
                    success: false,
                    responseCode: '',
                    message: 'Update PMD KYC failed: Invalid response from API'
                );
            }

            // Log response
            $this->loggingService->logResponse(
                $this->updatePmdKycEndpoint,
                $responseData,
                $response->getStatusCode()
            );

            // Audit log
            $this->auditService->log(
                'update_pmd_kyc',
                'onboarding',
                $dto->toArray(),
                (string) (auth()->id() ?? 'system'),
                $dto->rrn
            );

            return UpdatePmdKycResponseDTO::fromArray($responseData);
        } catch (GuzzleException $e) {
            $this->loggingService->logError(
                'Failed to update PMD and KYC',
                [
                    'mobile_number' => $dto->mobileNumber,
                    'account_id' => $dto->accountId,
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
                return new UpdatePmdKycResponseDTO(
                    success: false,
                    responseCode: '',
                    message: $errorResponse['ResponseDescription'] ?? $errorResponse['messages'] ?? 'Failed to update PMD and KYC',
                );
            }

            return new UpdatePmdKycResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to update PMD and KYC: ' . $e->getMessage(),
            );
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'Update PMD and KYC error',
                [
                    'mobile_number' => $dto->mobileNumber,
                ],
                $e
            );

            return new UpdatePmdKycResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to update PMD and KYC: ' . $e->getMessage(),
            );
        }
    }

    /**
     * Get L2 account upgrade discrepant data.
     */
    public function getL2AccountUpgradeDiscrepant(GetL2DiscrepantRequestDTO $dto): GetL2DiscrepantResponseDTO
    {
        try {
            $this->loggingService->logInfo('Getting L2 account upgrade discrepant data', [
                'mobile_number' => $dto->mobileNo,
                'cnic' => $dto->cnic,
                'rrn' => $dto->rrn,
            ]);

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
            $requestBody = $dto->toArray();

            // Log request
            $this->loggingService->logRequest($this->getL2DiscrepantEndpoint, $requestBody, $headers);

            // Make API request
            $response = $this->getL2DiscrepantClient->post($this->getL2DiscrepantEndpoint, [
                'headers' => $headers,
                'json' => $requestBody,
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            // Handle null or invalid JSON
            if (!is_array($responseData)) {
                $this->loggingService->logError(
                    'Invalid response from Get L2 Discrepant API',
                    ['response_body' => $responseBody],
                    new \RuntimeException('Invalid JSON response')
                );

                return new GetL2DiscrepantResponseDTO(
                    success: false,
                    responseCode: '',
                    message: 'Get L2 Discrepant failed: Invalid response from API'
                );
            }

            // Log response
            $this->loggingService->logResponse(
                $this->getL2DiscrepantEndpoint,
                $responseData,
                $response->getStatusCode()
            );

            return GetL2DiscrepantResponseDTO::fromArray($responseData);
        } catch (GuzzleException $e) {
            $this->loggingService->logError(
                'Failed to get L2 discrepant data',
                [
                    'mobile_number' => $dto->mobileNo,
                    'cnic' => $dto->cnic,
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
                return new GetL2DiscrepantResponseDTO(
                    success: false,
                    responseCode: '',
                    message: $errorResponse['ResponseDescription'] ?? $errorResponse['messages'] ?? 'Failed to get L2 discrepant data',
                );
            }

            return new GetL2DiscrepantResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to get L2 discrepant data: ' . $e->getMessage(),
            );
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'Get L2 discrepant data error',
                [
                     'mobile_number' => $dto->mobileNo,
                ],
                $e
            );

            return new GetL2DiscrepantResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to get L2 discrepant data: ' . $e->getMessage(),
            );
        }
    }

    /**
     * Get L2 accounts.
     */
    public function getL2Accounts(GetL2AccountsRequestDTO $dto): GetL2AccountsResponseDTO
    {
        try {
            $this->loggingService->logInfo('Getting L2 accounts', [
                'rrn' => $dto->rrn,
            ]);

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
            $requestBody = $dto->toArray();

            // Log request
            $this->loggingService->logRequest($this->getL2AccountsEndpoint, $requestBody, $headers);

            // Make API request
            $response = $this->getL2AccountsClient->post($this->getL2AccountsEndpoint, [
                'headers' => $headers,
                'json' => $requestBody,
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            // Handle null or invalid JSON
            if (!is_array($responseData)) {
                $this->loggingService->logError(
                    'Invalid response from Get L2 Accounts API',
                    ['response_body' => $responseBody],
                    new \RuntimeException('Invalid JSON response')
                );

                return new GetL2AccountsResponseDTO(
                    success: false,
                    responseCode: '',
                    message: 'Get L2 Accounts failed: Invalid response from API'
                );
            }

            // Log response
            $this->loggingService->logResponse(
                $this->getL2AccountsEndpoint,
                $responseData,
                $response->getStatusCode()
            );

            return GetL2AccountsResponseDTO::fromArray($responseData);
        } catch (GuzzleException $e) {
            $this->loggingService->logError(
                'Failed to get L2 accounts',
                [
                    'rrn' => $dto->rrn,
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
                return new GetL2AccountsResponseDTO(
                    success: false,
                    responseCode: '',
                    message: $errorResponse['ResponseDescription'] ?? $errorResponse['messages'] ?? 'Failed to get L2 accounts',
                );
            }

            return new GetL2AccountsResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to get L2 accounts: ' . $e->getMessage(),
            );
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'Get L2 accounts error',
                [
                    'rrn' => $dto->rrn,
                ],
                $e
            );

            return new GetL2AccountsResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to get L2 accounts: ' . $e->getMessage(),
            );
        }
    }

    /**
     * Submit L2 account upgrade discrepant data.
     */
    public function submitL2AccountUpgradeDiscrepant(L2AccountUpgradeDiscrepantRequestDTO $dto): L2AccountUpgradeDiscrepantResponseDTO
    {
        try {
            $this->loggingService->logInfo('Submitting L2 account upgrade discrepant data', [
                'mobile_number' => $dto->mobileNumber,
                'cnic' => $dto->cnic,
                'rrn' => $dto->rrn,
            ]);

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
            $requestBody = $dto->toArray();

            // Log request
            $this->loggingService->logRequest($this->l2AccountUpgradeDiscrepantEndpoint, $requestBody, $headers);

            // Make API request
            $response = $this->l2AccountUpgradeDiscrepantClient->post($this->l2AccountUpgradeDiscrepantEndpoint, [
                'headers' => $headers,
                'json' => $requestBody,
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            // Handle null or invalid JSON
            if (!is_array($responseData)) {
                $this->loggingService->logError(
                    'Invalid response from L2 Account Upgrade Discrepant API',
                    ['response_body' => $responseBody],
                    new \RuntimeException('Invalid JSON response')
                );

                return new L2AccountUpgradeDiscrepantResponseDTO(
                    success: false,
                    responseCode: '',
                    message: 'L2 Account Upgrade Discrepant failed: Invalid response from API'
                );
            }

            // Log response
            $this->loggingService->logResponse(
                $this->l2AccountUpgradeDiscrepantEndpoint,
                $responseData,
                $response->getStatusCode()
            );

            // Audit log
            $this->auditService->log(
                'l2_account_upgrade_discrepant',
                'onboarding',
                $dto->toArray(),
                (string) (auth()->id() ?? 'system'),
                $dto->rrn
            );

            return L2AccountUpgradeDiscrepantResponseDTO::fromArray($responseData);
        } catch (GuzzleException $e) {
            $this->loggingService->logError(
                'Failed to submit L2 account upgrade discrepant data',
                [
                    'mobile_number' => $dto->mobileNumber,
                    'cnic' => $dto->cnic,
                    'rrn' => $dto->rrn,
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
                return new L2AccountUpgradeDiscrepantResponseDTO(
                    success: false,
                    responseCode: '',
                    message: $errorResponse['ResponseDescription'] ?? $errorResponse['messages'] ?? 'Failed to submit L2 account upgrade discrepant data',
                );
            }

            return new L2AccountUpgradeDiscrepantResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to submit L2 account upgrade discrepant data: ' . $e->getMessage(),
            );
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'L2 Account Upgrade Discrepant error',
                [
                    'mobile_number' => $dto->mobileNumber,
                    'cnic' => $dto->cnic,
                ],
                $e
            );

            return new L2AccountUpgradeDiscrepantResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to submit L2 account upgrade discrepant data: ' . $e->getMessage(),
            );
        }
    }

    /**
     * Get L2 account status.
     */
    public function getL2AccountStatus(L2AccountStatusRequestDTO $dto): L2AccountStatusResponseDTO
    {
        try {
            $this->loggingService->logInfo('Getting L2 account status', [
                'mobile_no' => $dto->mobileNo,
                'rrn' => $dto->rrn,
            ]);

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
            $requestBody = $dto->toArray();

            // Log request
            $this->loggingService->logRequest($this->l2AccountStatusEndpoint, $requestBody, $headers);

            // Make API request
            $response = $this->l2AccountStatusClient->post($this->l2AccountStatusEndpoint, [
                'headers' => $headers,
                'json' => $requestBody,
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            // Handle null or invalid JSON
            if (!is_array($responseData)) {
                $this->loggingService->logError(
                    'Invalid response from L2 Account Status API',
                    ['response_body' => $responseBody],
                    new \RuntimeException('Invalid JSON response')
                );

                return new L2AccountStatusResponseDTO(
                    success: false,
                    responseCode: '',
                    message: 'Get L2 Account Status failed: Invalid response from API'
                );
            }

            // Log response
            $this->loggingService->logResponse(
                $this->l2AccountStatusEndpoint,
                $responseData,
                $response->getStatusCode()
            );

            // Audit log
            $this->auditService->log(
                'l2_account_status',
                'onboarding',
                $dto->toArray(),
                (string) (auth()->id() ?? 'system'),
                $dto->rrn
            );

            return L2AccountStatusResponseDTO::fromArray($responseData);
        } catch (GuzzleException $e) {
            $this->loggingService->logError(
                'Failed to get L2 account status',
                [
                    'mobile_no' => $dto->mobileNo,
                    'rrn' => $dto->rrn,
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
                return new L2AccountStatusResponseDTO(
                    success: false,
                    responseCode: '',
                    message: $errorResponse['ResponseDescription'] ?? $errorResponse['messages'] ?? 'Failed to get L2 account status',
                );
            }

            return new L2AccountStatusResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to get L2 account status: ' . $e->getMessage(),
            );
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'L2 Account Status error',
                [
                    'mobile_no' => $dto->mobileNo,
                ],
                $e
            );

            return new L2AccountStatusResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to get L2 account status: ' . $e->getMessage(),
            );
        }
    }

    /**
     * Get Level 2 account mother names.
     */
    public function getLevel2AccountMotherNames(Level2AccountMotherRequestDTO $dto): Level2AccountMotherResponseDTO
    {
        try {
            $this->loggingService->logInfo('Getting Level 2 account mother names', [
                'mobile_number' => $dto->mobileNumber,
            ]);

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
            $requestBody = $dto->toArray();

            // Log request
            $this->loggingService->logRequest($this->level2AccountMotherEndpoint, $requestBody, $headers);

            // Make API request
            $response = $this->level2AccountMotherClient->post($this->level2AccountMotherEndpoint, [
                'headers' => $headers,
                'json' => $requestBody,
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            // Handle null or invalid JSON
            if (!is_array($responseData)) {
                $this->loggingService->logError(
                    'Invalid response from Level 2 Account Mother API',
                    ['response_body' => $responseBody],
                    new \RuntimeException('Invalid JSON response')
                );

                return new Level2AccountMotherResponseDTO(
                    success: false,
                    responseCode: '',
                    message: 'Get Level 2 Account Mother Names failed: Invalid response from API'
                );
            }

            // Log response
            $this->loggingService->logResponse(
                $this->level2AccountMotherEndpoint,
                $responseData,
                $response->getStatusCode()
            );

            // Audit log
            $this->auditService->log(
                'level2_account_mother',
                'onboarding',
                $dto->toArray(),
                (string) (auth()->id() ?? 'system'),
                $dto->mobileNumber
            );

            return Level2AccountMotherResponseDTO::fromArray($responseData);
        } catch (GuzzleException $e) {
            $this->loggingService->logError(
                'Failed to get Level 2 account mother names',
                [
                    'mobile_number' => $dto->mobileNumber,
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
                return new Level2AccountMotherResponseDTO(
                    success: false,
                    responseCode: '',
                    message: $errorResponse['responseDescription'] ?? $errorResponse['messages'] ?? 'Failed to get Level 2 account mother names',
                );
            }

            return new Level2AccountMotherResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to get Level 2 account mother names: ' . $e->getMessage(),
            );
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'Get Level 2 account mother names error',
                [
                    'mobile_number' => $dto->mobileNumber,
                ],
                $e
            );

            return new Level2AccountMotherResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to get Level 2 account mother names: ' . $e->getMessage(),
            );
        }
    }

    /**
     * Get account information by mobile number.
     */
    public function getAccountInfo(AccountInfoRequestDTO $dto): AccountInfoResponseDTO
    {
        try {
            $this->loggingService->logInfo('Getting account information', [
                'mobile_number' => $dto->mobileNumber,
                'rrn' => $dto->rrn,
            ]);

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
            $requestBody = $dto->toArray();

            // Log request
            $this->loggingService->logRequest($this->accountInfoEndpoint, $requestBody, $headers);

            // Make API request
            $response = $this->accountInfoClient->post($this->accountInfoEndpoint, [
                'headers' => $headers,
                'json' => $requestBody,
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            // Handle null or invalid JSON
            if (!is_array($responseData)) {
                $this->loggingService->logError(
                    'Invalid response from account info API',
                    ['response_body' => $responseBody],
                    new \RuntimeException('Invalid JSON response')
                );

                return new AccountInfoResponseDTO(
                    success: false,
                    responseCode: '',
                    message: 'Get account info failed: Invalid response from API'
                );
            }

            // Log response
            $this->loggingService->logResponse(
                $this->accountInfoEndpoint,
                $responseData,
                $response->getStatusCode()
            );

            // Audit log
            $this->auditService->log(
                'account_info',
                'onboarding',
                $dto->toArray(),
                (string) (auth()->id() ?? 'system'),
                $dto->rrn
            );

            return AccountInfoResponseDTO::fromApiResponse($responseData);
        } catch (GuzzleException $e) {
            $this->loggingService->logError(
                'Failed to get account info',
                [
                    'mobile_number' => $dto->mobileNumber,
                    'rrn' => $dto->rrn,
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
                return new AccountInfoResponseDTO(
                    success: false,
                    responseCode: '',
                    message: $errorResponse['messages'] ?? 'Failed to get account info',
                    errorCode: $errorResponse['errorcode'] ?? null
                );
            }

            return new AccountInfoResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to get account info: ' . $e->getMessage(),
                errorCode: (string) $e->getCode()
            );
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'Get account info error',
                [
                    'mobile_number' => $dto->mobileNumber,
                ],
                $e
            );

            return new AccountInfoResponseDTO(
                success: false,
                responseCode: '',
                message: 'Failed to get account info: ' . $e->getMessage(),
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
    /**
     * Open minor account.
     */
    public function minorAccountOpening(MinorAccountOpeningRequestDTO $dto): MinorAccountOpeningResponseDTO
    {
        try {
            $this->loggingService->logInfo('Initiating minor account opening', [
                'rrn' => $dto->rrn,
                'cnic' => $dto->cnic,
                'mobile_number' => $dto->mobileNumber,
            ]);

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
            $requestBody = $dto->toArray();

            // Log request
            $this->loggingService->logRequest($this->minorAccountOpeningEndpoint, $requestBody, $headers);

            // Make API request
            $response = $this->minorAccountOpeningClient->post($this->minorAccountOpeningEndpoint, [
                'headers' => $headers,
                'json' => $requestBody,
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            // Handle null or invalid JSON
            if (!is_array($responseData)) {
                $this->loggingService->logError(
                    'Invalid response from Minor Account Opening API',
                    ['response_body' => $responseBody],
                    new \RuntimeException('Invalid JSON response')
                );

                return new MinorAccountOpeningResponseDTO(
                    success: false,
                    responseCode: '',
                    responseDescription: 'Minor Account Opening failed: Invalid response from API'
                );
            }

            // Log response
            $this->loggingService->logResponse(
                $this->minorAccountOpeningEndpoint,
                $responseData,
                $response->getStatusCode()
            );

            // Audit log
            $this->auditService->log(
                'minor_account_opening',
                'onboarding',
                $dto->toArray(),
                (string) (auth()->id() ?? 'system'),
                $dto->rrn
            );

            return MinorAccountOpeningResponseDTO::fromArray($responseData);
        } catch (GuzzleException $e) {
            $this->loggingService->logError(
                'Failed to open minor account',
                [
                    'rrn' => $dto->rrn,
                    'cnic' => $dto->cnic,
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
                return new MinorAccountOpeningResponseDTO(
                    success: false,
                    responseCode: (string) ($errorResponse['errorcode'] ?? ''),
                    responseDescription: $errorResponse['messages'] ?? 'Failed to open minor account',
                    originalResponse: $errorResponse
                );
            }

            return new MinorAccountOpeningResponseDTO(
                success: false,
                responseCode: (string) $e->getCode(),
                responseDescription: 'Failed to open minor account: ' . $e->getMessage(),
            );
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'Minor Account Opening error',
                [
                    'rrn' => $dto->rrn,
                ],
                $e
            );

            return new MinorAccountOpeningResponseDTO(
                success: false,
                responseCode: (string) $e->getCode(),
                responseDescription: 'Failed to open minor account: ' . $e->getMessage(),
            );
        }
    }
    /**
     * Verify minor account.
     */
    public function minorAccountVerification(MinorAccountVerificationRequestDTO $dto): MinorAccountVerificationResponseDTO
    {
        try {
            $this->loggingService->logInfo('Initiating minor account verification', [
                'rrn' => $dto->rrn,
                'cnic' => $dto->cnic,
                'mobile_number' => $dto->mobileNumber,
            ]);

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
            $requestBody = $dto->toArray();

            // Log request
            $this->loggingService->logRequest($this->minorAccountVerificationEndpoint, $requestBody, $headers);

            // Make API request
            $response = $this->minorAccountVerificationClient->post($this->minorAccountVerificationEndpoint, [
                'headers' => $headers,
                'json' => $requestBody,
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            // Handle null or invalid JSON
            if (!is_array($responseData)) {
                $this->loggingService->logError(
                    'Invalid response from Minor Account Verification API',
                    ['response_body' => $responseBody],
                    new \RuntimeException('Invalid JSON response')
                );

                return new MinorAccountVerificationResponseDTO(
                    success: false,
                    responseCode: '',
                    responseDescription: 'Minor Account Verification failed: Invalid response from API'
                );
            }

            // Log response
            $this->loggingService->logResponse(
                $this->minorAccountVerificationEndpoint,
                $responseData,
                $response->getStatusCode()
            );

            // Audit log
            $this->auditService->log(
                'minor_account_verification',
                'onboarding',
                $dto->toArray(),
                (string) (auth()->id() ?? 'system'),
                $dto->rrn
            );

            return MinorAccountVerificationResponseDTO::fromArray($responseData);
        } catch (GuzzleException $e) {
            $this->loggingService->logError(
                'Failed to verify minor account',
                [
                    'rrn' => $dto->rrn,
                    'cnic' => $dto->cnic,
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
                return new MinorAccountVerificationResponseDTO(
                    success: false,
                    responseCode: (string) ($errorResponse['errorcode'] ?? ''),
                    responseDescription: $errorResponse['messages'] ?? 'Failed to verify minor account',
                    originalResponse: $errorResponse
                );
            }

            return new MinorAccountVerificationResponseDTO(
                success: false,
                responseCode: (string) $e->getCode(),
                responseDescription: 'Failed to verify minor account: ' . $e->getMessage(),
            );
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'Minor Account Verification error',
                [
                    'rrn' => $dto->rrn,
                ],
                $e
            );

            return new MinorAccountVerificationResponseDTO(
                success: false,
                responseCode: (string) $e->getCode(),
                responseDescription: 'Failed to verify minor account: ' . $e->getMessage(),
            );
        }
    }
    /**
     * Upgrade minor account.
     */
    public function upgradeMinorAccount(UpgradeMinorAccountRequestDTO $dto): UpgradeMinorAccountResponseDTO
    {
        try {
            $this->loggingService->logInfo('Initiating upgrade minor account', [
                'rrn' => $dto->rrn,
                'mobile_number' => $dto->mobileNumber,
            ]);

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
            $requestBody = $dto->toArray();

            // Log request
            $this->loggingService->logRequest($this->upgradeMinorAccountEndpoint, $requestBody, $headers);

            // Make API request
            $response = $this->upgradeMinorAccountClient->post($this->upgradeMinorAccountEndpoint, [
                'headers' => $headers,
                'json' => $requestBody,
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            // Handle null or invalid JSON
            if (!is_array($responseData)) {
                $this->loggingService->logError(
                    'Invalid response from Upgrade Minor Account API',
                    ['response_body' => $responseBody],
                    new \RuntimeException('Invalid JSON response')
                );

                return new UpgradeMinorAccountResponseDTO(
                    success: false,
                    responseCode: '',
                    responseDescription: 'Upgrade Minor Account failed: Invalid response from API'
                );
            }

            // Log response
            $this->loggingService->logResponse(
                $this->upgradeMinorAccountEndpoint,
                $responseData,
                $response->getStatusCode()
            );

            // Audit log
            $this->auditService->log(
                'upgrade_minor_account',
                'onboarding',
                $dto->toArray(),
                (string) (auth()->id() ?? 'system'),
                $dto->rrn
            );

            return UpgradeMinorAccountResponseDTO::fromArray($responseData);
        } catch (GuzzleException $e) {
            $this->loggingService->logError(
                'Failed to upgrade minor account',
                [
                    'rrn' => $dto->rrn,
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
                return new UpgradeMinorAccountResponseDTO(
                    success: false,
                    responseCode: (string) ($errorResponse['errorcode'] ?? ''),
                    responseDescription: $errorResponse['messages'] ?? 'Failed to upgrade minor account',
                    originalResponse: $errorResponse
                );
            }

            return new UpgradeMinorAccountResponseDTO(
                success: false,
                responseCode: (string) $e->getCode(),
                responseDescription: 'Failed to upgrade minor account: ' . $e->getMessage(),
            );
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'Upgrade Minor Account error',
                [
                    'rrn' => $dto->rrn,
                ],
                $e
            );

            return new UpgradeMinorAccountResponseDTO(
                success: false,
                responseCode: (string) $e->getCode(),
                responseDescription: 'Failed to upgrade minor account: ' . $e->getMessage(),
            );
        }
    }
    /**
     * Get account statement V2.
     */
    public function accountStatementV2(AccountStatementV2RequestDTO $dto): AccountStatementV2ResponseDTO
    {
        try {
            $this->loggingService->logInfo('Initiating account statement V2 fetch', [
                'account_number' => $dto->accountNumber,
                'from_date' => $dto->fromDate,
                'to_date' => $dto->toDate,
            ]);

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
            $requestBody = $dto->toArray();

            // Log request
            $this->loggingService->logRequest($this->accountStatementV2Endpoint, $requestBody, $headers);

            // Make API request
            $response = $this->accountStatementV2Client->post($this->accountStatementV2Endpoint, [
                'headers' => $headers,
                'json' => $requestBody,
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            // Handle null or invalid JSON
            if (!is_array($responseData)) {
                $this->loggingService->logError(
                    'Invalid response from Account Statement V2 API',
                    ['response_body' => $responseBody],
                    new \RuntimeException('Invalid JSON response')
                );

                return new AccountStatementV2ResponseDTO(
                    success: false,
                    responseCode: '',
                    responseDescription: 'Account Statement V2 failed: Invalid response from API'
                );
            }

            // Log response
            $this->loggingService->logResponse(
                $this->accountStatementV2Endpoint,
                $responseData,
                $response->getStatusCode()
            );

            // Audit log
            $this->auditService->log(
                'account_statement_v2',
                'onboarding',
                $dto->toArray(),
                (string) (auth()->id() ?? 'system'),
                $dto->accountNumber
            );

            return AccountStatementV2ResponseDTO::fromArray($responseData);
        } catch (GuzzleException $e) {
            $this->loggingService->logError(
                'Failed to fetch account statement V2',
                [
                    'account_number' => $dto->accountNumber,
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
                return new AccountStatementV2ResponseDTO(
                    success: false,
                    responseCode: (string) ($errorResponse['errorcode'] ?? ''),
                    responseDescription: $errorResponse['messages'] ?? 'Failed to fetch account statement V2',
                    originalResponse: $errorResponse
                );
            }

            return new AccountStatementV2ResponseDTO(
                success: false,
                responseCode: (string) $e->getCode(),
                responseDescription: 'Failed to fetch account statement V2: ' . $e->getMessage(),
            );
        } catch (\Exception $e) {
            $this->loggingService->logError(
                'Account Statement V2 error',
                [
                    'account_number' => $dto->accountNumber,
                ],
                $e
            );

            return new AccountStatementV2ResponseDTO(
                success: false,
                responseCode: (string) $e->getCode(),
                responseDescription: 'Failed to fetch account statement V2: ' . $e->getMessage(),
            );
        }
    }

}

