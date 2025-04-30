<?php

namespace Spatie\LaravelData\Contracts;

use Illuminate\Contracts\Database\Eloquent\Castable as EloquentCastable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;

interface TransformableData extends JsonSerializable, Jsonable, Arrayable, EloquentCastable
{
    public function transform(
        null|TransformationContextFactory|TransformationContext $transformationContext = null,
    ): array;

    public function all(): array;

    public function toArray(): array;

    public function toJson($options = 0): string;

    public function jsonSerialize(): array;

    public function jsonSerializeWithTransformationContext(null|TransformationContextFactory|TransformationContext $transformationContext): array;

    public static function castUsing(array $arguments);
}
