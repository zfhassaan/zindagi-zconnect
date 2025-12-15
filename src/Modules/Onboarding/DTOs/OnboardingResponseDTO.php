<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class OnboardingResponseDTO
{
    public function __construct(
        public bool $success,
        public string $status,
        public ?string $referenceId = null,
        public ?string $message = null,
        public ?array $data = null,
        public ?array $errors = null
    ) {
    }

    /**
     * Create DTO from API response.
     */
    public static function fromApiResponse(array $response): self
    {
        return new self(
            success: $response['success'] ?? false,
            status: $response['status'] ?? 'failed',
            referenceId: $response['reference_id'] ?? null,
            message: $response['message'] ?? null,
            data: $response['data'] ?? null,
            errors: $response['errors'] ?? null
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'status' => $this->status,
            'reference_id' => $this->referenceId,
            'message' => $this->message,
            'data' => $this->data,
            'errors' => $this->errors,
        ];
    }
}

