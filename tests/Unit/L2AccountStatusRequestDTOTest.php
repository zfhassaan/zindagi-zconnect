<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use InvalidArgumentException;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\L2AccountStatusRequestDTO;

class L2AccountStatusRequestDTOTest extends TestCase
{
    /**
     * Test successful DTO creation with required fields.
     */
    public function test_successful_dto_creation_with_required_fields(): void
    {
        $dto = new L2AccountStatusRequestDTO(
            dateTime: '213212132132',
            rrn: '2161004065014056',
            mobileNo: '03313812399'
        );

        $this->assertEquals('213212132132', $dto->dateTime);
        $this->assertEquals('2161004065014056', $dto->rrn);
        $this->assertEquals('03313812399', $dto->mobileNo);
        $this->assertEquals('NOVA', $dto->channelId); // Default value
        $this->assertEquals('NOVA', $dto->terminalId); // Default value
    }

    /**
     * Test toArray method returns correct structure.
     */
    public function test_to_array_returns_correct_structure(): void
    {
        $dto = new L2AccountStatusRequestDTO(
            dateTime: '213212132132',
            rrn: '2161004065014056',
            mobileNo: '03313812399',
            channelId: 'MOBILE',
            terminalId: 'TERM001'
        );

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('l2AccountStatusReq', $array);
        
        $request = $array['l2AccountStatusReq'];
        $this->assertArrayHasKey('DateTime', $request);
        $this->assertArrayHasKey('Rrn', $request);
        $this->assertArrayHasKey('MobileNo', $request);
        $this->assertArrayHasKey('ChannelId', $request);
        $this->assertArrayHasKey('TerminalId', $request);
        $this->assertArrayHasKey('Reserved1', $request);
        $this->assertArrayHasKey('Reserved10', $request);

        $this->assertEquals('213212132132', $request['DateTime']);
        $this->assertEquals('2161004065014056', $request['Rrn']);
        $this->assertEquals('03313812399', $request['MobileNo']);
        $this->assertEquals('MOBILE', $request['ChannelId']);
        $this->assertEquals('TERM001', $request['TerminalId']);
    }

    /**
     * Test validation fails for invalid DateTime length.
     */
    public function test_validation_fails_for_invalid_datetime_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DateTime must be exactly 12 characters');

        new L2AccountStatusRequestDTO(
            dateTime: '21321213213',  // 11 characters - too short
            rrn: '2161004065014056',
            mobileNo: '03313812399'
        );
    }

    /**
     * Test validation fails for DateTime too long.
     */
    public function test_validation_fails_for_datetime_too_long(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DateTime must be exactly 12 characters');

        new L2AccountStatusRequestDTO(
            dateTime: '2132121321321',  // 13 characters - too long
            rrn: '2161004065014056',
            mobileNo: '03313812399'
        );
    }

    /**
     * Test validation fails for invalid RRN length.
     */
    public function test_validation_fails_for_invalid_rrn_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RRN must be exactly 16 characters');

        new L2AccountStatusRequestDTO(
            dateTime: '213212132132',
            rrn: '216100406501405',  // 15 characters - too short
            mobileNo: '03313812399'
        );
    }

    /**
     * Test validation fails for RRN too long.
     */
    public function test_validation_fails_for_rrn_too_long(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RRN must be exactly 16 characters');

        new L2AccountStatusRequestDTO(
            dateTime: '213212132132',
            rrn: '21610040650140561',  // 17 characters - too long
            mobileNo: '03313812399'
        );
    }

    /**
     * Test validation fails for invalid mobile number length.
     */
    public function test_validation_fails_for_invalid_mobile_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mobile number must be exactly 11 characters');

        new L2AccountStatusRequestDTO(
            dateTime: '213212132132',
            rrn: '2161004065014056',
            mobileNo: '0331381239'  // 10 characters - too short
        );
    }

    /**
     * Test validation fails for mobile number too long.
     */
    public function test_validation_fails_for_mobile_too_long(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mobile number must be exactly 11 characters');

        new L2AccountStatusRequestDTO(
            dateTime: '213212132132',
            rrn: '2161004065014056',
            mobileNo: '033138123999'  // 12 characters - too long
        );
    }

    /**
     * Test validation fails for empty ChannelId.
     */
    public function test_validation_fails_for_empty_channel_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ChannelId cannot be empty');

        new L2AccountStatusRequestDTO(
            dateTime: '213212132132',
            rrn: '2161004065014056',
            mobileNo: '03313812399',
            channelId: ''
        );
    }

    /**
     * Test validation fails for empty TerminalId.
     */
    public function test_validation_fails_for_empty_terminal_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('TerminalId cannot be empty');

        new L2AccountStatusRequestDTO(
            dateTime: '213212132132',
            rrn: '2161004065014056',
            mobileNo: '03313812399',
            channelId: 'NOVA',
            terminalId: ''
        );
    }

    /**
     * Test reserved fields default to empty strings.
     */
    public function test_reserved_fields_default_to_empty_strings(): void
    {
        $dto = new L2AccountStatusRequestDTO(
            dateTime: '213212132132',
            rrn: '2161004065014056',
            mobileNo: '03313812399'
        );

        $this->assertEquals('', $dto->reserved1);
        $this->assertEquals('', $dto->reserved2);
        $this->assertEquals('', $dto->reserved3);
        $this->assertEquals('', $dto->reserved4);
        $this->assertEquals('', $dto->reserved5);
        $this->assertEquals('', $dto->reserved6);
        $this->assertEquals('', $dto->reserved7);
        $this->assertEquals('', $dto->reserved8);
        $this->assertEquals('', $dto->reserved9);
        $this->assertEquals('', $dto->reserved10);
    }

    /**
     * Test DTO with custom reserved fields.
     */
    public function test_dto_with_custom_reserved_fields(): void
    {
        $dto = new L2AccountStatusRequestDTO(
            dateTime: '213212132132',
            rrn: '2161004065014056',
            mobileNo: '03313812399',
            reserved1: 'R1',
            reserved5: 'R5',
            reserved10: 'R10'
        );

        $array = $dto->toArray();
        $request = $array['l2AccountStatusReq'];

        $this->assertEquals('R1', $request['Reserved1']);
        $this->assertEquals('', $request['Reserved2']);
        $this->assertEquals('R5', $request['Reserved5']);
        $this->assertEquals('R10', $request['Reserved10']);
    }

    /**
     * Test DTO with all custom fields.
     */
    public function test_dto_with_all_custom_fields(): void
    {
        $dto = new L2AccountStatusRequestDTO(
            dateTime: '213212132132',
            rrn: '2161004065014056',
            mobileNo: '03313812399',
            channelId: 'WEB',
            terminalId: 'PORTAL',
            reserved1: 'A',
            reserved2: 'B',
            reserved3: 'C',
            reserved4: 'D',
            reserved5: 'E',
            reserved6: 'F',
            reserved7: 'G',
            reserved8: 'H',
            reserved9: 'I',
            reserved10: 'J'
        );

        $this->assertEquals('WEB', $dto->channelId);
        $this->assertEquals('PORTAL', $dto->terminalId);
        $this->assertEquals('A', $dto->reserved1);
        $this->assertEquals('J', $dto->reserved10);
    }
}
