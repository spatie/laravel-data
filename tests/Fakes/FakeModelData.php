<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Optional;

class FakeModelData extends Data
{
    public function __construct(
        public string $string,
        public ?string $nullable,
        public CarbonImmutable $date,
        #[DataCollectionOf(FakeNestedModelData::class)]
        public Optional|null|DataCollection $fake_nested_models,
        public string $accessor,
        public string $old_accessor,
    ) {
    }
}
