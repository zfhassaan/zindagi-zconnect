<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use InvalidArgumentException;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\MinorAccountOpeningRequestDTO;

class MinorAccountOpeningRequestDTOTest extends TestCase
{
    /**
     * Test successful DTO creation with required fields.
     */
    public function test_successful_dto_creation_with_required_fields(): void
    {
        $dto = new MinorAccountOpeningRequestDTO(
            rrn: '1255822445001',
            dateTime: '11172022',
            accountTitle: 'Ahsan',
            cnic: '3520243953533',
            issuanceDate: '2020-08-12',
            mobileNumber: '03200460403',
            motherMaidenName: 'Nusrat',
            fatherName: 'Javed',
            placeOfBirth: 'Lahore',
            dateOfBirth: '1994-09-30',
            address: 'Gulberg 3 lahore',
            nicExpiry: '2025-03-30',
            parentCnicPic: '',
            snicPic: '',
            minorCustomerPic: '',
            fatherMotherMobileNumber: '03734642041',
            fatherCnic: '3570730079593',
            fatherCnicIssuanceDate: '2020-08-25',
            fatherCnicExpiryDate: '2025-03-30',
            motherCnic: '3520130109590',
            email: 'test@example.com'
        );
        
        $this->assertEquals('1255822445001', $dto->rrn);
        $this->assertEquals('Ahsan', $dto->accountTitle);
    }
    
    /**
     * Test successful DTO to array.
     */
    public function test_dto_to_array(): void
    {
        $dto = new MinorAccountOpeningRequestDTO(
            rrn: '1255822445001',
            dateTime: '11172022',
            accountTitle: 'Ahsan',
            cnic: '3520243953533',
            issuanceDate: '2020-08-12',
            mobileNumber: '03200460403',
            motherMaidenName: 'Nusrat',
            fatherName: 'Javed',
            placeOfBirth: 'Lahore',
            dateOfBirth: '1994-09-30',
            address: 'Gulberg 3 lahore',
            nicExpiry: '2025-03-30',
            parentCnicPic: '',
            snicPic: '',
            minorCustomerPic: '',
            fatherMotherMobileNumber: '03734642041',
            fatherCnic: '3570730079593',
            fatherCnicIssuanceDate: '2020-08-25',
            fatherCnicExpiryDate: '2025-03-30',
            motherCnic: '3520130109590',
            email: 'test@example.com'
        );
        
        $array = $dto->toArray();
        $this->assertArrayHasKey('minorAccountOpeningReq', $array);
        $req = $array['minorAccountOpeningReq'];
        $this->assertEquals('1255822445001', $req['RRN']);
        $this->assertEquals('Ahsan', $req['AccountTilte']); // Keeping typo as per DTO/Docs
        $this->assertEquals('Nusrat', $req['MotherMedianName']);
    }

    /**
     * Test validation fails for empty RRN.
     */
    public function test_validation_fails_empty_rrn(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RRN cannot be empty');

        new MinorAccountOpeningRequestDTO(
            rrn: '',
            dateTime: '11172022',
            accountTitle: 'Ahsan',
            cnic: '3520243953533',
            issuanceDate: '2020-08-12',
            mobileNumber: '03200460403',
            motherMaidenName: 'Nusrat',
            fatherName: 'Javed',
            placeOfBirth: 'Lahore',
            dateOfBirth: '1994-09-30',
            address: 'Gulberg 3 lahore',
            nicExpiry: '2025-03-30',
            parentCnicPic: '',
            snicPic: '',
            minorCustomerPic: '',
            fatherMotherMobileNumber: '03734642041',
            fatherCnic: '3570730079593',
            fatherCnicIssuanceDate: '2020-08-25',
            fatherCnicExpiryDate: '2025-03-30',
            motherCnic: '3520130109590',
            email: 'test@example.com'
        );
    }
}
