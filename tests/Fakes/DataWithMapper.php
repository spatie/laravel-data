<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapFrom;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\CamelCaseToSnakeCaseMapper;

#[MapFrom(CamelCaseToSnakeCaseMapper::class)]
class DataWithMapper
{
    public string $casedProperty;

    public SimpleData $dataCasedProperty;

    #[DataCollectionOf(SimpleData::class)]
    public DataCollection $dataCollectionCasedProperty;
}
