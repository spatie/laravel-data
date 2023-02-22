<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Carbon\CarbonImmutable;
use DateTime;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Optional;

class ComplicatedData extends Data
{
    public function __construct(
        public $withoutType,
        public int $int,
        public bool $bool,
        public float $float,
        public string $string,
        public array $array,
        public ?int $nullable,
        public int|Optional $undefinable,
        public mixed $mixed,
        #[WithCast(DateTimeInterfaceCast::class, format: 'd-m-Y', type: CarbonImmutable::class)]
        public  $explicitCast,
        public DateTime $defaultCast,
        public SimpleData $nestedData,
        /** @var \Spatie\LaravelData\Tests\Fakes\SimpleData[] */
        public DataCollection $nestedCollection,
        #[DataCollectionOf(SimpleData::class)]
        public array $nestedArray,
    ) {
    }
}
