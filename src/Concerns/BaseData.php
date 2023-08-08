<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\DataPipeline;
use Spatie\LaravelData\DataPipes\AuthorizedDataPipe;
use Spatie\LaravelData\DataPipes\CastPropertiesDataPipe;
use Spatie\LaravelData\DataPipes\DefaultValuesDataPipe;
use Spatie\LaravelData\DataPipes\FillRouteParameterPropertiesDataPipe;
use Spatie\LaravelData\DataPipes\MapPropertiesDataPipe;
use Spatie\LaravelData\DataPipes\ValidatePropertiesDataPipe;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Resolvers\DataFromSomethingResolver;
use Spatie\LaravelData\Resolvers\EmptyDataResolver;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use Spatie\LaravelData\Transformers\DataTransformer;

trait BaseData
{
    protected static string $_collectionClass = DataCollection::class;

    protected static string $_paginatedCollectionClass = PaginatedDataCollection::class;

    protected static string $_cursorPaginatedCollectionClass = CursorPaginatedDataCollection::class;

    public static function optional(mixed ...$payloads): ?static
    {
        if (count($payloads) === 0) {
            return null;
        }

        foreach ($payloads as $payload) {
            if ($payload !== null) {
                return static::from(...$payloads);
            }
        }

        return null;
    }

    public static function from(mixed ...$payloads): static
    {
        return app(DataFromSomethingResolver::class)->execute(
            static::class,
            ...$payloads
        );
    }

    public static function withoutMagicalCreationFrom(mixed ...$payloads): static
    {
        return app(DataFromSomethingResolver::class)->withoutMagicalCreation()->execute(
            static::class,
            ...$payloads
        );
    }

    public static function normalizers(): array
    {
        return config('data.normalizers');
    }

    public static function pipeline(): DataPipeline
    {
        return DataPipeline::create()
            ->into(static::class)
            ->through(AuthorizedDataPipe::class)
            ->through(MapPropertiesDataPipe::class)
            ->through(FillRouteParameterPropertiesDataPipe::class)
            ->through(ValidatePropertiesDataPipe::class)
            ->through(DefaultValuesDataPipe::class)
            ->through(CastPropertiesDataPipe::class);
    }

    public static function collection(Enumerable|array|AbstractPaginator|Paginator|AbstractCursorPaginator|CursorPaginator|DataCollection|null $items): DataCollection|CursorPaginatedDataCollection|PaginatedDataCollection
    {
        if ($items instanceof Paginator || $items instanceof AbstractPaginator) {
            return new (static::$_paginatedCollectionClass)(static::class, $items);
        }

        if ($items instanceof AbstractCursorPaginator || $items instanceof CursorPaginator) {
            return new (static::$_cursorPaginatedCollectionClass)(static::class, $items);
        }

        return new (static::$_collectionClass)(static::class, $items);
    }

    public static function empty(array $extra = []): array
    {
        return app(EmptyDataResolver::class)->execute(static::class, $extra);
    }

    public function transform(
        bool $transformValues = true,
        WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
        bool $mapPropertyNames = true,
    ): array {
        return DataTransformer::create($transformValues, $wrapExecutionType, $mapPropertyNames)->transform($this);
    }

    public function getMorphClass(): string
    {
        /** @var class-string<\Spatie\LaravelData\Contracts\BaseData> $class */
        $class = static::class;

        return app(DataConfig::class)->morphMap->getDataClassAlias($class) ?? $class;
    }

    public function __sleep(): array
    {
        return app(DataConfig::class)->getDataClass(static::class)
            ->properties
            ->map(fn (DataProperty $property) => $property->name)
            ->push('_additional')
            ->toArray();
    }
}
