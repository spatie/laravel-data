<?php

namespace Spatie\LaravelData\Support\Iterables;

use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\LaravelData\Support\Iterables\Concerns\IterableData;

class LengthAwareDataPaginator extends LengthAwarePaginator
{
    use IterableData;
}
