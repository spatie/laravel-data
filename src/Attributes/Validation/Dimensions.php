<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Exceptions\CannotBuildValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Dimensions implements ValidationAttribute
{
    public function __construct(
        private ?int $minWidth = null,
        private ?int $minHeight = null,
        private ?int $maxWidth = null,
        private ?int $maxHeight = null,
        private null|float|string $ratio = null
    ) {
    }

    public function getRules(): array
    {
        $parameters = collect([
            'min_width' => $this->minWidth,
            'min_height' => $this->minHeight,
            'max_width' => $this->maxWidth,
            'max_height' => $this->maxHeight,
            'ratio' => $this->ratio,
        ])
            ->filter()
            ->whenEmpty(fn() => throw CannotBuildValidationRule::create('You must specify one of minWidth, minHeight, maxWidth, maxHeight, or ratio.'))
            ->map(fn($value, string $key) => "{$key}={$value}")
            ->implode(',');

        return ["dimensions:{$parameters}"];
    }
}
