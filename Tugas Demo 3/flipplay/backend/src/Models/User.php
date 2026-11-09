<?php

declare(strict_types=1);

namespace FlipPlay\Models;

class User
{
    private string $username;
    private array $collection = [];

    public function __construct(string $username)
    {
        $this->username = $username;
    }

    public function getUsername(): string { return $this->username; }

    private function key(Media $media): string
    {
        return $media->getType() . ':' . $media->getId();
    }

    public function addToCollection(Media $media): bool
    {
        if (isset($this->collection[$this->key($media)])) {
            return false;
        }
        $this->collection[$this->key($media)] = $media;
        return true;
    }

    public function removeFromCollection(Media $media): bool
    {
        if (!isset($this->collection[$this->key($media)])) {
            return false;
        }
        unset($this->collection[$this->key($media)]);
        return true;
    }

    public function getCollection(): array
    {
        return array_values($this->collection);
    }

    public function getTotalEpisodes(): int
    {
        return array_sum(array_map(
            fn(Media $m): int => $m instanceof Anime ? $m->getLength() : 0,
            $this->collection
        ));
    }
}
