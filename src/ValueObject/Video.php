<?php

namespace InvisibleSmiley\YouTubeApiTools\ValueObject;

final readonly class Video implements ValueObjectInterface
{
    private function __construct(
        private string $id,
        private string $title,
        private string $publishedAt,
        private Channel $channel
    ) {
    }

    public static function create(string $id, string $title, string $publishedAt, Channel $channel): self
    {
        return new self($id, $title, $publishedAt, $channel);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPublishedAt(): string
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
