<?php

declare(strict_types=1);

namespace FlipPlay\Auras;

use FlipPlay\Models\Media;

class AllAura extends Aura
{
    public function __construct()
    {
        parent::__construct('all', 'Semua');
    }

    public function matches(Media $media): bool
    {
        return true;
    }
}
