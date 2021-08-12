<?php

namespace Spatie\LaravelData;

use Illuminate\Contracts\Database\Eloquent\Castable as EloquentCastable;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Concerns\AppendableData;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\RequestableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Resolvers\DataFromArrayResolver;
use Spatie\LaravelData\Resolvers\DataFromSomethingResolver;
use Spatie\LaravelData\Resolvers\EmptyDataResolver;
use Spatie\LaravelData\Support\EloquentCasts\DataEloquentCast;
use Spatie\LaravelData\Transformers\DataTransformer;

abstract class Data implements Arrayable, Responsable, Jsonable, RequestData, EloquentCastable
{
    use ResponsableData;
    use IncludeableData;
    use AppendableData;
    use RequestableData;

    /**
     * - Maybe add support for the dto package casts?
     * - remarks freek: https://spatie.slack.com/archives/DCK4VGZ3K/p1625758265030900
     */

    public static function optional($payload): ?static
    {
        return $payload !== null ? static::from($payload) : null;
    }

    public static function from($payload): static
    {
        return app(DataFromSomethingResolver::class)->execute(static::class, $payload);
    }

    public static function fromArray(array $payload)
    {
        return app(DataFromArrayResolver::class)->execute(static::class, $payload);
    }

    public static function collection(Collection | array | AbstractPaginator | AbstractCursorPaginator | Paginator $items): DataCollection
    {
        return new DataCollection(static::class, $items);
    }

    public static function empty(array $extra = []): array
    {
        return app(EmptyDataResolver::class)->execute(static::class, $extra);
    }

    public function all(): array
    {
        return DataTransformer::create()
            ->withoutValueTransforming()
            ->transform($this);
    }

    public function toArray(): array
    {
        return DataTransformer::create()->transform($this);
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public static function castUsing(array $arguments)
    {
        return new DataEloquentCast(static::class);
    }
}
