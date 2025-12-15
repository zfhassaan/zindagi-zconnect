<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class L2AccountUpgradeDiscrepantRequestDTO
{
    public function __construct(
        public string $mobileNumber,
        public string $dateTime,
        public string $rrn,
        public string $cnic,
        public string $consumerName,
        public string $fatherHusbandName,
        public string $purposeOfAccount,
        public string $sourceOfIncome,
        public string $expectedMonthlyTurnover,
        public string $birthPlace,
        public string $motherMaiden,
        public string $emailAddress,
        public string $mailingAddress,
        public string $permanentAddress,
        public string $city,
        public string $area,
        public string $houseNumber,
        public string $channelId = 'NOVA',
        public string $terminalId = 'NOVA',
        public string $cnicFrontPic = '',
        public string $cnicBackPic = '',
        public string $customerPic = '',
        public string $sourceOfIncomePic = '',
        public string $signaturePic = '',
        public string $currencyCode = 'PKR',
        public string $usCitizenship = 'No',
        public string $usMobileNumber = '',
        public string $signatoryAuthority = 'No',
        public string $usLinks = '',
        public string $federalTaxClassification = '',
        public string $dualCitizenAddress = 'No',
        public string $taxIdNumber = '',
        public string $foreignTaxIdNumber = '',
        public string $usAccountNumber = '',
        public string $utilityBillPicture = '',
        public string $reserved1 = '',
        public string $reserved2 = '',
        public string $reserved3 = '',
        public string $reserved4 = '',
        public string $reserved5 = ''
    ) {
        $this->validate();
    }

    /**
     * Validate the DTO data.
     *
     * @throws InvalidArgumentException
     */
    protected function validate(): void
    {
        if (strlen($this->mobileNumber) !== 11) {
            throw new InvalidArgumentException('Mobile number must be exactly 11 characters');
        }

        if (strlen($this->cnic) !== 13) {
            throw new InvalidArgumentException('CNIC must be exactly 13 characters');
        }

        if (strlen($this->dateTime) !== 14) {
             throw new InvalidArgumentException('DateTime must be exactly 14 characters (YYYYMMDDHHMMSS)');
        }

        if (strlen($this->rrn) !== 12) {
            throw new InvalidArgumentException('RRN must be exactly 12 characters');
        }

        if (empty($this->consumerName)) {
            throw new InvalidArgumentException('Consumer name cannot be empty');
        }

        if (empty($this->fatherHusbandName)) {
            throw new InvalidArgumentException('Father/Husband name cannot be empty');
        }

        if (empty($this->emailAddress) || !filter_var($this->emailAddress, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Valid email address is required');
        }

        if (empty($this->purposeOfAccount)) {
            throw new InvalidArgumentException('Purpose of account cannot be empty');
        }

        if (empty($this->sourceOfIncome)) {
            throw new InvalidArgumentException('Source of income cannot be empty');
        }

        // Validate currency code
        $validCurrencies = ['PKR', 'USD'];
        if (!in_array($this->currencyCode, $validCurrencies)) {
            throw new InvalidArgumentException('Currency code must be PKR or USD');
        }

        // Validate yes/no fields
        $validYesNo = ['Yes', 'No'];
        if (!in_array($this->usCitizenship, $validYesNo)) {
            throw new InvalidArgumentException('US Citizenship must be Yes or No');
        }

        if (!in_array($this->signatoryAuthority, $validYesNo)) {
            throw new InvalidArgumentException('Signatory Authority must be Yes or No');
        }

        if (!in_array($this->dualCitizenAddress, $validYesNo)) {
            throw new InvalidArgumentException('Dual Citizen Address must be Yes or No');
        }
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return [
            'l2AccountUpgradeDiscrepantReq' => [
                'MobileNumber' => $this->mobileNumber,
                'DateTime' => $this->dateTime,
                'Rrn' => $this->rrn,
                'ChannelId' => $this->channelId,
                'TerminalId' => $this->terminalId,
                'Cnic' => $this->cnic,
                'CnicFrontPic' => $this->cnicFrontPic,
                'CnicBackPic' => $this->cnicBackPic,
                'CustomerPic' => $this->customerPic,
                'ConsumerName' => $this->consumerName,
                'FatherHusbandName' => $this->fatherHusbandName,
                'PurposeOfAccount' => $this->purposeOfAccount,
                'SourceOfIncome' => $this->sourceOfIncome,
                'SourceOfIncomePic' => $this->sourceOfIncomePic,
                'ExpectedMonthlyTurnover' => $this->expectedMonthlyTurnover,
                'BirthPlace' => $this->birthPlace,
                'MotherMaiden' => $this->motherMaiden,
                'EmailAddress' => $this->emailAddress,
                'MailingAddress' => $this->mailingAddress,
                'PermanentAddress' => $this->permanentAddress,
                'SignaturePic' => $this->signaturePic,
                'CurrencyCode' => $this->currencyCode,
                'UsCitizenship' => $this->usCitizenship,
                'UsMobileNumber' => $this->usMobileNumber,
                'SignatoryAuthority' => $this->signatoryAuthority,
                'USLinks' => $this->usLinks,
                'FederalTaxClassification' => $this->federalTaxClassification,
                'DualCitizenAddress' => $this->dualCitizenAddress,
                'TaxIdNumber' => $this->taxIdNumber,
                'ForeignTaxIdNumber' => $this->foreignTaxIdNumber,
                'UsAccountNumber' => $this->usAccountNumber,
                'UtilityBillPicture' => $this->utilityBillPicture,
                'City' => $this->city,
                'Area' => $this->area,
                'HouseNumber' => $this->houseNumber,
                'Reserved1' => $this->reserved1,
                'Reserved2' => $this->reserved2,
                'Reserved3' => $this->reserved3,
                'Reserved4' => $this->reserved4,
                'Reserved5' => $this->reserved5,
            ],
        ];
    }
}
