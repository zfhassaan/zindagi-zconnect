<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentOtpVerificationRequestDTO;

class AgentOtpVerificationRequestDTOTest extends TestCase
{
    public function test_successful_dto_creation(): void
    {
        $dto = $this->validDto();

        $this->assertEquals(5, $dto->dtid);
        $this->assertEquals('PLVIaH17OCzFw94Ze1wpZg==', $dto->pin);
        $this->assertEquals(1, $dto->encryptionType);
    }

    public function test_to_array_uses_agent_otp_verification_req_wrapper(): void
    {
        $array = $this->validDto()->toArray();

        $this->assertArrayHasKey('agentOtpVerificationReq', $array);
        $this->assertEquals(5, $array['agentOtpVerificationReq']['DTID']);
        $this->assertEquals('PLVIaH17OCzFw94Ze1wpZg==', $array['agentOtpVerificationReq']['PIN']);
        $this->assertEquals('new-pin-value', $array['agentOtpVerificationReq']['NPIN']);
        $this->assertEquals('new-pin-value', $array['agentOtpVerificationReq']['CPIN']);
        $this->assertEquals(1, $array['agentOtpVerificationReq']['ENCT']);
    }

    public function test_from_array_supports_snake_case_and_api_keys(): void
    {
        $dto = AgentOtpVerificationRequestDTO::fromArray([
            'DTID' => 5,
            'PIN' => 'otp-pin',
            'NPIN' => 'new-pin',
            'CPIN' => 'new-pin',
            'ENCT' => 1,
        ]);

        $this->assertEquals('otp-pin', $dto->pin);
        $this->assertEquals('new-pin', $dto->newPin);
        $this->assertEquals('new-pin', $dto->confirmPin);
    }

    public function test_validation_fails_for_empty_pin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PIN cannot be empty');

        new AgentOtpVerificationRequestDTO(
            dtid: 5,
            pin: '',
            newPin: 'new-pin',
            confirmPin: 'new-pin'
        );
    }

    public function test_validation_fails_for_empty_confirm_pin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CPIN cannot be empty');

        new AgentOtpVerificationRequestDTO(
            dtid: 5,
            pin: 'PLVIaH17OCzFw94Ze1wpZg==',
            newPin: 'new-pin',
            confirmPin: ''
        );
    }

    private function validDto(): AgentOtpVerificationRequestDTO
    {
        return new AgentOtpVerificationRequestDTO(
            dtid: 5,
            pin: 'PLVIaH17OCzFw94Ze1wpZg==',
            newPin: 'new-pin-value',
            confirmPin: 'new-pin-value'
        );
    }
}
