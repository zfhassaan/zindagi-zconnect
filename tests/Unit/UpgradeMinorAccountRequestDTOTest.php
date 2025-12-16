<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use InvalidArgumentException;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\UpgradeMinorAccountRequestDTO;

class UpgradeMinorAccountRequestDTOTest extends TestCase
{
    /**
     * Test successful DTO creation with required fields.
     */
    public function test_successful_dto_creation_with_required_fields(): void
    {
        $dto = new UpgradeMinorAccountRequestDTO(
            rrn: '0090909998881',
            dateTime: '20232311191919',
            mobileNumber: '03200460403'
        );
        
        $this->assertEquals('0090909998881', $dto->rrn);
        $this->assertEquals('20232311191919', $dto->dateTime);
    }
    
    /**
     * Test successful DTO to array.
     */
    public function test_dto_to_array(): void
    {
        $dto = new UpgradeMinorAccountRequestDTO(
            rrn: '0090909998881',
            dateTime: '20232311191919',
            mobileNumber: '03200460403'
        );
        
        $array = $dto->toArray();
        $this->assertArrayHasKey('upgradeMinorAccountReq', $array);
        $req = $array['upgradeMinorAccountReq'];
        $this->assertEquals('0090909998881', $req['Rrn']); // Case sensitive check as per extracted fields
    }

    /**
     * Test validation fails for empty fields.
     */
    public function test_validation_fails_empty_rrn(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new UpgradeMinorAccountRequestDTO(
            rrn: '',
            dateTime: '20232311191919',
            mobileNumber: '03200460403'
        );
    }
}
