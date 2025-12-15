<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateZconnectRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Validate API key if required
        if (config('zindagi-zconnect.security.api_key_required', false)) {
            $apiKey = $request->header('X-API-Key') ?? $request->input('api_key');
            $expectedKey = config('zindagi-zconnect.auth.api_key');

            if (!$apiKey || $apiKey !== $expectedKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API key',
                ], 401);
            }
        }

        // Validate request signature if enabled
        if (config('zindagi-zconnect.security.request_signing', false)) {
            if (!$this->validateSignature($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request signature',
                ], 401);
            }
        }

        return $next($request);
    }

    /**
     * Validate request signature.
     */
    protected function validateSignature(Request $request): bool
    {
        $signature = $request->header('X-Signature');
        
        if (!$signature) {
            return false;
        }

        // Implement signature validation logic
        // This is a placeholder - implement according to JS Bank's requirements
        $payload = $request->getContent();
        $secret = config('zindagi-zconnect.auth.client_secret');
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }
}

