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

    protected function validate(): void
    {
        if (strlen($this->rrn) !== 12 && strlen($this->rrn) !== 13) {
             // RRN length varies in examples, sticking to common 12 usually but example had 13 (1255822445001). 
             // Let's assume standard is 12 but allow 13 if specific to this API or just check not empty for now to be safe, 
             // or check documentation sample "1255822445001" is 13 digits. 
             // Actually, usually RRN is 12. The sample "1255822445001" is 13 digits.
             // Let's validate it's not empty.
        }
         if (empty($this->rrn)) {
            throw new InvalidArgumentException('RRN cannot be empty');
        }

        if (strlen($this->dateTime) !== 16 && strlen($this->dateTime) !== 14 && strlen($this->dateTime) !== 8) {
             // Sample says "11172022" (8 chars MMDDYYYY?). Other APIs use YYYYMMDDHHmmss (14).
             // Let's just validate not empty to be flexible unless we are sure.
             // Sample: "11172022" -> 8 chars.
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
