<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

use InvalidArgumentException;

class AccountUpgradeV2RequestDTO
{
    /**
     * @param array<int, array{index?: string, template?: string, Index?: string, Template?: string}> $fingerprints
     */
    public function __construct(
        public string $cnic,
        public string $mobileNo,
        public string $traceNo,
        public string $dateTime,
        public string $terminalId,
        public array $fingerprints,
        public string $latitude,
        public string $longitude,
        public string $udid,
        public string $processingCode = 'UpgradeAcc',
        public string $merchantType = '0088',
        public string $companyName = 'NOVA'
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            cnic: $data['cnic'] ?? $data['CNIC'] ?? '',
            mobileNo: $data['mobile_no'] ?? $data['MobileNo'] ?? '',
            traceNo: $data['trace_no'] ?? $data['TraceNo'] ?? '',
            dateTime: $data['date_time'] ?? $data['DateTime'] ?? '',
            terminalId: $data['terminal_id'] ?? $data['TerminalId'] ?? '',
            fingerprints: $data['fingerprints'] ?? $data['Fingerprints'] ?? [],
            latitude: $data['latitude'] ?? $data['LATITUDE'] ?? '',
            longitude: $data['longitude'] ?? $data['LONGITUDE'] ?? '',
            udid: $data['udid'] ?? $data['UDID'] ?? '',
            processingCode: $data['processing_code'] ?? $data['ProcessingCode'] ?? 'UpgradeAcc',
            merchantType: $data['merchant_type'] ?? $data['MerchantType'] ?? '0088',
            companyName: $data['company_name'] ?? $data['CompanyName'] ?? 'NOVA',
        );
    }

    protected function validate(): void
    {
        if (strlen($this->cnic) !== 13) {
            throw new InvalidArgumentException('CNIC must be exactly 13 characters');
        }

        if (strlen($this->mobileNo) !== 11) {
            throw new InvalidArgumentException('Mobile number must be exactly 11 characters');
        }

        if ($this->traceNo === '') {
            throw new InvalidArgumentException('TraceNo cannot be empty');
        }

        if (strlen($this->traceNo) > 6) {
            throw new InvalidArgumentException('TraceNo must be maximum 6 characters');
        }

        if (strlen($this->dateTime) !== 14) {
            throw new InvalidArgumentException('DateTime must be exactly 14 characters in YYYYMMDDHHMMSS format');
        }

        if ($this->terminalId === '') {
            throw new InvalidArgumentException('TerminalId cannot be empty');
        }

        if ($this->fingerprints === []) {
            throw new InvalidArgumentException('At least one fingerprint is required');
        }

        foreach ($this->fingerprints as $fingerprint) {
            $index = $fingerprint['index'] ?? $fingerprint['Index'] ?? '';
            $template = $fingerprint['template'] ?? $fingerprint['Template'] ?? '';

            if ($index === '' || $template === '') {
                throw new InvalidArgumentException('Each fingerprint must include index and template');
            }
        }

        if ($this->latitude === '') {
            throw new InvalidArgumentException('LATITUDE cannot be empty');
        }

        if ($this->longitude === '') {
            throw new InvalidArgumentException('LONGITUDE cannot be empty');
        }

        if ($this->udid === '') {
            throw new InvalidArgumentException('UDID cannot be empty');
        }
    }

    public function toArray(): array
    {
        return [
            'UpgradeAcc' => [
                'ProcessingCode' => $this->processingCode,
                'MerchantType' => $this->merchantType,
                'TraceNo' => str_pad($this->traceNo, 6, '0', STR_PAD_LEFT),
                'CompanyName' => $this->companyName,
                'DateTime' => $this->dateTime,
                'TerminalId' => $this->terminalId,
                'CNIC' => $this->cnic,
                'Fingerprints' => array_map(
                    fn (array $fingerprint): array => [
                        'index' => (string) ($fingerprint['index'] ?? $fingerprint['Index'] ?? ''),
                        'template' => (string) ($fingerprint['template'] ?? $fingerprint['Template'] ?? ''),
                    ],
                    $this->fingerprints
                ),
                'MobileNo' => $this->mobileNo,
                'LATITUDE' => $this->latitude,
                'LONGITUDE' => $this->longitude,
                'UDID' => $this->udid,
            ],
        ];
    }
}
