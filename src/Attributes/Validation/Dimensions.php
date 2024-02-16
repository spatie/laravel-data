<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Dimensions as BaseDimensions;
use Spatie\LaravelData\Exceptions\CannotBuildValidationRule;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Dimensions extends ObjectValidationAttribute
{
    public function __construct(
        protected null|int|RouteParameterReference $minWidth = null,
        protected null|int|RouteParameterReference $minHeight = null,
        protected null|int|RouteParameterReference $maxWidth = null,
        protected null|int|RouteParameterReference $maxHeight = null,
        protected null|float|string|RouteParameterReference $ratio = null,
        protected null|int|RouteParameterReference $width = null,
        protected null|int|RouteParameterReference $height = null,
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
        if($this->rule) {
            return $this->rule;
        }

        $minWidth = $this->normalizePossibleRouteReferenceParameter($this->minWidth);
        $minHeight = $this->normalizePossibleRouteReferenceParameter($this->minHeight);
        $maxWidth = $this->normalizePossibleRouteReferenceParameter($this->maxWidth);
        $maxHeight = $this->normalizePossibleRouteReferenceParameter($this->maxHeight);
        $ratio = $this->normalizePossibleRouteReferenceParameter($this->ratio);
        $width = $this->normalizePossibleRouteReferenceParameter($this->width);
        $height = $this->normalizePossibleRouteReferenceParameter($this->height);

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

        return $this->rule = $rule;
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
