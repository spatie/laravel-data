<?php

namespace Spatie\LaravelData\Tests\Support\TypeScriptTransformer;

use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use ReflectionClass;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\TypeScriptTransformer\DataTypeScriptTransformer;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class DataTypeScriptTransformerTest extends TestCase
{
    /** @test */
    public function it_can_covert_a_data_object_to_typescript()
    {
        $config = TypeScriptTransformerConfig::create();

        $data = new class (null, Optional::create(), 42, true, 'Hello world', 3.14, ['the', 'meaning', 'of', 'life'], Lazy::create(fn () => 'Lazy'), SimpleData::from('Simple data'), SimpleData::collection([]), SimpleData::collection([]), SimpleData::collection([])) extends Data {
            public function __construct(
                public null|int $nullable,
                public Optional | int $undefineable,
                public int $int,
                public bool $bool,
                public string $string,
                public float $float,
                /** @var string[] */
                public array $array,
                public Lazy|string $lazy,
                public SimpleData $simpleData,
                /** @var \Spatie\LaravelData\Tests\Fakes\SimpleData[] */
                public DataCollection $dataCollection,
                /** @var DataCollection<\Spatie\LaravelData\Tests\Fakes\SimpleData> */
                public DataCollection $dataCollectionAlternative,
                #[DataCollectionOf(SimpleData::class)]
                public DataCollection $dataCollectionWithAttribute,
            ) {
            }
        };

        $transformer = new DataTypeScriptTransformer($config);

        $reflection = new ReflectionClass($data);

        $this->assertTrue($transformer->canTransform($reflection));
        $this->assertMatchesSnapshot($transformer->transform($reflection, 'DataObject')->transformed);
    }

    /** @test */
    public function it_uses_the_correct_types_for_data_collection_of_attributes()
    {
        $config = TypeScriptTransformerConfig::create();

        $collection = SimpleData::collection([]);

        $data = new class ($collection, $collection, $collection, $collection, $collection, $collection, $collection) extends Data {
            public function __construct(
                #[DataCollectionOf(SimpleData::class)]
                public DataCollection $collection,
                #[DataCollectionOf(SimpleData::class)]
                public ?DataCollection $collectionWithNull,
                #[DataCollectionOf(SimpleData::class)]
                public DataCollection|null $collectionWithNullable,
                #[DataCollectionOf(SimpleData::class)]
                public DataCollection|Optional $optionalCollection,
                #[DataCollectionOf(SimpleData::class)]
                public DataCollection|Optional|null $optionalCollectionWithNullable,
                #[DataCollectionOf(SimpleData::class)]
                public DataCollection|Lazy $lazyCollection,
                #[DataCollectionOf(SimpleData::class)]
                public DataCollection|Lazy|null $lazyCollectionWithNullable,
            ) {
            }
        };

        $transformer = new DataTypeScriptTransformer($config);

        $reflection = new ReflectionClass($data);

        $this->assertTrue($transformer->canTransform($reflection));
        $this->assertMatchesSnapshot($transformer->transform($reflection, 'DataObject')->transformed);
    }

    /** @test */
    public function it_uses_the_correct_types_for_paginated_data_collection_of_attributes()
    {
        $config = TypeScriptTransformerConfig::create();

        $collection = SimpleData::collection(new LengthAwarePaginator([], 0, 15));

        $data = new class ($collection, $collection, $collection, $collection, $collection, $collection, $collection) extends Data {
            public function __construct(
                #[DataCollectionOf(SimpleData::class)]
                public PaginatedDataCollection $collection,
                #[DataCollectionOf(SimpleData::class)]
                public ?PaginatedDataCollection $collectionWithNull,
                #[DataCollectionOf(SimpleData::class)]
                public PaginatedDataCollection|null $collectionWithNullable,
                #[DataCollectionOf(SimpleData::class)]
                public PaginatedDataCollection|Optional $optionalCollection,
                #[DataCollectionOf(SimpleData::class)]
                public PaginatedDataCollection|Optional|null $optionalCollectionWithNullable,
                #[DataCollectionOf(SimpleData::class)]
                public PaginatedDataCollection|Lazy $lazyCollection,
                #[DataCollectionOf(SimpleData::class)]
                public PaginatedDataCollection|Lazy|null $lazyCollectionWithNullable,
            ) {
            }
        };

        $transformer = new DataTypeScriptTransformer($config);

        $reflection = new ReflectionClass($data);

        $this->assertTrue($transformer->canTransform($reflection));
        $this->assertMatchesSnapshot($transformer->transform($reflection, 'DataObject')->transformed);
    }

    /** @test */
    public function it_uses_the_correct_types_for_cursor_paginated_data_collection_of_attributes()
    {
        $config = TypeScriptTransformerConfig::create();

        $collection = SimpleData::collection(new CursorPaginator([], 15));

        $data = new class ($collection, $collection, $collection, $collection, $collection, $collection, $collection) extends Data {
            public function __construct(
                #[DataCollectionOf(SimpleData::class)]
                public CursorPaginatedDataCollection $collection,
                #[DataCollectionOf(SimpleData::class)]
                public ?CursorPaginatedDataCollection $collectionWithNull,
                #[DataCollectionOf(SimpleData::class)]
                public CursorPaginatedDataCollection|null $collectionWithNullable,
                #[DataCollectionOf(SimpleData::class)]
                public CursorPaginatedDataCollection|Optional $optionalCollection,
                #[DataCollectionOf(SimpleData::class)]
                public CursorPaginatedDataCollection|Optional|null $optionalCollectionWithNullable,
                #[DataCollectionOf(SimpleData::class)]
                public CursorPaginatedDataCollection|Lazy $lazyCollection,
                #[DataCollectionOf(SimpleData::class)]
                public CursorPaginatedDataCollection|Lazy|null $lazyCollectionWithNullable,
            ) {
            }
        };

        $transformer = new DataTypeScriptTransformer($config);

        $reflection = new ReflectionClass($data);

        $this->assertTrue($transformer->canTransform($reflection));
        $this->assertMatchesSnapshot($transformer->transform($reflection, 'DataObject')->transformed);
    }

    /** @test */
    public function it_outputs_types_with_properties_using_their_mapped_name()
    {
        $config = TypeScriptTransformerConfig::create();

        $data = new class ('Good job Ruben') extends Data {
            public function __construct(
                #[MapOutputName(SnakeCaseMapper::class)]
                public string $someCamelCaseProperty,
            ) {
            }
        };

        $transformer = new DataTypeScriptTransformer($config);
        $reflection = new ReflectionClass($data);

        $this->assertTrue($transformer->canTransform($reflection));
        $this->assertMatchesSnapshot($transformer->transform($reflection, 'DataObject')->transformed);
    }
}
