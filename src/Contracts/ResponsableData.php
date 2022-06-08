<?php

namespace Spatie\LaravelData\Contracts;

interface ResponsableData
{
    public static function allowedRequestIncludes(): ?array;

    public static function allowedRequestExcludes(): ?array;

    public static function allowedRequestOnly(): ?array;

    public static function allowedRequestExcept(): ?array;
}
