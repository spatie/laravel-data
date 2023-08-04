<?php

namespace Spatie\LaravelData\Contracts;

use Illuminate\Contracts\Database\Eloquent\Castable as EloquentCastable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;

interface TransformableData extends JsonSerializable, Jsonable, Arrayable, EloquentCastable
{
    public function all(): array;

    public function toArray(): array;

    public function toJson($options = 0): string;

    public function jsonSerialize(): array;

    public function transform(
        bool $transformValues = true,
        WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
        bool $mapPropertyNames = true,
    ): array;

    public static function castUsing(array $arguments);
}
