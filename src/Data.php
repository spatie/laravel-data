<?php

namespace Spatie\LaravelData;

use Exception;
use Illuminate\Contracts\Database\Eloquent\Castable as EloquentCastable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Resolvers\DataFromArrayResolver;
use Spatie\LaravelData\Resolvers\EmptyDataResolver;
use Spatie\LaravelData\Concerns\AppendableData;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\RequestableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Support\EloquentCasts\DataEloquentCast;
use Spatie\LaravelData\Transformers\DataTransformer;

abstract class Data implements Arrayable, Responsable, Jsonable, RequestData, EloquentCastable
{
    use ResponsableData;
    use IncludeableData;
    use AppendableData;
    use RequestableData;

    /**
     * - remove casts method
     * - rename create in data to from and try to find a correct function via reflection
     * - Maybe add support for the dto package casts?
     * - Check global casts based upon interface and not strictly class
     * - add support for authorization in requestabledata
     * - add a lot of better exceptions
     * - optional https://spatie.slack.com/archives/G011TEW1NEQ/p1627478861007300
     * - remarks freek: https://spatie.slack.com/archives/DCK4VGZ3K/p1625758265030900
     */

    public static function casts(): array
    {
        return [];
    }

    public static function create($payload): static
    {
        if ($payload instanceof Model) {
            return static::createFromModel($payload);
        }

        if ($payload instanceof Request) {
            return static::createFromRequest($payload);
        }

        if ($payload instanceof Arrayable) {
            return static::createFromArray($payload->toArray());
        }

        if (is_array($payload)) {
            return static::createFromArray($payload);
        }

        throw new Exception("Could not create Data object");
    }

    public static function createFromModel(Model $model): Data
    {
        return static::createFromArray($model->toArray());
    }

    public static function createFromArray(array $payload)
    {
        return app(DataFromArrayResolver::class)->execute(static::class, $payload);
    }

    public static function collection(Collection|array|AbstractPaginator|AbstractCursorPaginator|Paginator $items): DataCollection
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
