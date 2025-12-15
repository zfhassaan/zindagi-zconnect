<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class OnboardingRequestDTO
{
    public function __construct(
        public string $cnic,
        public string $fullName,
        public string $mobileNumber,
        public string $email,
        public string $dateOfBirth,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $country = null,
        public ?string $occupation = null,
        public ?string $sourceOfIncome = null,
        public ?array $additionalData = []
    ) {
    }

    /**
     * Create DTO from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            cnic: $data['cnic'],
            fullName: $data['full_name'],
            mobileNumber: $data['mobile_number'],
            email: $data['email'],
            dateOfBirth: $data['date_of_birth'],
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            country: $data['country'] ?? null,
            occupation: $data['occupation'] ?? null,
            sourceOfIncome: $data['source_of_income'] ?? null,
            additionalData: $data['additional_data'] ?? []
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'cnic' => $this->cnic,
            'full_name' => $this->fullName,
            'mobile_number' => $this->mobileNumber,
            'email' => $this->email,
            'date_of_birth' => $this->dateOfBirth,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'occupation' => $this->occupation,
            'source_of_income' => $this->sourceOfIncome,
            'additional_data' => $this->additionalData,
        ];
    }
}

