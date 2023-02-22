<?php

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Tests\Fakes\DataWithMapper;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedProperty;

it('can map using string', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('something')]
        public string $mapped;
    };

    $data = $dataClass::from([
        'something' => 'We are the knights who say, ni!',
    ]);

    expect($data->mapped)->toEqual('We are the knights who say, ni!');
});

it('can map in nested objects using strings', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('nested.something')]
        public string $mapped;
    };

    $data = $dataClass::from([
        'nested' => ['something' => 'We are the knights who say, ni!'],
    ]);

    expect($data->mapped)->toEqual('We are the knights who say, ni!');
});

it('replaces properties when a mapped alternative exists', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('something')]
        public string $mapped;
    };

    $data = $dataClass::from([
        'mapped' => 'We are the knights who say, ni!',
        'something' => 'Bring us a, shrubbery!',
    ]);

    expect($data->mapped)->toEqual('Bring us a, shrubbery!');
});

it('skips properties it cannot find ', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('something')]
        public string $mapped;
    };

    $data = $dataClass::from([
        'mapped' => 'We are the knights who say, ni!',
    ]);

    expect($data->mapped)->toEqual('We are the knights who say, ni!');
});

it('can use integers to map properties', function () {
    $dataClass = new class () extends Data {
        #[MapInputName(1)]
        public string $mapped;
    };

    $data = $dataClass::from([
        'We are the knights who say, ni!',
        'Bring us a, shrubbery!',
    ]);

    expect($data->mapped)->toEqual('Bring us a, shrubbery!');
});

it('can use integers to map properties in nested data', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('1.0')]
        public string $mapped;
    };

    $data = $dataClass::from([
        ['We are the knights who say, ni!'],
        ['Bring us a, shrubbery!'],
    ]);

    expect($data->mapped)->toEqual('Bring us a, shrubbery!');
});

it('can combine integers and strings to map properties', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('lines.1')]
        public string $mapped;
    };

    $data = $dataClass::from([
        'lines' => [
            'We are the knights who say, ni!',
            'Bring us a, shrubbery!',
        ],
    ]);

    expect($data->mapped)->toEqual('Bring us a, shrubbery!');
});

it('can use a dedicated mapper', function () {
    $dataClass = new class () extends Data {
        #[MapInputName(SnakeCaseMapper::class)]
        public string $mappedLine;
    };

    $data = $dataClass::from([
        'mapped_line' => 'We are the knights who say, ni!',
    ]);

    expect($data->mappedLine)->toEqual('We are the knights who say, ni!');
});

it('can map properties into data objects', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('something')]
        public SimpleData $mapped;
    };

    $value = collect([
        'something' => 'We are the knights who say, ni!',
    ]);

    $data = $dataClass::from($value);

    expect($data->mapped)->toEqual(
        SimpleData::from('We are the knights who say, ni!')
    );
});

it('can map properties into data objects which map properties again', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('something')]
        public SimpleDataWithMappedProperty $mapped;
    };

    $value = collect([
        'something' => [
            'description' => 'We are the knights who say, ni!',
        ],
    ]);

    $data = $dataClass::from($value);

    expect($data->mapped)->toEqual(
        new SimpleDataWithMappedProperty('We are the knights who say, ni!')
    );
});

it('can map properties into data collections', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('something'), DataCollectionOf(SimpleData::class)]
        public array $mapped;
    };

    $value = collect([
        'something' => [
            'We are the knights who say, ni!',
            'Bring us a, shrubbery!',
        ],
    ]);

    $data = $dataClass::from($value);

    expect($data->mapped)->toEqual(
        SimpleData::collect([
            'We are the knights who say, ni!',
            'Bring us a, shrubbery!',
        ])
    );
});

it('can map properties into data collections which map properties again', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('something'), DataCollectionOf(SimpleDataWithMappedProperty::class)]
        public array $mapped;
    };

    $value = collect([
        'something' => [
            ['description' => 'We are the knights who say, ni!'],
            ['description' => 'Bring us a, shrubbery!'],
        ],
    ]);

    $data = $dataClass::from($value);

    expect($data->mapped)->toEqual(
        SimpleDataWithMappedProperty::collect([
            ['description' => 'We are the knights who say, ni!'],
            ['description' => 'Bring us a, shrubbery!'],
        ])
    );
});

it('can map properties from a complete class', function () {
    $data = DataWithMapper::from([
        'cased_property' => 'We are the knights who say, ni!',
        'data_cased_property' =>
        ['string' => 'Bring us a, shrubbery!'],
        'data_collection_cased_property' => [
            ['string' => 'One that looks nice!'],
            ['string' => 'But not too expensive!'],
        ],
    ]);

    expect($data)
        ->casedProperty->toEqual('We are the knights who say, ni!')
        ->dataCasedProperty->toEqual(SimpleData::from('Bring us a, shrubbery!'))
        ->dataCollectionCasedProperty->toEqual(SimpleData::collect([
            'One that looks nice!',
            'But not too expensive!',
        ]));
});
