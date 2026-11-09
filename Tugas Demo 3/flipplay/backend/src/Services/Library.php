<?php

declare(strict_types=1);

namespace FlipPlay\Services;

use FlipPlay\Auras\Aura;
use FlipPlay\Models\Media;

class Library
{
    private array $medias = [];
    private array $auras = [];

    public function addMedia(Media $media): void
    {
        $this->medias[$media->getType() . ':' . $media->getId()] = $media;
    }

    public function addMany(array $medias): int
    {
        foreach ($medias as $media) {
            $this->addMedia($media);
        }
        return count($medias);
    }

    public function registerAura(Aura $aura): void
    {
        $this->auras[$aura->getId()] = $aura;
    }

    public function getAuras(): array
    {
        return array_values($this->auras);
    }

    public function getAll(): array
    {
        return array_values($this->medias);
    }

    public function count(): int
    {
        return count($this->medias);
    }

    public function getByType(string $type): array
    {
        return array_values(array_filter($this->medias, fn(Media $m): bool => $m->getType() === $type));
    }

    public function findByTitle(string $title): ?Media
    {
        foreach ($this->medias as $media) {
            if (strcasecmp($media->getTitle(), $title) === 0) {
                return $media;
            }
        }
        return null;
    }

    public function search(string $keyword = '', string $auraId = 'all'): array
    {
        $aura = $this->auras[$auraId] ?? null;
        if ($aura === null) {
            throw new \InvalidArgumentException("Aura \"$auraId\" tidak dikenal.");
        }

        $keyword = strtolower(trim($keyword));

        return array_values(array_filter(
            $this->medias,
            fn(Media $m): bool => str_contains(strtolower($m->getTitle()), $keyword) && $aura->matches($m)
        ));
    }

    public function getTopRated(int $limit = 3): array
    {
        $list = $this->getAll();
        usort($list, fn(Media $a, Media $b): int => $b->getRating() <=> $a->getRating());
        return array_slice($list, 0, $limit);
    }
}
