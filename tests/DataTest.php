<?php

namespace Spatie\LaravelData\Tests;

use DateTime;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Tests\Factories\DataBlueprintFactory;
use Spatie\LaravelData\Tests\Factories\DataPropertyBlueprintFactory;
use Spatie\LaravelData\Tests\Fakes\DefaultLazyData;
use Spatie\LaravelData\Tests\Fakes\DummyDto;
use Spatie\LaravelData\Tests\Fakes\DummyModel;
use Spatie\LaravelData\Tests\Fakes\EmptyData;
use Spatie\LaravelData\Tests\Fakes\LazyData;
use Spatie\LaravelData\Tests\Fakes\MultiLazyData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

class DataTest extends TestCase
{
    /** @test */
    public function it_can_create_a_resource()
    {
        $dataClass = DataBlueprintFactory::new()->withProperty(
            DataPropertyBlueprintFactory::new('string')->withType('string')
        )->create();

        $data = new $dataClass('Ruben');

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
        $dataClass = DataBlueprintFactory::new()->withProperty(
            DataPropertyBlueprintFactory::new('name')->lazy()->withType('string')
        )->create();

        $data = new $dataClass(Lazy::create(fn () => 'test'));

        $this->assertEquals([], $data->toArray());

        $this->assertEquals([
            'name' => 'test',
        ], $data->include('name')->toArray());
    }

    /** @test */
    public function it_can_have_a_pre_filled_in_lazy_property()
    {
        $dataClass = DataBlueprintFactory::new()->withProperty(
            DataPropertyBlueprintFactory::new('name')->lazy()->withType('string')
        )->create();

        $data = new $dataClass('test');

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
        $dataClass = DataBlueprintFactory::new()->withProperty(
            DataPropertyBlueprintFactory::new('data')->lazy()->withType(LazyData::class),
            DataPropertyBlueprintFactory::dataCollection('collection', LazyData::class)->lazy()
        )->create();

        $data = new $dataClass(
            Lazy::create(fn () => LazyData::from('Hello')),
            Lazy::create(fn () => LazyData::collection(['is', 'it', 'me', 'your', 'looking', 'for',])),
        );

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
        $dataClass = DataBlueprintFactory::new()->withProperty(
            DataPropertyBlueprintFactory::dataCollection('songs', MultiLazyData::class)->lazy()
        )->create();

        $collection = Lazy::create(fn () => MultiLazyData::collection([
            DummyDto::rick(),
            DummyDto::bon(),
        ]));

        $data = new $dataClass($collection);

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

            public static function create(string $name): static
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

            public static function create(string $name): static
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
        $data = DefaultLazyData::from('Freek');

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
    public function it_can_manually_specify_a_transformer()
    {
        $date = new DateTime('16 may 1994');

        $data = new class($date) extends Data {
            public function __construct(
                #[WithTransformer(DateTimeInterfaceTransformer::class, 'd-m-Y')]
                public $date
            ) {
            }
        };

        $this->assertEquals(['date' => '16-05-1994'], $data->toArray());
    }

    /** @test */
    public function a_transformer_will_never_handle_a_null_value()
    {
        $data = new class(null) extends Data {
            public function __construct(
                #[WithTransformer(DateTimeInterfaceTransformer::class, 'd-m-Y')]
                public $date
            ) {
            }
        };

        $this->assertEquals(['date' => null], $data->toArray());
    }

    /** @test */
    public function it_can_dynamically_include_data_based_upon_the_request()
    {
        $response = LazyData::from('Ruben')->toResponse(request());

        $includedResponse = LazyData::from('Ruben')->toResponse(request()->merge([
            'include' => 'name',
        ]));

        $this->assertEquals([], $response->getData(true));

        $this->assertEquals(['name' => 'Ruben'], $includedResponse->getData(true));
    }

    /** @test */
    public function it_can_disable_including_data_dynamically_from_the_request()
    {
        LazyData::$allowedIncludes = [];

        $response = LazyData::from('Ruben')->toResponse(request()->merge([
            'include' => 'name',
        ]));

        $this->assertEquals([], $response->getData(true));

        LazyData::$allowedIncludes = ['name'];

        $response = LazyData::from('Ruben')->toResponse(request()->merge([
            'include' => 'name',
        ]));

        $this->assertEquals(['name' => 'Ruben'], $response->getData(true));

        LazyData::$allowedIncludes = null;

        $response = LazyData::from('Ruben')->toResponse(request()->merge([
            'include' => 'name',
        ]));

        $this->assertEquals(['name' => 'Ruben'], $response->getData(true));
    }

    /** @test */
    public function it_can_dynamically_exclude_data_based_upon_the_request()
    {
        $response = DefaultLazyData::from('Ruben')->toResponse(request());

        $excludedResponse = DefaultLazyData::from('Ruben')->toResponse(request()->merge([
            'exclude' => 'name',
        ]));

        $this->assertEquals(['name' => 'Ruben'], $response->getData(true));

        $this->assertEquals([], $excludedResponse->getData(true));
    }

    /** @test */
    public function it_can_disable_excluding_data_dynamically_from_the_request()
    {
        DefaultLazyData::$allowedExcludes = [];

        $response = DefaultLazyData::from('Ruben')->toResponse(request()->merge([
            'exclude' => 'name',
        ]));

        $this->assertEquals(['name' => 'Ruben'], $response->getData(true));

        DefaultLazyData::$allowedExcludes = ['name'];

        $response = DefaultLazyData::from('Ruben')->toResponse(request()->merge([
            'exclude' => 'name',
        ]));

        $this->assertEquals([], $response->getData(true));

        DefaultLazyData::$allowedExcludes = null;

        $response = DefaultLazyData::from('Ruben')->toResponse(request()->merge([
            'exclude' => 'name',
        ]));

        $this->assertEquals([], $response->getData(true));
    }

    /** @test */
    public function it_can_get_the_data_object_without_transforming()
    {
        $data = new class($dataObject = new SimpleData('Test'), $dataCollection = SimpleData::collection([new SimpleData('A'), new SimpleData('B'), ]), Lazy::create(fn () => new SimpleData('Lazy')), 'Test', $transformable = new DateTime('16 may 1994'),) extends Data {
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

    /** @test */
    public function it_can_optionally_create_data()
    {
        /** @var class-string<\Spatie\LaravelData\Data> $dataClass */
        $dataClass = DataBlueprintFactory::new()
            ->withProperty(DataPropertyBlueprintFactory::new('string')->withType('string'))
            ->create();

        $this->assertNull($dataClass::optional(null));
        $this->assertEquals(
            new $dataClass('Hello world'),
            $dataClass::optional(['string' => 'Hello world'])
        );
    }
}
