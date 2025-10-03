<?php

namespace InvisibleSmiley\YouTubeApiTools\ValueObject;

use JsonSerializable;

interface ValueObjectInterface extends JsonSerializable
{
    public function equals(self $other): bool;

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array;
}
