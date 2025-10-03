<?php

declare(strict_types=1);

namespace InvisibleSmiley\YouTubeApiTools;

use Google\Service\Exception as GoogleServiceException;
use InvisibleSmiley\YouTubeApiTools\ValueObject\Channel;
use InvisibleSmiley\YouTubeApiTools\ValueObject\Video;
use Ramsey\Collection\Collection;
use RuntimeException;

final readonly class YouTubeApiService implements YouTubeApiServiceInterface
{
    public function __construct(private YouTubeApiClientInterface $apiClient)
    {
    }

    public function findPlaylistIdForName(string $channelId, string $playlistName): ?string
    {
        try {
            return $this->apiClient->findPlaylistIdForName($channelId, $playlistName);
        } catch (GoogleServiceException $e) {
            throw new RuntimeException('Unable to determine playlist ID', previous: $e);
        }
    }

    public function listVideosForPlaylist(string $playlistId): Collection
    {
        $result = new Collection(Video::class);

        try {
            $playlistItems = $this->apiClient->listItemsForPlaylist($playlistId);
        } catch (GoogleServiceException $e) {
            throw new RuntimeException('Unable to determine playlist items', previous: $e);
        }

        foreach ($playlistItems as $playlistItem) {
            if ($playlistItem->getStatus()->getPrivacyStatus() === 'private') {
                continue;
            }

            $contentDetails = $playlistItem->getContentDetails();
            $videoId = $contentDetails->getVideoId();
            $videoPublishedAt = $contentDetails->getVideoPublishedAt();

            $snippet = $playlistItem->getSnippet();
            $videoTitle = $snippet->getTitle();
            $channelid = $snippet->getVideoOwnerChannelId();
            $channelTitle = $snippet->getVideoOwnerChannelTitle();

            $result->add(
                Video::create(
                    $videoId,
                    $videoTitle,
                    $videoPublishedAt,
                    Channel::create($channelid, $channelTitle)
                )
            );
        }

        return $result;
    }
}
