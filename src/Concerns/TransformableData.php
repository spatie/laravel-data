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
        return $this->transform(context: 'array');
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->transform(context: 'json'), $options);
    }

    public function toEloquent(): string
    {
        return json_encode($this->transform(context: 'eloquent'));
    }

    public function jsonSerialize(): array
    {
        return $this->transform(context: 'json');
    }

    public static function castUsing(array $arguments)
    {
        return new DataEloquentCast(static::class, $arguments);
    }
}
