<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\L2AccountStatusResponseDTO;

class L2AccountStatusResponseDTOTest extends TestCase
{
    /**
     * Test fromArray with complete successful response data.
     */
    public function test_from_array_with_complete_response(): void
    {
        $responseData = [
            'l2AccountStatusRes' => [
                'HashData' => 'cfe358a472ffe5f238d245143691c3a15d48ccccd535c3763fe1f1d32a6c61eb',
                'ResponseDateTime' => '213212132131',
                'AccountStatus' => 'Rejected',
                'ResponseCode' => '00',
                'ResponseDescription' => 'Successful',
                'Rrn' => '2161004065014051',
            ],
        ];

        $dto = L2AccountStatusResponseDTO::fromArray($responseData);

        $this->assertInstanceOf(L2AccountStatusResponseDTO::class, $dto);
        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Successful', $dto->message);
        $this->assertEquals('2161004065014051', $dto->rrn);
        $this->assertEquals('213212132131', $dto->responseDateTime);
        $this->assertEquals('Rejected', $dto->accountStatus);
        $this->assertEquals('cfe358a472ffe5f238d245143691c3a15d48ccccd535c3763fe1f1d32a6c61eb', $dto->hashData);
        $this->assertEquals($responseData, $dto->originalResponse);
    }

    /**
     * Test fromArray with different account statuses.
     */
    public function test_from_array_with_different_account_statuses(): void
    {
        $statuses = ['Approved', 'Rejected', 'Pending', 'Active', 'Inactive'];

        foreach ($statuses as $status) {
            $responseData = [
                'l2AccountStatusRes' => [
                    'ResponseCode' => '00',
                    'ResponseDescription' => 'Success',
                    'Rrn' => '2161004065014051',
                    'AccountStatus' => $status,
                ],
            ];

            $dto = L2AccountStatusResponseDTO::fromArray($responseData);

            $this->assertTrue($dto->success);
            $this->assertEquals($status, $dto->accountStatus);
        }
    }

    /**
     * Test fromArray with error response.
     */
    public function test_from_array_with_error_response(): void
    {
        $responseData = [
            'l2AccountStatusRes' => [
                'ResponseCode' => '01',
                'ResponseDescription' => 'Invalid Request',
                'Rrn' => '2161004065014051',
            ],
        ];

        $dto = L2AccountStatusResponseDTO::fromArray($responseData);

        $this->assertFalse($dto->success);
        $this->assertEquals('01', $dto->responseCode);
        $this->assertEquals('Invalid Request', $dto->message);
    }

    /**
     * Test success property is true only for response code '00'.
     */
    public function test_success_property_based_on_response_code(): void
    {
        $successData = [
            'l2AccountStatusRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'Rrn' => '2161004065014051',
            ],
        ];

        $errorCodes = ['01', '02', '99', 'ERR'];

        $successDto = L2AccountStatusResponseDTO::fromArray($successData);
        $this->assertTrue($successDto->success);

        foreach ($errorCodes as $code) {
            $errorData = [
                'l2AccountStatusRes' => [
                    'ResponseCode' => $code,
                    'ResponseDescription' => 'Error',
                    'Rrn' => '2161004065014051',
                ],
            ];

            $errorDto = L2AccountStatusResponseDTO::fromArray($errorData);
            $this->assertFalse($errorDto->success);
        }
    }

    /**
     * Test fromArray with minimal response data.
     */
    public function test_from_array_with_minimal_response(): void
    {
        $responseData = [
            'l2AccountStatusRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
            ],
        ];

        $dto = L2AccountStatusResponseDTO::fromArray($responseData);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Success', $dto->message);
        $this->assertNull($dto->rrn);
        $this->assertNull($dto->responseDateTime);
        $this->assertNull($dto->accountStatus);
        $this->assertNull($dto->hashData);
    }

    /**
     * Test fromArray handles response without wrapper.
     */
    public function test_from_array_handles_response_without_wrapper(): void
    {
        $responseData = [
            'ResponseCode' => '00',
            'ResponseDescription' => 'Success',
            'Rrn' => '2161004065014051',
            'AccountStatus' => 'Approved',
        ];

        $dto = L2AccountStatusResponseDTO::fromArray($responseData);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Success', $dto->message);
        $this->assertEquals('2161004065014051', $dto->rrn);
        $this->assertEquals('Approved', $dto->accountStatus);
    }

    /**
     * Test manual DTO construction.
     */
    public function test_manual_dto_construction(): void
    {
        $dto = new L2AccountStatusResponseDTO(
            success: true,
            responseCode: '00',
            message: 'Test Success',
            rrn: '2161004065014051',
            responseDateTime: '213212132131',
            accountStatus: 'Active',
            hashData: 'test_hash',
            originalResponse: ['test' => 'data']
        );

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Test Success', $dto->message);
        $this->assertEquals('2161004065014051', $dto->rrn);
        $this->assertEquals('213212132131', $dto->responseDateTime);
        $this->assertEquals('Active', $dto->accountStatus);
        $this->assertEquals('test_hash', $dto->hashData);
        $this->assertEquals(['test' => 'data'], $dto->originalResponse);
    }

    /**
     * Test original response is preserved.
     */
    public function test_original_response_is_preserved(): void
    {
        $responseData = [
            'l2AccountStatusRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'Rrn' => '2161004065014051',
                'CustomField' => 'CustomValue',
            ],
        ];

        $dto = L2AccountStatusResponseDTO::fromArray($responseData);

        $this->assertEquals($responseData, $dto->originalResponse);
        $this->assertArrayHasKey('l2AccountStatusRes', $dto->originalResponse);
        $this->assertEquals('CustomValue', $dto->originalResponse['l2AccountStatusRes']['CustomField']);
    }

    /**
     * Test response with all fields null except required.
     */
    public function test_response_with_required_fields_only(): void
    {
        $responseData = [
            'l2AccountStatusRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
            ],
        ];

        $dto = L2AccountStatusResponseDTO::fromArray($responseData);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Success', $dto->message);
        $this->assertNull($dto->rrn);
        $this->assertNull($dto->responseDateTime);
        $this->assertNull($dto->accountStatus);
        $this->assertNull($dto->hashData);
        $this->assertIsArray($dto->originalResponse);
    }
}
