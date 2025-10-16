<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Dimensions as BaseDimensions;
use Spatie\LaravelData\Exceptions\CannotBuildValidationRule;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Dimensions extends ObjectValidationAttribute
{
    public function __construct(
        protected null|int|ExternalReference $minWidth = null,
        protected null|int|ExternalReference $minHeight = null,
        protected null|int|ExternalReference $maxWidth = null,
        protected null|int|ExternalReference $maxHeight = null,
        protected null|float|string|ExternalReference $ratio = null,
        protected null|int|ExternalReference $width = null,
        protected null|int|ExternalReference $height = null,
        protected null|BaseDimensions $rule = null,
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
    }

    public function getRule(ValidationPath $path): object|string
    {
        if ($this->rule) {
            return $this->rule;
        }

        $minWidth = $this->normalizePossibleExternalReferenceParameter($this->minWidth);
        $minHeight = $this->normalizePossibleExternalReferenceParameter($this->minHeight);
        $maxWidth = $this->normalizePossibleExternalReferenceParameter($this->maxWidth);
        $maxHeight = $this->normalizePossibleExternalReferenceParameter($this->maxHeight);
        $ratio = $this->normalizePossibleExternalReferenceParameter($this->ratio);
        $width = $this->normalizePossibleExternalReferenceParameter($this->width);
        $height = $this->normalizePossibleExternalReferenceParameter($this->height);

        $rule = new BaseDimensions();

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

        return $rule;
    }

    public static function keyword(): string
    {
        return 'dimensions';
    }

    public static function create(string ...$parameters): static
    {
        $parameters = collect($parameters)->mapWithKeys(function (string $parameter) {
            return [Str::camel(Str::before($parameter, '=')) => Str::after($parameter, '=')];
        })->all();

        return new static(...$parameters);
    }
}
