<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountUpgradeV2ResponseDTO;

class AccountUpgradeV2ResponseDTOTest extends TestCase
{
    public function test_from_api_response_success(): void
    {
        $dto = AccountUpgradeV2ResponseDTO::fromApiResponse([
            'UpgradeAcc' => [
                'responseCode' => '00',
                'responseDescription' => 'Successful',
                'token' => 'abc-token',
                'coolingOffStatus' => false,
                'coolingOffTime' => null,
                'coolingOffText' => null,
                'coolingOffTimeLeft' => null,
            ],
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Successful', $dto->responseDescription);
        $this->assertEquals('abc-token', $dto->token);
        $this->assertFalse($dto->coolingOffStatus);
    }

    public function test_from_api_response_partial_failure_from_portal_sample(): void
    {
        $dto = AccountUpgradeV2ResponseDTO::fromApiResponse([
            'UpgradeAcc' => [
                'responseCode' => '100',
                'responseDescription' => 'Biometric verification succeeded, but account upgrade failed. Please contact support.',
                'token' => '',
                'coolingOffStatus' => false,
            ],
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('100', $dto->responseCode);
        $this->assertStringContainsString('account upgrade failed', $dto->message);
    }

    public function test_from_api_response_gateway_error(): void
    {
        $dto = AccountUpgradeV2ResponseDTO::fromApiResponse([
            'messages' => 'Bad Request - Invalid Request Payload',
            'errorcode' => '4002',
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('4002', $dto->errorCode);
        $this->assertEquals('Bad Request - Invalid Request Payload', $dto->message);
    }
}
