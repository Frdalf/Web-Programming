<?php

declare(strict_types=1);

namespace FlipPlay\Models;

abstract class Media
{
    protected string $id;
    protected string $title;
    protected float $rating;
    protected array $genres;
    protected string $posterUrl;

    public function __construct(string $id, string $title, float $rating, array $genres = [], string $posterUrl = '')
    {
        $this->id = $id;
        $this->title = $title !== '' ? $title : 'Tanpa judul';
        $this->setRating($rating);
        $this->genres = $genres;
        $this->posterUrl = $posterUrl;
    }

    public function setRating(float $rating): void
    {
        if ($rating < 0 || $rating > 10) {
            throw new \InvalidArgumentException("Rating \"{$this->title}\" harus di antara 0 - 10, diberikan: $rating");
        }
        $this->rating = $rating;
    }

    public function getId(): string { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getRating(): float { return $this->rating; }
    public function getGenres(): array { return $this->genres; }
    public function getPosterUrl(): string { return $this->posterUrl; }

    public function hasAnyGenre(array $targetGenres): bool
    {
        return count(array_intersect($this->genres, $targetGenres)) > 0;
    }

    abstract public function getType(): string;
    abstract public function getLength(): int;
    abstract public function getLengthUnit(): string;
    abstract public function getBingeThreshold(): int;

    public function getSummary(): string
    {
        $length = $this->getLength() > 0 ? $this->getLength() . ' ' . $this->getLengthUnit() : '? ' . $this->getLengthUnit();
        return sprintf("[%-6s] %-6s %-38s ⭐ %.1f · %s", $this->id, $this->getType(), mb_strimwidth($this->title, 0, 36, '..'), $this->rating, $length);
    }

    public function getDetail(): string
    {
        $genres = $this->genres ? implode(', ', array_slice($this->genres, 0, 5)) : 'Tanpa genre';
        return implode(PHP_EOL, [
            "  ID      : {$this->id}",
            "  Judul   : {$this->title}",
            "  Tipe    : {$this->getType()}",
            "  Rating  : " . number_format($this->rating, 1) . " / 10",
            "  Panjang : " . ($this->getLength() ?: '?') . " {$this->getLengthUnit()}",
            "  Genre   : $genres",
            "  Poster  : " . ($this->posterUrl ?: '-'),
        ]);
    }
}
