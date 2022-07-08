<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\Dimensions as BaseDimensions;
use Spatie\LaravelData\Exceptions\CannotBuildValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Dimensions extends ValidationAttribute
{
    protected BaseDimensions $rule;

    public function __construct(
        ?int $minWidth = null,
        ?int $minHeight = null,
        ?int $maxWidth = null,
        ?int $maxHeight = null,
        null|float|string $ratio = null,
        ?int $width = null,
        ?int $height = null,
        ?BaseDimensions $rule = null,
    ) {
        if (
            $minWidth === null
            && $minHeight === null
            && $maxWidth === null
            && $maxHeight === null
            && $ratio === null
            && $width === null
            && $height === null
            && $rule === null
        ) {
            throw CannotBuildValidationRule::create('You must specify one of width, height, minWidth, minHeight, maxWidth, maxHeight, ratio or a dimensions rule.');
        }

        $rule = $rule ?? new BaseDimensions();

        if ($minWidth !== null) {
            $rule->minWidth($minWidth);
        }

        if ($minHeight !== null) {
            $rule->minHeight($minHeight);
        }

        if ($maxWidth !== null) {
            $rule->maxWidth($maxWidth);
        }

        if ($maxHeight !== null) {
            $rule->maxHeight($maxHeight);
        }

        if ($width !== null) {
            $rule->width($width);
        }

        if ($height !== null) {
            $rule->height($height);
        }

        if ($ratio !== null) {
            $rule->ratio($ratio);
        }

        $this->rule = $rule;
    }

    public function getRules(): array
    {
        return [$this->rule];
    }

    public static function keyword(): string
    {
        return 'dimensions';
    }

    public static function create(string ...$parameters): static
    {
        return new static(
            $parameters['min_width'] ?? null,
            $parameters['min_height'] ?? null,
            $parameters['max_width'] ?? null,
            $parameters['max_height'] ?? null,
            $parameters['ratio'] ?? null,
            $parameters['width'] ?? null,
            $parameters['height'] ?? null,
        );
    }
}
