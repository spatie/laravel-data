<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use LogicException;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Dimensions implements ValidationAttribute
{
    public function __construct(
        private ?int $minWidth = null,
        private ?int $minHeight = null,
        private ?int $maxWidth = null,
        private ?int $maxHeight = null,
        private null | float | string $ratio = null
    ) {
        //
    }

    public function getRules(): array
    {
        return ['dimensions:' . implode(',', $this->getParameters())];
    }

    private function getParameters(): array
    {
        // Validate the options we were given.
        $options = array_filter(
            [
                'min_width' => $this->minWidth,
                'min_height' => $this->minHeight,
                'max_width' => $this->maxWidth,
                'max_height' => $this->maxHeight,
                'ratio' => $this->ratio,
            ],
            fn ($value) => ! is_null($value)
        );

        if (empty($options)) {
            throw new LogicException(
                '['. static::class .'] You must specify one of minWidth, minHeight, maxWidth, maxHeight, or ratio.'
            );
        }

        // Create parameters out of the set options.
        $parameters = [];

        foreach ($options as $key => $value) {
            $parameters[] = "{$key}={$value}";
        }

        return $parameters;
    }
}
