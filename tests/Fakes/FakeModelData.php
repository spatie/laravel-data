<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Undefined;

class FakeModelData extends Data
{
    public function __construct(
        public string $string,
        public ?string $nullable,
        public CarbonImmutable $date,
        #[DataCollectionOf(FakeNestedModelData::class)]
        public Undefined|null|DataCollection $fake_nested_models,
    ) {
    }
}
