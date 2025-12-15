<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationRequestDTO;
use Carbon\Carbon;

class AccountVerificationRequestDTOTest extends TestCase
{
    /**
     * Test DTO creation with all required fields.
     */
    public function test_dto_creation_with_required_fields(): void
    {
        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567'
        );

        $this->assertEquals('1234567890123', $dto->cnic);
        $this->assertEquals('03001234567', $dto->mobileNo);
        $this->assertNotNull($dto->merchantType);
        $this->assertNotNull($dto->traceNo);
        $this->assertNotNull($dto->dateTime);
        $this->assertNotNull($dto->companyName);
    }

    /**
     * Test DTO creation from array.
     */
    public function test_dto_creation_from_array(): void
    {
        $data = [
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
        ];

        $dto = AccountVerificationRequestDTO::fromArray($data);

        $this->assertEquals('1234567890123', $dto->cnic);
        $this->assertEquals('03001234567', $dto->mobileNo);
    }

    /**
     * Test DTO creation from array with alternative keys.
     */
    public function test_dto_creation_from_array_with_alternative_keys(): void
    {
        $data = [
            'CNIC' => '1234567890123',
            'MobileNo' => '03001234567',
            'MerchantType' => '0088',
            'TraceNo' => '000009',
        ];

        $dto = AccountVerificationRequestDTO::fromArray($data);

        $this->assertEquals('1234567890123', $dto->cnic);
        $this->assertEquals('03001234567', $dto->mobileNo);
        $this->assertEquals('0088', $dto->merchantType);
        $this->assertEquals('000009', $dto->traceNo);
    }

    /**
     * Test DTO creation with mobile_number key.
     */
    public function test_dto_creation_with_mobile_number_key(): void
    {
        $data = [
            'cnic' => '1234567890123',
            'mobile_number' => '03001234567',
        ];

        $dto = AccountVerificationRequestDTO::fromArray($data);

        $this->assertEquals('03001234567', $dto->mobileNo);
    }

    /**
     * Test DTO defaults are set from config.
     */
    public function test_dto_defaults_from_config(): void
    {
        config([
            'zindagi-zconnect.modules.onboarding.account_verification' => [
                'merchant_type' => '9999',
                'company_name' => 'TEST',
                'transaction_type' => '99',
            ],
        ]);

        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567'
        );

        $this->assertEquals('9999', $dto->merchantType);
        $this->assertEquals('TEST', $dto->companyName);
        $this->assertEquals('99', $dto->transactionType);
    }

    /**
     * Test trace number auto-generation.
     */
    public function test_trace_number_auto_generation(): void
    {
        $dto1 = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567'
        );

        $dto2 = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567'
        );

        // Trace numbers should be different
        $this->assertNotEquals($dto1->traceNo, $dto2->traceNo);
        // Trace numbers should be 6 digits
        $this->assertEquals(6, strlen($dto1->traceNo));
        $this->assertEquals(6, strlen($dto2->traceNo));
    }

    /**
     * Test date time auto-generation.
     */
    public function test_date_time_auto_generation(): void
    {
        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567'
        );

        // DateTime should be 14 characters (YYYYMMDDHHmmss)
        $this->assertEquals(14, strlen($dto->dateTime));
        // Should be valid date format
        $this->assertTrue((bool) preg_match('/^\d{14}$/', $dto->dateTime));
    }

    /**
     * Test toApiRequest format.
     */
    public function test_to_api_request_format(): void
    {
        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            merchantType: '0088',
            traceNo: '000009',
            dateTime: '20210105201527',
            companyName: 'NOVA',
            reserved1: '01',
            reserved2: '01',
            transactionType: '02'
        );

        $apiRequest = $dto->toApiRequest();

        $this->assertArrayHasKey('VerifyAccLinkAccRequest', $apiRequest);
        $this->assertEquals('0088', $apiRequest['VerifyAccLinkAccRequest']['MerchantType']);
        $this->assertEquals('000009', $apiRequest['VerifyAccLinkAccRequest']['TraceNo']);
        $this->assertEquals('1234567890123', $apiRequest['VerifyAccLinkAccRequest']['CNIC']);
        $this->assertEquals('03001234567', $apiRequest['VerifyAccLinkAccRequest']['MobileNo']);
        $this->assertEquals('20210105201527', $apiRequest['VerifyAccLinkAccRequest']['DateTime']);
        $this->assertEquals('NOVA', $apiRequest['VerifyAccLinkAccRequest']['CompanyName']);
        $this->assertEquals('01', $apiRequest['VerifyAccLinkAccRequest']['Reserved1']);
        $this->assertEquals('01', $apiRequest['VerifyAccLinkAccRequest']['Reserved2']);
        $this->assertEquals('02', $apiRequest['VerifyAccLinkAccRequest']['TransactionType']);
    }

    /**
     * Test toArray format.
     */
    public function test_to_array_format(): void
    {
        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            merchantType: '0088',
            traceNo: '000009',
            dateTime: '20210105201527',
            companyName: 'NOVA',
            reserved1: '01',
            reserved2: '01',
            transactionType: '02'
        );

        $array = $dto->toArray();

        $this->assertEquals('1234567890123', $array['cnic']);
        $this->assertEquals('03001234567', $array['mobile_no']);
        $this->assertEquals('0088', $array['merchant_type']);
        $this->assertEquals('000009', $array['trace_no']);
        $this->assertEquals('20210105201527', $array['date_time']);
        $this->assertEquals('NOVA', $array['company_name']);
        $this->assertEquals('01', $array['reserved1']);
        $this->assertEquals('01', $array['reserved2']);
        $this->assertEquals('02', $array['transaction_type']);
    }

    /**
     * Test empty CNIC handling.
     */
    public function test_empty_cnic_handling(): void
    {
        $data = [
            'cnic' => '',
            'mobile_no' => '03001234567',
        ];

        $dto = AccountVerificationRequestDTO::fromArray($data);

        $this->assertEquals('', $dto->cnic);
    }

    /**
     * Test empty mobile number handling.
     */
    public function test_empty_mobile_number_handling(): void
    {
        $data = [
            'cnic' => '1234567890123',
            'mobile_no' => '',
        ];

        $dto = AccountVerificationRequestDTO::fromArray($data);

        $this->assertEquals('', $dto->mobileNo);
    }

    /**
     * Test reserved fields default values.
     */
    public function test_reserved_fields_defaults(): void
    {
        $dto = new AccountVerificationRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567'
        );

        $this->assertEquals('01', $dto->reserved1);
        $this->assertEquals('01', $dto->reserved2);
    }
}

