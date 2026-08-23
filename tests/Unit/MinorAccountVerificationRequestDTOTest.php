<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use InvalidArgumentException;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\MinorAccountVerificationRequestDTO;

class MinorAccountVerificationRequestDTOTest extends TestCase
{
    /**
     * Test successful DTO creation with required fields.
     */
    public function test_successful_dto_creation_with_required_fields(): void
    {
        $dto = new MinorAccountVerificationRequestDTO(
            rrn: '1255822445001',
            dateTime: '11172022',
            cnic: '3520243953533',
            issuanceDate: '2020-08-12',
            mobileNumber: '03200460403'
        );
        
        $this->assertEquals('1255822445001', $dto->rrn);
        $this->assertEquals('3520243953533', $dto->cnic);
        $this->assertEquals('NOVA', $dto->channelId); // Default
    }
    
    /**
     * Test successful DTO to array.
     */
    public function test_dto_to_array(): void
    {
        $dto = new MinorAccountVerificationRequestDTO(
            rrn: '1255822445001',
            dateTime: '11172022',
            cnic: '3520243953533',
            issuanceDate: '2020-08-12',
            mobileNumber: '03200460403'
        );
        
        $array = $dto->toArray();
        $this->assertArrayHasKey('minorAccountVerifyReq', $array);
        $req = $array['minorAccountVerifyReq'];
        $this->assertEquals('1255822445001', $req['RRN']);
        $this->assertEquals('3520243953533', $req['Cnic']);
        $this->assertEquals('2020-08-12', $req['IssuanceDate']);
        $this->assertEquals('NOVA', $req['ChannelId']);
    }

    public function test_from_array_accepts_snake_case(): void
    {
        $dto = MinorAccountVerificationRequestDTO::fromArray([
            'rrn' => '1255822445001',
            'date_time' => '11172022',
            'cnic' => '3520243953533',
            'issuance_date' => '2020-08-12',
            'mobile_number' => '03200460403',
            'channel_id' => 'LEND',
            'terminal_id' => 'TERM1',
        ]);

        $this->assertEquals('LEND', $dto->channelId);
        $this->assertEquals('TERM1', $dto->terminalId);
        $this->assertEquals('03200460403', $dto->mobileNumber);
    }

    public function test_validation_fails_empty_cnic(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CNIC cannot be empty');

        new MinorAccountVerificationRequestDTO(
            rrn: '1255822445001',
            dateTime: '11172022',
            cnic: '',
            issuanceDate: '2020-08-12',
            mobileNumber: '03200460403'
        );
    }

    public function test_validation_fails_empty_mobile_number(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mobile Number cannot be empty');

        new MinorAccountVerificationRequestDTO(
            rrn: '1255822445001',
            dateTime: '11172022',
            cnic: '3520243953533',
            issuanceDate: '2020-08-12',
            mobileNumber: ''
        );
    }

    /**
     * Test validation fails for empty RRN.
     */
    public function test_validation_fails_empty_rrn(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RRN cannot be empty');

        new MinorAccountVerificationRequestDTO(
            rrn: '',
            dateTime: '11172022',
            cnic: '3520243953533',
            issuanceDate: '2020-08-12',
            mobileNumber: '03200460403'
        );
    }
}
