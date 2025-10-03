<?php

declare(strict_types=1);

namespace InvisibleSmiley\YouTubeApiTools;

use Google_Client as GoogleClient;
use GuzzleHttp\Client as GuzzleClient;
use SensitiveParameter;

final readonly class GoogleClientHandler
{
    private const string SCOPE_YOUTUBE = 'https://www.googleapis.com/auth/youtube';

    private GoogleClient $client;

    public function __construct(
        string $clientId,
        #[SensitiveParameter] string $clientSecret,
        private string $redirectUri,
        string $caCertPath
    ) {
        $this->client = new GoogleClient();
        $this->client->setClientId($clientId);
        $this->client->setClientSecret($clientSecret);
        $this->client->setScopes(self::SCOPE_YOUTUBE);
        $this->client->setRedirectUri($redirectUri);
        $this->client->setHttpClient(new GuzzleClient(['verify' => $caCertPath]));
    }

    public function authenticate(string $code, string $state): void
    {
        // Check if an auth token exists for the required scopes
        $tokenSessionKey = 'token-' . $this->client->prepareScopes();
        if ($code !== '') {
            if ($_SESSION['state'] !== $state) {
                die('The session state did not match.');
            }

            $this->client->fetchAccessTokenWithAuthCode($code);
            $_SESSION[$tokenSessionKey] = $this->client->getAccessToken();
            header('Location: ' . $this->redirectUri);
            exit;
        }

        if (isset($_SESSION[$tokenSessionKey])) {
            $this->client->setAccessToken($_SESSION[$tokenSessionKey]);
        }

        if (!$this->client->getAccessToken() || $this->client->isAccessTokenExpired()) {
            // If the user hasn't authorized the app, initiate the OAuth flow
            $state = (string) mt_rand();
            $this->client->setState($state);
            $_SESSION['state'] = $state;
            $authUrl = $this->client->createAuthUrl();
            header('Location: ' . $authUrl);
            exit;
        }
    }

    public function getClient(): GoogleClient
    {
        return $this->client;
    }
}
