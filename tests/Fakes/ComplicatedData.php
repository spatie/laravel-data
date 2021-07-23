<?php

namespace Spatie\LaravelData\Tests\Fakes;

use DateTime;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

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
        public mixed $mixed,
        public DateTime $defaultCast,
        #[WithCast(DateTimeCast::class, format: 'd-m-Y')]
        public $explicitCast,
        public SimpleData $nestedData,
        /** @var \Spatie\LaravelData\Tests\Fakes\SimpleData[] */
        public DataCollection $nestedCollection,
    ) {
    }
}
