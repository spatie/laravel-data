<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

class EmptyData extends Data
{
    public function __construct(
        public string $property,
        public string|Lazy $lazyProperty,
        public array $array,
        public Collection $collection,
        public DataCollection $dataCollection,
        public SimpleData $data,
        public Lazy|SimpleData $lazyData,
        public bool $defaultProperty = true,
    )
    {
    }
}
