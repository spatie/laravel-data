<?php

namespace Spatie\LaravelData\Support;

class TransformationType
{
    private const FULL = 'full';
    private const REQUEST = 'request';
    private const WITHOUT_VALUE_TRANSFORMING = 'without_value_transforming';

    private function __construct(protected string $type)
    {
    }

    public static function full(): static
    {
        return new self(self::FULL);
    }

    public static function request(): static
    {
        return new self(self::REQUEST);
    }

    public static function withoutValueTransforming(): static
    {
        return new self(self::WITHOUT_VALUE_TRANSFORMING);
    }

    public function useTransformers(): bool
    {
        return match ($this->type){
            self::FULL, self::REQUEST => true,
            self::WITHOUT_VALUE_TRANSFORMING => false,
        };
    }

    public function limitIncludesAndExcludes(): bool
    {
        return match ($this->type){
            self::FULL, self::WITHOUT_VALUE_TRANSFORMING => false,
            self::REQUEST => true,
        };
    }
}
