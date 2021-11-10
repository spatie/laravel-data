<?php

namespace Spatie\LaravelData\Tests\Support;

use Generator;
use ReflectionProperty;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Support\DataCollectionAnnotationReader;
use Spatie\LaravelData\Tests\Fakes\CollectionAnnotationsData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;

class DataCollectionAnnotationReaderTest extends TestCase
{
    private DataCollectionAnnotationReader $reader;

    public function setUp(): void
    {
        parent::setUp();

        $this->reader = new DataCollectionAnnotationReader();
    }

    /**
     * @test
     * @dataProvider annotationsDataProvider
     */
    public function it_can_get_the_data_class_for_a_data_collection(
        string $property,
        ?string $expected
    ) {
        $this->assertEquals($expected, $this->reader->getClass(
            new ReflectionProperty(CollectionAnnotationsData::class, $property)
        ));
    }

    public function annotationsDataProvider(): Generator
    {
        yield [
            'property' => 'propertyA',
            'expected' => SimpleData::class,
        ];

        yield [
            'property' => 'propertyB',
            'expected' => SimpleData::class,
        ];

        yield [
            'property' => 'propertyC',
            'expected' => SimpleData::class,
        ];

        yield [
            'property' => 'propertyD',
            'expected' => SimpleData::class,
        ];

        yield [
            'property' => 'propertyE',
            'expected' => SimpleData::class,
        ];

        yield [
            'property' => 'propertyF',
            'expected' => SimpleData::class,
        ];

        yield [
            'property' => 'propertyG',
            'expected' => SimpleData::class,
        ];

        yield [
            'property' => 'propertyH',
            'expected' => SimpleData::class,
        ];

        yield [
            'property' => 'propertyI',
            'expected' => SimpleData::class,
        ];

        yield [
            'property' => 'propertyJ',
            'expected' => SimpleData::class,
        ];

        yield [
            'property' => 'propertyK',
            'expected' => SimpleData::class,
        ];

        yield [
            'property' => 'propertyL',
            'expected' => null,
        ];

        yield [
            'property' => 'propertyM',
            'expected' => null,
        ];

        yield [
            'property' => 'propertyN',
            'expected' => null,
        ];
    }
}
