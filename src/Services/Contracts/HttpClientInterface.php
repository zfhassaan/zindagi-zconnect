<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Services\Contracts;

use Psr\Http\Message\ResponseInterface;

interface HttpClientInterface
{
    /**
     * Make a GET request.
     *
     * @param string $endpoint
     * @param array $headers
     * @return ResponseInterface
     */
    public function get(string $endpoint, array $headers = []): ResponseInterface;

    /**
     * Make a POST request.
     *
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     * @return ResponseInterface
     */
    public function post(string $endpoint, array $data = [], array $headers = []): ResponseInterface;

    /**
     * Make a PUT request.
     *
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     * @return ResponseInterface
     */
    public function put(string $endpoint, array $data = [], array $headers = []): ResponseInterface;

    /**
     * Make a DELETE request.
     *
     * @param string $endpoint
     * @param array $headers
     * @return ResponseInterface
     */
    public function delete(string $endpoint, array $headers = []): ResponseInterface;
}

