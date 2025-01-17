<?php

namespace Spatie\LaravelData\Enums;

use Exception;

enum DataTypeKind: string
{
    case Default = 'Default';
    case Array = 'Array';
    case Enumerable = 'Enumerable';
    case Paginator = 'Paginator';
    case CursorPaginator = 'CursorPaginator';
    case DataObject = 'DataObject';
    case DataCollection = 'DataCollection';
    case DataPaginatedCollection = 'DataPaginatedCollection';
    case DataCursorPaginatedCollection = 'DataCursorPaginatedCollection';
    case DataArray = 'DataArray';
    case DataEnumerable = 'DataEnumerable';
    case DataPaginator = 'DataPaginator';
    case DataCursorPaginator = 'DataCursorPaginator';

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
