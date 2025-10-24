<?php

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Annotations\DataIterableAnnotation;
use Spatie\LaravelData\Support\Annotations\DataIterableAnnotationReader;
use Spatie\LaravelData\Tests\Fakes\CollectionDataAnnotationsData;
use Spatie\LaravelData\Tests\Fakes\CollectionNonDataAnnotationsData;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\Error;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithUnicodeCharséÄöü;

it(
    'can get the data class for a data collection by annotation',
    function (string $property, ?DataIterableAnnotation $expected) {
        $annotations = app(DataIterableAnnotationReader::class)->getForProperty(new ReflectionProperty(CollectionDataAnnotationsData::class, $property));

        expect($annotations)->toEqual($expected);
    }
)->with(function () {
    yield 'propertyA' => [
        'propertyA', // property
        new DataIterableAnnotation(SimpleData::class, isData: true), // expected
    ];

    yield 'propertyB' => [
        'propertyB', // property
        new DataIterableAnnotation(SimpleData::class, isData: true), // expected
    ];

    yield 'propertyC' => [
        'propertyC', // property
        new DataIterableAnnotation(SimpleData::class, isData: true), // expected
    ];

    yield 'propertyD' => [
        'propertyD', // property
        new DataIterableAnnotation(SimpleData::class, isData: true), // expected
    ];

    yield 'propertyE' => [
        'propertyE', // property
        new DataIterableAnnotation(SimpleData::class, isData: true), // expected
    ];

    yield 'propertyE' => [
        'propertyE', // property
        new DataIterableAnnotation(SimpleData::class, isData: true), // expected
    ];

    yield 'propertyF' => [
        'propertyF', // property
        new DataIterableAnnotation(SimpleData::class, isData: true), // expected
    ];

    yield 'propertyG' => [
        'propertyG', // property
        new DataIterableAnnotation(SimpleData::class, isData: true), // expected
    ];

    yield 'propertyH' => [ // Attribute
        'propertyH', // property
        null, // expected
    ];

    yield 'propertyI' => [ // Invalid definition
        'propertyI', // property
        new DataIterableAnnotation(SimpleData::class, isData: true), // expected
    ];

    yield 'propertyJ' => [ // No definition
        'propertyJ', // property
        null, // expected
    ];

    yield 'propertyK' => [
        'propertyK', // property
        new DataIterableAnnotation(SimpleData::class, isData: true), // expected
    ];

    yield 'propertyL' => [
        'propertyL', // property
        new DataIterableAnnotation(SimpleData::class, isData: true), // expected
    ];

    yield 'propertyM' => [
        'propertyM', // property
        new DataIterableAnnotation(SimpleData::class, isData: true), // expected
    ];

    yield 'propertyU' => [
        'propertyU', // property
        new DataIterableAnnotation(SimpleData::class, isData: true), // expected
    ];

    yield 'propertyV' => [
        'propertyV', // property
        new DataIterableAnnotation(SimpleDataWithUnicodeCharséÄöü::class, isData: true), // expected
    ];
});

it('can get the data class for a data collection by class annotation', function () {
    $annotations = app(DataIterableAnnotationReader::class)->getForClass(new ReflectionClass(CollectionDataAnnotationsData::class));

    expect($annotations)->toEqualCanonicalizing([
        'propertyN' => new DataIterableAnnotation(SimpleData::class, isData: true, property: 'propertyN'),
        'propertyO' => new DataIterableAnnotation(SimpleData::class, isData: true, property: 'propertyO'),
        'propertyP' => new DataIterableAnnotation(SimpleData::class, isData: true, property: 'propertyP'),
        'propertyQ' => new DataIterableAnnotation(SimpleData::class, isData: true, property: 'propertyQ'),
        'propertyR' => new DataIterableAnnotation(SimpleData::class, isData: true, property: 'propertyR'),
        'propertyS' => new DataIterableAnnotation(SimpleData::class, isData: true, property: 'propertyS'),
        'propertyT' => new DataIterableAnnotation(SimpleData::class, isData: true, property: 'propertyT'),
        'propertyW' => new DataIterableAnnotation(SimpleDataWithUnicodeCharséÄöü::class, isData: true, property: 'propertyW'),
    ]);
});

it('can get data class for a data collection by method annotation', function () {
    $annotations = app(DataIterableAnnotationReader::class)->getForMethod(new ReflectionMethod(CollectionDataAnnotationsData::class, 'method'));

    expect($annotations)->toEqualCanonicalizing([
        'paramA' => new DataIterableAnnotation(SimpleData::class, isData: true, property: 'paramA'),
        'paramB' => new DataIterableAnnotation(SimpleData::class, isData: true, property: 'paramB'),
        'paramC' => new DataIterableAnnotation(SimpleData::class, isData: true, property: 'paramC'),
        'paramD' => new DataIterableAnnotation(SimpleData::class, isData: true, property: 'paramD'),
        'paramE' => new DataIterableAnnotation(SimpleData::class, isData: true, property: 'paramE'),
        'paramF' => new DataIterableAnnotation(SimpleData::class, isData: true, property: 'paramF'),
        'paramG' => new DataIterableAnnotation(SimpleData::class, isData: true, property: 'paramG'),
        'paramH' => new DataIterableAnnotation(SimpleData::class, isData: true, property: 'paramH'),
        'paramJ' => new DataIterableAnnotation(SimpleData::class, isData: true, keyType: 'int', property: 'paramJ'),
        'paramI' => new DataIterableAnnotation(SimpleData::class, isData: true, keyType: 'int', property: 'paramI'),
        'paramK' => new DataIterableAnnotation(SimpleData::class, isData: true, property: 'paramK'),
        'paramL' => new DataIterableAnnotation(SimpleDataWithUnicodeCharséÄöü::class, isData: true, property: 'paramL'),
        'paramM' => new DataIterableAnnotation(SimpleDataWithUnicodeCharséÄöü::class, isData: true, property: 'paramM'),
        'paramN' => new DataIterableAnnotation(SimpleDataWithUnicodeCharséÄöü::class, isData: true, property: 'paramN'),
        'paramO' => new DataIterableAnnotation(SimpleDataWithUnicodeCharséÄöü::class, isData: true, property: 'paramO'),
    ]);
});

it(
    'can get the iterable class for a collection by annotation',
    function (string $property, ?DataIterableAnnotation $expected) {
        $annotations = app(DataIterableAnnotationReader::class)->getForProperty(new ReflectionProperty(CollectionNonDataAnnotationsData::class, $property));

        expect($annotations)->toEqual($expected);
    }
)->with(function () {
    yield 'propertyA' => [
        'propertyA', // property
        new DataIterableAnnotation(DummyBackedEnum::class, isData: false), // expected
    ];

    yield 'propertyB' => [
        'propertyB', // property
        new DataIterableAnnotation(DummyBackedEnum::class, isData: false), // expected
    ];

    yield 'propertyC' => [
        'propertyC', // property
        new DataIterableAnnotation(DummyBackedEnum::class, isData: false), // expected
    ];

    yield 'propertyD' => [
        'propertyD', // property
        new DataIterableAnnotation(DummyBackedEnum::class, isData: false), // expected
    ];

    yield 'propertyE' => [
        'propertyE', // property
        new DataIterableAnnotation('string', isData: false), // expected
    ];

    yield 'propertyF' => [
        'propertyF', // property
        new DataIterableAnnotation('string', isData: false), // expected
    ];

    yield 'propertyG' => [
        'propertyG', // property
        new DataIterableAnnotation(DummyBackedEnum::class, isData: false), // expected
    ];

    yield 'propertyH' => [ // Invalid
        'propertyH', // property
        null, // expected
    ];

    yield 'propertyI' => [ // No definition
        'propertyI', // property
        null, // expected
    ];

    yield 'propertyJ' => [
        'propertyJ', // property
        new DataIterableAnnotation(DummyBackedEnum::class, isData: false), // expected
    ];

    yield 'propertyK' => [
        'propertyK', // property
        new DataIterableAnnotation(DummyBackedEnum::class, isData: false), // expected
    ];

    yield 'propertyL' => [
        'propertyL', // property
        new DataIterableAnnotation(DummyBackedEnum::class, isData: false), // expected
    ];

    yield 'propertyP' => [
        'propertyP', // property
        new DataIterableAnnotation(Error::class, isData: true), // expected
    ];

    yield 'propertyR' => [
        'propertyR', // property
        new DataIterableAnnotation(Error::class, isData: true), // expected
    ];
});

it('can get the iterable class for a collection by class annotation', function () {
    $annotations = app(DataIterableAnnotationReader::class)->getForClass(new ReflectionClass(CollectionNonDataAnnotationsData::class));

    expect($annotations)->toEqualCanonicalizing([
        'propertyM' => new DataIterableAnnotation(DummyBackedEnum::class, isData: false, property: 'propertyM'),
        'propertyN' => new DataIterableAnnotation(DummyBackedEnum::class, isData: false, property: 'propertyN'),
        'propertyO' => new DataIterableAnnotation(DummyBackedEnum::class, isData: false, property: 'propertyO'),
        'propertyQ' => new DataIterableAnnotation(DummyBackedEnum::class, isData: false, property: 'propertyQ'),
    ]);
});

it('can get iterable class for a data by method annotation', function () {
    $annotations = app(DataIterableAnnotationReader::class)->getForMethod(new ReflectionMethod(CollectionNonDataAnnotationsData::class, 'method'));

    expect($annotations)->toEqualCanonicalizing([
        'paramA' => new DataIterableAnnotation(DummyBackedEnum::class, isData: false, property: 'paramA'),
        'paramB' => new DataIterableAnnotation(DummyBackedEnum::class, isData: false, property: 'paramB'),
        'paramC' => new DataIterableAnnotation(DummyBackedEnum::class, isData: false, property: 'paramC'),
        'paramD' => new DataIterableAnnotation(DummyBackedEnum::class, isData: false, property: 'paramD'),
        'paramE' => new DataIterableAnnotation(DummyBackedEnum::class, isData: false, property: 'paramE'),
        'paramF' => new DataIterableAnnotation(DummyBackedEnum::class, isData: false, property: 'paramF'),
        'paramG' => new DataIterableAnnotation(DummyBackedEnum::class, isData: false, property: 'paramG'),
        'paramH' => new DataIterableAnnotation(DummyBackedEnum::class, isData: false, property: 'paramH'),
        'paramJ' => new DataIterableAnnotation(DummyBackedEnum::class, isData: false, keyType: 'int', property: 'paramJ'),
        'paramI' => new DataIterableAnnotation(DummyBackedEnum::class, isData: false, keyType: 'int', property: 'paramI'),
        'paramK' => new DataIterableAnnotation(DummyBackedEnum::class, isData: false, property: 'paramK'),
    ]);
});

it('will always prefer the data version of the annotation in unions', function () {
    $dataClass = new class () extends Data {
        /** @var array<string|Spatie\LaravelData\Tests\Fakes\SimpleData> */
        public array $property;
        /** @var Collection<int, string|Spatie\LaravelData\Tests\Fakes\SimpleData> */
        public Collection $collection;
    };

    $annotations = app(DataIterableAnnotationReader::class)->getForProperty(
        new ReflectionProperty($dataClass::class, 'property')
    );

    expect($annotations)->toEqual(new DataIterableAnnotation(SimpleData::class, isData: true));

    $annotations = app(DataIterableAnnotationReader::class)->getForProperty(
        new ReflectionProperty($dataClass::class, 'collection')
    );

    expect($annotations)->toEqual(new DataIterableAnnotation(SimpleData::class, isData: true, keyType: 'int'));
});

it('will recognize default PHP types', function (string $type) {
    $dataClass = new class () extends Data {
        /** @var array<string> */
        public array $string;

        /** @var array<int> */
        public array $int;

        /** @var array<float> */
        public array $float;

        /** @var array<bool> */
        public array $bool;

        /** @var array<mixed> */
        public array $mixed;

        /** @var array<array> */
        public array $array;

        /** @var array<iterable> */
        public array $iterable;

        /** @var array<object> */
        public array $object;

        /** @var array<callable> */
        public array $callable;
    };

    expect(
        app(DataIterableAnnotationReader::class)->getForProperty(
            new ReflectionProperty($dataClass::class, $type)
        )
    )->toEqual(new DataIterableAnnotation(str_replace(['array', 'iterable'], 'mixed', $type), isData: false));
})->with([
    'string' => ['string'],
    'int' => ['int'],
    'float' => ['float'],
    'bool' => ['bool'],
    'mixed' => ['mixed'],
    'array' => ['array'],
    'iterable' => ['iterable'],
    'object' => ['object'],
    'callable' => ['callable'],
]);

it('can recognize the key of an iterable', function (
    string $property,
    string $keyType,
) {
    $dataClass = new class () extends Data {
        /** @var array<string, float> */
        public array $propertyA;

        /** @var array<int, float> */
        public array $propertyB;

        /** @var array<array-key, float> */
        public array $propertyC;

        /** @var array<int|string, float> */
        public array $propertyD;

        /** @var array<string|int, float> */
        public array $propertyE;

        /** @var array<int , float> */
        public array $propertyF;
    };

    expect(
        app(DataIterableAnnotationReader::class)->getForProperty(
            new ReflectionProperty($dataClass::class, $property)
        )
    )->toEqual(new DataIterableAnnotation('float', isData: false, keyType: $keyType));
})->with([
    ['propertyA', 'string'], // string key
    ['propertyB', 'int'], // int key
    ['propertyC', 'array-key'], // array key
    ['propertyD', 'int|string'], // int|string key
    ['propertyE', 'string|int'], // string|int key
    ['propertyF', 'int'], // spaces
]);
