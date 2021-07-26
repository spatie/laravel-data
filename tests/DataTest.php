<?php

namespace Spatie\LaravelData\Tests;

use DateTime;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Tests\Fakes\DefaultLazyData;
use Spatie\LaravelData\Tests\Fakes\DummyDto;
use Spatie\LaravelData\Tests\Fakes\DummyModel;
use Spatie\LaravelData\Tests\Fakes\EmptyData;
use Spatie\LaravelData\Tests\Fakes\LazyData;
use Spatie\LaravelData\Tests\Fakes\MultiLazyData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

class DataTest extends TestCase
{
    /** @test */
    public function it_can_create_a_resource()
    {
        $data = new class('Ruben') extends Data {
            public function __construct(public string $string)
            {
            }
        };

        $this->assertEquals([
            'string' => 'Ruben',
        ], $data->toArray());
    }

    /** @test */
    public function it_can_create_a_collection_of_resources()
    {
        $collection = SimpleData::collection(collect([
            'Ruben',
            'Freek',
            'Brent',
        ]));

        $this->assertEquals([
            ['string' => 'Ruben'],
            ['string' => 'Freek'],
            ['string' => 'Brent'],
        ], $collection->toArray());
    }

    /** @test */
    public function it_can_include_a_lazy_property()
    {
        $data = new class(Lazy::create(fn () => 'test')) extends Data {
            public function __construct(
                public string | Lazy $name
            ) {
            }
        };

        $this->assertEquals([], $data->toArray());

        $this->assertEquals([
            'name' => 'test',
        ], $data->include('name')->toArray());
    }

    /** @test */
    public function it_can_have_a_pre_filled_in_lazy_property()
    {
        $data = new class('test') extends Data {
            public function __construct(
                public string | Lazy $name
            ) {
            }
        };

        $this->assertEquals([
            'name' => 'test',
        ], $data->toArray());

        $this->assertEquals([
            'name' => 'test',
        ], $data->include('name')->toArray());
    }

    /** @test */
    public function it_can_include_a_nested_lazy_property()
    {
        $data = new class(Lazy::create(fn () => LazyData::create('Hello')), Lazy::create(fn () => LazyData::collection([ 'is', 'it', 'me', 'your', 'looking', 'for', ])),) extends Data {
            public function __construct(
                public Lazy | LazyData $data,
                /** @var \Spatie\LaravelData\Tests\Fakes\LazyData[] */
                public Lazy | DataCollection $collection
            ) {
            }
        };

        $this->assertEquals([], (clone $data)->toArray());

        $this->assertEquals([
            'data' => [],
        ], (clone $data)->include('data')->toArray());

        $this->assertEquals([
            'data' => ['name' => 'Hello'],
        ], (clone $data)->include('data.name')->toArray());

        $this->assertEquals([
            'collection' => [
                [],
                [],
                [],
                [],
                [],
                [],
            ],
        ], (clone $data)->include('collection')->toArray());

        $this->assertEquals([
            'collection' => [
                ['name' => 'is'],
                ['name' => 'it'],
                ['name' => 'me'],
                ['name' => 'your'],
                ['name' => 'looking'],
                ['name' => 'for'],
            ],
        ], (clone $data)->include('collection.name')->toArray());
    }

    /** @test */
    public function it_can_include_specific_nested_data()
    {
        $collection = Lazy::create(fn () => MultiLazyData::collection([
            DummyDto::rick(),
            DummyDto::bon(),
        ]));

        $data = new class($collection) extends Data {
            public function __construct(
                public Lazy | DataCollection $songs
            ) {
            }
        };

        $this->assertEquals([
            'songs' => [
                ['name' => DummyDto::rick()->name],
                ['name' => DummyDto::bon()->name],
            ],
        ], $data->include('songs.name')->toArray());

        $this->assertEquals([
            'songs' => [
                [
                    'name' => DummyDto::rick()->name,
                    'artist' => DummyDto::rick()->artist,
                ],
                [
                    'name' => DummyDto::bon()->name,
                    'artist' => DummyDto::bon()->artist,
                ],
            ],
        ], $data->include('songs.{name,artist}')->toArray());

        $this->assertEquals([
            'songs' => [
                [
                    'name' => DummyDto::rick()->name,
                    'artist' => DummyDto::rick()->artist,
                    'year' => DummyDto::rick()->year,
                ],
                [
                    'name' => DummyDto::bon()->name,
                    'artist' => DummyDto::bon()->artist,
                    'year' => DummyDto::bon()->year,
                ],
            ],
        ], $data->include('songs.*')->toArray());
    }

    /** @test */
    public function it_can_have_conditional_lazy_data()
    {
        $blueprint = new class() extends Data {
            public function __construct(
                public string | Lazy | null $name = null
            ) {
            }

            public static function create(string $name)
            {
                return new self(
                    Lazy::when(fn () => $name === 'Ruben', fn () => $name)
                );
            }
        };

        $data = $blueprint::create('Freek');

        $this->assertEquals([], $data->toArray());

        $data = $blueprint::create('Ruben');

        $this->assertEquals(['name' => 'Ruben'], $data->toArray());
    }

    /** @test */
    public function it_can_have_conditional_lazy_data_manually_loaded()
    {
        $blueprint = new class() extends Data {
            public function __construct(
                public string | Lazy | null $name = null
            ) {
            }

            public static function create(string $name)
            {
                return new self(
                    Lazy::when(fn () => $name === 'Ruben', fn () => $name)
                );
            }
        };

        $data = $blueprint::create('Freek');

        $this->assertEquals(['name' => 'Freek'], $data->include('name')->toArray());
    }

    /** @test */
    public function it_can_include_data_based_upon_relations_loaded()
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = DummyModel::make();

        $data = new class(Lazy::whenLoaded('relation', $model, fn () => 'loaded')) extends Data {
            public function __construct(
                public string | Lazy $relation,
            ) {
            }
        };

        $this->assertEquals([], $data->toArray());

        $model->setRelation('relation', []);

        $this->assertEquals([
            'relation' => 'loaded',
        ], $data->toArray());
    }

    /** @test */
    public function it_can_have_default_included_lazy_data()
    {
        $data = new class('Freek') extends Data {
            public function __construct(public string | Lazy $name)
            {
            }
        };

        $this->assertEquals(['name' => 'Freek'], $data->toArray());
    }

    /** @test */
    public function it_can_exclude_default_lazy_data()
    {
        $data = DefaultLazyData::create('Freek');

        $this->assertEquals([], $data->exclude('name')->toArray());
    }

    /** @test */
    public function it_can_get_the_empty_version_of_a_data_object()
    {
        $this->assertEquals([
            'property' => null,
            'lazyProperty' => null,
            'array' => [],
            'collection' => [],
            'dataCollection' => [],
            'data' => [
                'string' => null,
            ],
            'lazyData' => [
                'string' => null,
            ],
            'defaultProperty' => true,
        ], EmptyData::empty());
    }

    /** @test */
    public function it_can_overwrite_properties_in_an_empty_version_of_a_data_object()
    {
        $this->assertEquals([
            'string' => null,
        ], SimpleData::empty());

        $this->assertEquals([
            'string' => 'Ruben',
        ], SimpleData::empty(['string' => 'Ruben']));
    }

    /** @test */
    public function it_will_use_transformers_to_convert_specific_types()
    {
        $date = new DateTime('16 may 1994');

        $data = new class($date) extends Data {
            public function __construct(public DateTime $date)
            {
            }
        };

        $this->assertEquals(['date' => '1994-05-16T00:00:00+00:00'], $data->toArray());
    }

    /** @test */
    public function it_can_dynamically_include_data_based_upon_the_request()
    {
        $response = LazyData::create('Ruben')->toResponse(request());

        $includedResponse = LazyData::create('Ruben')->toResponse(request()->merge([
            'include' => 'name',
        ]));

        $this->assertEquals([], $response->getData(true));

        $this->assertEquals(['name' => 'Ruben'], $includedResponse->getData(true));
    }

    /** @test */
    public function it_can_get_the_data_object_without_transforming()
    {
        $data = new class($dataObject = new SimpleData('Test'), $dataCollection = SimpleData::collection([ new SimpleData('A'), new SimpleData('B'), ]), Lazy::create(fn () => new SimpleData('Lazy')), 'Test', $transformable = new DateTime('16 may 1994'),) extends Data {
            public function __construct(
                public SimpleData $data,
                public DataCollection $dataCollection,
                public Lazy | Data $lazy,
                public string $string,
                public DateTime $transformable
            ) {
            }
        };

        $this->assertEquals([
            'data' => $dataObject,
            'dataCollection' => $dataCollection,
            'string' => 'Test',
            'transformable' => $transformable,
        ], $data->all());

        $this->assertEquals([
            'data' => $dataObject,
            'dataCollection' => $dataCollection,
            'lazy' => (new SimpleData('Lazy'))->withPartialsTrees([], []),
            'string' => 'Test',
            'transformable' => $transformable,
        ], $data->include('lazy')->all());
    }

    /** @test */
    public function it_can_append_data_via_method_overwrite()
    {
        $data = new class('Freek') extends Data {
            public function __construct(public string $name)
            {
            }

            public function with(): array
            {
                return ['alt_name' => "{$this->name} from Spatie"];
            }
        };

        $this->assertEquals([
            'name' => 'Freek',
            'alt_name' => 'Freek from Spatie',
        ], $data->toArray());
    }

    /** @test */
    public function it_can_append_data_via_method_call()
    {
        $data = new class('Freek') extends Data {
            public function __construct(public string $name)
            {
            }
        };

        $transformed = $data->additional([
            'company' => 'Spatie',
            'alt_name' => fn (Data $data) => "{$data->name} from Spatie",
        ])->toArray();

        $this->assertEquals([
            'name' => 'Freek',
            'company' => 'Spatie',
            'alt_name' => 'Freek from Spatie',
        ], $transformed);
    }
}
