<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountOpeningRequestDTO;

class AccountOpeningRequestDTOTest extends TestCase
{
    /**
     * Test DTO creation with required fields.
     */
    public function test_dto_creation_with_required_fields(): void
    {
        $dto = new AccountOpeningRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            emailId: 'test@example.com',
            cnicIssuanceDate: '20151116',
            mobileNetwork: 'UFONE'
        );

        $this->assertEquals('1234567890123', $dto->cnic);
        $this->assertEquals('03001234567', $dto->mobileNo);
        $this->assertEquals('test@example.com', $dto->emailId);
        $this->assertEquals('20151116', $dto->cnicIssuanceDate);
        $this->assertEquals('UFONE', $dto->mobileNetwork);
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
            'email_id' => 'test@example.com',
            'cnic_issuance_date' => '20151116',
            'mobile_network' => 'UFONE',
            'merchant_type' => '0088',
            'trace_no' => '000011',
            'date_time' => '20220117115415',
            'company_name' => 'NOVA',
        ];

        $dto = AccountOpeningRequestDTO::fromArray($data);

        $this->assertEquals('1234567890123', $dto->cnic);
        $this->assertEquals('03001234567', $dto->mobileNo);
        $this->assertEquals('test@example.com', $dto->emailId);
        $this->assertEquals('20151116', $dto->cnicIssuanceDate);
        $this->assertEquals('UFONE', $dto->mobileNetwork);
        $this->assertEquals('0088', $dto->merchantType);
        $this->assertEquals('000011', $dto->traceNo);
        $this->assertEquals('20220117115415', $dto->dateTime);
        $this->assertEquals('NOVA', $dto->companyName);
    }

    /**
     * Test DTO creation from array with alternative keys.
     */
    public function test_dto_creation_from_array_with_alternative_keys(): void
    {
        $data = [
            'CNIC' => '1234567890123',
            'MobileNo' => '03001234567',
            'EmailId' => 'test@example.com',
            'CnicIssuanceDate' => '20151116',
            'MobileNetwork' => 'UFONE',
            'MerchantType' => '0088',
            'TraceNo' => '000011',
            'DateTime' => '20220117115415',
            'CompanyName' => 'NOVA',
        ];

        $dto = AccountOpeningRequestDTO::fromArray($data);

        $this->assertEquals('1234567890123', $dto->cnic);
        $this->assertEquals('03001234567', $dto->mobileNo);
        $this->assertEquals('test@example.com', $dto->emailId);
        $this->assertEquals('20151116', $dto->cnicIssuanceDate);
        $this->assertEquals('UFONE', $dto->mobileNetwork);
    }

    /**
     * Test default values from config.
     */
    public function test_default_values_from_config(): void
    {
        config([
            'zindagi-zconnect.modules.onboarding.account_opening' => [
                'merchant_type' => '0088',
                'company_name' => 'NOVA',
            ],
        ]);

        $dto = new AccountOpeningRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            emailId: 'test@example.com',
            cnicIssuanceDate: '20151116',
            mobileNetwork: 'UFONE'
        );

        $this->assertEquals('0088', $dto->merchantType);
        $this->assertEquals('NOVA', $dto->companyName);
    }

    /**
     * Test trace number auto generation.
     */
    public function test_trace_number_auto_generation(): void
    {
        $dto1 = new AccountOpeningRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            emailId: 'test@example.com',
            cnicIssuanceDate: '20151116',
            mobileNetwork: 'UFONE'
        );

        usleep(1000); // Small delay to ensure different trace numbers

        $dto2 = new AccountOpeningRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            emailId: 'test@example.com',
            cnicIssuanceDate: '20151116',
            mobileNetwork: 'UFONE'
        );

        $this->assertNotNull($dto1->traceNo);
        $this->assertNotNull($dto2->traceNo);
        $this->assertEquals(6, strlen($dto1->traceNo));
        $this->assertEquals(6, strlen($dto2->traceNo));
    }

    /**
     * Test date time auto generation.
     */
    public function test_date_time_auto_generation(): void
    {
        $dto = new AccountOpeningRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            emailId: 'test@example.com',
            cnicIssuanceDate: '20151116',
            mobileNetwork: 'UFONE'
        );

        $this->assertNotNull($dto->dateTime);
        $this->assertEquals(14, strlen($dto->dateTime));
        $this->assertMatchesRegularExpression('/^\d{14}$/', $dto->dateTime);
    }

    /**
     * Test API request format conversion.
     */
    public function test_api_request_format_conversion(): void
    {
        $dto = new AccountOpeningRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            emailId: 'test@example.com',
            cnicIssuanceDate: '20151116',
            mobileNetwork: 'UFONE',
            merchantType: '0088',
            traceNo: '000011',
            dateTime: '20220117115415',
            companyName: 'NOVA'
        );

        $apiRequest = $dto->toApiRequest();

        $this->assertArrayHasKey('AccountOpeningRequest', $apiRequest);
        $this->assertEquals('0088', $apiRequest['AccountOpeningRequest']['MerchantType']);
        $this->assertEquals('000011', $apiRequest['AccountOpeningRequest']['TraceNo']);
        $this->assertEquals('1234567890123', $apiRequest['AccountOpeningRequest']['CNIC']);
        $this->assertEquals('03001234567', $apiRequest['AccountOpeningRequest']['MobileNo']);
        $this->assertEquals('NOVA', $apiRequest['AccountOpeningRequest']['CompanyName']);
        $this->assertEquals('20220117115415', $apiRequest['AccountOpeningRequest']['DateTime']);
        $this->assertEquals('20151116', $apiRequest['AccountOpeningRequest']['CnicIssuanceDate']);
        $this->assertEquals('UFONE', $apiRequest['AccountOpeningRequest']['MobileNetwork']);
        $this->assertEquals('test@example.com', $apiRequest['AccountOpeningRequest']['EmailId']);
    }

    /**
     * Test array format conversion.
     */
    public function test_array_format_conversion(): void
    {
        $dto = new AccountOpeningRequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            emailId: 'test@example.com',
            cnicIssuanceDate: '20151116',
            mobileNetwork: 'UFONE',
            merchantType: '0088',
            traceNo: '000011',
            dateTime: '20220117115415',
            companyName: 'NOVA'
        );

        $array = $dto->toArray();

        $this->assertEquals('1234567890123', $array['cnic']);
        $this->assertEquals('03001234567', $array['mobile_no']);
        $this->assertEquals('test@example.com', $array['email_id']);
        $this->assertEquals('20151116', $array['cnic_issuance_date']);
        $this->assertEquals('UFONE', $array['mobile_network']);
        $this->assertEquals('0088', $array['merchant_type']);
        $this->assertEquals('000011', $array['trace_no']);
        $this->assertEquals('20220117115415', $array['date_time']);
        $this->assertEquals('NOVA', $array['company_name']);
    }

    /**
     * Test empty field handling.
     */
    public function test_empty_field_handling(): void
    {
        $data = [
            'cnic' => '',
            'mobile_no' => '',
            'email_id' => '',
            'cnic_issuance_date' => '',
            'mobile_network' => '',
        ];

        $dto = AccountOpeningRequestDTO::fromArray($data);

        $this->assertEquals('', $dto->cnic);
        $this->assertEquals('', $dto->mobileNo);
        $this->assertEquals('', $dto->emailId);
        $this->assertEquals('', $dto->cnicIssuanceDate);
        $this->assertEquals('', $dto->mobileNetwork);
    }
}

