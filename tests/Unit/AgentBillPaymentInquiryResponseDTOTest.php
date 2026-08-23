<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentBillPaymentInquiryResponseDTO;

class AgentBillPaymentInquiryResponseDTOTest extends TestCase
{
    public function test_from_api_response_success(): void
    {
        $dto = AgentBillPaymentInquiryResponseDTO::fromApiResponse([
            'agentBillPaymentInquiryRes' => [
                'BAMTF' => '.00',
                'CMOB' => '03323331679',
                'LBAMTF' => '500.00',
                'BPAID' => '0',
                'PNAME' => 'Ufone Prepaid',
                'ISOVERDUE' => '0',
                'CONSUMER' => '03463564149',
                'BAMT' => '0.0',
                'DUEDATEF' => 'Fri, 07 February 2025 11:59 PM',
                'CNIC' => '4230168850895',
                'id' => '36',
                'DUEDATE' => '07/02/2025',
                'LBAMT' => '500.0',
            ],
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('36', $dto->id);
        $this->assertEquals('Ufone Prepaid', $dto->productName);
        $this->assertEquals('500.0', $dto->lateBillAmount);
        $this->assertEquals('4230168850895', $dto->cnic);
    }

    public function test_from_api_response_failure_id(): void
    {
        $dto = AgentBillPaymentInquiryResponseDTO::fromApiResponse([
            'agentBillPaymentInquiryRes' => [
                'id' => '-1',
            ],
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('-1', $dto->id);
    }

    public function test_from_api_response_gateway_error(): void
    {
        $dto = AgentBillPaymentInquiryResponseDTO::fromApiResponse([
            'messages' => 'Record Not Found',
            'errorcode' => '4005',
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('4005', $dto->errorCode);
        $this->assertEquals('Record Not Found', $dto->message);
    }
}
