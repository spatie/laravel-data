<?php

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\PartialTreesMapping;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedOutputName;

it('wont create a mapping for non mapped properties', function () {
    $mapping = PartialTreesMapping::fromRootDataClass(
        app(DataConfig::class)->getDataClass(SimpleData::class)
    );

    expect($mapping)
        ->mapped->toBe('root')
        ->original->toBe('root')
        ->children->toBeEmpty();
});

it('can create a mapping', function () {
    $dataClass = new class () extends Data {
        #[MapOutputName('naam')]
        public string $name;

        #[MapOutputName('genest')]
        public SimpleData $nested;

        public SimpleDataWithMappedOutputName $nested_with_mapping;

        public SimpleData $data_always_included;
    };

    $mapping = PartialTreesMapping::fromRootDataClass(
        app(DataConfig::class)->getDataClass($dataClass::class)
    );

    expect($mapping)
        ->mapped->toBe('root')
        ->original->toBe('root')
        ->children->toHaveCount(4);

    expect($mapping->children[0])
        ->mapped->toBe('naam')
        ->original->toBe('name')
        ->children->toHaveCount(0);

    expect($mapping->children[1])
        ->mapped->toBe('genest')
        ->original->toBe('nested')
        ->children->toHaveCount(0);

    expect($mapping->children[2])
        ->mapped->toBe('nested_with_mapping')
        ->original->toBe('nested_with_mapping')
        ->children->toHaveCount(4);

    expect($mapping->children[2]->children[0])
        ->mapped->toBe('id')
        ->original->toBe('id');

    expect($mapping->children[2]->children[1])
        ->mapped->toBe('paid_amount')
        ->original->toBe('amount');

    expect($mapping->children[2]->children[2])
        ->mapped->toBe('any_string')
        ->original->toBe('anyString');

    expect($mapping->children[2]->children[3])
        ->mapped->toBe('child')
        ->original->toBe('child')
        ->children->toHaveCount(1);

    expect($mapping->children[2]->children[3]->children[0])
        ->mapped->toBe('child_amount')
        ->original->toBe('amount');

    expect($mapping->children[3])
        ->mapped->toBe('data_always_included')
        ->original->toBe('data_always_included')
        ->children->toHaveCount(0);
});
