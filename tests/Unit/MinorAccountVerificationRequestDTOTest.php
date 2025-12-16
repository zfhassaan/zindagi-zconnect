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
