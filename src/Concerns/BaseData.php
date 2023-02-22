<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\Contracts\IncludeableData as IncludeableDataContract;
use Spatie\LaravelData\Contracts\WrappableData as WrappableDataContract;
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
use Spatie\LaravelData\Resolvers\DataCollectableFromSomethingResolver;
use Spatie\LaravelData\Resolvers\DataFromSomethingResolver;
use Spatie\LaravelData\Resolvers\EmptyDataResolver;
use Spatie\LaravelData\Resolvers\TransformedDataResolver;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Partials\PartialsDefinition;
use Spatie\LaravelData\Support\Transformation\DataContext;
use Spatie\LaravelData\Support\Transformation\PartialTransformationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;
use Spatie\LaravelData\Support\Wrapping\Wrap;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use Spatie\LaravelData\Support\Wrapping\WrapType;

trait BaseData
{
    protected ?DataContext $_dataContext = null;

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

    public static function collect(mixed $items, ?string $into = null): array|DataCollection|PaginatedDataCollection|CursorPaginatedDataCollection|Enumerable|AbstractPaginator|Paginator|AbstractCursorPaginator|CursorPaginator
    {
        return app(DataCollectableFromSomethingResolver::class)->execute(
            static::class,
            $items,
            $into
        );
    }

    public static function withoutMagicalCreationCollect(mixed $items, ?string $into = null): array|DataCollection|PaginatedDataCollection|CursorPaginatedDataCollection|Enumerable|AbstractPaginator|Paginator|AbstractCursorPaginator|CursorPaginator
    {
        return app(DataCollectableFromSomethingResolver::class)->withoutMagicalCreation()->execute(
            static::class,
            $items,
            $into
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

    public static function prepareForPipeline(Collection $properties): Collection
    {
        return $properties;
    }

    public function __sleep(): array
    {
        return app(DataConfig::class)->getDataClass(static::class)
            ->properties
            ->map(fn(DataProperty $property) => $property->name)
            ->push('_additional')
            ->toArray();
    }

    public function getDataContext(): DataContext
    {
        if ($this->_dataContext === null) {
            return $this->_dataContext = new DataContext(
                new PartialsDefinition(
                    $this instanceof IncludeableDataContract ? $this->includeProperties() : [],
                    $this instanceof IncludeableDataContract ? $this->excludeProperties() : [],
                    $this instanceof IncludeableDataContract ? $this->onlyProperties() : [],
                    $this instanceof IncludeableDataContract ? $this->exceptProperties() : [],
                ),
                $this instanceof WrappableDataContract ? $this->getWrap() : new Wrap(WrapType::UseGlobal),
            );
        }

        return $this->_dataContext;
    }
}
