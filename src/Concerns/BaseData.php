<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\DataPipeline;
use Spatie\LaravelData\DataPipes\AuthorizedDataPipe;
use Spatie\LaravelData\DataPipes\CastPropertiesDataPipe;
use Spatie\LaravelData\DataPipes\DefaultValuesDataPipe;
use Spatie\LaravelData\DataPipes\MapPropertiesDataPipe;
use Spatie\LaravelData\DataPipes\ValidatePropertiesDataPipe;
use Spatie\LaravelData\Normalizers\ArraybleNormalizer;
use Spatie\LaravelData\Normalizers\ArrayNormalizer;
use Spatie\LaravelData\Normalizers\ModelNormalizer;
use Spatie\LaravelData\Normalizers\ObjectNormalizer;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Resolvers\DataFromSomethingResolver;
use Spatie\LaravelData\Resolvers\EmptyDataResolver;
use Spatie\LaravelData\Support\EloquentCasts\DataEloquentCast;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use Spatie\LaravelData\Transformers\DataTransformer;

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

    public static function from(mixed ...$payloads): static
    {
        return app(DataFromSomethingResolver::class)->execute(
            static::class,
            ...$payloads
        );
    }

    public static function normalizers(): array
    {
        return [
            ModelNormalizer::class,
            ArraybleNormalizer::class,
            ObjectNormalizer::class,
            ArrayNormalizer::class,
        ];
    }

    public static function pipeline(): DataPipeline
    {
        return DataPipeline::create()
            ->into(static::class)
            ->through(AuthorizedDataPipe::class)
            ->through(MapPropertiesDataPipe::class)
            ->through(ValidatePropertiesDataPipe::class)
            ->through(DefaultValuesDataPipe::class)
            ->through(CastPropertiesDataPipe::class);
    }

    public static function collection(Enumerable|array|AbstractPaginator|AbstractCursorPaginator|Paginator|DataCollection $items): DataCollection|PaginatedDataCollection
    {
        $isPaginated = $items instanceof AbstractPaginator
            || $items instanceof AbstractCursorPaginator
            || $items instanceof Paginator;

        return $isPaginated
            ? new PaginatedDataCollection(static::class, $items)
            : new DataCollection(static::class, $items);
    }

    public static function empty(array $extra = []): array
    {
        return app(EmptyDataResolver::class)->execute(static::class, $extra);
    }

    public function transform(
        bool $transformValues = true,
        WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
    ): array {
        return DataTransformer::create($transformValues, $wrapExecutionType)->transform($this);
    }

    public static function castUsing(array $arguments)
    {
        return new DataEloquentCast(static::class);
    }
}
