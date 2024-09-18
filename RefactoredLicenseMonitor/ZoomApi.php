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
        $this->client = new Client([
            'base_uri' => config('services.zoom.base_uri'),
        ]);
        $this->apiKey = config('services.zoom.api_key');
        $this->apiSecret = config('services.zoom.api_secret');
    }

    public function connect()
    {
        $token = $this->getToken();
        $response = $this->postRequest('/v2/accounts/me/session', $token);

        if (!$this->isSuccessfulResponse($response)) {
            throw new ZoomException('Failed to initialize session: ' . $this->getErrorMessage($response));
        }
    }

    private function getToken()
    {
        $payload = [
            'iss' => $this->apiKey,
            'exp' => time() + 3600, // Token valid for 1 hour
        ];

        return \Firebase\JWT\JWT::encode($payload, $this->apiSecret);
    }

    public function getLicenseCount()
    {
        $this->connect(); // Ensure connection is established before making the API call
        $token = $this->getToken();
        $response = $this->getRequest('/v2/accounts/me/licenses', $token);

        if ($this->isSuccessfulResponse($response)) {
            return $this->extractLicenseCount($response);
        }

        throw new ZoomException($this->getErrorMessage($response));
    }

    private function postRequest($uri, $token)
    {
        return $this->client->post($uri, [
            RequestOptions::HEADERS => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);
    }

    private function getRequest($uri, $token)
    {
        return $this->client->get($uri, [
            RequestOptions::HEADERS => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);
    }

    private function isSuccessfulResponse($response)
    {
        return $response->getStatusCode() == 200;
    }

    private function extractLicenseCount($response)
    {
        $data = json_decode($response->getBody()->getContents(), true);
        return $data['total_licenses'] ?? 0;
    }

    private function getErrorMessage($response)
    {
        return $response->getBody()->getContents();
    }
}