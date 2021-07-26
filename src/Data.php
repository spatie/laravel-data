<?php

namespace Spatie\LaravelData;

use Illuminate\Contracts\Database\Eloquent\Castable as EloquentCastable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Actions\ResolveDataObjectFromArrayAction;
use Spatie\LaravelData\Actions\ResolveEmptyDataObjectAction;
use Spatie\LaravelData\Concerns\AppendableData;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\RequestableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Support\EloquentCasts\DataEloquentCast;
use Spatie\LaravelData\Support\EmptyDataResolver;
use Spatie\LaravelData\Transformers\DataTransformer;

/**
 * @method static array create()
 */
abstract class Data implements Arrayable, Responsable, Jsonable, RequestData, EloquentCastable
{
    use ResponsableData;
    use IncludeableData;
    use AppendableData;
    use RequestableData;

    public static function casts(): array
    {
        return [];
    }

    public static function collection(Collection | array | AbstractPaginator | CursorPaginator | LengthAwarePaginator $items): DataCollection
    {
        return new DataCollection(static::class, $items);
    }

    public static function empty(array $extra = []): array
    {
        return app(ResolveEmptyDataObjectAction::class)->execute(static::class, $extra);
    }

    public static function createFromArray(array $payload)
    {
        return app(ResolveDataObjectFromArrayAction::class)->execute(static::class, $payload);
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
