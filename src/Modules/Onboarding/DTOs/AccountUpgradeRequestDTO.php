<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use DateTime;
use InvalidArgumentException;
use zfhassaan\ZindagiZconnect\Helpers\ValidationHelper;

class AccountUpgradeRequestDTO
{
    public function __construct(
        public string $cnic,
        public string $mobileNo,
        public string $fingerTemplate,
        public string $traceNo,
        public string $dateTime,
        public string $terminalId,
        public string $mpin,
        public string $templateType = '0',
        public string $fingerIndex = '1',
        public string $processingCode = 'UpgradeAccount',
        public string $merchantType = '0088',
        public string $companyName = 'NOVA',
        public string $reserved1 = ''
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
        
        if (strlen($this->terminalId) > 8) {
            throw new InvalidArgumentException('TerminalId must be maximum 8 characters');
        }

         if (strlen($this->mpin) !== 4) {
            throw new InvalidArgumentException('MPin must be exactly 4 characters');
        }
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return [
            'UpgradeAcc' => [
                'ProcessingCode' => $this->processingCode,
                'MerchantType' => $this->merchantType,
                'TraceNo' => str_pad($this->traceNo, 6, '0', STR_PAD_LEFT),
                'CompanyName' => $this->companyName,
                'DateTime' => $this->dateTime,
                'CNIC' => $this->cnic,
                'FingerIndex' => $this->fingerIndex,
                'FingerTemplate' => $this->fingerTemplate,
                'TemplateType' => $this->templateType,
                'MobileNo' => $this->mobileNo,
                'TerminalId' => $this->terminalId,
                'MPin' => $this->mpin,
                'Reserved1' => $this->reserved1,
            ],
        ];
    }
}
