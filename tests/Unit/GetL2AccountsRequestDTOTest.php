<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use InvalidArgumentException;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\GetL2AccountsRequestDTO;

class GetL2AccountsRequestDTOTest extends TestCase
{
    /**
     * Test successful DTO creation with required fields.
     */
    public function test_successful_dto_creation_with_required_fields(): void
    {
        $dto = new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '229830310784'
        );

        $this->assertEquals('2024111045235146', $dto->dateTime);
        $this->assertEquals('229830310784', $dto->rrn);
        $this->assertEquals('NOVA', $dto->channelId); // Default value
        $this->assertEquals('NOVA', $dto->terminalId); // Default value
    }

    /**
     * Test successful DTO creation with all fields.
     */
    public function test_successful_dto_creation_with_all_fields(): void
    {
        $dto = new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '229830310784',
            channelId: 'MOBILE',
            terminalId: 'TERM001',
            reserved1: 'R1',
            reserved2: 'R2',
            reserved3: 'R3',
            reserved4: 'R4',
            reserved5: 'R5',
            reserved6: 'R6',
            reserved7: 'R7',
            reserved8: 'R8',
            reserved9: 'R9',
            reserved10: 'R10'
        );

        $this->assertEquals('2024111045235146', $dto->dateTime);
        $this->assertEquals('229830310784', $dto->rrn);
        $this->assertEquals('MOBILE', $dto->channelId);
        $this->assertEquals('TERM001', $dto->terminalId);
        $this->assertEquals('R1', $dto->reserved1);
        $this->assertEquals('R10', $dto->reserved10);
    }

    /**
     * Test toArray method returns correct structure.
     */
    public function test_to_array_returns_correct_structure(): void
    {
        $dto = new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '229830310784',
            channelId: 'NOVA',
            terminalId: 'NOVA'
        );

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('level2AccountsReq', $array);
        
        $request = $array['level2AccountsReq'];
        $this->assertArrayHasKey('DateTime', $request);
        $this->assertArrayHasKey('Rrn', $request);
        $this->assertArrayHasKey('ChannelId', $request);
        $this->assertArrayHasKey('TerminalId', $request);
        $this->assertArrayHasKey('Reserved1', $request);
        $this->assertArrayHasKey('Reserved2', $request);
        $this->assertArrayHasKey('Reserved3', $request);
        $this->assertArrayHasKey('Reserved4', $request);
        $this->assertArrayHasKey('Reserved5', $request);
        $this->assertArrayHasKey('Reserved6', $request);
        $this->assertArrayHasKey('Reserved7', $request);
        $this->assertArrayHasKey('Reserved8', $request);
        $this->assertArrayHasKey('Reserved9', $request);
        $this->assertArrayHasKey('Reserved10', $request);

        $this->assertEquals('2024111045235146', $request['DateTime']);
        $this->assertEquals('229830310784', $request['Rrn']);
        $this->assertEquals('NOVA', $request['ChannelId']);
        $this->assertEquals('NOVA', $request['TerminalId']);
        $this->assertEquals('', $request['Reserved1']);
    }

    /**
     * Test validation fails for DateTime with incorrect length.
     */
    public function test_validation_fails_for_invalid_datetime_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DateTime must be exactly 16 characters');

        new GetL2AccountsRequestDTO(
            dateTime: '20241110452351',  // 14 characters - too short
            rrn: '229830310784'
        );
    }

    /**
     * Test validation fails for DateTime that is too long.
     */
    public function test_validation_fails_for_datetime_too_long(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DateTime must be exactly 16 characters');

        new GetL2AccountsRequestDTO(
            dateTime: '20241110452351461234',  // Too long
            rrn: '229830310784'
        );
    }

    /**
     * Test validation fails for empty RRN.
     */
    public function test_validation_fails_for_empty_rrn(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RRN cannot be empty');

        new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: ''
        );
    }

    /**
     * Test validation fails for RRN with incorrect length.
     */
    public function test_validation_fails_for_invalid_rrn_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RRN must be exactly 12 characters');

        new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '12345'  // Too short
        );
    }

    /**
     * Test validation fails for RRN that is too long.
     */
    public function test_validation_fails_for_rrn_too_long(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RRN must be exactly 12 characters');

        new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '1234567890123'  // 13 characters - too long
        );
    }

    /**
     * Test validation fails for empty ChannelId.
     */
    public function test_validation_fails_for_empty_channel_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ChannelId cannot be empty');

        new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '229830310784',
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

        new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '229830310784',
            channelId: 'NOVA',
            terminalId: ''
        );
    }

    /**
     * Test reserved fields default to empty strings.
     */
    public function test_reserved_fields_default_to_empty_strings(): void
    {
        $dto = new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '229830310784'
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
     * Test DTO creation with custom reserved fields.
     */
    public function test_dto_creation_with_custom_reserved_fields(): void
    {
        $dto = new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '229830310784',
            reserved1: 'Custom1',
            reserved5: 'Custom5',
            reserved10: 'Custom10'
        );

        $array = $dto->toArray();
        $request = $array['level2AccountsReq'];

        $this->assertEquals('Custom1', $request['Reserved1']);
        $this->assertEquals('', $request['Reserved2']);
        $this->assertEquals('Custom5', $request['Reserved5']);
        $this->assertEquals('Custom10', $request['Reserved10']);
    }

    /**
     * Test correct DateTime format matches API specification.
     */
    public function test_correct_datetime_format(): void
    {
        // Format: YYYYMMDDHHMMSSms (16 characters)
        $dto = new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '229830310784'
        );

        $this->assertEquals(16, strlen($dto->dateTime));
    }

    /**
     * Test correct RRN format matches API specification.
     */
    public function test_correct_rrn_format(): void
    {
        $dto = new GetL2AccountsRequestDTO(
            dateTime: '2024111045235146',
            rrn: '229830310784'
        );

        $this->assertEquals(12, strlen($dto->rrn));
    }
}
