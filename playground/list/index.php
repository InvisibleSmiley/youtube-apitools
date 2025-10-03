<?php

declare(strict_types=1);

use Google_Service_YouTube as GoogleYouTubeService;
use InvisibleSmiley\YouTubeApiTools\ValueObject\Video;
use InvisibleSmiley\YouTubeApiTools\YouTubeApiService;
use InvisibleSmiley\YouTubeApiTools\YouTubeApiClient;

$googleClient = require dirname(__DIR__) . '/bootstrap.php';
$googleYouTubeService = new GoogleYouTubeService($googleClient);
$apiClient = new YouTubeApiClient($googleYouTubeService);
$apiService = new YouTubeApiService($apiClient);

echo <<<EOT
<html lang="en">
<head>
    <title>YouTube Playlist Overview</title>
</head>
<body>
EOT;

[$playlistName, $targetMapping] = require(__DIR__ . '/config.php');
$playlistId = $apiService->findPlaylistIdForName($_ENV['CHANNEL_ID'], $playlistName);
if ($playlistId !== null) {
    $videos = $apiService->listVideosForPlaylist($playlistId);
    if (!$videos->isEmpty()) {
        echo '<ul>';
    }

    $lastTarget = null;
    /** @var Video $video */
    foreach ($videos as $video) {
        $channelTitle = trim($video->getChannel()->getTitle());
        if (!isset($targetMapping[$channelTitle])) {
            $channelTitle = '<span style="color: darkred;">' . $channelTitle . '</span>';
        }

        $videoTitle = trim($video->getTitle());
        $target = $targetMapping[$channelTitle] ?? null;
        if ($target !== $lastTarget) {
            $videoTitle = '<span style="color: red;">' . $videoTitle . '</span>';
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        print sprintf(
            '<li>%s (channel: %s, published %s)</li>',
            $videoTitle,
            $channelTitle,
            new DateTime($video->getPublishedAt())->format('Y-m-d')
        );

        $lastTarget = $target;
    }

    if (!$videos->isEmpty()) {
        echo '</ul>';
    }
}

echo <<<EOT
</body>
</html>
EOT;
