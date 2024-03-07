<?php

namespace Spatie\LaravelData\Support\Types\Storage;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\PaginatedDataCollection;

class AcceptedTypesStorage
{
    /** @var array<string, string[]> */
    public static array $acceptedTypes = [];

    /** @var array<string, DataTypeKind> */
    public static array $acceptedKinds = [];

    /** @return array{acceptedTypes:string[], kind: DataTypeKind} */
    public static function getAcceptedTypesAndKind(string $name): array
    {
        $acceptedTypes = static::getAcceptedTypes($name);

        return [
            'acceptedTypes' => $acceptedTypes,
            'kind' => static::$acceptedKinds[$name] ??= static::resolveDataTypeKind($name, $acceptedTypes),
        ];
    }

    /** @return string[] */
    public static function getAcceptedTypes(string $name): array
    {
        return static::$acceptedTypes[$name] ??= static::resolveAcceptedTypes($name);
    }

    /** @return string[] */
    protected static function resolveAcceptedTypes(string $name): array
    {
        if (! class_exists($name) && ! interface_exists($name)) {
            return [];
        }

        return array_unique([
            ...array_values(class_parents($name)),
            ...array_values(class_implements($name)),
        ]);
    }

    protected static function resolveDataTypeKind(string $name, array $acceptedTypes): DataTypeKind
    {
        return match (true) {
            in_array(BaseData::class, $acceptedTypes) => DataTypeKind::DataObject,
            $name === 'array' => DataTypeKind::Array,
            in_array(Enumerable::class, $acceptedTypes) => DataTypeKind::Enumerable,
            in_array(DataCollection::class, $acceptedTypes) || $name === DataCollection::class => DataTypeKind::DataCollection,
            in_array(PaginatedDataCollection::class, $acceptedTypes) || $name === PaginatedDataCollection::class => DataTypeKind::DataPaginatedCollection,
            in_array(CursorPaginatedDataCollection::class, $acceptedTypes) || $name === CursorPaginatedDataCollection::class => DataTypeKind::DataCursorPaginatedCollection,
            in_array(Paginator::class, $acceptedTypes) || in_array(AbstractPaginator::class, $acceptedTypes) => DataTypeKind::Paginator,
            in_array(CursorPaginator::class, $acceptedTypes) || in_array(AbstractCursorPaginator::class, $acceptedTypes) => DataTypeKind::CursorPaginator,
            default => DataTypeKind::Default,
        };
    }

    public static function reset(): void
    {
        static::$acceptedTypes = [];
        static::$acceptedKinds = [];
    }
}
