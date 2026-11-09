<?php

declare(strict_types=1);

namespace FlipPlay\Models;

class Manga extends Media
{
    private int $chapterCount;

    public function __construct(string $id, string $title, float $rating, int $chapterCount, array $genres = [], string $posterUrl = '')
    {
        parent::__construct($id, $title, $rating, $genres, $posterUrl);
        $this->chapterCount = $chapterCount;
    }

    public function getType(): string { return 'Manga'; }
    public function getLength(): int { return $this->chapterCount; }
    public function getLengthUnit(): string { return 'chapter'; }
    public function getBingeThreshold(): int { return 100; }
}
