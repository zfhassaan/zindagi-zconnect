<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashWithdrawalRequestDTO;

class AgentCashWithdrawalRequestDTOTest extends TestCase
{
    public function test_successful_dto_creation(): void
    {
        $dto = $this->validDto();

        $this->assertEquals(5, $dto->dtid);
        $this->assertEquals(50006, $dto->pid);
        $this->assertEquals('03324779796', $dto->customerMobile);
        $this->assertEquals('03312008511', $dto->agentMobile);
        $this->assertEquals(200, $dto->transactionAmount);
        $this->assertEquals(1, $dto->isOtpRequired);
    }

    public function test_to_array_uses_portal_wrapper_typos(): void
    {
        $array = $this->validDto()->toArray();

        $this->assertArrayHasKey('agentCashdWithDrawlReq', $array);
        $this->assertEquals(5, $array['agentCashdWithDrawlReq']['DTID']);
        $this->assertEquals(50006, $array['agentCashdWithDrawlReq']['PID']);
        $this->assertEquals('6tBH5Et3C3b9p7Xzr1YVIQ==', $array['agentCashdWithDrawlReq']['PIN']);
        $this->assertEquals(1, $array['agentCashdWithDrawlReq']['ENCT']);
        $this->assertEquals('03324779796', $array['agentCashdWithDrawlReq']['CMOB']);
        $this->assertEquals('3520251717474', $array['agentCashdWithDrawlReq']['CNIC']);
        $this->assertEquals('03312008511', $array['agentCashdWithDrawlReq']['AMOB']);
        $this->assertEquals(200, $array['agentCashdWithDrawlReq']['TXAM']);
        $this->assertEquals(17.24, $array['agentCashdWithDrawlReq']['CAMT']);
        $this->assertEquals(20, $array['agentCashdWithDrawlReq']['TPAM']);
        $this->assertEquals(220, $array['agentCashdWithDrawlReq']['TAMT']);
        $this->assertEquals('QiIiSmLMGCkUSXEST+Ewiw==', $array['agentCashdWithDrawlReq']['OTPIN']);
        $this->assertEquals('', $array['agentCashdWithDrawlReq']['TXID']);
        $this->assertEquals(1, $array['agentCashdWithDrawlReq']['IS_OTP_REQ']);
    }

    public function test_from_array_supports_snake_case_and_api_keys(): void
    {
        $dto = AgentCashWithdrawalRequestDTO::fromArray([
            'DTID' => 5,
            'PID' => 50006,
            'PIN' => 'pin-value',
            'ENCT' => 1,
            'CMOB' => '03324779796',
            'CNIC' => '3520251717474',
            'AMOB' => '03312008511',
            'TXAM' => 200,
            'CAMT' => 17.24,
            'TPAM' => 20,
            'TAMT' => 220,
            'OTPIN' => 'otp-value',
            'TXID' => 'tx-1',
            'IS_OTP_REQ' => 1,
        ]);

        $this->assertEquals('pin-value', $dto->pin);
        $this->assertEquals('otp-value', $dto->otpPin);
        $this->assertEquals('tx-1', $dto->transactionId);
        $this->assertEquals(17.24, $dto->commissionAmount);
    }

    public function test_validation_fails_for_empty_otp_when_required(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('OTPIN cannot be empty when IS_OTP_REQ is 1');

        new AgentCashWithdrawalRequestDTO(
            dtid: 5,
            pid: 50006,
            pin: '6tBH5Et3C3b9p7Xzr1YVIQ==',
            customerMobile: '03324779796',
            agentMobile: '03312008511',
            cnic: '3520251717474',
            transactionAmount: 200,
            commissionAmount: 17.24,
            thirdPartyAmount: 20,
            totalAmount: 220,
            otpPin: '',
            isOtpRequired: 1
        );
    }

    public function test_validation_fails_for_invalid_cnic(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CNIC must be exactly 13 characters');

        new AgentCashWithdrawalRequestDTO(
            dtid: 5,
            pid: 50006,
            pin: '6tBH5Et3C3b9p7Xzr1YVIQ==',
            customerMobile: '03324779796',
            agentMobile: '03312008511',
            cnic: '123',
            transactionAmount: 200,
            commissionAmount: 17.24,
            thirdPartyAmount: 20,
            totalAmount: 220,
            otpPin: 'QiIiSmLMGCkUSXEST+Ewiw=='
        );
    }

    private function validDto(): AgentCashWithdrawalRequestDTO
    {
        return new AgentCashWithdrawalRequestDTO(
            dtid: 5,
            pid: 50006,
            pin: '6tBH5Et3C3b9p7Xzr1YVIQ==',
            customerMobile: '03324779796',
            agentMobile: '03312008511',
            cnic: '3520251717474',
            transactionAmount: 200,
            commissionAmount: 17.24,
            thirdPartyAmount: 20,
            totalAmount: 220,
            otpPin: 'QiIiSmLMGCkUSXEST+Ewiw=='
        );
    }
}
