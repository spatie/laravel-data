<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class DataWithMapper extends Data
{
    public string $casedProperty;

    public SimpleData $dataCasedProperty;

    #[DataCollectionOf(SimpleData::class)]
    public array $dataCollectionCasedProperty;
}
