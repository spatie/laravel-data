<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Support\EloquentCasts\DataEloquentCast;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;

trait TransformableData
{
    public function all(): array
    {
        return $this->transform2(TransformationContextFactory::create()->transformValues(false));
    }

    public function toArray(): array
    {
        return $this->transform2();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->transform2(), $options);
    }

    public function jsonSerialize(): array
    {
        return $this->transform2();
    }

    public static function castUsing(array $arguments)
    {
        return new DataEloquentCast(static::class);
    }
}
