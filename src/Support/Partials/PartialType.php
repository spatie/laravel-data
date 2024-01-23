<?php

namespace Spatie\LaravelData\Support\Partials;

use Spatie\LaravelData\Support\DataClass;

enum PartialType
{
    case Include;
    case Exclude;
    case Only;
    case Except;

    public function getRequestParameterName(): string
    {
        return match ($this) {
            self::Include => 'include',
            self::Exclude => 'exclude',
            self::Only => 'only',
            self::Except => 'except',
        };
    }

    public function getVerb(): string
    {
        return match ($this) {
            self::Include => 'include',
            self::Exclude => 'exclude',
            self::Only => 'only',
            self::Except => 'except',
        };
    }

    /**
     * @return string[]|null
     */
    public function getAllowedPartials(DataClass $dataClass): ?array
    {
        return match ($this) {
            self::Include => $dataClass->allowedRequestIncludes->resolve(),
            self::Exclude => $dataClass->allowedRequestExcludes->resolve(),
            self::Only => $dataClass->allowedRequestOnly->resolve(),
            self::Except => $dataClass->allowedRequestExcept->resolve(),
        };
    }
}
