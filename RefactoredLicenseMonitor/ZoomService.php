<?php

class ZoomApi extends ZoomService
{
    /**
     * Constructor to initialize the Zoom API client.
     */
    public function __construct($config)
    {
        parent::__construct();
        $this->client = new Client([
            'base_uri' => $config['base_uri'],
        ]);
        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->redirectUri = $config['redirect_uri'];
        $this->baseUri = $config['base_uri'];
        $this->cache = [];
    }

    /**
     * Generate the authorization URL for OAuth.
     */
    public function createAuthUrl()
    {
        $query = http_build_query([
            'response_type' => 'code',
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
        ]);

        return $this->baseUri . "/oauth/authorize?$query";
    }

    /**
     * Save the OAuth credentials after authorization.
     */
    public function saveCredentials(string $code)
    {
        $response = $this->client->post('/oauth/token', [
            RequestOptions::FORM_PARAMS => [
                'grant_type'   => 'authorization_code',
                'code'         => $code,
                'client_id'    => $this->clientId,
                'redirect_uri' => $this->redirectUri,
            ],
            RequestOptions::HEADERS     => [
                'Authorization' => 'Basic ' . base64_encode("{$this->clientId}:{$this->clientSecret}"),
            ],
        ]);

        $raw = $response->getBody()->getContents();
        Storage::disk('local')->put('/credentials/zoom.json', $raw);

        $credentials = json_decode($raw, true);
        Cache::put(self::CACHE_KEY, [
            'access_token' => $credentials['access_token'],
        ], now()->addMinutes(50));
    }

    /**
     * Get the access token from the cache or refresh it if necessary.
     */
    private function getToken()
    {
        if (!Cache::has(self::CACHE_KEY)) {
            $this->refreshToken();
        }

        $credentials = Cache::get(self::CACHE_KEY);

        return $credentials['access_token'];
    }

    /**
     * Refresh the OAuth token using the refresh token.
     */
    public function refreshToken()
    {
        if (!Storage::disk('local')->exists('/credentials/zoom.json')) {
            throw new Exception("Zoom credentials not found.");
        }
        $raw = Storage::disk('local')->get('/credentials/zoom.json');

        $credentials = json_decode($raw, true);

        $response = $this->client->post('/oauth/token', [
            RequestOptions::QUERY   => [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $credentials['refresh_token'],
            ],
            RequestOptions::HEADERS => [
                'Authorization' => 'Basic ' . base64_encode("{$this->clientId}:{$this->clientSecret}"),
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

    /**
     * Update the status of a user.
     */
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

    /**
     * Disable a user by deactivating their account.
     */
    public function disableUser(string $email)
    {
        return $this->updateUserStatus($email, 'deactivate');
    }

    /**
     * Get a list of users with optional status, page size, and page number.
     */
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

    /**
     * Update user information.
     */
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

    /**
     * Update user settings.
     */
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

    /**
     * Get user settings.
     */
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

    /**
     * Get user information.
     */
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