<?php

namespace Spatie\LaravelData\Enums;

use Exception;

enum DataTypeKind
{
    case Default;
    case Array;
    case Enumerable;
    case Paginator;
    case CursorPaginator;
    case DataObject;
    case DataCollection;
    case DataPaginatedCollection;
    case DataCursorPaginatedCollection;
    case DataArray;
    case DataEnumerable;
    case DataPaginator;
    case DataCursorPaginator;

    public function isDataObject(): bool
    {
        return $this === self::DataObject;
    }

    public function isDataCollectable(): bool
    {
        return $this === self::DataCollection
            || $this === self::DataPaginatedCollection
            || $this === self::DataCursorPaginatedCollection
            || $this === self::DataArray
            || $this === self::DataEnumerable
            || $this === self::DataPaginator
            || $this === self::DataCursorPaginator;
    }

    public function isDataRelated(): bool
    {
        return $this->isDataObject() || $this->isDataCollectable();
    }

    public function isNonDataRelated(): bool
    {
        return $this === self::Default
            || $this === self::Array
            || $this === self::Enumerable
            || $this === self::Paginator
            || $this === self::CursorPaginator;
    }

    public function isNonDataIteratable(): bool
    {
        return $this === self::Array
            || $this === self::Enumerable
            || $this === self::Paginator
            || $this === self::CursorPaginator;
    }

    public function getDataRelatedEquivalent(): self
    {
        return match ($this) {
            self::Array => self::DataArray,
            self::Enumerable => self::DataEnumerable,
            self::Paginator => self::DataPaginator,
            self::CursorPaginator => self::DataCursorPaginator,
            self::DataCollection => self::DataCollection,
            self::DataPaginatedCollection => self::DataPaginatedCollection,
            self::DataCursorPaginatedCollection => self::DataCursorPaginatedCollection,
            default => throw new Exception("No equivalent for {$this->name}")
        };
    }
}
