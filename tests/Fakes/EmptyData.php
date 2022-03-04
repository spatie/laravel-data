<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

class EmptyData extends Data
{
    public function __construct(
        public string $property,
        public string | Lazy $lazyProperty,
        public array $array,
        public Collection $collection,
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection $dataCollection,
        public SimpleData $data,
        public Lazy | SimpleData $lazyData,
        public bool $defaultProperty = true,
    ) {
    }
}
