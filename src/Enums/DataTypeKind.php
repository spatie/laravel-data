<?php

namespace Spatie\LaravelData\Enums;

enum DataTypeKind
{
    case Default;
    case Iterable;
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
        return $this !== self::Default
            && $this !== self::Iterable
            && $this !== self::DataObject;
    }

    public function isDataRelated(): bool
    {
        return $this !== self::Default && $this !== self::Iterable;
    }

    public function isNonDataRelated(): bool
    {
        return $this === self::Default || $this === self::Iterable;
    }
}
