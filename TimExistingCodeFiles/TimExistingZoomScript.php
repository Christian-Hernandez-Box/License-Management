<?php

namespace ITESC\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use ITESC\Exceptions\ZoomException;

class ZoomService
{
    const CACHE_KEY = 'ZOOM_CACHE';

    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('services.zoom.base_uri'),
        ]);
    }

    public function createAuthUrl()
    {
        $query = http_build_query([
            'response_type' => 'code',
            'client_id'     => config('services.zoom.client_id'),
            'redirect_uri'  => config('services.zoom.redirect_uri'),
        ]);

        return config('services.zoom.base_uri') . "/oauth/authorize?$query";
    }

    public function saveCredentials(string $code)
    {
        $clientId     = config('services.zoom.client_id');
        $clientSecret = config('services.zoom.client_secret');

        $response = $this->client->post('/oauth/token', [
            RequestOptions::FORM_PARAMS => [
                'grant_type'   => 'authorization_code',
                'code'         => $code,
                'client_id'    => config('services.zoom.client_id'),
                'redirect_uri' => 'http://localhost',
            ],
            RequestOptions::HEADERS     => [
                'Authorization' => 'Basic ' . base64_encode("{$clientId}:{$clientSecret}"),
            ],
        ]);

        $raw = $response->getBody()->getContents();

        Storage::disk('local')->put('/credentials/zoom.json', $raw);

        $credentials = json_decode($raw, true);
        Cache::put(self::CACHE_KEY, [
            'access_token' => $credentials['access_token'],
        ], now()->addMinutes(50));
    }

    private function getToken()
    {
        if (!Cache::has(self::CACHE_KEY)) {
            $this->refreshToken();
        }

        $credentials = Cache::get(self::CACHE_KEY);

        return $credentials['access_token'];
    }

    public function refreshToken()
    {
        if (!Storage::disk('local')->exists('/credentials/zoom.json')) {
            throw new Exception("Zoom credentials not found. Run \"php artisan zoom:oauth2\"");
        }
        $raw = Storage::disk('local')->get('/credentials/zoom.json');

        $credentials = json_decode($raw, true);

        $clientId     = config('services.zoom.client_id');
        $clientSecret = config('services.zoom.client_secret');

        $response = $this->client->post('/oauth/token', [
            RequestOptions::QUERY   => [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $credentials['refresh_token'],
            ],
            RequestOptions::HEADERS => [
                'Authorization' => 'Basic ' . base64_encode("{$clientId}:{$clientSecret}"),
            ],
        ]);

        $raw = $response->getBody()->getContents();

        Storage::disk('local')->put('/credentials/zoom.json', $raw);

        $credentials = json_decode($raw, true);
        Cache::put(self::CACHE_KEY, [
            'access_token' => $credentials['access_token'],
        ], now()->addMinutes(50));

        return $credentials['access_token'];
    }

    public function updateUserStatus(string $email, string $action)
    {
        $token = $this->getToken();

        $response = $this->client->request('PUT', "/v2/users/{$email}/status", [
            RequestOptions::JSON    => ['action' => $action],
            RequestOptions::HEADERS => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);

        $contents = $response->getBody()->getContents();

        if (app()->environment('local')) {
            Storage::disk('local')->put('integrations/zoom/updateUserStatus/' . milliseconds() . '.json', $contents);
        }

        return true;
    }

    public function disableUser(string $email)
    {
        $token = $this->getToken();

        $response = $this->client->request('PUT', "/v2/users/{$email}/status", [
            RequestOptions::JSON    => ['action' => 'deactivate'],
            RequestOptions::HEADERS => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);

        $contents = $response->getBody()->getContents();

        if (app()->environment('local')) {
            Storage::disk('local')->put('integrations/zoom/disableUser/' . milliseconds() . '.json', $contents);
        }

        return true;
    }

    public function users($status = null, $pageSize = 30, $pageNumber = 1)
    {
        $token = $this->getToken();

        $response = $this->client->get('/v2/users', [
            RequestOptions::QUERY   => [
                'status'      => !is_null($status) ? $status : '',
                'page_size'   => $pageSize,
                'page_number' => $pageNumber,
            ],
            RequestOptions::HEADERS => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);

        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody()->getContents());
        }

        throw new ZoomException($response->getBody()->getContents());
    }

    public function updateUser($userId, $data)
    {
        $token = $this->getToken();

        $response = $this->client->patch("/v2/users/{$userId}", [
            RequestOptions::JSON    => $data,
            RequestOptions::HEADERS => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);

        if ($response->getStatusCode() == 204) {
            return true;
        }

        throw new ZoomException($response->getBody()->getContents());
    }

    public function updateUserSettings($userId, $settings)
    {
        $token = $this->getToken();

        $response = $this->client->patch("/v2/users/{$userId}/settings", [
            RequestOptions::JSON    => $settings,
            RequestOptions::HEADERS => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);

        if ($response->getStatusCode() == 204) {
            return true;
        }

        throw new ZoomException($response->getBody()->getContents());
    }

    public function userSettings($userId)
    {
        $token = $this->getToken();

        $response = $this->client->get("/v2/users/{$userId}/settings", [
            RequestOptions::HEADERS => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);

        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody()->getContents());
        }

        throw new ZoomException($response->getBody()->getContents());
    }

    public function getUser($userId)
    {
        $token = $this->getToken();

        $response = $this->client->get("/v2/users/{$userId}", [
            RequestOptions::HEADERS => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);

        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody()->getContents());
        }

        throw new ZoomException($response->getBody()->getContents());
    }
}