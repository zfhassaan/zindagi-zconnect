<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashDepositInquiryRequestDTO;

class AgentCashDepositInquiryRequestDTOTest extends TestCase
{
    public function test_successful_dto_creation(): void
    {
        $dto = $this->validDto();

        $this->assertEquals(5, $dto->dtid);
        $this->assertEquals(50002, $dto->pid);
        $this->assertEquals('03215013511', $dto->agentMobile);
        $this->assertEquals('03377805512', $dto->customerMobile);
        $this->assertEquals(50000, $dto->transactionAmount);
    }

    public function test_to_array_uses_agent_cash_deposit_inquiry_req_wrapper(): void
    {
        $array = $this->validDto()->toArray();

        $this->assertArrayHasKey('agentCashDepositInquiryReq', $array);
        $this->assertEquals(5, $array['agentCashDepositInquiryReq']['DTID']);
        $this->assertEquals(50002, $array['agentCashDepositInquiryReq']['PID']);
        $this->assertEquals('03215013511', $array['agentCashDepositInquiryReq']['AMOB']);
        $this->assertEquals('03377805512', $array['agentCashDepositInquiryReq']['CMOB']);
        $this->assertEquals(50000, $array['agentCashDepositInquiryReq']['TXAM']);
    }

    public function test_from_array_supports_snake_case_and_api_keys(): void
    {
        $dto = AgentCashDepositInquiryRequestDTO::fromArray([
            'DTID' => 5,
            'PID' => 50002,
            'AMOB' => '03215013511',
            'CMOB' => '03377805512',
            'TXAM' => 25000,
        ]);

        $this->assertEquals(25000, $dto->transactionAmount);
    }

    public function test_validation_fails_for_invalid_mobile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Agent mobile must be exactly 11 characters');

        new AgentCashDepositInquiryRequestDTO(
            dtid: 5,
            pid: 50002,
            agentMobile: '032150135',
            customerMobile: '03377805512',
            transactionAmount: 50000
        );
    }

    public function test_validation_fails_for_zero_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Transaction amount must be greater than zero');

        new AgentCashDepositInquiryRequestDTO(
            dtid: 5,
            pid: 50002,
            agentMobile: '03215013511',
            customerMobile: '03377805512',
            transactionAmount: 0
        );
    }

    private function validDto(): AgentCashDepositInquiryRequestDTO
    {
        return new AgentCashDepositInquiryRequestDTO(
            dtid: 5,
            pid: 50002,
            agentMobile: '03215013511',
            customerMobile: '03377805512',
            transactionAmount: 50000
        );
    }
}
