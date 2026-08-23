<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashDepositRequestDTO;

class AgentCashDepositRequestDTOTest extends TestCase
{
    public function test_successful_dto_creation(): void
    {
        $dto = $this->validDto();

        $this->assertEquals(5, $dto->dtid);
        $this->assertEquals(50002, $dto->pid);
        $this->assertEquals('03377805512', $dto->customerMobile);
        $this->assertEquals('03215013511', $dto->agentMobile);
        $this->assertEquals(50000, $dto->transactionAmount);
        $this->assertEquals(1, $dto->encryptionType);
    }

    public function test_to_array_uses_agent_cash_deposit_req_wrapper(): void
    {
        $array = $this->validDto()->toArray();

        $this->assertArrayHasKey('agentCashDepositReq', $array);
        $this->assertEquals(5, $array['agentCashDepositReq']['DTID']);
        $this->assertEquals(50002, $array['agentCashDepositReq']['PID']);
        $this->assertEquals('6tBH5Et3C3b9p7Xzr1YVIQ==', $array['agentCashDepositReq']['PIN']);
        $this->assertEquals(1, $array['agentCashDepositReq']['ENCT']);
        $this->assertEquals('03377805512', $array['agentCashDepositReq']['CMOB']);
        $this->assertEquals('03215013511', $array['agentCashDepositReq']['AMOB']);
        $this->assertEquals('3740528738129', $array['agentCashDepositReq']['CNIC']);
        $this->assertEquals(50000, $array['agentCashDepositReq']['TXAM']);
        $this->assertEquals(200, $array['agentCashDepositReq']['CAMT']);
        $this->assertEquals(0, $array['agentCashDepositReq']['TPAM']);
        $this->assertEquals(50000, $array['agentCashDepositReq']['TAMT']);
    }

    public function test_from_array_supports_snake_case_and_api_keys(): void
    {
        $dto = AgentCashDepositRequestDTO::fromArray([
            'DTID' => 5,
            'PID' => 50002,
            'PIN' => 'pin-value',
            'ENCT' => 1,
            'CMOB' => '03377805512',
            'AMOB' => '03215013511',
            'CNIC' => '3740528738129',
            'TXAM' => 50000,
            'CAMT' => 200,
            'TPAM' => 0,
            'TAMT' => 50000,
        ]);

        $this->assertEquals('pin-value', $dto->pin);
        $this->assertEquals(200, $dto->commissionAmount);
    }

    public function test_validation_fails_for_empty_pin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PIN cannot be empty');

        new AgentCashDepositRequestDTO(
            dtid: 5,
            pid: 50002,
            pin: '',
            customerMobile: '03377805512',
            agentMobile: '03215013511',
            cnic: '3740528738129',
            transactionAmount: 50000,
            commissionAmount: 200,
            thirdPartyAmount: 0,
            totalAmount: 50000
        );
    }

    public function test_validation_fails_for_invalid_cnic(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CNIC must be exactly 13 characters');

        new AgentCashDepositRequestDTO(
            dtid: 5,
            pid: 50002,
            pin: '6tBH5Et3C3b9p7Xzr1YVIQ==',
            customerMobile: '03377805512',
            agentMobile: '03215013511',
            cnic: '123',
            transactionAmount: 50000,
            commissionAmount: 200,
            thirdPartyAmount: 0,
            totalAmount: 50000
        );
    }

    private function validDto(): AgentCashDepositRequestDTO
    {
        return new AgentCashDepositRequestDTO(
            dtid: 5,
            pid: 50002,
            pin: '6tBH5Et3C3b9p7Xzr1YVIQ==',
            customerMobile: '03377805512',
            agentMobile: '03215013511',
            cnic: '3740528738129',
            transactionAmount: 50000,
            commissionAmount: 200,
            thirdPartyAmount: 0,
            totalAmount: 50000
        );
    }
}
