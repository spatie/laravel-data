<?php

namespace Spatie\LaravelData;

use Illuminate\Contracts\Database\Eloquent\Castable as EloquentCastable;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Enumerable;
use JsonSerializable;
use Spatie\LaravelData\Concerns\AppendableData;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Concerns\ValidateableData;
use Spatie\LaravelData\Resolvers\DataFromSomethingResolver;
use Spatie\LaravelData\Resolvers\EmptyDataResolver;
use Spatie\LaravelData\Serializers\ArraySerializer;
use Spatie\LaravelData\Serializers\MagicMethodSerializer;
use Spatie\LaravelData\Serializers\ModelSerializer;
use Spatie\LaravelData\Serializers\RequestSerializer;
use Spatie\LaravelData\Support\EloquentCasts\DataEloquentCast;
use Spatie\LaravelData\Support\TransformationType;
use Spatie\LaravelData\Transformers\DataTransformer;

abstract class Data implements Arrayable, Responsable, Jsonable, EloquentCastable, JsonSerializable
{
    use ResponsableData;
    use IncludeableData;
    use AppendableData;
    use ValidateableData;

    public static function optional($payload): ?static
    {
        return $payload === null
            ? null
            : static::from($payload);
    }

    public static function from($payload): static
    {
        return app(DataFromSomethingResolver::class)->execute(
            static::class,
            $payload
        );
    }

    public static function collection(Enumerable|array|AbstractPaginator|AbstractCursorPaginator|Paginator $items): DataCollection
    {
        return new DataCollection(static::class, $items);
    }

    public static function empty(array $extra = []): array
    {
        return app(EmptyDataResolver::class)->execute(static::class, $extra);
    }

    public function transform(TransformationType $type): array
    {
        return DataTransformer::create($type)->transform($this);
    }

    public function all(): array
    {
        return $this->transform(TransformationType::withoutValueTransforming());
    }

    public function toArray(): array
    {
        return $this->transform(TransformationType::full());
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public static function castUsing(array $arguments)
    {
        return new DataEloquentCast(static::class);
    }

    public static function serializers(): array
    {
        return [
            RequestSerializer::class,
            MagicMethodSerializer::class,
            ModelSerializer::class,
            ArraySerializer::class
        ];
    }
}
