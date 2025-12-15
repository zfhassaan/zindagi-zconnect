<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingRequestDTO;

class AccountLinkingRequestDTOTest extends TestCase
{
    /**
     * Test DTO creation with required fields.
     */
    public function test_dto_creation_with_required_fields(): void
    {
        $dto = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567'
        );

        $this->assertEquals('1234567890123', $dto->cnic);
        $this->assertEquals('03001234567', $dto->mobileNo);
        $this->assertNotNull($dto->merchantType);
        $this->assertNotNull($dto->traceNo);
        $this->assertNotNull($dto->dateTime);
        $this->assertNotNull($dto->companyName);
        $this->assertNotNull($dto->transactionType);
        $this->assertNotNull($dto->reserved1);
    }

    /**
     * Test DTO creation from array.
     */
    public function test_dto_creation_from_array(): void
    {
        $data = [
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'trace_no' => '000001',
            'date_time' => '20210105201527',
            'company_name' => 'NOVA',
            'transaction_type' => '01',
            'reserved1' => '02',
            'otp_pin' => '123456',
        ];

        $dto = AccountLinkingRequestDTO::fromArray($data);

        $this->assertEquals('1234567890123', $dto->cnic);
        $this->assertEquals('03001234567', $dto->mobileNo);
        $this->assertEquals('0088', $dto->merchantType);
        $this->assertEquals('000001', $dto->traceNo);
        $this->assertEquals('20210105201527', $dto->dateTime);
        $this->assertEquals('NOVA', $dto->companyName);
        $this->assertEquals('01', $dto->transactionType);
        $this->assertEquals('02', $dto->reserved1);
        $this->assertEquals('123456', $dto->otpPin);
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
            'TraceNo' => '000001',
            'DateTime' => '20210105201527',
            'CompanyName' => 'NOVA',
            'TransactionType' => '01',
            'Reserved1' => '02',
            'OtpPin' => '123456',
        ];

        $dto = AccountLinkingRequestDTO::fromArray($data);

        $this->assertEquals('1234567890123', $dto->cnic);
        $this->assertEquals('03001234567', $dto->mobileNo);
        $this->assertEquals('0088', $dto->merchantType);
        $this->assertEquals('000001', $dto->traceNo);
    }

    /**
     * Test default values from config.
     */
    public function test_default_values_from_config(): void
    {
        config([
            'zindagi-zconnect.modules.onboarding.account_linking' => [
                'merchant_type' => '9999',
                'company_name' => 'TEST',
                'transaction_type' => '99',
                'reserved1' => '99',
            ],
        ]);

        $dto = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567'
        );

        $this->assertEquals('9999', $dto->merchantType);
        $this->assertEquals('TEST', $dto->companyName);
        $this->assertEquals('99', $dto->transactionType);
        $this->assertEquals('99', $dto->reserved1);
    }

    /**
     * Test trace number auto-generation.
     */
    public function test_trace_number_auto_generation(): void
    {
        $dto1 = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567'
        );

        $dto2 = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567'
        );

        $this->assertNotEquals($dto1->traceNo, $dto2->traceNo);
        $this->assertEquals(6, strlen($dto1->traceNo));
        $this->assertEquals(6, strlen($dto2->traceNo));
    }

    /**
     * Test date time auto-generation.
     */
    public function test_date_time_auto_generation(): void
    {
        $dto = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567'
        );

        $this->assertEquals(14, strlen($dto->dateTime));
        $this->assertMatchesRegularExpression('/^\d{14}$/', $dto->dateTime);
    }

    /**
     * Test API request format conversion.
     */
    public function test_api_request_format_conversion(): void
    {
        $dto = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            merchantType: '0088',
            traceNo: '000001',
            dateTime: '20210105201527',
            companyName: 'NOVA',
            transactionType: '01',
            reserved1: '02',
            otpPin: '123456'
        );

        $apiRequest = $dto->toApiRequest();

        $this->assertArrayHasKey('LinkAccountRequest', $apiRequest);
        $this->assertEquals('0088', $apiRequest['LinkAccountRequest']['MerchantType']);
        $this->assertEquals('000001', $apiRequest['LinkAccountRequest']['TraceNo']);
        $this->assertEquals('NOVA', $apiRequest['LinkAccountRequest']['CompanyName']);
        $this->assertEquals('20210105201527', $apiRequest['LinkAccountRequest']['DateTime']);
        $this->assertEquals('01', $apiRequest['LinkAccountRequest']['TransactionType']);
        $this->assertEquals('03001234567', $apiRequest['LinkAccountRequest']['MobileNo']);
        $this->assertEquals('1234567890123', $apiRequest['LinkAccountRequest']['Cnic']);
        $this->assertEquals('02', $apiRequest['LinkAccountRequest']['Reserved1']);
        $this->assertEquals('123456', $apiRequest['LinkAccountRequest']['OtpPin']);
    }

    /**
     * Test API request format without OtpPin.
     */
    public function test_api_request_format_without_otp_pin(): void
    {
        $dto = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            otpPin: null
        );

        $apiRequest = $dto->toApiRequest();

        $this->assertArrayNotHasKey('OtpPin', $apiRequest['LinkAccountRequest']);
    }

    /**
     * Test API request format with empty OtpPin.
     */
    public function test_api_request_format_with_empty_otp_pin(): void
    {
        $dto = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            otpPin: ''
        );

        $apiRequest = $dto->toApiRequest();

        $this->assertArrayNotHasKey('OtpPin', $apiRequest['LinkAccountRequest']);
    }

    /**
     * Test array format conversion.
     */
    public function test_array_format_conversion(): void
    {
        $dto = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            merchantType: '0088',
            traceNo: '000001',
            dateTime: '20210105201527',
            companyName: 'NOVA',
            transactionType: '01',
            reserved1: '02',
            otpPin: '123456'
        );

        $array = $dto->toArray();

        $this->assertEquals('1234567890123', $array['cnic']);
        $this->assertEquals('03001234567', $array['mobile_no']);
        $this->assertEquals('0088', $array['merchant_type']);
        $this->assertEquals('000001', $array['trace_no']);
        $this->assertEquals('20210105201527', $array['date_time']);
        $this->assertEquals('NOVA', $array['company_name']);
        $this->assertEquals('01', $array['transaction_type']);
        $this->assertEquals('02', $array['reserved1']);
        $this->assertEquals('123456', $array['otp_pin']);
    }

    /**
     * Test empty field handling.
     */
    public function test_empty_field_handling(): void
    {
        $dto = AccountLinkingRequestDTO::fromArray([
            'cnic' => '',
            'mobile_no' => '',
        ]);

        $this->assertEquals('', $dto->cnic);
        $this->assertEquals('', $dto->mobileNo);
    }

    /**
     * Test reserved1 default value.
     */
    public function test_reserved1_default_value(): void
    {
        $dto = new AccountLinkingRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567'
        );

        $this->assertEquals('02', $dto->reserved1);
    }
}

