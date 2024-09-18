<?php

namespace ITESC\Services;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use ITESC\Exceptions\ZoomException;

class ZoomApi
{
    private $client;
    private $apiKey;
    private $apiSecret;

    public function __construct()
    {
        // Initialize the HTTP client with the base URI for the Zoom API
        $this->client = new Client([
            'base_uri' => config('services.zoom.base_uri'),
        ]);
        // Retrieve the API key and secret from the configuration
        $this->apiKey = config('services.zoom.api_key');
        $this->apiSecret = config('services.zoom.api_secret');
    }

    public function connect()
    {
        // Generate a JWT token
        $token = $this->getToken();
        // Make a POST request to initialize a session
        $response = $this->postRequest('/v2/accounts/me/session', $token);

        // Check if the response is successful
        if (!$this->isSuccessfulResponse($response)) {
            // Throw an exception if the session initialization failed
            throw new ZoomException('Failed to initialize session: ' . $this->getErrorMessage($response));
        }
    }

    private function getToken()
    {
        // Create a payload for the JWT token
        $payload = [
            'iss' => $this->apiKey,
            'exp' => time() + 3600, // Token valid for 1 hour
        ];

        // Encode the payload using the API secret to generate the JWT token
        return \Firebase\JWT\JWT::encode($payload, $this->apiSecret);
    }

    public function getLicenseCount()
    {
        // Ensure a connection is established before making the API call
        $this->connect();
        // Generate a JWT token
        $token = $this->getToken();
        // Make a GET request to fetch the license count
        $response = $this->getRequest('/v2/accounts/me/licenses', $token);

        // Check if the response is successful
        if ($this->isSuccessfulResponse($response)) {
            // Extract and return the license count from the response
            return $this->extractLicenseCount($response);
        }

        // Throw an exception if the request failed
        throw new ZoomException($this->getErrorMessage($response));
    }

    private function postRequest($uri, $token)
    {
        // Make a POST request with the provided URI and JWT token
        return $this->client->post($uri, [
            RequestOptions::HEADERS => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);
    }

    private function getRequest($uri, $token)
    {
        // Make a GET request with the provided URI and JWT token
        return $this->client->get($uri, [
            RequestOptions::HEADERS => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);
    }

    private function isSuccessfulResponse($response)
    {
        // Check if the response status code is 200 (OK)
        return $response->getStatusCode() == 200;
    }

    private function extractLicenseCount($response)
    {
        // Decode the JSON response body to an associative array
        $data = json_decode($response->getBody()->getContents(), true);
        // Return the total license count or 0 if not found
        return $data['total_licenses'] ?? 0;
    }

    private function getErrorMessage($response)
    {
        // Return the response body as the error message
        return $response->getBody()->getContents();
    }
}