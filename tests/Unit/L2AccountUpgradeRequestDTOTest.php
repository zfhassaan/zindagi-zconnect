<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\L2AccountUpgradeRequestDTO;

class L2AccountUpgradeRequestDTOTest extends TestCase
{
    public function test_successful_dto_creation_with_required_fields(): void
    {
        $dto = $this->validDto();

        $this->assertEquals('03001234567', $dto->mobileNumber);
        $this->assertEquals('1001', $dto->accountId);
        $this->assertEquals('1', $dto->fingerIndex);
        $this->assertEquals('NOVA', $dto->channelId);
    }

    public function test_to_array_uses_l2_account_upgrade_req_wrapper(): void
    {
        $array = $this->validDto(transactionId: 'TXN-99', termCondition: 'Y')->toArray();

        $this->assertArrayHasKey('l2AccountUpgradeReq', $array);
        $req = $array['l2AccountUpgradeReq'];
        $this->assertEquals('03001234567', $req['MobileNumber']);
        $this->assertEquals('1001', $req['AccountID']);
        $this->assertEquals('1234567890123', $req['Cnic']);
        $this->assertEquals('FINGERPRINT', $req['FingerTemplate']);
        $this->assertEquals('TXN-99', $req['TransactionId']);
        $this->assertEquals('Y', $req['TermCondition']);
    }

    public function test_from_array_accepts_snake_case(): void
    {
        $dto = L2AccountUpgradeRequestDTO::fromArray([
            'mobile_number' => '03001234567',
            'date_time' => '20250116174251',
            'rrn' => '123456789012',
            'account_id' => '1001',
            'cnic' => '1234567890123',
            'finger_template' => 'FINGERPRINT',
            'transaction_id' => 'TXN-1',
        ]);

        $this->assertEquals('1001', $dto->accountId);
        $this->assertEquals('TXN-1', $dto->transactionId);
    }

    public function test_validation_fails_invalid_mobile_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mobile number must be exactly 11 characters');

        $this->validDto(mobileNumber: '0300');
    }

    public function test_validation_fails_empty_account_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Account ID cannot be empty');

        $this->validDto(accountId: '');
    }

    public function test_validation_fails_empty_finger_template(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Finger template cannot be empty');

        $this->validDto(fingerTemplate: '');
    }

    public function test_validation_fails_invalid_cnic_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CNIC must be exactly 13 characters');

        $this->validDto(cnic: '12345');
    }

    private function validDto(
        string $mobileNumber = '03001234567',
        string $accountId = '1001',
        string $cnic = '1234567890123',
        string $fingerTemplate = 'FINGERPRINT',
        string $transactionId = '',
        string $termCondition = ''
    ): L2AccountUpgradeRequestDTO {
        return new L2AccountUpgradeRequestDTO(
            mobileNumber: $mobileNumber,
            dateTime: '20250116174251',
            rrn: '123456789012',
            accountId: $accountId,
            cnic: $cnic,
            fingerTemplate: $fingerTemplate,
            transactionId: $transactionId,
            termCondition: $termCondition
        );
    }
}
