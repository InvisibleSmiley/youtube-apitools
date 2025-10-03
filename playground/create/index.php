<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Google_Service_YouTube as GoogleYouTubeService;
use InvisibleSmiley\YouTubeApiTools\YouTubeApiClient;

$googleClient = require dirname(__DIR__) . '/bootstrap.php';
$googleYouTubeService = new GoogleYouTubeService($googleClient);
$apiClient = new YouTubeApiClient($googleYouTubeService);

echo <<<EOT
<html lang="en">
<head>
    <title>YouTube Playlist Creation</title>
</head>
<body>
EOT;

[$playlistData, $videoData] = require __DIR__ . '/config.php';

[$playlistTitle, $playlistDescription] = $playlistData;
$playlist = YouTubeApiClient::createPlaylist($playlistTitle, $playlistDescription);
$playlist = $apiClient->addPlaylist($playlist);
$playlistId = $playlist->getId();
printf('<p>Added playlist %s (%s).</p>', $playlist->getSnippet()->getTitle(), $playlistId);

[$videoId, $videoDescription] = $videoData;
$playlistItem = YouTubeApiClient::createPlaylistItem($playlistId, $videoId, $videoDescription);
$playlistItem = $apiClient->addPlaylistItem($playlistItem);
$playlistItemId = $playlist->getId();
printf('<p>Added playlist item %s (%s).</p>', $playlistItem->getSnippet()->getTitle(), $playlistItemId);

echo <<<EOT
</body>
</html>
EOT;
