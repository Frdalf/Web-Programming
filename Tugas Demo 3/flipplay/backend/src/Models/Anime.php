<?php

declare(strict_types=1);

namespace FlipPlay\Models;

class Anime extends Media
{
    private int $episodeCount;

    public function __construct(string $id, string $title, float $rating, int $episodeCount, array $genres = [], string $posterUrl = '')
    {
        parent::__construct($id, $title, $rating, $genres, $posterUrl);
        $this->episodeCount = $episodeCount;
    }

    public function getType(): string { return 'Anime'; }
    public function getLength(): int { return $this->episodeCount; }
    public function getLengthUnit(): string { return 'episode'; }
    public function getBingeThreshold(): int { return 24; }
}
