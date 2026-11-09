<?php

declare(strict_types=1);

namespace FlipPlay\Auras;

use FlipPlay\Models\Media;

abstract class Aura
{
    protected string $id;
    protected string $label;

    public function __construct(string $id, string $label)
    {
        $this->id = $id;
        $this->label = $label;
    }

    public function getId(): string { return $this->id; }
    public function getLabel(): string { return $this->label; }

    abstract public function matches(Media $media): bool;
}
