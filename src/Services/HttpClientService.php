<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface;
use zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface;

class HttpClientService implements HttpClientInterface
{
    protected Client $client;

    public function __construct(
        protected AuthenticationServiceInterface $authService,
        protected LoggingServiceInterface $loggingService,
        protected AuditServiceInterface $auditService
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
     * Make a GET request.
     */
    public function get(string $endpoint, array $headers = []): ResponseInterface
    {
        return $this->makeRequest('GET', $endpoint, [], $headers);
    }

    /**
     * Make a POST request.
     */
    public function post(string $endpoint, array $data = [], array $headers = []): ResponseInterface
    {
        return $this->makeRequest('POST', $endpoint, $data, $headers);
    }

    /**
     * Make a PUT request.
     */
    public function put(string $endpoint, array $data = [], array $headers = []): ResponseInterface
    {
        return $this->makeRequest('PUT', $endpoint, $data, $headers);
    }

    /**
     * Make a DELETE request.
     */
    public function delete(string $endpoint, array $headers = []): ResponseInterface
    {
        return $this->makeRequest('DELETE', $endpoint, [], $headers);
    }

    /**
     * Make an HTTP request with authentication and logging.
     */
    protected function makeRequest(
        string $method,
        string $endpoint,
        array $data = [],
        array $headers = []
    ): ResponseInterface {
        $token = $this->authService->authenticate();
        
        $headers = array_merge($headers, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $options = [
            'headers' => $headers,
        ];

        if (!empty($data)) {
            $options['json'] = $data;
        }

        // Log request
        $this->loggingService->logRequest($endpoint, $data, $headers);

        try {
            $response = $this->client->request($method, $endpoint, $options);
            
            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true) ?? $responseBody;
            
            // Log response
            $this->loggingService->logResponse(
                $endpoint,
                $responseData,
                $response->getStatusCode()
            );

            // Audit log
            $this->auditService->log(
                'api_request',
                'http_client',
                [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'status_code' => $response->getStatusCode(),
                ]
            );

            // Reset stream position for reading
            $response->getBody()->rewind();
            
            return $response;
        } catch (GuzzleException $e) {
            $this->loggingService->logError(
                'HTTP request failed',
                [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage(),
                ],
                $e
            );

            throw $e;
        }
    }
}

