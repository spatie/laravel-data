<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\Dimensions as BaseDimensions;
use Spatie\LaravelData\Exceptions\CannotBuildValidationRule;
use Spatie\LaravelData\Support\Validation\Rules\FoundationDimensions;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Dimensions extends FoundationDimensions
{

    public function __construct(
        ?int $minWidth = null,
        ?int $minHeight = null,
        ?int $maxWidth = null,
        ?int $maxHeight = null,
        null|float|string $ratio = null,
        ?int $width = null,
        ?int $height = null,
    ) {
        $rule = new BaseDimensions();

        if (
            $minWidth === null
            && $minHeight === null
            && $maxWidth === null
            && $maxHeight === null
            && $ratio === null
            && $width === null
            && $height === null
        ) {
            throw CannotBuildValidationRule::create('You must specify one of width, height, minWidth, minHeight, maxWidth, maxHeight, or ratio.');
        }

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

        parent::__construct($rule);
    }
}
