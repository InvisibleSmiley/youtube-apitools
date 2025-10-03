<?php

namespace InvisibleSmiley\YouTubeApiTools\ValueObject;

final readonly class Channel implements ValueObjectInterface
{
    private function __construct(private string $id, private string $title)
    {
    }

    public static function create(string $id, string $title): self
    {
        return new self($id ,$title);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }


    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof $this
            && $other->id === $this->id
            && $other->title === $this->title;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
        ];
    }
}
