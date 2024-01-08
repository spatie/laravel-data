<?php

namespace Spatie\LaravelData\Enums;

enum DataTypeKind
{
    case Default;
    case DataObject;
    case DataCollection;
    case DataPaginatedCollection;
    case DataCursorPaginatedCollection;
    case Array;
    case Enumerable;
    case Paginator;
    case CursorPaginator;

    public function isDataObject(): bool
    {
        return $this === self::DataObject;
    }

    public function isDataCollectable(): bool
    {
        return $this !== self::Default && $this !== self::DataObject;
    }
}
