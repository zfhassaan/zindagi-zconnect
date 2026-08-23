<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashWithdrawalInquiryRequestDTO;

class AgentCashWithdrawalInquiryRequestDTOTest extends TestCase
{
    public function test_successful_dto_creation(): void
    {
        $dto = $this->validDto();

        $this->assertEquals(5, $dto->dtid);
        $this->assertEquals(50006, $dto->pid);
        $this->assertEquals('03463564149', $dto->agentMobile);
        $this->assertEquals('03422142169', $dto->customerMobile);
        $this->assertEquals(100, $dto->transactionAmount);
        $this->assertEquals(1, $dto->isOtpRequired);
    }

    public function test_to_array_uses_portal_wrapper_typos(): void
    {
        $array = $this->validDto()->toArray();

        $this->assertArrayHasKey('agentCashdWithDrawlIinquiryReq', $array);
        $payload = $array['agentCashdWithDrawlIinquiryReq'];
        $this->assertEquals(5, $payload['DTID']);
        $this->assertEquals(1, $payload['APPID']);
        $this->assertEquals(50006, $payload['PID']);
        $this->assertEquals('03463564149', $payload['AMOB']);
        $this->assertEquals('03422142169', $payload['CMOB']);
        $this->assertEquals(100, $payload['TXAM']);
        $this->assertEquals('', $payload['CNIC']);
        $this->assertEquals('', $payload['PAYMENT_MODE']);
        $this->assertEquals(1, $payload['IS_OTP_REQ']);
        $this->assertEquals(1, $payload['HRA_LINKED_REQUEST']);
    }

    public function test_from_array_supports_snake_case_and_api_keys(): void
    {
        $dto = AgentCashWithdrawalInquiryRequestDTO::fromArray([
            'DTID' => 5,
            'APPID' => 2,
            'PID' => 50006,
            'AMOB' => '03463564149',
            'CMOB' => '03422142169',
            'TXAM' => 250,
            'IS_OTP_REQ' => 0,
        ]);

        $this->assertEquals(2, $dto->appId);
        $this->assertEquals(250, $dto->transactionAmount);
        $this->assertEquals(0, $dto->isOtpRequired);
    }

    public function test_validation_fails_for_invalid_mobile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer mobile must be exactly 11 characters');

        new AgentCashWithdrawalInquiryRequestDTO(
            dtid: 5,
            pid: 50006,
            agentMobile: '03463564149',
            customerMobile: '03422142',
            transactionAmount: 100
        );
    }

    public function test_validation_fails_for_invalid_cnic_when_provided(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CNIC must be empty or exactly 13 characters');

        new AgentCashWithdrawalInquiryRequestDTO(
            dtid: 5,
            pid: 50006,
            agentMobile: '03463564149',
            customerMobile: '03422142169',
            transactionAmount: 100,
            cnic: '123'
        );
    }

    private function validDto(): AgentCashWithdrawalInquiryRequestDTO
    {
        return new AgentCashWithdrawalInquiryRequestDTO(
            dtid: 5,
            pid: 50006,
            agentMobile: '03463564149',
            customerMobile: '03422142169',
            transactionAmount: 100
        );
    }
}
