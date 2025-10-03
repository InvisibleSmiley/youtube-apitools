<?php

namespace InvisibleSmiley\YouTubeApiTools;

use InvisibleSmiley\YouTubeApiTools\ValueObject\Video;
use Ramsey\Collection\Collection;
use RuntimeException;

interface YouTubeApiServiceInterface
{
    public function findPlaylistIdForName(string $channelId, string $playlistName): ?string;

    /**
     * @return Collection<Video>
     * @throws RuntimeException
     */
    public function listVideosForPlaylist(string $playlistId): Collection;
}
