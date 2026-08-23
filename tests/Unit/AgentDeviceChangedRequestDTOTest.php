<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentDeviceChangedRequestDTO;

class AgentDeviceChangedRequestDTOTest extends TestCase
{
    public function test_successful_dto_creation(): void
    {
        $dto = $this->validDto();

        $this->assertEquals(5, $dto->dtid);
        $this->assertEquals('d203c723645b31a3', $dto->udid);
        $this->assertEquals(1063683, $dto->userId);
        $this->assertEquals(3, $dto->userType);
        $this->assertEquals(0, $dto->action);
    }

    public function test_to_array_uses_agent_device_changed_req_wrapper(): void
    {
        $array = $this->validDto()->toArray();

        $this->assertArrayHasKey('agentDeviceChangedReq', $array);
        $this->assertEquals(5, $array['agentDeviceChangedReq']['DTID']);
        $this->assertEquals('PLVIaH17OCzFw94Ze1wpZg==', $array['agentDeviceChangedReq']['PIN']);
        $this->assertEquals('d203c723645b31a3', $array['agentDeviceChangedReq']['UDID']);
        $this->assertEquals(1, $array['agentDeviceChangedReq']['ENCT']);
        $this->assertEquals(1063683, $array['agentDeviceChangedReq']['UID']);
        $this->assertEquals(3, $array['agentDeviceChangedReq']['USTY']);
        $this->assertEquals(0, $array['agentDeviceChangedReq']['ACTION']);
    }

    public function test_from_array_supports_snake_case_and_api_keys(): void
    {
        $dto = AgentDeviceChangedRequestDTO::fromArray([
            'DTID' => 5,
            'PIN' => 'pin-value',
            'UDID' => 'd203c723645b31a3',
            'ENCT' => 1,
            'UID' => 1063683,
            'USTY' => 3,
            'ACTION' => 0,
        ]);

        $this->assertEquals('pin-value', $dto->pin);
        $this->assertEquals(1063683, $dto->userId);
    }

    public function test_validation_fails_for_empty_udid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('UDID cannot be empty');

        new AgentDeviceChangedRequestDTO(
            dtid: 5,
            pin: 'PLVIaH17OCzFw94Ze1wpZg==',
            udid: '',
            userId: 1063683,
            userType: 3,
            action: 0
        );
    }

    public function test_validation_fails_for_invalid_user_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('UID must be greater than zero');

        new AgentDeviceChangedRequestDTO(
            dtid: 5,
            pin: 'PLVIaH17OCzFw94Ze1wpZg==',
            udid: 'd203c723645b31a3',
            userId: 0,
            userType: 3,
            action: 0
        );
    }

    private function validDto(): AgentDeviceChangedRequestDTO
    {
        return new AgentDeviceChangedRequestDTO(
            dtid: 5,
            pin: 'PLVIaH17OCzFw94Ze1wpZg==',
            udid: 'd203c723645b31a3',
            userId: 1063683,
            userType: 3,
            action: 0
        );
    }
}
