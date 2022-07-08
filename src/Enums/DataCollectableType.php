<?php

namespace Spatie\LaravelData\Enums;

enum DataCollectableType
{
    case Default;
    case Paginated;
    case CursorPaginated;
}
