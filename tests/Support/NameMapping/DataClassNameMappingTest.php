<?php

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\NameMapping\DataClassNameMapping;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedOutputName;

it('can create a mapping', function () {
    $dataClass = new class () extends Data {
        public string $non_mapped;

        #[MapOutputName('naam')]
        public string $name;

        #[MapOutputName('genest')]
        public SimpleData $nested;

        public SimpleDataWithMappedOutputName $nested_with_mapping;

        public SimpleData $data_always_included;
    };

    /** @var \Spatie\LaravelData\Support\NameMapping\DataClassNameMapping $mapping */
    $mapping = app(DataConfig::class)->getDataClass($dataClass::class)
        ->outputNameMapping;

    expect($mapping->getOriginal('non_mapped'))->toBeNull();
    expect($mapping->getOriginal('naam'))->toBe('name');
    expect($mapping->getOriginal('genest'))->toBe('nested');

    expect($mapping->resolveNextMapping(app(DataConfig::class), 'nested'))
        ->toBeInstanceOf(DataClassNameMapping::class);

    expect($mapping->resolveNextMapping(app(DataConfig::class), 'nested_with_mapping'))
        ->toBeInstanceOf(DataClassNameMapping::class)
        ->getOriginal('paid_amount')->toBe('amount')
        ->getOriginal('any_string')->toBe('anyString')
        ->getOriginal('child')->toBe('child');
});
