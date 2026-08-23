<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class AgentAccountOpeningUpgradeRequestDTO
{
    public function __construct(
        public int $dtid,
        public string $pin,
        public string $customerMobile,
        public string $cnic,
        public string $birthPlace,
        public string $customerName,
        public string $motherMaiden,
        public string $dateOfBirth,
        public string $cnicExpiry,
        public string $presentAddress,
        public string $permanentAddress,
        public string $accountTitle,
        public string $gender,
        public string $agentMobile,
        public int $pid,
        public string $customerMobileNetwork,
        public int $customerAccountType,
        public int $encryptionType = 1,
        public string $registrationState = '',
        public string $registrationStateId = '',
        public string $response = '',
        public string $cnicStatus = '',
        public string $presentCity = '',
        public string $permanentCity = '',
        public string $fatherHusbandName = '',
        public int $isCnicSeen = 1,
        public int $depositAmountFlag = 0,
        public string $depositAmount = '',
        public int $isBvsAccount = 1,
        public string $transactionId = '',
        public string $isHra = '',
        public string $nextOfKinMobile = '',
        public string $transactionPurpose = '',
        public string $occupation = '',
        public string $orgLocation1 = '',
        public string $orgLocation2 = '',
        public string $orgLocation3 = '',
        public string $orgLocation4 = '',
        public string $orgLocation5 = '',
        public string $orgRelation1 = '',
        public string $orgRelation2 = '',
        public string $orgRelation3 = '',
        public string $orgRelation4 = '',
        public string $orgRelation5 = ''
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            dtid: (int) ($data['dtid'] ?? $data['DTID'] ?? 0),
            pin: $data['pin'] ?? $data['PIN'] ?? '',
            customerMobile: $data['customer_mobile'] ?? $data['CMOB'] ?? '',
            cnic: $data['cnic'] ?? $data['CNIC'] ?? '',
            birthPlace: $data['birth_place'] ?? $data['BIRTH_PLACE'] ?? '',
            customerName: $data['customer_name'] ?? $data['CNAME'] ?? '',
            motherMaiden: $data['mother_maiden'] ?? $data['MOTHER_MAIDEN'] ?? '',
            dateOfBirth: $data['date_of_birth'] ?? $data['CDOB'] ?? '',
            cnicExpiry: $data['cnic_expiry'] ?? $data['CNIC_EXP'] ?? '',
            presentAddress: $data['present_address'] ?? $data['PRESENT_ADDR'] ?? '',
            permanentAddress: $data['permanent_address'] ?? $data['PERMANENT_ADDR'] ?? '',
            accountTitle: $data['account_title'] ?? $data['ACTITLE'] ?? '',
            gender: $data['gender'] ?? $data['GENDER'] ?? '',
            agentMobile: $data['agent_mobile'] ?? $data['AMOB'] ?? '',
            pid: (int) ($data['pid'] ?? $data['PID'] ?? 0),
            customerMobileNetwork: $data['customer_mobile_network'] ?? $data['CUST_MOB_NETWORK'] ?? '',
            customerAccountType: (int) ($data['customer_account_type'] ?? $data['CUST_ACC_TYPE'] ?? 0),
            encryptionType: (int) ($data['encryption_type'] ?? $data['ENCT'] ?? 1),
            registrationState: $data['registration_state'] ?? $data['CREG_STATE'] ?? '',
            registrationStateId: $data['registration_state_id'] ?? $data['CREG_STATE_ID'] ?? '',
            response: $data['response'] ?? $data['RESP'] ?? '',
            cnicStatus: $data['cnic_status'] ?? $data['CNIC_STATUS'] ?? '',
            presentCity: $data['present_city'] ?? $data['PRESENT_CITY'] ?? '',
            permanentCity: $data['permanent_city'] ?? $data['PERMANENT_CITY'] ?? '',
            fatherHusbandName: $data['father_husband_name'] ?? $data['FATHER_HUSBND_NAME'] ?? '',
            isCnicSeen: (int) ($data['is_cnic_seen'] ?? $data['IS_CNIC_SEEN'] ?? 1),
            depositAmountFlag: (int) ($data['deposit_amount_flag'] ?? $data['DEPOSIT_AMT_FLAG'] ?? 0),
            depositAmount: $data['deposit_amount'] ?? $data['DEPOSIT_AMT'] ?? '',
            isBvsAccount: (int) ($data['is_bvs_account'] ?? $data['IS_BVS_ACCOUNT'] ?? 1),
            transactionId: $data['transaction_id'] ?? $data['TRXID'] ?? '',
            isHra: $data['is_hra'] ?? $data['IS_HRA'] ?? '',
            nextOfKinMobile: $data['next_of_kin_mobile'] ?? $data['NOKMOB'] ?? '',
            transactionPurpose: $data['transaction_purpose'] ?? $data['TRX_PUR'] ?? '',
            occupation: $data['occupation'] ?? $data['OCCUPATION'] ?? '',
            orgLocation1: $data['org_location_1'] ?? $data['ORG_LOC1'] ?? '',
            orgLocation2: $data['org_location_2'] ?? $data['ORG_LOC2'] ?? '',
            orgLocation3: $data['org_location_3'] ?? $data['ORG_LOC3'] ?? '',
            orgLocation4: $data['org_location_4'] ?? $data['ORG_LOC4'] ?? '',
            orgLocation5: $data['org_location_5'] ?? $data['ORG_LOC5'] ?? '',
            orgRelation1: $data['org_relation_1'] ?? $data['ORG_REL1'] ?? '',
            orgRelation2: $data['org_relation_2'] ?? $data['ORG_REL2'] ?? '',
            orgRelation3: $data['org_relation_3'] ?? $data['ORG_REL3'] ?? '',
            orgRelation4: $data['org_relation_4'] ?? $data['ORG_REL4'] ?? '',
            orgRelation5: $data['org_relation_5'] ?? $data['ORG_REL5'] ?? '',
        );
    }

    protected function validate(): void
    {
        if ($this->dtid <= 0) {
            throw new InvalidArgumentException('DTID must be greater than zero');
        }

        if ($this->pin === '') {
            throw new InvalidArgumentException('PIN cannot be empty');
        }

        if (strlen($this->customerMobile) !== 11) {
            throw new InvalidArgumentException('Customer mobile must be exactly 11 characters');
        }

        if (strlen($this->agentMobile) !== 11) {
            throw new InvalidArgumentException('Agent mobile must be exactly 11 characters');
        }

        if (strlen($this->cnic) !== 13) {
            throw new InvalidArgumentException('CNIC must be exactly 13 characters');
        }

        if ($this->customerName === '') {
            throw new InvalidArgumentException('Customer name cannot be empty');
        }

        if ($this->motherMaiden === '') {
            throw new InvalidArgumentException('Mother maiden name cannot be empty');
        }

        if ($this->dateOfBirth === '') {
            throw new InvalidArgumentException('Date of birth cannot be empty');
        }

        if ($this->cnicExpiry === '') {
            throw new InvalidArgumentException('CNIC expiry cannot be empty');
        }

        if ($this->birthPlace === '') {
            throw new InvalidArgumentException('Birth place cannot be empty');
        }

        if ($this->presentAddress === '') {
            throw new InvalidArgumentException('Present address cannot be empty');
        }

        if ($this->permanentAddress === '') {
            throw new InvalidArgumentException('Permanent address cannot be empty');
        }

        if ($this->accountTitle === '') {
            throw new InvalidArgumentException('Account title cannot be empty');
        }

        if ($this->gender === '') {
            throw new InvalidArgumentException('Gender cannot be empty');
        }

        if ($this->pid <= 0) {
            throw new InvalidArgumentException('PID must be greater than zero');
        }

        if ($this->customerMobileNetwork === '') {
            throw new InvalidArgumentException('Customer mobile network cannot be empty');
        }

        if ($this->customerAccountType <= 0) {
            throw new InvalidArgumentException('Customer account type must be greater than zero');
        }
    }

    public function toArray(): array
    {
        return [
            'accountOpeningAgentL0Req' => [
                'DTID' => $this->dtid,
                'ENCT' => $this->encryptionType,
                'CREG_STATE' => $this->registrationState,
                'CREG_STATE_ID' => $this->registrationStateId,
                'CMOB' => $this->customerMobile,
                'PIN' => $this->pin,
                'CNIC' => $this->cnic,
                'BIRTH_PLACE' => $this->birthPlace,
                'RESP' => $this->response,
                'CNAME' => $this->customerName,
                'MOTHER_MAIDEN' => $this->motherMaiden,
                'CDOB' => $this->dateOfBirth,
                'CNIC_STATUS' => $this->cnicStatus,
                'CNIC_EXP' => $this->cnicExpiry,
                'PRESENT_ADDR' => $this->presentAddress,
                'PRESENT_CITY' => $this->presentCity,
                'PERMANENT_ADDR' => $this->permanentAddress,
                'PERMANENT_CITY' => $this->permanentCity,
                'ACTITLE' => $this->accountTitle,
                'GENDER' => $this->gender,
                'FATHER_HUSBND_NAME' => $this->fatherHusbandName,
                'IS_CNIC_SEEN' => $this->isCnicSeen,
                'DEPOSIT_AMT_FLAG' => $this->depositAmountFlag,
                'DEPOSIT_AMT' => $this->depositAmount,
                'AMOB' => $this->agentMobile,
                'PID' => $this->pid,
                'IS_BVS_ACCOUNT' => $this->isBvsAccount,
                'CUST_ACC_TYPE' => $this->customerAccountType,
                'TRXID' => $this->transactionId,
                'CUST_MOB_NETWORK' => $this->customerMobileNetwork,
                'IS_HRA' => $this->isHra,
                'NOKMOB' => $this->nextOfKinMobile,
                'TRX_PUR' => $this->transactionPurpose,
                'OCCUPATION' => $this->occupation,
                'ORG_LOC1' => $this->orgLocation1,
                'ORG_LOC2' => $this->orgLocation2,
                'ORG_LOC3' => $this->orgLocation3,
                'ORG_LOC4' => $this->orgLocation4,
                'ORG_LOC5' => $this->orgLocation5,
                'ORG_REL1' => $this->orgRelation1,
                'ORG_REL2' => $this->orgRelation2,
                'ORG_REL3' => $this->orgRelation3,
                'ORG_REL4' => $this->orgRelation4,
                'ORG_REL5' => $this->orgRelation5,
            ],
        ];
    }
}
