<?php

namespace Spatie\LaravelData\Tests\Pipes;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapFrom;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeToCamelCaseMapper;
use Spatie\LaravelData\Tests\Fakes\DataWithMapper;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedProperty;
use Spatie\LaravelData\Tests\TestCase;

class MapPropertiesPipeTest extends TestCase
{
    /** @test */
    public function it_can_map_using_string()
    {
        $dataClass = new class () extends Data {
            #[MapFrom('something')]
            public string $mapped;
        };

        $data = $dataClass::from([
            'something' => 'We are the knights who say, ni!',
        ]);

        $this->assertEquals('We are the knights who say, ni!', $data->mapped);
    }

    /** @test */
    public function it_can_map_in_nested_objects_using_strings()
    {
        $dataClass = new class () extends Data {
            #[MapFrom('nested.something')]
            public string $mapped;
        };

        $data = $dataClass::from([
            'nested' => ['something' => 'We are the knights who say, ni!'],
        ]);

        $this->assertEquals('We are the knights who say, ni!', $data->mapped);
    }

    /** @test */
    public function it_replaces_properties_when_a_mapped_alternative_exists()
    {
        $dataClass = new class () extends Data {
            #[MapFrom('something')]
            public string $mapped;
        };

        $data = $dataClass::from([
            'mapped' => 'We are the knights who say, ni!',
            'something' => 'Bring us a, shrubbery!',
        ]);

        $this->assertEquals('Bring us a, shrubbery!', $data->mapped);
    }

    /** @test */
    public function it_skips_properties_it_cannot_find()
    {
        $dataClass = new class () extends Data {
            #[MapFrom('something')]
            public string $mapped;
        };

        $data = $dataClass::from([
            'mapped' => 'We are the knights who say, ni!',
        ]);

        $this->assertEquals('We are the knights who say, ni!', $data->mapped);
    }

    /** @test */
    public function it_can_use_integers_to_map_properties()
    {
        $dataClass = new class () extends Data {
            #[MapFrom(1)]
            public string $mapped;
        };

        $data = $dataClass::from([
            'We are the knights who say, ni!',
            'Bring us a, shrubbery!',
        ]);

        $this->assertEquals('Bring us a, shrubbery!', $data->mapped);
    }

    /** @test */
    public function it_can_use_integers_to_map_properties_in_nested_data()
    {
        $dataClass = new class () extends Data {
            #[MapFrom('1.0')]
            public string $mapped;
        };

        $data = $dataClass::from([
            ['We are the knights who say, ni!'],
            ['Bring us a, shrubbery!'],
        ]);

        $this->assertEquals('Bring us a, shrubbery!', $data->mapped);
    }

    /** @test */
    public function it_can_combine_integers_and_strings_to_map_properties()
    {
        $dataClass = new class () extends Data {
            #[MapFrom('lines.1')]
            public string $mapped;
        };

        $data = $dataClass::from([
            'lines' => [
                'We are the knights who say, ni!',
                'Bring us a, shrubbery!',
            ],
        ]);

        $this->assertEquals('Bring us a, shrubbery!', $data->mapped);
    }

    /** @test */
    public function it_can_use_a_dedicated_mapper()
    {
        $dataClass = new class () extends Data {
            #[MapFrom(SnakeToCamelCaseMapper::class)]
            public string $mappedLine;
        };

        $data = $dataClass::from([
            'mapped_line' => 'We are the knights who say, ni!',
        ]);

        $this->assertEquals('We are the knights who say, ni!', $data->mappedLine);
    }

    /** @test */
    public function it_can_map_properties_into_data_objects()
    {
        $dataClass = new class () extends Data {
            #[MapFrom('something')]
            public SimpleData $mapped;
        };

        $value = collect([
            'something' => 'We are the knights who say, ni!',
        ]);

        $data = $dataClass::from($value);

        $this->assertEquals(SimpleData::from('We are the knights who say, ni!'), $data->mapped);
    }

    /** @test */
    public function it_can_map_properties_into_data_objects_which_map_properties_again()
    {
        $dataClass = new class () extends Data {
            #[MapFrom('something')]
            public SimpleDataWithMappedProperty $mapped;
        };

        $value = collect([
            'something' => [
                'description' => 'We are the knights who say, ni!',
            ],
        ]);

        $data = $dataClass::from($value);

        $this->assertEquals(
            new SimpleDataWithMappedProperty('We are the knights who say, ni!'),
            $data->mapped
        );
    }

    /** @test */
    public function it_can_map_properties_into_data_collections()
    {
        $dataClass = new class () extends Data {
            #[MapFrom('something'), DataCollectionOf(SimpleData::class)]
            public DataCollection $mapped;
        };

        $value = collect([
            'something' => [
                'We are the knights who say, ni!',
                'Bring us a, shrubbery!',
            ],
        ]);

        $data = $dataClass::from($value);

        $this->assertEquals(
            SimpleData::collection([
                'We are the knights who say, ni!',
                'Bring us a, shrubbery!',
            ]),
            $data->mapped
        );
    }

    /** @test */
    public function it_can_map_properties_into_data_collections_wich_map_properties_again()
    {
        $dataClass = new class () extends Data {
            #[MapFrom('something'), DataCollectionOf(SimpleDataWithMappedProperty::class)]
            public DataCollection $mapped;
        };

        $value = collect([
            'something' => [
                ['description' => 'We are the knights who say, ni!'],
                ['description' => 'Bring us a, shrubbery!'],
            ],
        ]);

        $data = $dataClass::from($value);

        $this->assertEquals(
            SimpleDataWithMappedProperty::collection([
                ['description' => 'We are the knights who say, ni!'],
                ['description' => 'Bring us a, shrubbery!'],
            ]),
            $data->mapped
        );
    }

    /** @test */
    public function it_can_map_properties_from_a_complete_class()
    {
        $data = DataWithMapper::from([
            'cased_property' => 'We are the knights who say, ni!',
            'data_cased_property' =>
                ['string' => 'Bring us a, shrubbery!'],
            'data_collection_cased_property' => [
                ['string' => 'One that looks nice!'],
                ['string' => 'But not too expensive!'],
            ],
        ]);

        $this->assertEquals('We are the knights who say, ni!', $data->casedProperty);
        $this->assertEquals(SimpleData::from('Bring us a, shrubbery!'), $data->dataCasedProperty);
        $this->assertEquals(SimpleData::collection([
            'One that looks nice!',
            'But not too expensive!',
        ]), $data->dataCollectionCasedProperty);
    }
}
