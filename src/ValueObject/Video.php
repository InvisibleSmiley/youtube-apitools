<?php

namespace InvisibleSmiley\YouTubeApiTools\ValueObject;

use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;

final readonly class Video implements ValueObjectInterface
{
    private function __construct(
        private string $id,
        private string $title,
        private DateTimeInterface $publishedAt,
        private Channel $channel
    ) {
    }

    /**
     * @throws DateMalformedStringException
     */
    public static function create(string $id, string $title, string $publishedAt, Channel $channel): self
    {
        return new self($id, $title, new DateTimeImmutable($publishedAt), $channel);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPublishedAt(): DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof $this
            && $other->id === $this->id
            && $other->title === $this->title
            && $other->publishedAt === $this->publishedAt
            && $other->channel->equals($this->channel);
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'publishedAt' => $this->publishedAt,
            'channel' => $this->channel,
        ];
    }
}
