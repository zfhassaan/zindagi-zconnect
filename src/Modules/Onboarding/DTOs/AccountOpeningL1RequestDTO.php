<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use DateTime;
use InvalidArgumentException;
use zfhassaan\ZindagiZconnect\Helpers\ValidationHelper;

class AccountOpeningL1RequestDTO
{
    public function __construct(
        public string $cnic,
        public string $mobileNo,
        public string $fingerTemplate,
        public string $traceNo,
        public string $dateTime,
        public string $cnicIssuanceDate,
        public string $mobileNetwork,
        public string $emailId,
        public string $fingerIndex = '1',
        public string $processingCode = 'AccountOpening',
        public string $merchantType = '0088',
        public string $companyName = 'NOVA'
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
        if (strlen($this->cnic) !== 13) {
            throw new InvalidArgumentException('CNIC must be exactly 13 characters');
        }

        if (strlen($this->mobileNo) !== 11) {
            throw new InvalidArgumentException('Mobile number must be exactly 11 characters');
        }

        if (empty($this->fingerTemplate)) {
            throw new InvalidArgumentException('Finger template cannot be empty');
        }

        if (strlen($this->traceNo) > 6) {
            throw new InvalidArgumentException('TraceNo must be maximum 6 characters');
        }

        if (strlen($this->dateTime) !== 14) {
            throw new InvalidArgumentException('DateTime must be exactly 14 characters in YYYYMMDDHHMMSS format');
        }
        
        if (strlen($this->emailId) > 25) {
             throw new InvalidArgumentException('EmailId must be maximum 25 characters');
        }
        
        $validNetworks = ['MOBILINK', 'TELENOR', 'UFONE', 'WARID', 'ZONG', 'SCO'];
        if (!in_array(strtoupper($this->mobileNetwork), $validNetworks)) {
            throw new InvalidArgumentException('Invalid mobile network. Allowed values: ' . implode(', ', $validNetworks));
        }
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return [
            'AccOpenL1Request' => [
                'processingCode' => $this->processingCode,
                'merchantType' => $this->merchantType,
                'traceNo' => str_pad($this->traceNo, 6, '0', STR_PAD_LEFT),
                'companyName' => $this->companyName,
                'dateTime' => $this->dateTime,
                'cnic' => $this->cnic,
                'fingerIndex' => $this->fingerIndex,
                'fingerTemplate' => $this->fingerTemplate,
                'cnicIssuanceDate' => $this->cnicIssuanceDate,
                'mobileNo' => $this->mobileNo,
                'mobileNetwork' => strtoupper($this->mobileNetwork),
                'emailId' => $this->emailId,
            ],
        ];
    }
}
