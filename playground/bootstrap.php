<?php

declare(strict_types=1);

use InvisibleSmiley\YouTubeApiTools\GoogleClientHandler;
use Symfony\Component\Dotenv\Dotenv;

session_start();

$root = dirname(__DIR__);

require $root . '/vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load($root . '/.env');

$redirectUri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
$redirectUri = filter_var($redirectUri, FILTER_SANITIZE_URL);
if ($redirectUri === false) {
    die('Invalid redirect URI');
}

$googleClientHandler = new GoogleClientHandler(
    clientId: $_ENV['CLIENT_ID'],
    clientSecret: $_ENV['CLIENT_SECRET'],
    redirectUri: $redirectUri,
    caCertPath: __DIR__ . '/resource/cacert.pem'
);

$code = $_REQUEST['code'] ?? '';
$state = $_REQUEST['state'] ?? '';

if (!is_string($code)) {
    die('Invalid code');
}

if (!is_string($state)) {
    die('Invalid state');
}

$googleClientHandler->authenticate($code, $state);

return $googleClientHandler->getClient();
