<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\OnboardingRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\Contracts\OnboardingServiceInterface;

class OnboardingServiceTest extends TestCase
{
    /**
     * Test onboarding service is registered.
     */
    public function test_onboarding_service_is_registered(): void
    {
        $service = $this->app->make(OnboardingServiceInterface::class);
        
        $this->assertInstanceOf(OnboardingServiceInterface::class, $service);
    }

    /**
     * Test onboarding request DTO creation.
     */
    public function test_onboarding_request_dto_creation(): void
    {
        $data = [
            'cnic' => '1234567890123',
            'full_name' => 'John Doe',
            'mobile_number' => '03001234567',
            'email' => 'john@example.com',
            'date_of_birth' => '1990-01-01',
        ];

        $dto = OnboardingRequestDTO::fromArray($data);

        $this->assertEquals($data['cnic'], $dto->cnic);
        $this->assertEquals($data['full_name'], $dto->fullName);
        $this->assertEquals($data['mobile_number'], $dto->mobileNumber);
        $this->assertEquals($data['email'], $dto->email);
    }
}

