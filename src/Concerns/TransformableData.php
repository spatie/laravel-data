<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Support\EloquentCasts\DataEloquentCast;

trait TransformableData
{
    public function all(): array
    {
        return $this->transform(transformValues: false);
    }

    public function toArray(): array
    {
        return $this->transform();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->transform(), $options);
    }

    public function jsonSerialize(): array
    {
        return $this->transform();
    }

    public static function castUsing(array $arguments)
    {
        return new DataEloquentCast(static::class, $arguments);
    }
}
