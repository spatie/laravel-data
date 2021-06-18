<?php

namespace Spatie\LaravelData;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use ReflectionClass;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Support\EmptyDataResolver;
use Spatie\LaravelData\Transformers\DataTransformer;

/**
 * @method static array create()
 */
abstract class Data implements Arrayable, Responsable
{
    use ResponsableData, IncludeableData;

    public static function collection(Collection|array|LengthAwarePaginator $items): DataCollection|PaginatedDataCollection
    {
        if ($items instanceof LengthAwarePaginator) {
            return new PaginatedDataCollection(static::class, $items);
        }

        return new DataCollection(static::class, $items);
    }

    public function endpoints(): array
    {
        return [];
    }

    public function append(): array
    {
        $endpoints = $this->endpoints();

        if (count($endpoints) === 0) {
            return [];
        }

        return [
            'endpoints' => $endpoints,
        ];
    }

    public function all(): array
    {
        // TODO: reimplement this obv `toArray`
    }

    public function toArray(): array
    {
        return DataTransformer::create()->transform($this);
    }

    public static function empty(array $extra = []): array
    {
        $reflection = new ReflectionClass(static::class);

        return EmptyDataResolver::create($reflection)->get($extra);
    }
}
