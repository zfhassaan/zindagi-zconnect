<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentChangePinRequestDTO;

class AgentChangePinRequestDTOTest extends TestCase
{
    public function test_successful_dto_creation(): void
    {
        $dto = $this->validDto();

        $this->assertEquals(5, $dto->dtid);
        $this->assertEquals('eYlXC3w446A08W+XvqJVBA==', $dto->pin);
        $this->assertEquals(1, $dto->encryptionType);
    }

    public function test_to_array_uses_agent_change_pin_req_wrapper(): void
    {
        $array = $this->validDto()->toArray();

        $this->assertArrayHasKey('agentChangePinReq', $array);
        $this->assertEquals(5, $array['agentChangePinReq']['DTID']);
        $this->assertEquals('eYlXC3w446A08W+XvqJVBA==', $array['agentChangePinReq']['PIN']);
        $this->assertEquals('new-pin-value', $array['agentChangePinReq']['NPIN']);
        $this->assertEquals('new-pin-value', $array['agentChangePinReq']['CPIN']);
        $this->assertEquals(1, $array['agentChangePinReq']['ENCT']);
    }

    public function test_from_array_supports_snake_case_and_api_keys(): void
    {
        $dto = AgentChangePinRequestDTO::fromArray([
            'DTID' => 5,
            'PIN' => 'old-pin',
            'NPIN' => 'new-pin',
            'CPIN' => 'new-pin',
            'ENCT' => 1,
        ]);

        $this->assertEquals('old-pin', $dto->pin);
        $this->assertEquals('new-pin', $dto->newPin);
        $this->assertEquals('new-pin', $dto->confirmPin);
    }

    public function test_validation_fails_for_empty_new_pin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('NPIN cannot be empty');

        new AgentChangePinRequestDTO(
            dtid: 5,
            pin: 'eYlXC3w446A08W+XvqJVBA==',
            newPin: '',
            confirmPin: 'confirm'
        );
    }

    public function test_validation_fails_for_empty_confirm_pin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CPIN cannot be empty');

        new AgentChangePinRequestDTO(
            dtid: 5,
            pin: 'eYlXC3w446A08W+XvqJVBA==',
            newPin: 'new-pin',
            confirmPin: ''
        );
    }

    private function validDto(): AgentChangePinRequestDTO
    {
        return new AgentChangePinRequestDTO(
            dtid: 5,
            pin: 'eYlXC3w446A08W+XvqJVBA==',
            newPin: 'new-pin-value',
            confirmPin: 'new-pin-value'
        );
    }
}
