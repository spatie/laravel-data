<?php

namespace Spatie\LaravelData\Tests;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use Generator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\PartialTrees;
use Spatie\LaravelData\Tests\Factories\DataBlueprintFactory;
use Spatie\LaravelData\Tests\Factories\DataPropertyBlueprintFactory;
use Spatie\LaravelData\Tests\Fakes\Casts\ConfidentialDataCast;
use Spatie\LaravelData\Tests\Fakes\Casts\ConfidentialDataCollectionCast;
use Spatie\LaravelData\Tests\Fakes\Casts\StringToUpperCast;
use Spatie\LaravelData\Tests\Fakes\DataWithMapper;
use Spatie\LaravelData\Tests\Fakes\DefaultLazyData;
use Spatie\LaravelData\Tests\Fakes\DefaultUndefinedData;
use Spatie\LaravelData\Tests\Fakes\DummyDto;
use Spatie\LaravelData\Tests\Fakes\DummyModel;
use Spatie\LaravelData\Tests\Fakes\EmptyData;
use Spatie\LaravelData\Tests\Fakes\ExceptData;
use Spatie\LaravelData\Tests\Fakes\FakeModelData;
use Spatie\LaravelData\Tests\Fakes\FakeNestedModelData;
use Spatie\LaravelData\Tests\Fakes\IntersectionTypeData;
use Spatie\LaravelData\Tests\Fakes\LazyData;
use Spatie\LaravelData\Tests\Fakes\Models\FakeNestedModel;
use Spatie\LaravelData\Tests\Fakes\MultiData;
use Spatie\LaravelData\Tests\Fakes\MultiLazyData;
use Spatie\LaravelData\Tests\Fakes\OnlyData;
use Spatie\LaravelData\Tests\Fakes\ReadonlyData;
use Spatie\LaravelData\Tests\Fakes\RequestData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedProperty;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithoutConstructor;
use Spatie\LaravelData\Tests\Fakes\Transformers\ConfidentialDataCollectionTransformer;
use Spatie\LaravelData\Tests\Fakes\Transformers\ConfidentialDataTransformer;
use Spatie\LaravelData\Tests\Fakes\Transformers\StringToUpperTransformer;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\LaravelData\Undefined;
use Spatie\LaravelData\WithData;

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

        $data = new $dataClass(Lazy::create(fn() => 'test'));

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
            Lazy::create(fn() => LazyData::from('Hello')),
            Lazy::create(fn() => LazyData::collection(['is', 'it', 'me', 'your', 'looking', 'for',])),
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

        $collection = Lazy::create(fn() => MultiLazyData::collection([
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
        $blueprint = new class () extends Data {
            public function __construct(
                public string|Lazy|null $name = null
            ) {
            }

            public static function create(string $name): static
            {
                return new self(
                    Lazy::when(fn() => $name === 'Ruben', fn() => $name)
                );
            }
        };

        $data = $blueprint::create('Freek');

        $this->assertEquals([], $data->toArray());

        $data = $blueprint::create('Ruben');

        $this->assertEquals(['name' => 'Ruben'], $data->toArray());
    }

    /** @test */
    public function it_cannot_have_conditional_lazy_data_manually_loaded()
    {
        $blueprint = new class () extends Data {
            public function __construct(
                public string|Lazy|null $name = null
            ) {
            }

            public static function create(string $name): static
            {
                return new self(
                    Lazy::when(fn() => $name === 'Ruben', fn() => $name)
                );
            }
        };

        $data = $blueprint::create('Freek');

        $this->assertEmpty($data->include('name')->toArray());
    }

    /** @test */
    public function it_can_include_data_based_upon_relations_loaded()
    {
        $model = FakeNestedModel::factory()->create();

        $transformed = FakeNestedModelData::createWithLazyWhenLoaded($model)->all();

        $this->assertArrayNotHasKey('fake_model', $transformed);

        $transformed = FakeNestedModelData::createWithLazyWhenLoaded($model->load('fakeModel'))->all();

        $this->assertArrayHasKey('fake_model', $transformed);
        $this->assertInstanceOf(FakeModelData::class, $transformed['fake_model']);
    }

    /** @test */
    public function it_can_include_data_based_upon_relations_loaded_when_they_are_null()
    {
        $model = FakeNestedModel::factory(['fake_model_id' => null])->create();

        $transformed = FakeNestedModelData::createWithLazyWhenLoaded($model)->all();

        $this->assertArrayNotHasKey('fake_model', $transformed);

        $transformed = FakeNestedModelData::createWithLazyWhenLoaded($model->load('fakeModel'))->all();

        $this->assertArrayHasKey('fake_model', $transformed);
        $this->assertNull($transformed['fake_model']);
    }

    /** @test */
    public function it_can_have_default_included_lazy_data()
    {
        $data = new class ('Freek') extends Data {
            public function __construct(public string|Lazy $name)
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

        $data = new class ($date) extends Data {
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

        $data = new class ($date) extends Data {
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
        $data = new class (null) extends Data {
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

        LazyData::$allowedIncludes = ['name'];

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

        DefaultLazyData::$allowedExcludes = ['name'];

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
    public function it_can_disable_only_data_dynamically_from_the_request()
    {
        OnlyData::$allowedOnly = [];

        $response = OnlyData::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche'])->toResponse(request()->merge([
            'only' => 'first_name, last_name',
        ]));

        $this->assertEquals([], $response->getData(true));

        OnlyData::$allowedOnly = ['first_name'];

        $response = OnlyData::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche'])->toResponse(request()->merge([
            'only' => 'first_name,last_name',
        ]));

        $this->assertEquals([
            'first_name' => 'Ruben'
        ], $response->getData(true));

        OnlyData::$allowedOnly = null;

        $response = OnlyData::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche'])->toResponse(request()->merge([
            'only' => 'first_name,last_name',
        ]));

        $this->assertEquals([
            'first_name' => 'Ruben',
            'last_name' => 'Van Assche'
        ], $response->getData(true));
    }

    /** @test */
    public function it_can_disable_except_data_dynamically_from_the_request()
    {
        ExceptData::$allowedExcept = [];

        $response = ExceptData::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche'])->toResponse(request()->merge([
            'except' => 'first_name, last_name',
        ]));

        $this->assertEquals([
            'first_name' => 'Ruben',
            'last_name' => 'Van Assche'
        ], $response->getData(true));

        ExceptData::$allowedExcept = ['first_name'];

        $response = ExceptData::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche'])->toResponse(request()->merge([
            'except' => 'first_name,last_name',
        ]));

        $this->assertEquals([
            'last_name' => 'Van Assche'
        ], $response->getData(true));

        ExceptData::$allowedExcept = null;

        $response = ExceptData::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche'])->toResponse(request()->merge([
            'except' => 'first_name,last_name',
        ]));

        $this->assertEquals([], $response->getData(true));
    }

    /** @test */
    public function it_can_get_the_data_object_without_transforming()
    {
        $data = new class ($dataObject = new SimpleData('Test'), $dataCollection = SimpleData::collection([new SimpleData('A'), new SimpleData('B'),]), Lazy::create(fn() => new SimpleData('Lazy')), 'Test', $transformable = new DateTime('16 may 1994'),) extends Data {
            public function __construct(
                public SimpleData $data,
                #[DataCollectionOf(SimpleData::class)]
                public DataCollection $dataCollection,
                public Lazy|Data $lazy,
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
            'lazy' => (new SimpleData('Lazy'))->withPartialTrees(new PartialTrees([], null, null, null)),
            'string' => 'Test',
            'transformable' => $transformable,
        ], $data->include('lazy')->all());
    }

    /** @test */
    public function it_can_append_data_via_method_overwrite()
    {
        $data = new class ('Freek') extends Data {
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
        $data = new class ('Freek') extends Data {
            public function __construct(public string $name)
            {
            }
        };

        $transformed = $data->additional([
            'company' => 'Spatie',
            'alt_name' => fn(Data $data) => "{$data->name} from Spatie",
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

    /** @test */
    public function it_can_validate_if_an_array_fits_a_data_object_and_will_throw_an_exception()
    {
        $dataClass = DataBlueprintFactory::new()
            ->withProperty(DataPropertyBlueprintFactory::new('string')->withType('string'))
            ->create();

        try {
            $dataClass::validate(['string' => 10]);
        } catch (ValidationException $exception) {
            $this->assertEquals([
                'string' => ['The string must be a string.'],
            ], $exception->errors());

            return;
        }

        $this->assertFalse(true, 'We should not end up here');
    }

    /** @test */
    public function it_can_validate_if_an_array_fits_a_data_object_and_returns_the_data_object()
    {
        $dataClass = DataBlueprintFactory::new()
            ->withProperty(DataPropertyBlueprintFactory::new('string')->withType('string'))
            ->create();

        $data = $dataClass::validate(['string' => 'Hello World']);

        $this->assertEquals('Hello World', $data->string);
    }

    /** @test */
    public function it_can_create_a_data_model_without_constructor()
    {
        $this->assertEquals(
            SimpleDataWithoutConstructor::fromString('Hello'),
            SimpleDataWithoutConstructor::from('Hello')
        );

        $this->assertEquals(
            SimpleDataWithoutConstructor::fromString('Hello'),
            SimpleDataWithoutConstructor::from([
                'string' => 'Hello',
            ])
        );

        $this->assertEquals(
            new DataCollection(SimpleDataWithoutConstructor::class, [
                SimpleDataWithoutConstructor::fromString('Hello'),
                SimpleDataWithoutConstructor::fromString('World'),
            ]),
            SimpleDataWithoutConstructor::collection(['Hello', 'World'])
        );
    }

    /** @test */
    public function it_can_create_a_data_object_from_a_model()
    {
        DummyModel::migrate();

        $model = DummyModel::create([
            'string' => 'test',
            'boolean' => true,
            'date' => CarbonImmutable::create(2020, 05, 16, 12, 00, 00),
            'nullable_date' => null,
        ]);

        $dataClass = new class () extends Data {
            public string $string;

            public bool $boolean;

            public Carbon $date;

            public ?Carbon $nullable_date;
        };

        $data = $dataClass::from(DummyModel::findOrFail($model->id));

        $this->assertEquals('test', $data->string);
        $this->assertTrue($data->boolean);
        $this->assertTrue(CarbonImmutable::create(2020, 05, 16, 12, 00, 00)->eq($data->date));
        $this->assertNull($data->nullable_date);
    }

    /** @test */
    public function it_can_create_a_data_object_from_a_stdClass_object()
    {
        $object = (object) [
            'string' => 'test',
            'boolean' => true,
            'date' => CarbonImmutable::create(2020, 05, 16, 12, 00, 00),
            'nullable_date' => null,
        ];

        $dataClass = new class () extends Data {
            public string $string;

            public bool $boolean;

            public CarbonImmutable $date;

            public ?Carbon $nullable_date;
        };

        $data = $dataClass::from($object);

        $this->assertEquals('test', $data->string);
        $this->assertTrue($data->boolean);
        $this->assertTrue(CarbonImmutable::create(2020, 05, 16, 12, 00, 00)->eq($data->date));
        $this->assertNull($data->nullable_date);
    }


    /** @test */
    public function it_can_add_the_with_data_trait_to_a_request()
    {
        $formRequest = new class () extends FormRequest {
            use WithData;

            public string $dataClass = SimpleData::class;
        };

        $formRequest->replace([
            'string' => 'Hello World',
        ]);

        $data = $formRequest->getData();

        $this->assertEquals(SimpleData::from('Hello World'), $data);
    }

    /** @test */
    public function it_can_add_the_with_data_trait_to_a_model()
    {
        $model = new class () extends Model {
            use WithData;

            protected string $dataClass = SimpleData::class;
        };

        $model->fill([
            'string' => 'Hello World',
        ]);

        $data = $model->getData();

        $this->assertEquals(SimpleData::from('Hello World'), $data);
    }

    /** @test */
    public function it_can_define_the_with_data_trait_data_class_by_method()
    {
        $arrayable = new class () implements Arrayable {
            use WithData;

            public function toArray()
            {
                return [
                    'string' => 'Hello World',
                ];
            }

            protected function dataClass(): string
            {
                return SimpleData::class;
            }
        };

        $data = $arrayable->getData();

        $this->assertEquals(SimpleData::from('Hello World'), $data);
    }

    /** @test */
    public function it_always_validates_requests_when_passed_to_the_from_method()
    {
        RequestData::clear();

        try {
            RequestData::from(new Request());
        } catch (ValidationException $exception) {
            $this->assertEquals([
                'string' => [__('validation.required', ['attribute' => 'string'])],
            ], $exception->errors());

            return;
        }

        $this->fail('We should not end up here');
    }

    /** @test */
    public function it_has_support_for_readonly_properties()
    {
        $this->onlyPHP81();

        $data = ReadonlyData::from(['string' => 'Hello world']);

        $this->assertInstanceOf(ReadonlyData::class, $data);
        $this->assertEquals('Hello world', $data->string);
    }

    /** @test */
    public function it_has_support_for_intersection_types()
    {
        $this->onlyPHP81();

        $collection = collect(['a', 'b', 'c']);

        $data = IntersectionTypeData::from(['intersection' => $collection]);

        $this->assertInstanceOf(IntersectionTypeData::class, $data);
        $this->assertEquals($collection, $data->intersection);
    }

    /** @test */
    public function it_can_transform_to_json()
    {
        $this->assertEquals('{"string":"Hello"}', SimpleData::from('Hello')->toJson());
        $this->assertEquals('{"string":"Hello"}', json_encode(SimpleData::from('Hello')));
    }

    /** @test */
    public function it_can_construct_a_data_object_with_both_constructor_promoted_and_default_properties()
    {
        $dataClass = new class ('') extends Data {
            public string $property;

            public function __construct(
                public string $promoted_property,
            ) {
            }
        };

        $data = $dataClass::from([
            'property' => 'A',
            'promoted_property' => 'B',
        ]);

        $this->assertEquals('A', $data->property);
        $this->assertEquals('B', $data->promoted_property);
    }

    /** @test */
    public function it_can_construct_a_data_object_with_default_values()
    {
        $data = DataWithDefaults::from([
            'property' => 'Test',
            'promoted_property' => 'Test Again',
        ]);

        $this->assertEquals('Test', $data->property);
        $this->assertEquals('Test Again', $data->promoted_property);
        $this->assertEquals('Hello', $data->default_property);
        $this->assertEquals('Hello Again', $data->default_promoted_property);
    }

    /** @test */
    public function it_can_construct_a_data_object_with_default_values_and_overwrite_them()
    {
        $data = DataWithDefaults::from([
            'property' => 'Test',
            'default_property' => 'Test',
            'promoted_property' => 'Test Again',
            'default_promoted_property' => 'Test Again',
        ]);

        $this->assertEquals('Test', $data->property);
        $this->assertEquals('Test Again', $data->promoted_property);
        $this->assertEquals('Test', $data->default_property);
        $this->assertEquals('Test Again', $data->default_promoted_property);
    }

    /** @test */
    public function it_can_use_a_custom_transformer_to_transform_data_objects_and_collections()
    {
        $nestedData = new class (42, 'Hello World') extends Data {
            public function __construct(
                public int $integer,
                public string $string,
            ) {
            }
        };

        $nestedDataCollection = $nestedData::collection([
            ['integer' => 314, 'string' => 'pi'],
            ['integer' => '69', 'string' => 'Laravel after hours'],
        ]);

        $dataWithDefaultTransformers = new class ($nestedData, $nestedDataCollection) extends Data {
            public function __construct(
                public Data $nestedData,
                #[DataCollectionOf(SimpleData::class)]
                public DataCollection $nestedDataCollection,
            ) {
            }
        };

        $dataWithSpecificTransformers = new class ($nestedData, $nestedDataCollection) extends Data {
            public function __construct(
                #[WithTransformer(ConfidentialDataTransformer::class)]
                public Data $nestedData,
                #[WithTransformer(ConfidentialDataCollectionTransformer::class),
                    DataCollectionOf(SimpleData::class)]
                public DataCollection $nestedDataCollection,
            ) {
            }
        };

        $this->assertEquals([
            'nestedData' => ['integer' => 42, 'string' => 'Hello World'],
            'nestedDataCollection' => [
                ['integer' => 314, 'string' => 'pi'],
                ['integer' => '69', 'string' => 'Laravel after hours'],
            ],
        ], $dataWithDefaultTransformers->toArray());

        $this->assertEquals([
            'nestedData' => ['integer' => 'CONFIDENTIAL', 'string' => 'CONFIDENTIAL'],
            'nestedDataCollection' => [
                ['integer' => 'CONFIDENTIAL', 'string' => 'CONFIDENTIAL'],
                ['integer' => 'CONFIDENTIAL', 'string' => 'CONFIDENTIAL'],
            ],
        ], $dataWithSpecificTransformers->toArray());
    }

    /** @test */
    public function it_can_transform_built_in_types_with_custom_transformers()
    {
        $data = new class ('Hello World', 'Hello World') extends Data {
            public function __construct(
                public string $without_transformer,
                #[WithTransformer(StringToUpperTransformer::class)]
                public string $with_transformer
            ) {
            }
        };
        $this->assertEquals([
            'without_transformer' => 'Hello World',
            'with_transformer' => 'HELLO WORLD',
        ], $data->toArray());
    }

    /** @test */
    public function it_can_cast_data_objects_and_collections_using_a_custom_cast()
    {
        $dataWithDefaultCastsClass = new class (new SimpleData(''), SimpleData::collection([])) extends Data {
            public function __construct(
                public SimpleData $nestedData,
                #[DataCollectionOf(SimpleData::class)]
                public DataCollection $nestedDataCollection,
            ) {
            }
        };

        $dataWithCustomCastsClass = new class (new SimpleData(''), SimpleData::collection([])) extends Data {
            public function __construct(
                #[WithCast(ConfidentialDataCast::class)]
                public SimpleData $nestedData,
                #[WithCast(ConfidentialDataCollectionCast::class)]
                #[DataCollectionOf(SimpleData::class)]
                public DataCollection $nestedDataCollection,
            ) {
            }
        };

        $dataWithDefaultCasts = $dataWithDefaultCastsClass::from([
            'nestedData' => 'a secret',
            'nestedDataCollection' => ['another secret', 'yet another secret'],
        ]);

        $dataWithCustomCasts = $dataWithCustomCastsClass::from([
            'nestedData' => 'a secret',
            'nestedDataCollection' => ['another secret', 'yet another secret'],
        ]);

        $this->assertEquals(SimpleData::from('a secret'), $dataWithDefaultCasts->nestedData);
        $this->assertEquals(SimpleData::collection(['another secret', 'yet another secret']), $dataWithDefaultCasts->nestedDataCollection);

        $this->assertEquals(SimpleData::from('CONFIDENTIAL'), $dataWithCustomCasts->nestedData);
        $this->assertEquals(SimpleData::collection(['CONFIDENTIAL', 'CONFIDENTIAL']), $dataWithCustomCasts->nestedDataCollection);
    }

    /** @test */
    public function it_can_cast_built_in_types_with_custom_casts()
    {
        $dataClass = new class ('', '') extends Data {
            public function __construct(
                public string $without_cast,
                #[WithCast(StringToUpperCast::class)]
                public string $with_cast
            ) {
            }
        };

        $data = $dataClass::from([
            'without_cast' => 'Hello World',
            'with_cast' => 'Hello World',
        ]);

        $this->assertEquals('Hello World', $data->without_cast);
        $this->assertEquals('HELLO WORLD', $data->with_cast);
    }

    /** @test */
    public function it_continues_value_assignment_after_a_false_boolean()
    {
        $dataClass = new class () extends Data {
            public bool $false;

            public bool $true;

            public string $string;

            public Carbon $date;
        };

        $data = $dataClass::from([
            'false' => false,
            'true' => true,
            'string' => 'string',
            'date' => Carbon::create(2020, 05, 16, 12, 00, 00),
        ]);

        $this->assertFalse($data->false);
        $this->assertTrue($data->true);
        $this->assertEquals('string', $data->string);
        $this->assertTrue(Carbon::create(2020, 05, 16, 12, 00, 00)->equalTo($data->date));
    }

    /** @test */
    public function it_can_create_an_partial_data_object()
    {
        $dataClass = new class ('', Undefined::create(), Undefined::create()) extends Data {
            public function __construct(
                public string $string,
                public string|Undefined $undefinable_string,
                #[WithCast(StringToUpperCast::class)]
                public string|Undefined $undefinable_string_with_cast,
            ) {
            }
        };

        $partialData = $dataClass::from([
            'string' => 'Hello World',
        ]);

        $this->assertEquals('Hello World', $partialData->string);
        $this->assertEquals(Undefined::create(), $partialData->undefinable_string);
        $this->assertEquals(Undefined::create(), $partialData->undefinable_string_with_cast);

        $fullData = $dataClass::from([
            'string' => 'Hello World',
            'undefinable_string' => 'Hello World',
            'undefinable_string_with_cast' => 'Hello World',
        ]);

        $this->assertEquals('Hello World', $fullData->string);
        $this->assertEquals('Hello World', $fullData->undefinable_string);
        $this->assertEquals('HELLO WORLD', $fullData->undefinable_string_with_cast);
    }

    /** @test */
    public function it_can_transform_a_partial_object()
    {
        $dataClass = new class ('', Undefined::create(), Undefined::create()) extends Data {
            public function __construct(
                public string $string,
                public string|Undefined $undefinable_string,
                #[WithTransformer(StringToUpperTransformer::class)]
                public string|Undefined $undefinable_string_with_transformer,
            ) {
            }
        };

        $partialData = $dataClass::from([
            'string' => 'Hello World',
        ]);

        $fullData = $dataClass::from([
            'string' => 'Hello World',
            'undefinable_string' => 'Hello World',
            'undefinable_string_with_transformer' => 'Hello World',
        ]);

        $this->assertEquals([
            'string' => 'Hello World',
        ], $partialData->toArray());

        $this->assertEquals([
            'string' => 'Hello World',
            'undefinable_string' => 'Hello World',
            'undefinable_string_with_transformer' => 'HELLO WORLD',
        ], $fullData->toArray());
    }

    /** @test */
    public function it_will_not_include_lazy_undefined_values_when_transforming()
    {
        $data = new class ('Hello World', Lazy::create(fn() => Undefined::make())) extends Data {
            public function __construct(
                public string $string,
                public string|Undefined|Lazy $lazy_undefined_string,
            ) {
            }
        };

        $this->assertEquals($data->toArray(), [
            'string' => 'Hello World',
        ]);
    }

    /** @test */
    public function it_excludes_undefined_values_data()
    {
        $data = DefaultUndefinedData::from([]);

        $this->assertEquals([], $data->toArray());
    }

    /** @test */
    public function it_includes_value_if_not_undefined_data()
    {
        $data = DefaultUndefinedData::from([
            'name' => 'Freek',
        ]);

        $this->assertEquals([
            'name' => 'Freek',
        ], $data->toArray());
    }

    /** @test */
    public function it_can_map_transformed_property_names()
    {
        $data = new SimpleDataWithMappedProperty('hello');
        $dataCollection = SimpleDataWithMappedProperty::collection([
            ['description' => 'never'],
            ['description' => 'gonna'],
            ['description' => 'give'],
            ['description' => 'you'],
            ['description' => 'up'],
        ]);

        $dataClass = new class ('hello', $data, $data, $dataCollection, $dataCollection) extends Data {
            public function __construct(
                #[MapOutputName('property')]
                public string $string,
                public SimpleDataWithMappedProperty $nested,
                #[MapOutputName('nested_other')]
                public SimpleDataWithMappedProperty $nested_renamed,
                #[DataCollectionOf(SimpleDataWithMappedProperty::class)]
                public DataCollection $nested_collection,
                #[MapOutputName('nested_other_collection'),
                    DataCollectionOf(SimpleDataWithMappedProperty::class)]
                public DataCollection $nested_renamed_collection,
            ) {
            }
        };

        $this->assertEquals([
            'property' => 'hello',
            'nested' => [
                'description' => 'hello',
            ],
            'nested_other' => [
                'description' => 'hello',
            ],
            'nested_collection' => [
                ['description' => 'never'],
                ['description' => 'gonna'],
                ['description' => 'give'],
                ['description' => 'you'],
                ['description' => 'up'],
            ],
            'nested_other_collection' => [
                ['description' => 'never'],
                ['description' => 'gonna'],
                ['description' => 'give'],
                ['description' => 'you'],
                ['description' => 'up'],
            ],
        ], $dataClass->toArray());
    }

    /** @test */
    public function it_can_map_transformed_properties_from_a_complete_class()
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

        $this->assertEquals([
            'cased_property' => 'We are the knights who say, ni!',
            'data_cased_property' =>
                ['string' => 'Bring us a, shrubbery!'],
            'data_collection_cased_property' => [
                ['string' => 'One that looks nice!'],
                ['string' => 'But not too expensive!'],
            ],
        ], $data->toArray());
    }

    /**
     * @test
     * @dataProvider onlyInclusionDataProvider
     */
    public function it_can_include_only_specific_properties_when_transforming(
        array $directive,
        array $expectedOnly,
        array $expectedExcept
    ) {
        $dataClass = new class extends Data {
            public string $first;

            public string $second;

            public MultiData $nested;

            #[DataCollectionOf(MultiData::class)]
            public DataCollection $collection;
        };

        $data = $dataClass::from([
            'first' => 'A',
            'second' => 'B',
            'nested' => ['first' => 'C', 'second' => 'D'],
            'collection' => [
                ['first' => 'E', 'second' => 'F'],
                ['first' => 'G', 'second' => 'H'],
            ],
        ]);

        $this->assertEquals($expectedOnly, $data->only(...$directive)->toArray());
        $this->assertEquals($expectedExcept, $data->except(...$directive)->toArray());
    }

    public function onlyInclusionDataProvider(): Generator
    {
        yield 'single' => [
            'directive' => ['first'],
            'expectedOnly' => [
                'first' => 'A',
            ],
            'expectedExcept' => [
                'second' => 'B',
                'nested' => ['first' => 'C', 'second' => 'D'],
                'collection' => [
                    ['first' => 'E', 'second' => 'F'],
                    ['first' => 'G', 'second' => 'H'],
                ],
            ],
        ];

        yield 'multi' => [
            'directive' => ['first', 'second'],
            'expectedOnly' => [
                'first' => 'A',
                'second' => 'B',
            ],
            'expectedExcept' => [
                'nested' => ['first' => 'C', 'second' => 'D'],
                'collection' => [
                    ['first' => 'E', 'second' => 'F'],
                    ['first' => 'G', 'second' => 'H'],
                ],
            ],
        ];

        yield 'multi-2' => [
            'directive' => ['{first,second}'],
            'expectedOnly' => [
                'first' => 'A',
                'second' => 'B',
            ],
            'expectedExcept' => [
                'nested' => ['first' => 'C', 'second' => 'D'],
                'collection' => [
                    ['first' => 'E', 'second' => 'F'],
                    ['first' => 'G', 'second' => 'H'],
                ],
            ],
        ];

        yield 'all' => [
            'directive' => ['*'],
            'expectedOnly' => [
                'first' => 'A',
                'second' => 'B',
                'nested' => ['first' => 'C', 'second' => 'D'],
                'collection' => [
                    ['first' => 'E', 'second' => 'F'],
                    ['first' => 'G', 'second' => 'H'],
                ],
            ],
            'expectedExcept' => [],
        ];

        yield 'nested' => [
            'directive' => ['nested'],
            'expectedOnly' => [
                'nested' => [],
            ],
            'expectedExcept' => [
                'first' => 'A',
                'second' => 'B',
                'collection' => [
                    ['first' => 'E', 'second' => 'F'],
                    ['first' => 'G', 'second' => 'H'],
                ],
            ],
        ];

        yield 'nested.single' => [
            'directive' => ['nested.first'],
            'expectedOnly' => [
                'nested' => ['first' => 'C'],
            ],
            'expectedExcept' => [
                'first' => 'A',
                'second' => 'B',
                'nested' => ['second' => 'D'],
                'collection' => [
                    ['first' => 'E', 'second' => 'F'],
                    ['first' => 'G', 'second' => 'H'],
                ],
            ],
        ];

        yield 'nested.multi' => [
            'directive' => ['nested.{first, second}'],
            'expectedOnly' => [
                'nested' => ['first' => 'C', 'second' => 'D'],
            ],
            'expectedExcept' => [
                'first' => 'A',
                'second' => 'B',
                'nested' => [],
                'collection' => [
                    ['first' => 'E', 'second' => 'F'],
                    ['first' => 'G', 'second' => 'H'],
                ],
            ],
        ];

        yield 'nested-all' => [
            'directive' => ['nested.*'],
            'expectedOnly' => [
                'nested' => ['first' => 'C', 'second' => 'D'],
            ],
            'expectedExcept' => [
                'first' => 'A',
                'second' => 'B',
                'nested' => [],
                'collection' => [
                    ['first' => 'E', 'second' => 'F'],
                    ['first' => 'G', 'second' => 'H'],
                ],
            ],
        ];

        yield 'collection' => [
            'directive' => ['collection'],
            'expectedOnly' => [
                'collection' => [
                    [],
                    [],
//                    ['first' => 'E', 'second' => 'F'],
//                    ['first' => 'G', 'second' => 'H'],
                ],
            ],
            'expectedExcept' => [
                'first' => 'A',
                'second' => 'B',
                'nested' => ['first' => 'C', 'second' => 'D'],
            ],
        ];

        yield 'collection-single' => [
            'directive' => ['collection.first'],
            'expectedOnly' => [
                'collection' => [
                    ['first' => 'E'],
                    ['first' => 'G'],
                ],
            ],
            'expectedExcept' => [
                'first' => 'A',
                'second' => 'B',
                'nested' => ['first' => 'C', 'second' => 'D'],
                'collection' => [
                    ['second' => 'F'],
                    ['second' => 'H'],
                ]
            ],
        ];

        yield 'collection-multi' => [
            'directive' => ['collection.first', 'collection.second'],
            'expectedOnly' => [
                'collection' => [
                    ['first' => 'E', 'second' => 'F'],
                    ['first' => 'G', 'second' => 'H'],
                ],
            ],
            'expectedExcept' => [
                'first' => 'A',
                'second' => 'B',
                'nested' => ['first' => 'C', 'second' => 'D'],
                'collection' => [
                    [],
                    [],
                ]
            ],
        ];

        yield 'collection-all' => [
            'directive' => ['collection.*'],
            'expectedOnly' => [
                'collection' => [
                    ['first' => 'E', 'second' => 'F'],
                    ['first' => 'G', 'second' => 'H'],
                ],
            ],
            'expectedExcept' => [
                'first' => 'A',
                'second' => 'B',
                'nested' => ['first' => 'C', 'second' => 'D'],
                'collection' => [
                    [],
                    [],
                ]
            ],
        ];
    }
}
