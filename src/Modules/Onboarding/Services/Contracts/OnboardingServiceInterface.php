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
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountInfoRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountInfoResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountStatementV2RequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountStatementV2ResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\MinorAccountOpeningRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\MinorAccountOpeningResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\MinorAccountVerificationRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\MinorAccountVerificationResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\UpgradeMinorAccountRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\UpgradeMinorAccountResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\L2AccountUpgradeRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\L2AccountUpgradeResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingV2RequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingV2ResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountUpgradeV2RequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountUpgradeV2ResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentLoginRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentLoginResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentAccountVerificationRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentAccountVerificationResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashDepositInquiryRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashDepositInquiryResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashDepositRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashDepositResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashWithdrawalInquiryRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashWithdrawalInquiryResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashWithdrawalRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashWithdrawalResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentBillPaymentInquiryRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentBillPaymentInquiryResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentBillPaymentRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentBillPaymentResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentDebitCardIssuanceInfoRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentDebitCardIssuanceInfoResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentDebitCardIssuanceRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentDebitCardIssuanceResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentAccountOpeningUpgradeRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentAccountOpeningUpgradeResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentDeviceChangedRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentDeviceChangedResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentChangePinRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentChangePinResponseDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentOtpVerificationRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentOtpVerificationResponseDTO;

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

    /**
     * Get account information by mobile number.
     *
     * @param AccountInfoRequestDTO $dto
     * @return AccountInfoResponseDTO
     */
    public function getAccountInfo(AccountInfoRequestDTO $dto): AccountInfoResponseDTO;

    /**
     * Get account statement V2.
     *
     * @param AccountStatementV2RequestDTO $dto
     * @return AccountStatementV2ResponseDTO
     */
    public function accountStatementV2(AccountStatementV2RequestDTO $dto): AccountStatementV2ResponseDTO;

    /**
     * Open a minor (M0) account.
     */
    public function minorAccountOpening(MinorAccountOpeningRequestDTO $dto): MinorAccountOpeningResponseDTO;

    /**
     * Verify a minor (M0) account.
     */
    public function minorAccountVerification(MinorAccountVerificationRequestDTO $dto): MinorAccountVerificationResponseDTO;

    /**
     * Upgrade a minor (M0) account.
     */
    public function upgradeMinorAccount(UpgradeMinorAccountRequestDTO $dto): UpgradeMinorAccountResponseDTO;

    /**
     * Upgrade an account to Level 2.
     */
    public function upgradeL2Account(L2AccountUpgradeRequestDTO $dto): L2AccountUpgradeResponseDTO;

    /**
     * Link account with CNIC, mobile number, and encrypted MPIN (v2).
     */
    public function linkAccountV2(AccountLinkingV2RequestDTO $dto): AccountLinkingV2ResponseDTO;

    /**
     * Upgrade existing account with multi-fingerprint biometrics (v2).
     */
    public function upgradeAccountV2(AccountUpgradeV2RequestDTO $dto): AccountUpgradeV2ResponseDTO;

    /**
     * Authenticate an agent session.
     */
    public function agentLogin(AgentLoginRequestDTO $dto): AgentLoginResponseDTO;

    /**
     * Verify a customer account via an authenticated agent session.
     */
    public function agentAccountVerification(AgentAccountVerificationRequestDTO $dto): AgentAccountVerificationResponseDTO;

    /**
     * Inquire cash deposit fees and totals before an agent cash deposit.
     */
    public function agentCashDepositInquiry(AgentCashDepositInquiryRequestDTO $dto): AgentCashDepositInquiryResponseDTO;

    /**
     * Execute an agent cash deposit.
     */
    public function agentCashDeposit(AgentCashDepositRequestDTO $dto): AgentCashDepositResponseDTO;

    /**
     * Inquire cash withdrawal fees and eligibility before an agent cash withdrawal.
     */
    public function agentCashWithdrawalInquiry(AgentCashWithdrawalInquiryRequestDTO $dto): AgentCashWithdrawalInquiryResponseDTO;

    /**
     * Execute an agent cash withdrawal.
     */
    public function agentCashWithdrawal(AgentCashWithdrawalRequestDTO $dto): AgentCashWithdrawalResponseDTO;

    /**
     * Inquire bill details before an agent bill payment.
     */
    public function agentBillPaymentInquiry(AgentBillPaymentInquiryRequestDTO $dto): AgentBillPaymentInquiryResponseDTO;

    /**
     * Execute an agent bill payment.
     */
    public function agentBillPayment(AgentBillPaymentRequestDTO $dto): AgentBillPaymentResponseDTO;

    /**
     * Check debit card issuance eligibility before requesting a card.
     */
    public function agentDebitCardIssuanceInfo(AgentDebitCardIssuanceInfoRequestDTO $dto): AgentDebitCardIssuanceInfoResponseDTO;

    /**
     * Request agent debit card issuance for a customer.
     */
    public function agentDebitCardIssuance(AgentDebitCardIssuanceRequestDTO $dto): AgentDebitCardIssuanceResponseDTO;

    /**
     * Open or upgrade a customer account via an agent.
     */
    public function agentAccountOpeningUpgrade(AgentAccountOpeningUpgradeRequestDTO $dto): AgentAccountOpeningUpgradeResponseDTO;

    /**
     * Register an agent device change.
     */
    public function agentDeviceChanged(AgentDeviceChangedRequestDTO $dto): AgentDeviceChangedResponseDTO;

    /**
     * Change an agent PIN.
     */
    public function agentChangePin(AgentChangePinRequestDTO $dto): AgentChangePinResponseDTO;

    /**
     * Verify an agent OTP.
     */
    public function agentOtpVerification(AgentOtpVerificationRequestDTO $dto): AgentOtpVerificationResponseDTO;
}

