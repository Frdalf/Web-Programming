<?php

declare(strict_types=1);

namespace FlipPlay\Auras;

use FlipPlay\Models\Media;

class GenreAura extends Aura
{
    private array $genres;

    public function __construct(string $id, string $label, array $genres)
    {
        parent::__construct($id, $label);
        $this->genres = $genres;
    }

    public function matches(Media $media): bool
    {
        return $media->hasAnyGenre($this->genres);
    }
}
