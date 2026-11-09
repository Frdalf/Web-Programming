<?php

declare(strict_types=1);

namespace FlipPlay\Auras;

use FlipPlay\Models\Media;

class BingeAura extends Aura
{
    public function __construct()
    {
        parent::__construct('binge', 'Binge Mode');
    }

    public function matches(Media $media): bool
    {
        return $media->getLength() > $media->getBingeThreshold();
    }
}
