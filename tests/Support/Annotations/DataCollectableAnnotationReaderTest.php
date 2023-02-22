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
