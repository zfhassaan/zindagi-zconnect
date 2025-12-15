<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\L2AccountUpgradeDiscrepantResponseDTO;

class L2AccountUpgradeDiscrepantResponseDTOTest extends TestCase
{
    /**
     * Test fromArray with complete successful response data.
     */
    public function test_from_array_with_complete_response(): void
    {
        $responseData = [
            'l2AccountUpgradeDiscrepantRes' => [
                'Rrn' => '000000770011',
                'ResponseCode' => '00',
                'ResponseDescription' => 'Request has been submitted successfully',
                'ResponseDateTime' => '20220729171717',
                'HashData' => '8a5e2fcb0522a0b32801262937a6699cfed2651fdeb03f6e65d2726ae238d3f7',
            ],
        ];

        $dto = L2AccountUpgradeDiscrepantResponseDTO::fromArray($responseData);

        $this->assertInstanceOf(L2AccountUpgradeDiscrepantResponseDTO::class, $dto);
        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Request has been submitted successfully', $dto->message);
        $this->assertEquals('000000770011', $dto->rrn);
        $this->assertEquals('20220729171717', $dto->responseDateTime);
        $this->assertEquals('8a5e2fcb0522a0b32801262937a6699cfed2651fdeb03f6e65d2726ae238d3f7', $dto->hashData);
        $this->assertEquals($responseData, $dto->originalResponse);
    }

    /**
     * Test fromArray with error response.
     */
    public function test_from_array_with_error_response(): void
    {
        $responseData = [
            'l2AccountUpgradeDiscrepantRes' => [
                'ResponseCode' => '01',
                'ResponseDescription' => 'Invalid Request',
                'Rrn' => '000000770011',
            ],
        ];

        $dto = L2AccountUpgradeDiscrepantResponseDTO::fromArray($responseData);

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
            'l2AccountUpgradeDiscrepantRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'Rrn' => '000000770011',
            ],
        ];

        $errorData = [
            'l2AccountUpgradeDiscrepantRes' => [
                'ResponseCode' => '99',
                'ResponseDescription' => 'Error',
                'Rrn' => '000000770011',
            ],
        ];

        $successDto = L2AccountUpgradeDiscrepantResponseDTO::fromArray($successData);
        $errorDto = L2AccountUpgradeDiscrepantResponseDTO::fromArray($errorData);

        $this->assertTrue($successDto->success);
        $this->assertFalse($errorDto->success);
    }

    /**
     * Test fromArray with minimal response data.
     */
    public function test_from_array_with_minimal_response(): void
    {
        $responseData = [
            'l2AccountUpgradeDiscrepantRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
            ],
        ];

        $dto = L2AccountUpgradeDiscrepantResponseDTO::fromArray($responseData);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Success', $dto->message);
        $this->assertNull($dto->rrn);
        $this->assertNull($dto->responseDateTime);
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
            'Rrn' => '000000770011',
        ];

        $dto = L2AccountUpgradeDiscrepantResponseDTO::fromArray($responseData);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Success', $dto->message);
        $this->assertEquals('000000770011', $dto->rrn);
    }

    /**
     * Test manual DTO construction.
     */
    public function test_manual_dto_construction(): void
    {
        $dto = new L2AccountUpgradeDiscrepantResponseDTO(
            success: true,
            responseCode: '00',
            message: 'Test Success',
            rrn: '000000770011',
            responseDateTime: '20220729171717',
            hashData: 'test_hash',
            originalResponse: ['test' => 'data']
        );

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Test Success', $dto->message);
        $this->assertEquals('000000770011', $dto->rrn);
        $this->assertEquals('20220729171717', $dto->responseDateTime);
        $this->assertEquals('test_hash', $dto->hashData);
        $this->assertEquals(['test' => 'data'], $dto->originalResponse);
    }

    /**
     * Test original response is preserved.
     */
    public function test_original_response_is_preserved(): void
    {
        $responseData = [
            'l2AccountUpgradeDiscrepantRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'Rrn' => '000000770011',
                'CustomField' => 'CustomValue',
            ],
        ];

        $dto = L2AccountUpgradeDiscrepantResponseDTO::fromArray($responseData);

        $this->assertEquals($responseData, $dto->originalResponse);
        $this->assertArrayHasKey('l2AccountUpgradeDiscrepantRes', $dto->originalResponse);
        $this->assertEquals('CustomValue', $dto->originalResponse['l2AccountUpgradeDiscrepantRes']['CustomField']);
    }
}
