<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\Contracts\OnboardingServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\OnboardingRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationRequestDTO;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingRequestDTO;

class OnboardingController
{
    public function __construct(
        protected OnboardingServiceInterface $onboardingService
    ) {
    }

    /**
     * Initiate customer onboarding.
     */
    public function initiate(Request $request): JsonResponse
    {
        $request->validate([
            'cnic' => 'required|string',
            'full_name' => 'required|string',
            'mobile_number' => 'required|string',
            'email' => 'required|email',
            'date_of_birth' => 'required|date',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'occupation' => 'nullable|string',
            'source_of_income' => 'nullable|string',
        ]);

        $dto = OnboardingRequestDTO::fromArray($request->all());
        $response = $this->onboardingService->initiate($dto);

        return response()->json($response->toArray(), $response->success ? 200 : 400);
    }

    /**
     * Verify customer information.
     */
    public function verify(Request $request, string $referenceId): JsonResponse
    {
        $request->validate([
            'verification_code' => 'required|string',
            'otp' => 'nullable|string',
        ]);

        $response = $this->onboardingService->verify($referenceId, $request->all());

        return response()->json($response->toArray(), $response->success ? 200 : 400);
    }

    /**
     * Get onboarding status.
     */
    public function status(string $referenceId): JsonResponse
    {
        $response = $this->onboardingService->getStatus($referenceId);

        return response()->json($response->toArray(), $response->success ? 200 : 400);
    }

    /**
     * Complete onboarding process.
     */
    public function complete(Request $request, string $referenceId): JsonResponse
    {
        $request->validate([
            'account_number' => 'nullable|string',
            'additional_info' => 'nullable|array',
        ]);

        $response = $this->onboardingService->complete($referenceId, $request->all());

        return response()->json($response->toArray(), $response->success ? 200 : 400);
    }

    /**
     * Verify account link with CNIC and mobile number.
     */
    public function verifyAccount(Request $request): JsonResponse
    {
        $request->validate([
            'cnic' => 'required|string|size:13',
            'mobile_no' => 'required|string|size:11',
            'merchant_type' => 'nullable|string|size:4',
            'trace_no' => 'nullable|string|size:6',
            'date_time' => 'nullable|string|size:14',
            'company_name' => 'nullable|string|size:4',
            'reserved1' => 'nullable|string|size:2',
            'reserved2' => 'nullable|string|size:2',
            'transaction_type' => 'nullable|string|size:2',
        ]);

        $dto = AccountVerificationRequestDTO::fromArray($request->all());
        $response = $this->onboardingService->verifyAccount($dto);

        return response()->json($response->toArray(), $response->success ? 200 : 400);
    }

    /**
     * Link account with CNIC and mobile number.
     */
    public function linkAccount(Request $request): JsonResponse
    {
        $request->validate([
            'cnic' => 'required|string|size:13',
            'mobile_no' => 'required|string|size:11',
            'merchant_type' => 'nullable|string|size:4',
            'trace_no' => 'nullable|string|size:6',
            'date_time' => 'nullable|string|size:14',
            'company_name' => 'nullable|string|size:4',
            'transaction_type' => 'nullable|string|size:2',
            'reserved1' => 'nullable|string|size:2',
            'otp_pin' => 'nullable|string',
        ]);

        $dto = AccountLinkingRequestDTO::fromArray($request->all());
        $response = $this->onboardingService->linkAccount($dto);

        return response()->json($response->toArray(), $response->success ? 200 : 400);
    }
}

