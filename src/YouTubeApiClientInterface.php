<?php

declare(strict_types=1);

namespace InvisibleSmiley\YouTubeApiTools;

use Google\Service\Exception as GoogleServiceException;
use Google_Service_YouTube_Playlist as Playlist;
use Google_Service_YouTube_PlaylistItem as PlaylistItem;

interface YouTubeApiClientInterface
{
    /**
     * @throws GoogleServiceException
     */
    public function addPlaylist(Playlist $playlist): Playlist;

    /**
     * @throws GoogleServiceException
     */
    public function addPlaylistItem(PlaylistItem $playlistItem): PlaylistItem;

    /**
     * @return array<Playlist>
     * @throws GoogleServiceException
     */
    public function findPlaylistsForChannel(string $channelId): array;

    /**
     * @throws GoogleServiceException
     */
    public function findPlaylistIdForName(string $channelId, string $playlistName): ?string;

    /**
     * @return array<PlaylistItem>
     * @throws GoogleServiceException
     */
    public function listItemsForPlaylist(string $playlistId): array;
}
