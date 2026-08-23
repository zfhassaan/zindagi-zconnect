<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashDepositInquiryResponseDTO;

class AgentCashDepositInquiryResponseDTOTest extends TestCase
{
    public function test_from_api_response_success(): void
    {
        $dto = AgentCashDepositInquiryResponseDTO::fromApiResponse([
            'agentCashDepositInquiryRes' => [
                'CMOB' => '03377805512',
                'TAMT' => '50100.0',
                'TXAM' => '50000.0',
                'CNIC' => '6110180873549',
                'CAMT' => '86.21',
                'id' => '78',
                'TPAMF' => '100.00',
                'TAMTF' => '50,100.00',
                'TXAMF' => '50,000.00',
                'NAME' => 'SAOUD NASEER',
                'CAMTF' => '86.21',
                'TPAM' => '100.0',
            ],
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('78', $dto->id);
        $this->assertEquals('03377805512', $dto->customerMobile);
        $this->assertEquals('50100.0', $dto->totalAmount);
        $this->assertEquals('50000.0', $dto->transactionAmount);
        $this->assertEquals('6110180873549', $dto->cnic);
        $this->assertEquals('SAOUD NASEER', $dto->name);
        $this->assertEquals('50,100.00', $dto->totalAmountFormatted);
    }

    public function test_from_api_response_with_negative_id_is_failure(): void
    {
        $dto = AgentCashDepositInquiryResponseDTO::fromApiResponse([
            'agentCashDepositInquiryRes' => [
                'id' => '-1',
            ],
        ]);

        $this->assertFalse($dto->success);
    }

    public function test_from_api_response_gateway_error(): void
    {
        $dto = AgentCashDepositInquiryResponseDTO::fromApiResponse([
            'messages' => 'Record Not Found',
            'errorcode' => '4005',
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('4005', $dto->errorCode);
        $this->assertEquals('Record Not Found', $dto->message);
    }
}
