<?php

declare(strict_types=1);

namespace InvisibleSmiley\YouTubeApiTools\Test\Unit;

use DateMalformedStringException;
use Google\Service\Exception as GoogleServiceException;
use Google\Service\YouTube\PlaylistItem;
use Google\Service\YouTube\PlaylistItemContentDetails;
use Google\Service\YouTube\PlaylistItemSnippet;
use Google\Service\YouTube\PlaylistItemStatus;
use InvisibleSmiley\YouTubeApiTools\ValueObject\Channel;
use InvisibleSmiley\YouTubeApiTools\ValueObject\Video;
use InvisibleSmiley\YouTubeApiTools\YouTubeApiClientInterface;
use InvisibleSmiley\YouTubeApiTools\YouTubeApiService;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(YouTubeApiService::class)]
#[UsesClass(Channel::class)]
#[UsesClass(Video::class)]
final class YouTubeApiServiceTest extends TestCase
{
    private const string TEST_CHANNEL_ID = 'test_channel_id';
    private const string TEST_PLAYLIST_ID = 'test_playlist_id';
    private const string TEST_PLAYLIST_NAME = 'My Playlist';

    private YouTubeApiClientInterface&MockObject $apiClient;

    private YouTubeApiService $service;

    #[Override]
    protected function setUp(): void
    {
        $this->apiClient = $this->createMock(YouTubeApiClientInterface::class);
        $this->service = new YouTubeApiService($this->apiClient);
    }

    public function testFindPlaylistIdForNameReturnsPlaylistId(): void
    {
        $this->apiClient
            ->expects($this->once())
            ->method('findPlaylistIdForName')
            ->with(self::TEST_CHANNEL_ID, self::TEST_PLAYLIST_NAME)
            ->willReturn(self::TEST_PLAYLIST_ID);

        $result = $this->service->findPlaylistIdForName(self::TEST_CHANNEL_ID, self::TEST_PLAYLIST_NAME);

        self::assertSame(self::TEST_PLAYLIST_ID, $result);
    }

    public function testFindPlaylistIdForNameReturnsNullWhenNotFound(): void
    {
        $this->apiClient
            ->expects($this->once())
            ->method('findPlaylistIdForName')
            ->with(self::TEST_CHANNEL_ID, self::TEST_PLAYLIST_NAME)
            ->willReturn(null);

        $result = $this->service->findPlaylistIdForName(self::TEST_CHANNEL_ID, self::TEST_PLAYLIST_NAME);

        self::assertNull($result);
    }

    public function testFindPlaylistIdForNameThrowsException(): void
    {
        $this->apiClient
            ->expects($this->once())
            ->method('findPlaylistIdForName')
            ->with(self::TEST_CHANNEL_ID, self::TEST_PLAYLIST_NAME)
            ->willThrowException(new GoogleServiceException('API error'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to determine playlist ID');

        $this->service->findPlaylistIdForName(self::TEST_CHANNEL_ID, self::TEST_PLAYLIST_NAME);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testListVideosForPlaylistReturnsVideos(): void
    {
        $playlistItem1 = $this->createPlaylistItemMock(
            'Video_ID_1',
            'Video Title 1',
            '2024-01-01T10:00:00Z',
            'Channel_ID_1',
            'Channel Title 1',
            'public'
        );

        $playlistItem2 = $this->createPlaylistItemMock(
            'Video_ID_2',
            'Video Title 2',
            '2024-01-02T12:00:00Z',
            'Channel_ID_2',
            'Channel Title 2',
            'public'
        );

        $this->apiClient
            ->expects($this->once())
            ->method('listItemsForPlaylist')
            ->with(self::TEST_PLAYLIST_ID)
            ->willReturn([$playlistItem1, $playlistItem2]);

        $result = $this->service->listVideosForPlaylist(self::TEST_PLAYLIST_ID);

        self::assertCount(2, $result);

        self::assertEquals(
            [
                Video::create(
                    'Video_ID_1',
                    'Video Title 1',
                    '2024-01-01T10:00:00Z',
                    Channel::create('Channel_ID_1', 'Channel Title 1')
                ),
                Video::create(
                    'Video_ID_2',
                    'Video Title 2',
                    '2024-01-02T12:00:00Z',
                    Channel::create('Channel_ID_2', 'Channel Title 2')
                ),
            ],
            $result->toArray()
        );
    }

    public function testListVideosForPlaylistFiltersPrivateVideos(): void
    {
        $publicItem = $this->createPlaylistItemMock(
            'Video_ID_public',
            'Public Video',
            '2024-01-01T10:00:00Z',
            'Channel_ID_1',
            'Channel Title 1',
            'public'
        );

        $privateItem = $this->createPlaylistItemMock(
            'Video_ID_private',
            'Private Video',
            '2024-01-02T12:00:00Z',
            'Channel_ID_2',
            'Channel Title 2',
            'private'
        );

        $this->apiClient
            ->expects($this->once())
            ->method('listItemsForPlaylist')
            ->with(self::TEST_PLAYLIST_ID)
            ->willReturn([$publicItem, $privateItem]);

        $result = $this->service->listVideosForPlaylist(self::TEST_PLAYLIST_ID);

        self::assertCount(1, $result);
    }

    public function testListVideosForPlaylistHandlesEmptyPlaylist(): void
    {
        $this->apiClient
            ->expects($this->once())
            ->method('listItemsForPlaylist')
            ->with(self::TEST_PLAYLIST_ID)
            ->willReturn([]);

        $result = $this->service->listVideosForPlaylist(self::TEST_PLAYLIST_ID);

        self::assertCount(0, $result);
    }

    public function testListVideosForPlaylistThrowsException(): void
    {
        $googleException = new GoogleServiceException('API error');

        $this->apiClient
            ->expects($this->once())
            ->method('listItemsForPlaylist')
            ->with(self::TEST_PLAYLIST_ID)
            ->willThrowException($googleException);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to determine playlist items');

        $this->service->listVideosForPlaylist(self::TEST_PLAYLIST_ID);
    }

    private function createPlaylistItemMock(
        string $videoId,
        string $videoTitle,
        string $videoPublishedAt,
        string $channelId,
        string $channelTitle,
        string $privacyStatus
    ): PlaylistItem&MockObject {
        $contentDetails = $this->createMock(PlaylistItemContentDetails::class);
        $contentDetails->method('getVideoId')->willReturn($videoId);
        $contentDetails->method('getVideoPublishedAt')->willReturn($videoPublishedAt);

        $snippet = $this->createMock(PlaylistItemSnippet::class);
        $snippet->method('getTitle')->willReturn($videoTitle);
        $snippet->method('getVideoOwnerChannelId')->willReturn($channelId);
        $snippet->method('getVideoOwnerChannelTitle')->willReturn($channelTitle);

        $status = $this->createMock(PlaylistItemStatus::class);
        $status->method('getPrivacyStatus')->willReturn($privacyStatus);

        $playlistItem = $this->createMock(PlaylistItem::class);
        $playlistItem->method('getContentDetails')->willReturn($contentDetails);
        $playlistItem->method('getSnippet')->willReturn($snippet);
        $playlistItem->method('getStatus')->willReturn($status);

        return $playlistItem;
    }
}
