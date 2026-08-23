<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentLoginRequestDTO;

class AgentLoginRequestDTOTest extends TestCase
{
    public function test_successful_dto_creation(): void
    {
        $dto = $this->validDto();

        $this->assertEquals('6tBH5Et3C3b9p7Xzr1YVIQ==', $dto->pin);
        $this->assertEquals('1063857', $dto->uid);
        $this->assertEquals('b227ee26ac146393', $dto->udid);
    }

    public function test_to_array_uses_login_agent_req_wrapper(): void
    {
        $array = $this->validDto()->toArray();

        $this->assertArrayHasKey('loginAgentReq', $array);
        $this->assertEquals('6tBH5Et3C3b9p7Xzr1YVIQ==', $array['loginAgentReq']['PIN']);
        $this->assertEquals('1063857', $array['loginAgentReq']['UID']);
        $this->assertEquals('2.0.10.43', $array['loginAgentReq']['APPV']);
        $this->assertEquals('Android', $array['loginAgentReq']['OS']);
        $this->assertEquals('11', $array['loginAgentReq']['OSVERSION']);
        $this->assertEquals('SM-A505F', $array['loginAgentReq']['MODEL']);
        $this->assertEquals('samsung', $array['loginAgentReq']['VENDOR']);
        $this->assertEquals('Ufone', $array['loginAgentReq']['NETWORK']);
        $this->assertEquals('b227ee26ac146393', $array['loginAgentReq']['UDID']);
    }

    public function test_from_array_supports_snake_case_and_api_keys(): void
    {
        $dto = AgentLoginRequestDTO::fromArray([
            'PIN' => 'pin-value',
            'UID' => '999',
            'APPV' => '1.0.0',
            'OS' => 'Android',
            'OSVERSION' => '12',
            'MODEL' => 'Pixel',
            'VENDOR' => 'Google',
            'NETWORK' => 'Jazz',
            'UDID' => 'device-1',
        ]);

        $this->assertEquals('pin-value', $dto->pin);
        $this->assertEquals('999', $dto->uid);
        $this->assertEquals('device-1', $dto->udid);
    }

    public function test_validation_fails_for_empty_pin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PIN cannot be empty');

        new AgentLoginRequestDTO(
            pin: '',
            uid: '1063857',
            appVersion: '2.0.10.43',
            os: 'Android',
            osVersion: '11',
            model: 'SM-A505F',
            vendor: 'samsung',
            network: 'Ufone',
            udid: 'b227ee26ac146393'
        );
    }

    public function test_validation_fails_for_empty_uid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('UID cannot be empty');

        new AgentLoginRequestDTO(
            pin: '6tBH5Et3C3b9p7Xzr1YVIQ==',
            uid: '',
            appVersion: '2.0.10.43',
            os: 'Android',
            osVersion: '11',
            model: 'SM-A505F',
            vendor: 'samsung',
            network: 'Ufone',
            udid: 'b227ee26ac146393'
        );
    }

    private function validDto(): AgentLoginRequestDTO
    {
        return new AgentLoginRequestDTO(
            pin: '6tBH5Et3C3b9p7Xzr1YVIQ==',
            uid: '1063857',
            appVersion: '2.0.10.43',
            os: 'Android',
            osVersion: '11',
            model: 'SM-A505F',
            vendor: 'samsung',
            network: 'Ufone',
            udid: 'b227ee26ac146393'
        );
    }
}
