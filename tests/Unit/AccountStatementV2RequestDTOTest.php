<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use InvalidArgumentException;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountStatementV2RequestDTO;

class AccountStatementV2RequestDTOTest extends TestCase
{
    /**
     * Test successful DTO creation with required fields.
     */
    public function test_successful_dto_creation_with_required_fields(): void
    {
        $dto = new AccountStatementV2RequestDTO(
            transmissionDatetime: '0116174253',
            systemsTraceAuditNumber: '396583',
            timeLocalTransaction: '054253',
            dateLocalTransaction: '20250116174251',
            accountNumber: '03343118436',
            fromDate: '12/16/2025',
            toDate: '01/16/2025'
        );
        
        $this->assertEquals('03343118436', $dto->accountNumber);
        $this->assertEquals('12/16/2025', $dto->fromDate);
    }
    
    /**
     * Test successful DTO to array.
     */
    public function test_dto_to_array(): void
    {
        $dto = new AccountStatementV2RequestDTO(
            transmissionDatetime: '0116174253',
            systemsTraceAuditNumber: '396583',
            timeLocalTransaction: '054253',
            dateLocalTransaction: '20250116174251',
            accountNumber: '03343118436',
            fromDate: '12/16/2025',
            toDate: '01/16/2025'
        );
        
        $array = $dto->toArray();
        $this->assertArrayHasKey('AccountStatementReq', $array);
        $req = $array['AccountStatementReq'];
        $this->assertEquals('0116174253', $req['TransmissionDatetime']);
        $this->assertEquals('03343118436', $req['AccountNumber']); // Casing check
        $this->assertEquals('396583', $req['SystemsTraceAuditNumber']);
        $this->assertEquals('12/16/2025', $req['FromDate']);
        $this->assertEquals('01/16/2025', $req['ToDate']);
    }

    public function test_from_array_accepts_snake_case(): void
    {
        $dto = AccountStatementV2RequestDTO::fromArray([
            'transmission_datetime' => '0116174253',
            'systems_trace_audit_number' => '396583',
            'time_local_transaction' => '054253',
            'date_local_transaction' => '20250116174251',
            'account_number' => '03343118436',
            'from_date' => '12/16/2025',
            'to_date' => '01/16/2025',
            'merchant_type' => '0088',
        ]);

        $this->assertEquals('03343118436', $dto->accountNumber);
        $this->assertEquals('0088', $dto->merchantType);
    }

    public function test_validation_fails_empty_from_date(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('From Date cannot be empty');

        new AccountStatementV2RequestDTO(
            transmissionDatetime: '0116174253',
            systemsTraceAuditNumber: '396583',
            timeLocalTransaction: '054253',
            dateLocalTransaction: '20250116174251',
            accountNumber: '03343118436',
            fromDate: '',
            toDate: '01/16/2025'
        );
    }

    /**
     * Test validation fails for empty account number.
     */
    public function test_validation_fails_empty_account_number(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Account Number cannot be empty');

        new AccountStatementV2RequestDTO(
            transmissionDatetime: '0116174253',
            systemsTraceAuditNumber: '396583',
            timeLocalTransaction: '054253',
            dateLocalTransaction: '20250116174251',
            accountNumber: '',
            fromDate: '12/16/2025',
            toDate: '01/16/2025'
        );
    }
}
