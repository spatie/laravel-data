<?php

namespace Spatie\LaravelData;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use ReflectionClass;
use Spatie\LaravelData\Concerns\AppendableData;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Support\EmptyDataResolver;
use Spatie\LaravelData\Transformers\DataTransformer;

/**
 * @method static array create()
 */
abstract class Data implements Arrayable, Responsable, Jsonable
{
    use ResponsableData;
    use IncludeableData;
    use AppendableData;

    public static function collection(Collection | array | AbstractPaginator | CursorPaginator | LengthAwarePaginator $items): DataCollection
    {
        return new DataCollection(static::class, $items);
    }

    public static function empty(array $extra = []): array
    {
        $reflection = new ReflectionClass(static::class);

        return EmptyDataResolver::create($reflection)->get($extra);
    }

    public function all(): array
    {
        return DataTransformer::create()->withoutValueTransforming()->transform($this);
    }

    public function toArray(): array
    {
        return DataTransformer::create()->transform($this);
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
