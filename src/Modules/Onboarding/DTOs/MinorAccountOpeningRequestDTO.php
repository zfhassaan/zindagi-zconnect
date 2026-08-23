<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class MinorAccountOpeningRequestDTO
{
    public function __construct(
        public string $rrn,
        public string $dateTime,
        public string $accountTitle,
        public string $cnic,
        public string $issuanceDate,
        public string $mobileNumber,
        public string $motherMaidenName,
        public string $fatherName,
        public string $placeOfBirth,
        public string $dateOfBirth,
        public string $address,
        public string $nicExpiry,
        public string $parentCnicPic,
        public string $snicPic,
        public string $minorCustomerPic,
        public string $fatherMotherMobileNumber,
        public string $fatherCnic,
        public string $fatherCnicIssuanceDate,
        public string $fatherCnicExpiryDate,
        public string $motherCnic,
        public string $email,
        public string $bFormPic = '',
        public string $channelId = 'NOVA',
        public string $terminalId = 'NOVA',
        public string $reserved1 = '',
        public string $reserved2 = '',
        public string $reserved3 = '',
        public string $reserved4 = '',
        public string $reserved5 = '',
        public string $reserved6 = '',
        public string $reserved7 = '',
        public string $reserved8 = '',
        public string $reserved9 = '',
        public string $reserved10 = ''
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            rrn: $data['rrn'] ?? $data['RRN'] ?? '',
            dateTime: $data['date_time'] ?? $data['DateTime'] ?? '',
            accountTitle: $data['account_title'] ?? $data['AccountTitle'] ?? $data['AccountTilte'] ?? '',
            cnic: $data['cnic'] ?? $data['Cnic'] ?? '',
            issuanceDate: $data['issuance_date'] ?? $data['IssuanceDate'] ?? '',
            mobileNumber: $data['mobile_number'] ?? $data['MobileNumber'] ?? '',
            motherMaidenName: $data['mother_maiden_name'] ?? $data['MotherMaidenName'] ?? $data['MotherMedianName'] ?? '',
            fatherName: $data['father_name'] ?? $data['FatherName'] ?? '',
            placeOfBirth: $data['place_of_birth'] ?? $data['PlaceOfBirth'] ?? $data['PlaceOfbirth'] ?? '',
            dateOfBirth: $data['date_of_birth'] ?? $data['DateOfBirth'] ?? '',
            address: $data['address'] ?? $data['Address'] ?? '',
            nicExpiry: $data['nic_expiry'] ?? $data['NicExpiry'] ?? '',
            parentCnicPic: $data['parent_cnic_pic'] ?? $data['parentCnicPic'] ?? '',
            snicPic: $data['snic_pic'] ?? $data['SnicPic'] ?? '',
            minorCustomerPic: $data['minor_customer_pic'] ?? $data['minorCustomerPic'] ?? $data['minorCutomerPic'] ?? '',
            fatherMotherMobileNumber: $data['father_mother_mobile_number'] ?? $data['fatherMotherMobileNumber'] ?? '',
            fatherCnic: $data['father_cnic'] ?? $data['fatherCnic'] ?? '',
            fatherCnicIssuanceDate: $data['father_cnic_issuance_date'] ?? $data['FatherCnicIssuanceDate'] ?? '',
            fatherCnicExpiryDate: $data['father_cnic_expiry_date'] ?? $data['FatherCnicExpiryDate'] ?? '',
            motherCnic: $data['mother_cnic'] ?? $data['motherCnic'] ?? '',
            email: $data['email'] ?? '',
            bFormPic: $data['b_form_pic'] ?? $data['BFormPic'] ?? '',
            channelId: $data['channel_id'] ?? $data['ChannelId'] ?? 'NOVA',
            terminalId: $data['terminal_id'] ?? $data['TerminalId'] ?? 'NOVA',
            reserved1: $data['reserved1'] ?? $data['Reserved1'] ?? '',
            reserved2: $data['reserved2'] ?? $data['Reserved2'] ?? '',
            reserved3: $data['reserved3'] ?? $data['Reserved3'] ?? '',
            reserved4: $data['reserved4'] ?? $data['Reserved4'] ?? '',
            reserved5: $data['reserved5'] ?? $data['Reserved5'] ?? '',
            reserved6: $data['reserved6'] ?? $data['Reserved6'] ?? '',
            reserved7: $data['reserved7'] ?? $data['Reserved7'] ?? '',
            reserved8: $data['reserved8'] ?? $data['Reserved8'] ?? '',
            reserved9: $data['reserved9'] ?? $data['Reserved9'] ?? '',
            reserved10: $data['reserved10'] ?? $data['Reserved10'] ?? '',
        );
    }

    protected function validate(): void
    {
        if (empty($this->rrn)) {
            throw new InvalidArgumentException('RRN cannot be empty');
        }

        if (empty($this->cnic)) {
            throw new InvalidArgumentException('CNIC cannot be empty');
        }

        if (empty($this->mobileNumber)) {
            throw new InvalidArgumentException('Mobile Number cannot be empty');
        }
    }

    public function toArray(): array
    {
        return [
            'minorAccountOpeningReq' => [
                'RRN' => $this->rrn,
                'DateTime' => $this->dateTime,
                'AccountTilte' => $this->accountTitle, // Note: Typos in API 'AccountTilte' kept as per docs/sample? Sample has "AccountTilte"
                'Cnic' => $this->cnic,
                'IssuanceDate' => $this->issuanceDate,
                'MobileNumber' => $this->mobileNumber,
                'MotherMedianName' => $this->motherMaidenName, // Sample has "MotherMedianName"
                'FatherName' => $this->fatherName,
                'PlaceOfbirth' => $this->placeOfBirth, // Sample has "PlaceOfbirth" (lowercase b)
                'DateOfBirth' => $this->dateOfBirth,
                'Address' => $this->address,
                'NicExpiry' => $this->nicExpiry,
                'parentCnicPic' => $this->parentCnicPic,
                'SnicPic' => $this->snicPic,
                'minorCutomerPic' => $this->minorCustomerPic, // Sample has "minorCutomerPic" (typo Cutomer)
                'fatherMotherMobileNumber' => $this->fatherMotherMobileNumber,
                'fatherCnic' => $this->fatherCnic,
                'FatherCnicIssuanceDate' => $this->fatherCnicIssuanceDate,
                'FatherCnicExpiryDate' => $this->fatherCnicExpiryDate,
                'motherCnic' => $this->motherCnic,
                'email' => $this->email,
                'BFormPic' => $this->bFormPic,
                'ChannelId' => $this->channelId,
                'TerminalId' => $this->terminalId,
                'Reserved1' => $this->reserved1,
                'Reserved2' => $this->reserved2,
                'Reserved3' => $this->reserved3,
                'Reserved4' => $this->reserved4,
                'Reserved5' => $this->reserved5,
                'Reserved6' => $this->reserved6,
                'Reserved7' => $this->reserved7,
                'Reserved8' => $this->reserved8,
                'Reserved9' => $this->reserved9,
                'Reserved10' => $this->reserved10,
            ],
        ];
    }
}
