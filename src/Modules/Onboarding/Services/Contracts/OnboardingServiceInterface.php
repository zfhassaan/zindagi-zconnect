<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\Contracts;

use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\OnboardingRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\OnboardingResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationResponseDTO;

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
}

