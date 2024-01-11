<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Contracts\Pagination\CursorPaginator as CursorPaginatorContract;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;
use Spatie\LaravelData\Contracts\BaseData as BaseDataContract;
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
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;

trait BaseData
{
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

    public static function from(mixed ...$payloads): BaseDataContract
    {
        return static::factory()->from(...$payloads);
    }

    public static function collect(mixed $items, ?string $into = null): array|DataCollection|PaginatedDataCollection|CursorPaginatedDataCollection|Enumerable|AbstractPaginator|PaginatorContract|AbstractCursorPaginator|CursorPaginatorContract|LazyCollection|Collection
    {
        return static::factory()->collect($items, $into);
    }

    public static function factory(?CreationContext $creationContext = null): CreationContextFactory|CreationContext
    {
        if ($creationContext) {
            $creationContext->dataClass = static::class;

            return $creationContext;
        }

        return CreationContextFactory::createFromConfig(static::class);
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

    public static function prepareForPipeline(Collection $properties): Collection
    {
        return $properties;
    }

    public function getMorphClass(): string
    {
        /** @var class-string<BaseDataContract> $class */
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
