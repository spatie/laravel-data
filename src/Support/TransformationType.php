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

    public static function full(): TransformationType
    {
        return new self(self::FULL);
    }

    public static function request(): TransformationType
    {
        return new self(self::REQUEST);
    }

    public static function withoutValueTransforming(): TransformationType
    {
        return new self(self::WITHOUT_VALUE_TRANSFORMING);
    }

    public function useTransformers(): bool
    {
        return $this->type !== self::WITHOUT_VALUE_TRANSFORMING;
    }

    public function limitIncludesAndExcludes(): bool
    {
        return $this->type === self::REQUEST;
    }
}
