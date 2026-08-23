<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountInfoRequestDTO;

class AccountInfoRequestDTOTest extends TestCase
{
    public function test_successful_dto_creation_with_required_fields(): void
    {
        $dto = new AccountInfoRequestDTO(
            mobileNumber: '03343118436',
            dateTime: '20250116174251',
            rrn: '12345678901234'
        );

        $this->assertEquals('03343118436', $dto->mobileNumber);
        $this->assertEquals('20250116174251', $dto->dateTime);
        $this->assertEquals('12345678901234', $dto->rrn);
        $this->assertEquals('Lending', $dto->channelId);
        $this->assertEquals('Lending', $dto->terminalId);
    }

    public function test_from_array_accepts_snake_case(): void
    {
        $dto = AccountInfoRequestDTO::fromArray([
            'mobile_number' => '03343118436',
            'date_time' => '20250116174251',
            'rrn' => '1234567890123456',
            'channel_id' => 'NOVA',
            'terminal_id' => 'NOVA',
        ]);

        $this->assertEquals('03343118436', $dto->mobileNumber);
        $this->assertEquals('NOVA', $dto->channelId);
        $this->assertEquals('1234567890123456', $dto->rrn);
    }

    public function test_to_array_uses_account_info_req_wrapper(): void
    {
        $dto = new AccountInfoRequestDTO(
            mobileNumber: '03343118436',
            dateTime: '20250116174251',
            rrn: '12345678901234',
            channelId: 'NOVA',
            terminalId: 'TERM1'
        );

        $array = $dto->toArray();

        $this->assertArrayHasKey('accountInfoReq', $array);
        $this->assertEquals('03343118436', $array['accountInfoReq']['MobileNumber']);
        $this->assertEquals('20250116174251', $array['accountInfoReq']['DateTime']);
        $this->assertEquals('12345678901234', $array['accountInfoReq']['Rrn']);
        $this->assertEquals('NOVA', $array['accountInfoReq']['ChannelId']);
        $this->assertEquals('TERM1', $array['accountInfoReq']['TerminalId']);
    }

    public function test_validation_fails_empty_mobile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('MobileNumber must be exactly 11 characters');

        new AccountInfoRequestDTO(
            mobileNumber: '',
            dateTime: '20250116174251',
            rrn: '12345678901234'
        );
    }

    public function test_validation_fails_invalid_datetime_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DateTime must be exactly 14 characters (YYYYMMDDHHmmss)');

        new AccountInfoRequestDTO(
            mobileNumber: '03343118436',
            dateTime: '20250116',
            rrn: '12345678901234'
        );
    }

    public function test_validation_fails_invalid_rrn_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Rrn must be 14 or 16 characters');

        new AccountInfoRequestDTO(
            mobileNumber: '03343118436',
            dateTime: '20250116174251',
            rrn: '12345'
        );
    }
}
