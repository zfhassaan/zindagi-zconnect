<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Helpers\ValidationHelper;

class ValidationHelperTest extends TestCase
{
    /**
     * Test CNIC validation.
     */
    public function test_validate_cnic(): void
    {
        $this->assertTrue(ValidationHelper::validateCnic('1234567890123'));
        $this->assertTrue(ValidationHelper::validateCnic('12345-6789012-3'));
        $this->assertFalse(ValidationHelper::validateCnic('123456789012'));
        $this->assertFalse(ValidationHelper::validateCnic('invalid'));
    }

    /**
     * Test mobile number validation.
     */
    public function test_validate_mobile_number(): void
    {
        $this->assertTrue(ValidationHelper::validateMobileNumber('03001234567'));
        $this->assertTrue(ValidationHelper::validateMobileNumber('0300-1234567'));
        $this->assertFalse(ValidationHelper::validateMobileNumber('0300123456'));
        $this->assertFalse(ValidationHelper::validateMobileNumber('1234567890'));
    }

    /**
     * Test CNIC formatting.
     */
    public function test_format_cnic(): void
    {
        $formatted = ValidationHelper::formatCnic('1234567890123');
        $this->assertEquals('12345-6789012-3', $formatted);
    }

    /**
     * Test mobile number formatting.
     */
    public function test_format_mobile_number(): void
    {
        $formatted = ValidationHelper::formatMobileNumber('03001234567');
        $this->assertEquals('+923001234567', $formatted);
    }
}

