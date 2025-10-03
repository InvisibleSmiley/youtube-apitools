<?php

declare(strict_types=1);

namespace InvisibleSmiley\YouTubeApiTools;

use Google\Service\Exception as GoogleServiceException;
use Google_Service_YouTube as GoogleYouTubeService;
use Google_Service_YouTube_Playlist as Playlist;
use Google_Service_YouTube_PlaylistItem as PlaylistItem;
use Google_Service_YouTube_PlaylistItemSnippet as PlaylistItemSnippet;
use Google_Service_YouTube_PlaylistSnippet as PlaylistSnippet;
use Google_Service_YouTube_PlaylistStatus as PlaylistStatus;
use Google_Service_YouTube_ResourceId as ResourceId;

/**
 * @todo write tests
 *
 * @phpstan-type VIDEO_SPEC array{id: string, channel: array{id: string, title: string}}
 */
final class YouTubeApiClient
{
    private const int CHUNK_SIZE = 50;

    public function __construct(private readonly GoogleYouTubeService $googleYouTubeService)
    {
    }

    public static function createPlaylist(string $title, string $description): Playlist
    {
        $playlistSnippet = new PlaylistSnippet();
        $playlistSnippet->setTitle($title);
        $playlistSnippet->setDescription($description);

        $playlistStatus = new PlaylistStatus();
        $playlistStatus->setPrivacyStatus('private');

        $playlist = new Playlist();
        $playlist->setSnippet($playlistSnippet);
        $playlist->setStatus($playlistStatus);

        return $playlist;
    }


    /**
     * @throws GoogleServiceException
     */
    public function addPlaylist(Playlist $playlist): Playlist
    {
        return $this->googleYouTubeService->playlists->insert('snippet,status', $playlist);
    }

    public static function createPlaylistItem(
        string $playlistId,
        string $videoId,
        string $title
    ): PlaylistItem {
        $resourceId = new ResourceId();
        $resourceId->setVideoId($videoId);
        $resourceId->setKind('youtube#video');

        $playlistItemSnippet = new PlaylistItemSnippet();
        $playlistItemSnippet->setTitle($title);
        $playlistItemSnippet->setPlaylistId($playlistId);
        $playlistItemSnippet->setResourceId($resourceId);

        $playlistItem = new PlaylistItem();
        $playlistItem->setSnippet($playlistItemSnippet);

        return $playlistItem;
    }

    /**
     * @throws GoogleServiceException
     */
    public function addPlaylistItem(PlaylistItem $playlistItem): PlaylistItem
    {
        return $this->googleYouTubeService->playlistItems->insert('snippet,contentDetails', $playlistItem);
    }

    /**
     * @return array<Playlist>
     * @throws GoogleServiceException
     */
    public function findPlaylistsForChannel(string $channelId): array
    {
        $result = [];
        $params = [
            'channelId'  => $channelId,
            'maxResults' => self::CHUNK_SIZE
        ];
        $nextToken = null;
        do {
            $response = $this->googleYouTubeService->playlists->listPlaylists(
                'snippet,contentDetails',
                $params + [
                    'pageToken' => $nextToken
                ]
            );
            $result = [...$result, ...$response->getItems()];
            $nextToken = $response->getNextPageToken();
        } while ($nextToken !== null);

        return $result;
    }

    /**
     * @throws GoogleServiceException
     */
    public function findPlaylistIdForName(string $channelId, string $playlistName): ?string
    {
        foreach ($this->findPlaylistsForChannel($channelId) as $item) {
            if ($item->getSnippet()->getTitle() === $playlistName) {
                return $item->getId();
            }
        }

        return null;
    }

    /**
     * @return array<PlaylistItem>
     * @throws GoogleServiceException
     */
    public function listItemsForPlaylist(string $playlistId): array
    {
        $result = [];
        $params = [
            'playlistId' => $playlistId,
            'maxResults' => self::CHUNK_SIZE
        ];
        $nextToken = null;
        do {
            $response = $this->googleYouTubeService->playlistItems->listPlaylistItems(
                'snippet,contentDetails,status',
                $params + [
                    'pageToken' => $nextToken
                ]
            );
            $result = [...$result, ...$response->getItems()];
            $nextToken = $response->getNextPageToken();
        } while ($nextToken !== null);

        return $result;
    }
}
