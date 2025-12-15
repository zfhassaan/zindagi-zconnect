<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use InvalidArgumentException;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\L2AccountUpgradeDiscrepantRequestDTO;

class L2AccountUpgradeDiscrepantRequestDTOTest extends TestCase
{
    /**
     * Test successful DTO creation with required fields.
     */
    public function test_successful_dto_creation_with_required_fields(): void
    {
        $dto = new L2AccountUpgradeDiscrepantRequestDTO(
            mobileNumber: '03200460403',
            dateTime: '20220729171717',
            rrn: '000000770011',
            cnic: '3660238069587',
            consumerName: 'HAMZA',
            fatherHusbandName: 'KHALID',
            purposeOfAccount: 'BUSINESS',
            sourceOfIncome: 'JOB',
            expectedMonthlyTurnover: '400000',
            birthPlace: 'LAHORE',
            motherMaiden: 'KIRA',
            emailAddress: 'aqeel.fazal@gmail.com',
            mailingAddress: 'ISLAMABAD',
            permanentAddress: 'ISLAMABAD',
            city: 'Islamabad',
            area: 'Gulberg Greens',
            houseNumber: '35/69'
        );

        $this->assertEquals('03200460403', $dto->mobileNumber);
        $this->assertEquals('20220729171717', $dto->dateTime);
        $this->assertEquals('000000770011', $dto->rrn);
        $this->assertEquals('3660238069587', $dto->cnic);
        $this->assertEquals('HAMZA', $dto->consumerName);
        $this->assertEquals('NOVA', $dto->channelId); // Default value
        $this->assertEquals('PKR', $dto->currencyCode); // Default value
    }

    /**
     * Test toArray method returns correct structure.
     */
    public function test_to_array_returns_correct_structure(): void
    {
        $dto = new L2AccountUpgradeDiscrepantRequestDTO(
            mobileNumber: '03200460403',
            dateTime: '20220729171717',
            rrn: '000000770011',
            cnic: '3660238069587',
            consumerName: 'HAMZA',
            fatherHusbandName: 'KHALID',
            purposeOfAccount: 'BUSINESS',
            sourceOfIncome: 'JOB',
            expectedMonthlyTurnover: '400000',
            birthPlace: 'LAHORE',
            motherMaiden: 'KIRA',
            emailAddress: 'test@example.com',
            mailingAddress: 'Address 1',
            permanentAddress: 'Address 2',
            city: 'Lahore',
            area: 'Area',
            houseNumber: '123'
        );

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('l2AccountUpgradeDiscrepantReq', $array);
        
        $request = $array['l2AccountUpgradeDiscrepantReq'];
        $this->assertArrayHasKey('MobileNumber', $request);
        $this->assertArrayHasKey('DateTime', $request);
        $this->assertArrayHasKey('Rrn', $request);
        $this->assertArrayHasKey('Cnic', $request);
        $this->assertArrayHasKey('ConsumerName', $request);
        $this->assertArrayHasKey('PurposeOfAccount', $request);
        $this->assertArrayHasKey('City', $request);
        $this->assertArrayHasKey('Area', $request);
        $this->assertArrayHasKey('HouseNumber', $request);

        $this->assertEquals('03200460403', $request['MobileNumber']);
        $this->assertEquals('HAMZA', $request['ConsumerName']);
    }

    /**
     * Test validation fails for invalid mobile number length.
     */
    public function test_validation_fails_for_invalid_mobile_number_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mobile number must be exactly 11 characters');

        new L2AccountUpgradeDiscrepantRequestDTO(
            mobileNumber: '0320046040',  // 10 characters - too short
            dateTime: '20220729171717',
            rrn: '000000770011',
            cnic: '3660238069587',
            consumerName: 'HAMZA',
            fatherHusbandName: 'KHALID',
            purposeOfAccount: 'BUSINESS',
            sourceOfIncome: 'JOB',
            expectedMonthlyTurnover: '400000',
            birthPlace: 'LAHORE',
            motherMaiden: 'KIRA',
            emailAddress: 'test@example.com',
            mailingAddress: 'Address',
            permanentAddress: 'Address',
            city: 'City',
            area: 'Area',
            houseNumber: '123'
        );
    }

    /**
     * Test validation fails for invalid CNIC length.
     */
    public function test_validation_fails_for_invalid_cnic_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CNIC must be exactly 13 characters');

        new L2AccountUpgradeDiscrepantRequestDTO(
            mobileNumber: '03200460403',
            dateTime: '20220729171717',
            rrn: '000000770011',
            cnic: '366023806958',  // 12 characters - too short
            consumerName: 'HAMZA',
            fatherHusbandName: 'KHALID',
            purposeOfAccount: 'BUSINESS',
            sourceOfIncome: 'JOB',
            expectedMonthlyTurnover: '400000',
            birthPlace: 'LAHORE',
            motherMaiden: 'KIRA',
            emailAddress: 'test@example.com',
            mailingAddress: 'Address',
            permanentAddress: 'Address',
            city: 'City',
            area: 'Area',
            houseNumber: '123'
        );
    }

    /**
     * Test validation fails for invalid DateTime length.
     */
    public function test_validation_fails_for_invalid_datetime_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DateTime must be exactly 14 characters');

        new L2AccountUpgradeDiscrepantRequestDTO(
            mobileNumber: '03200460403',
            dateTime: '2022072917171',  // 13 characters - too short
            rrn: '000000770011',
            cnic: '3660238069587',
            consumerName: 'HAMZA',
            fatherHusbandName: 'KHALID',
            purposeOfAccount: 'BUSINESS',
            sourceOfIncome: 'JOB',
            expectedMonthlyTurnover: '400000',
            birthPlace: 'LAHORE',
            motherMaiden: 'KIRA',
            emailAddress: 'test@example.com',
            mailingAddress: 'Address',
            permanentAddress: 'Address',
            city: 'City',
            area: 'Area',
            houseNumber: '123'
        );
    }

    /**
     * Test validation fails for invalid RRN length.
     */
    public function test_validation_fails_for_invalid_rrn_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RRN must be exactly 12 characters');

        new L2AccountUpgradeDiscrepantRequestDTO(
            mobileNumber: '03200460403',
            dateTime: '20220729171717',
            rrn: '00000077001',  // 11 characters - too short
            cnic: '3660238069587',
            consumerName: 'HAMZA',
            fatherHusbandName: 'KHALID',
            purposeOfAccount: 'BUSINESS',
            sourceOfIncome: 'JOB',
            expectedMonthlyTurnover: '400000',
            birthPlace: 'LAHORE',
            motherMaiden: 'KIRA',
            emailAddress: 'test@example.com',
            mailingAddress: 'Address',
            permanentAddress: 'Address',
            city: 'City',
            area: 'Area',
            houseNumber: '123'
        );
    }

    /**
     * Test validation fails for empty consumer name.
     */
    public function test_validation_fails_for_empty_consumer_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Consumer name cannot be empty');

        new L2AccountUpgradeDiscrepantRequestDTO(
            mobileNumber: '03200460403',
            dateTime: '20220729171717',
            rrn: '000000770011',
            cnic: '3660238069587',
            consumerName: '',
            fatherHusbandName: 'KHALID',
            purposeOfAccount: 'BUSINESS',
            sourceOfIncome: 'JOB',
            expectedMonthlyTurnover: '400000',
            birthPlace: 'LAHORE',
            motherMaiden: 'KIRA',
            emailAddress: 'test@example.com',
            mailingAddress: 'Address',
            permanentAddress: 'Address',
            city: 'City',
            area: 'Area',
            houseNumber: '123'
        );
    }

    /**
     * Test validation fails for invalid email.
     */
    public function test_validation_fails_for_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Valid email address is required');

        new L2AccountUpgradeDiscrepantRequestDTO(
            mobileNumber: '03200460403',
            dateTime: '20220729171717',
            rrn: '000000770011',
            cnic: '3660238069587',
            consumerName: 'HAMZA',
            fatherHusbandName: 'KHALID',
            purposeOfAccount: 'BUSINESS',
            sourceOfIncome: 'JOB',
            expectedMonthlyTurnover: '400000',
            birthPlace: 'LAHORE',
            motherMaiden: 'KIRA',
            emailAddress: 'invalid-email',  // Invalid email format
            mailingAddress: 'Address',
            permanentAddress: 'Address',
            city: 'City',
            area: 'Area',
            houseNumber: '123'
        );
    }

    /**
     * Test validation fails for invalid currency code.
     */
    public function test_validation_fails_for_invalid_currency_code(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Currency code must be PKR or USD');

        new L2AccountUpgradeDiscrepantRequestDTO(
            mobileNumber: '03200460403',
            dateTime: '20220729171717',
            rrn: '000000770011',
            cnic: '3660238069587',
            consumerName: 'HAMZA',
            fatherHusbandName: 'KHALID',
            purposeOfAccount: 'BUSINESS',
            sourceOfIncome: 'JOB',
            expectedMonthlyTurnover: '400000',
            birthPlace: 'LAHORE',
            motherMaiden: 'KIRA',
            emailAddress: 'test@example.com',
            mailingAddress: 'Address',
            permanentAddress: 'Address',
            city: 'City',
            area: 'Area',
            houseNumber: '123',
            currencyCode: 'EUR'  // Invalid currency
        );
    }

    /**
     * Test DTO creation with USD currency.
     */
    public function test_dto_creation_with_usd_currency(): void
    {
        $dto = new L2AccountUpgradeDiscrepantRequestDTO(
            mobileNumber: '03200460403',
            dateTime: '20220729171717',
            rrn: '000000770011',
            cnic: '3660238069587',
            consumerName: 'HAMZA',
            fatherHusbandName: 'KHALID',
            purposeOfAccount: 'BUSINESS',
            sourceOfIncome: 'JOB',
            expectedMonthlyTurnover: '5000',
            birthPlace: 'LAHORE',
            motherMaiden: 'KIRA',
            emailAddress: 'test@example.com',
            mailingAddress: 'Address',
            permanentAddress: 'Address',
            city: 'City',
            area: 'Area',
            houseNumber: '123',
            currencyCode: 'USD'
        );

        $this->assertEquals('USD', $dto->currencyCode);
    }

    /**
     * Test validation for US citizenship field.
     */
    public function test_validation_fails_for_invalid_us_citizenship(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('US Citizenship must be Yes or No');

        new L2AccountUpgradeDiscrepantRequestDTO(
            mobileNumber: '03200460403',
            dateTime: '20220729171717',
            rrn: '000000770011',
            cnic: '3660238069587',
            consumerName: 'HAMZA',
            fatherHusbandName: 'KHALID',
            purposeOfAccount: 'BUSINESS',
            sourceOfIncome: 'JOB',
            expectedMonthlyTurnover: '400000',
            birthPlace: 'LAHORE',
            motherMaiden: 'KIRA',
            emailAddress: 'test@example.com',
            mailingAddress: 'Address',
            permanentAddress: 'Address',
            city: 'City',
            area: 'Area',
            houseNumber: '123',
            usCitizenship: 'Maybe'  // Invalid value
        );
    }

    /**
     * Test DTO with all optional fields populated.
     */
    public function test_dto_with_all_optional_fields(): void
    {
        $dto = new L2AccountUpgradeDiscrepantRequestDTO(
            mobileNumber: '03200460403',
            dateTime: '20220729171717',
            rrn: '000000770011',
            cnic: '3660238069587',
            consumerName: 'HAMZA',
            fatherHusbandName: 'KHALID',
            purposeOfAccount: 'BUSINESS',
            sourceOfIncome: 'JOB',
            expectedMonthlyTurnover: '400000',
            birthPlace: 'LAHORE',
            motherMaiden: 'KIRA',
            emailAddress: 'test@example.com',
            mailingAddress: 'ISLAMABAD',
            permanentAddress: 'ISLAMABAD',
            city: 'Islamabad',
            area: 'Gulberg Greens',
            houseNumber: '35/69',
            cnicFrontPic: 'front_pic_data',
            cnicBackPic: 'back_pic_data',
            customerPic: 'customer_pic_data',
            sourceOfIncomePic: 'income_pic_data',
            signaturePic: 'signature_pic_data',
            usCitizenship: 'Yes',
            usMobileNumber: '91356987212354',
            signatoryAuthority: 'Yes',
            taxIdNumber: '3569874100',
            utilityBillPicture: 'utility_bill_data'
        );

        $array = $dto->toArray();
        $request = $array['l2AccountUpgradeDiscrepantReq'];

        $this->assertEquals('front_pic_data', $request['CnicFrontPic']);
        $this->assertEquals('Yes', $request['UsCitizenship']);
        $this->assertEquals('3569874100', $request['TaxIdNumber']);
    }
}
