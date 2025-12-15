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

    /**
     * Upgrade existing account.
     *
     * @param AccountUpgradeRequestDTO $dto
     * @return AccountUpgradeResponseDTO
     */
    public function upgradeAccount(AccountUpgradeRequestDTO $dto): AccountUpgradeResponseDTO;

    /**
     * Get L2 account fields.
     *
     * @param L2AccountFieldsRequestDTO $dto
     * @return L2AccountFieldsResponseDTO
     */
    public function getL2AccountFields(L2AccountFieldsRequestDTO $dto): L2AccountFieldsResponseDTO;

    /**
     * Update PMD and KYC.
     *
     * @param UpdatePmdKycRequestDTO $dto
     * @return UpdatePmdKycResponseDTO
     */
    public function updatePmdAndKyc(UpdatePmdKycRequestDTO $dto): UpdatePmdKycResponseDTO;

    /**
     * Get L2 account upgrade discrepant data.
     *
     * @param GetL2DiscrepantRequestDTO $dto
     * @return GetL2DiscrepantResponseDTO
     */
    public function getL2AccountUpgradeDiscrepant(GetL2DiscrepantRequestDTO $dto): GetL2DiscrepantResponseDTO;

    /**
     * Submit L2 account upgrade discrepant data.
     *
     * @param L2AccountUpgradeDiscrepantRequestDTO $dto
     * @return L2AccountUpgradeDiscrepantResponseDTO
     */
    public function submitL2AccountUpgradeDiscrepant(L2AccountUpgradeDiscrepantRequestDTO $dto): L2AccountUpgradeDiscrepantResponseDTO;

    /**
     * Get L2 accounts.
     *
     * @param GetL2AccountsRequestDTO $dto
     * @return GetL2AccountsResponseDTO
     */
    public function getL2Accounts(GetL2AccountsRequestDTO $dto): GetL2AccountsResponseDTO;

    /**
     * Get L2 account status.
     *
     * @param L2AccountStatusRequestDTO $dto
     * @return L2AccountStatusResponseDTO
     */
    public function getL2AccountStatus(L2AccountStatusRequestDTO $dto): L2AccountStatusResponseDTO;

    /**
     * Get Level 2 account mother name list.
     *
     * @param Level2AccountMotherRequestDTO $dto
     * @return Level2AccountMotherResponseDTO
     */
    public function getLevel2AccountMotherNames(Level2AccountMotherRequestDTO $dto): Level2AccountMotherResponseDTO;
}

