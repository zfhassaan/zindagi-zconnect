<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\Contracts;

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

interface OnboardingServiceInterface
{
    /**
     * Initiate customer onboarding.
     *
     * @param OnboardingRequestDTO $dto
     * @return OnboardingResponseDTO
     */
    public function initiate(OnboardingRequestDTO $dto): OnboardingResponseDTO;

    /**
     * Verify customer information.
     *
     * @param string $referenceId
     * @param array $verificationData
     * @return OnboardingResponseDTO
     */
    public function verify(string $referenceId, array $verificationData): OnboardingResponseDTO;

    /**
     * Get onboarding status.
     *
     * @param string $referenceId
     * @return OnboardingResponseDTO
     */
    public function getStatus(string $referenceId): OnboardingResponseDTO;

    /**
     * Complete onboarding process.
     *
     * @param string $referenceId
     * @param array $completionData
     * @return OnboardingResponseDTO
     */
    public function complete(string $referenceId, array $completionData): OnboardingResponseDTO;

    /**
     * Verify account link with CNIC and mobile number.
     *
     * @param AccountVerificationRequestDTO $dto
     * @return AccountVerificationResponseDTO
     */
    public function verifyAccount(AccountVerificationRequestDTO $dto): AccountVerificationResponseDTO;

    /**
     * Link account with CNIC and mobile number.
     *
     * @param AccountLinkingRequestDTO $dto
     * @return AccountLinkingResponseDTO
     */
    public function linkAccount(AccountLinkingRequestDTO $dto): AccountLinkingResponseDTO;

    /**
     * Open account with customer information.
     *
     * @param AccountOpeningRequestDTO $dto
     * @return AccountOpeningResponseDTO
     */
    public function openAccount(AccountOpeningRequestDTO $dto): AccountOpeningResponseDTO;
    
    /**
     * Open L1 account with customer information.
     *
     * @param AccountOpeningL1RequestDTO $dto
     * @return AccountOpeningL1ResponseDTO
     */
    public function openAccountL1(AccountOpeningL1RequestDTO $dto): AccountOpeningL1ResponseDTO;
}

