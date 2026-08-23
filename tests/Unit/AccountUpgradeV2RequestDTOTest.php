<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountUpgradeV2RequestDTO;

class AccountUpgradeV2RequestDTOTest extends TestCase
{
    public function test_successful_dto_creation_with_required_fields(): void
    {
        $dto = $this->validDto();

        $this->assertEquals('1234567890123', $dto->cnic);
        $this->assertEquals('03001234567', $dto->mobileNo);
        $this->assertCount(2, $dto->fingerprints);
        $this->assertEquals('UpgradeAcc', $dto->processingCode);
    }

    public function test_to_array_uses_upgrade_acc_wrapper(): void
    {
        $array = $this->validDto()->toArray();

        $this->assertArrayHasKey('UpgradeAcc', $array);
        $this->assertEquals('UpgradeAcc', $array['UpgradeAcc']['ProcessingCode']);
        $this->assertEquals('1234567890123', $array['UpgradeAcc']['CNIC']);
        $this->assertEquals('33.6844', $array['UpgradeAcc']['LATITUDE']);
        $this->assertEquals('73.0479', $array['UpgradeAcc']['LONGITUDE']);
        $this->assertEquals('efc2b31481cd070a', $array['UpgradeAcc']['UDID']);
        $this->assertEquals('2', $array['UpgradeAcc']['Fingerprints'][0]['index']);
    }

    public function test_from_array_supports_snake_case(): void
    {
        $dto = AccountUpgradeV2RequestDTO::fromArray([
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'trace_no' => '211045',
            'date_time' => '20210105201527',
            'terminal_id' => 'NOVA',
            'fingerprints' => [
                ['index' => '2', 'template' => 'template-a'],
            ],
            'latitude' => '33.6844',
            'longitude' => '73.0479',
            'udid' => 'device-id',
        ]);

        $this->assertEquals('211045', $dto->traceNo);
        $this->assertEquals('device-id', $dto->udid);
    }

    public function test_validation_fails_without_fingerprints(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one fingerprint is required');

        new AccountUpgradeV2RequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            traceNo: '211045',
            dateTime: '20210105201527',
            terminalId: 'NOVA',
            fingerprints: [],
            latitude: '33.6844',
            longitude: '73.0479',
            udid: 'efc2b31481cd070a'
        );
    }

    public function test_validation_fails_for_incomplete_fingerprint(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Each fingerprint must include index and template');

        new AccountUpgradeV2RequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            traceNo: '211045',
            dateTime: '20210105201527',
            terminalId: 'NOVA',
            fingerprints: [['index' => '2']],
            latitude: '33.6844',
            longitude: '73.0479',
            udid: 'efc2b31481cd070a'
        );
    }

    private function validDto(): AccountUpgradeV2RequestDTO
    {
        return new AccountUpgradeV2RequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            traceNo: '211045',
            dateTime: '20210105201527',
            terminalId: 'NOVA',
            fingerprints: [
                ['index' => '2', 'template' => 'template-a'],
                ['index' => '3', 'template' => 'template-b'],
            ],
            latitude: '33.6844',
            longitude: '73.0479',
            udid: 'efc2b31481cd070a'
        );
    }
}
