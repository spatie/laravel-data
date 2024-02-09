<?php

use Spatie\LaravelData\Support\Annotations\DataCollectableAnnotation;
use Spatie\LaravelData\Support\Annotations\DataCollectableAnnotationReader;
use Spatie\LaravelData\Tests\Fakes\CollectionAnnotationsData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it(
    'can get the data class for a data collection by annotation',
    function (string $property, ?DataCollectableAnnotation $expected) {
        $annotations = app(DataCollectableAnnotationReader::class)->getForProperty(new ReflectionProperty(CollectionAnnotationsData::class, $property));

        expect($annotations)->toEqual($expected);
    }
)->with(function () {
    yield 'propertyA' => [
        'property' => 'propertyA',
        'expected' => new DataCollectableAnnotation(SimpleData::class),
    ];

    yield 'propertyB' => [
        'property' => 'propertyB',
        'expected' => new DataCollectableAnnotation(SimpleData::class),
    ];

    yield 'propertyC' => [
        'property' => 'propertyC',
        'expected' => new DataCollectableAnnotation(SimpleData::class),
    ];

    yield 'propertyD' => [
        'property' => 'propertyD',
        'expected' => new DataCollectableAnnotation(SimpleData::class),
    ];

    yield 'propertyE' => [
        'property' => 'propertyE',
        'expected' => new DataCollectableAnnotation(SimpleData::class),
    ];

    yield 'propertyF' => [
        'property' => 'propertyF',
        'expected' => new DataCollectableAnnotation(SimpleData::class),
    ];

    yield 'propertyG' => [
        'property' => 'propertyG',
        'expected' => new DataCollectableAnnotation(SimpleData::class),
    ];

    yield 'propertyH' => [
        'property' => 'propertyH',
        'expected' => null, // Attribute
    ];

    yield 'propertyI' => [
        'property' => 'propertyI',
        'expected' => null, // Invalid definition
    ];

    yield 'propertyJ' => [
        'property' => 'propertyJ',
        'expected' => null,// Invalid definition
    ];

    yield 'propertyK' => [
        'property' => 'propertyK',
        'expected' => null, // No definition
    ];

    yield 'propertyL' => [
        'property' => 'propertyL',
        'expected' => new DataCollectableAnnotation(SimpleData::class),
    ];

    yield 'propertyM' => [
        'property' => 'propertyM',
        'expected' => new DataCollectableAnnotation(SimpleData::class),
    ];

    yield 'propertyN' => [
        'property' => 'propertyN',
        'expected' => new DataCollectableAnnotation(SimpleData::class),
    ];
});

it('can get the data class for a data collection by class annotation', function () {
    $annotations = app(DataCollectableAnnotationReader::class)->getForClass(new ReflectionClass(CollectionAnnotationsData::class));

    expect($annotations)->toEqualCanonicalizing([
        new DataCollectableAnnotation(SimpleData::class, property: 'propertyO'),
        new DataCollectableAnnotation(SimpleData::class, property: 'propertyP'),
        new DataCollectableAnnotation(SimpleData::class, property: 'propertyQ'),
        new DataCollectableAnnotation(SimpleData::class, property: 'propertyR'),
        new DataCollectableAnnotation(SimpleData::class, property: 'propertyS'),
        new DataCollectableAnnotation(SimpleData::class, property: 'propertyT'),
    ]);
});

it('can get data class for a data collection by method annotation', function () {
    $annotations = app(DataCollectableAnnotationReader::class)->getForMethod(new ReflectionMethod(CollectionAnnotationsData::class, 'method'));

    expect($annotations)->toEqualCanonicalizing([
        new DataCollectableAnnotation(SimpleData::class, property: 'paramA'),
        new DataCollectableAnnotation(SimpleData::class, property: 'paramB'),
        new DataCollectableAnnotation(SimpleData::class, property: 'paramC'),
        new DataCollectableAnnotation(SimpleData::class, property: 'paramD'),
        new DataCollectableAnnotation(SimpleData::class, property: 'paramE'),
        new DataCollectableAnnotation(SimpleData::class, property: 'paramF'),
        new DataCollectableAnnotation(SimpleData::class, property: 'paramG'),
        new DataCollectableAnnotation(SimpleData::class, property: 'paramH'),
        new DataCollectableAnnotation(SimpleData::class, property: 'paramJ'),
        new DataCollectableAnnotation(SimpleData::class, property: 'paramI'),
    ]);
});
