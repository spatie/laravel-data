<?php

namespace Spatie\LaravelData\Contracts;

interface RelativeRuleData
{
    public static function rules(array $payload, ?string $path): array;
}
