<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;

class AuthenticationService implements AuthenticationServiceInterface
{
    protected string $cacheKey = 'zindagi_zconnect_token';
    protected Client $client;

    public function __construct(
        protected LoggingServiceInterface $loggingService
    ) {
        $config = config('zindagi-zconnect');
        
        $this->client = new Client([
            'base_uri' => $config['api']['base_url'],
            'timeout' => $config['api']['timeout'],
            'verify' => $config['security']['verify_ssl'] ?? true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Authenticate and get access token.
     */
    public function authenticate(): string
    {
        // Check cache first
        $token = $this->getToken();
        if ($token && $this->isTokenValid()) {
            return $token;
        }

        // Get new token
        return $this->refreshToken();
    }

    /**
     * Get cached access token.
     */
    public function getToken(): ?string
    {
        return Cache::get($this->cacheKey);
    }

    /**
     * Refresh the access token.
     */
    public function refreshToken(): string
    {
        $config = config('zindagi-zconnect');
        
        $response = $this->client->post('/auth/token', [
            'json' => [
                'client_id' => $config['auth']['client_id'],
                'client_secret' => $config['auth']['client_secret'],
                'grant_type' => 'client_credentials',
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        $token = $data['access_token'] ?? null;

        if (!$token) {
            throw new \RuntimeException('Failed to obtain access token');
        }

        // Cache the token
        $ttl = $config['auth']['token_cache_ttl'] ?? 3600;
        Cache::put($this->cacheKey, $token, now()->addSeconds($ttl));

        $this->loggingService->logInfo('Access token refreshed', [
            'token_ttl' => $ttl,
        ]);

        return $token;
    }

    /**
     * Check if token is valid.
     */
    public function isTokenValid(): bool
    {
        $token = $this->getToken();
        return $token !== null;
    }
}

