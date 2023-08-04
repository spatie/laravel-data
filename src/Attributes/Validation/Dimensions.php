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
    protected BaseDimensions $rule;

    public function __construct(
        null|int|RouteParameterReference          $minWidth = null,
        null|int|RouteParameterReference          $minHeight = null,
        null|int|RouteParameterReference          $maxWidth = null,
        null|int|RouteParameterReference          $maxHeight = null,
        null|float|string|RouteParameterReference $ratio = null,
        null|int|RouteParameterReference          $width = null,
        null|int|RouteParameterReference          $height = null,
        null|BaseDimensions                       $rule = null,
    ) {
        $minWidth = $this->normalizePossibleRouteReferenceParameter($minWidth);
        $minHeight = $this->normalizePossibleRouteReferenceParameter($minHeight);
        $maxWidth = $this->normalizePossibleRouteReferenceParameter($maxWidth);
        $maxHeight = $this->normalizePossibleRouteReferenceParameter($maxHeight);
        $ratio = $this->normalizePossibleRouteReferenceParameter($ratio);
        $width = $this->normalizePossibleRouteReferenceParameter($width);
        $height = $this->normalizePossibleRouteReferenceParameter($height);

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

    public function getRule(ValidationPath $path): object|string
    {
        return $this->rule;
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
