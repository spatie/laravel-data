<?php

use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\Lazy\ClosureLazy;
use Spatie\LaravelData\Support\TypeScriptTransformer\DataTypeScriptTransformer;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\Snapshots\Driver;
use Spatie\TypeScriptTransformer\Attributes\Optional as TypeScriptOptional;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Support\WritingContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformed\Untransformable;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use function Spatie\Snapshots\assertMatchesSnapshot as baseAssertMatchesSnapshot;

function assertMatchesSnapshot($actual, Driver $driver = null): void
{
    baseAssertMatchesSnapshot(str_replace('\\r\\n', '\\n', $actual), $driver);
}

function transformData(Data $data): string
{
    $transformer = app(DataTypeScriptTransformer::class);

    $transformed = $transformer->transform(
        new ReflectionClass($data),
        new TransformationContext('SomeData', ['App', 'Data'])
    );

    return $transformed->typeScriptNode->write(new WritingContext(
        fn (Reference $reference) => '{%'.$reference->humanFriendlyName().'%}'
    ));
}

it('will transform data objects', function () {
    $transformer = app(DataTypeScriptTransformer::class);

    $transformed = $transformer->transform(
        new ReflectionClass(SimpleData::class),
        new TransformationContext('SomeData', ['App', 'Data'])
    );

    expect($transformed)->toBeInstanceOf(Transformed::class);

    $someClass = new class {

    };

    $transformed = $transformer->transform(
        new ReflectionClass($someClass::class),
        new TransformationContext('SomeData', ['App', 'Data'])
    );

    expect($transformed)->toBeInstanceOf(Untransformable::class);
});

it('can convert a data object to Typescript', function () {
    $data = new class (null, Optional::create(), 42, true, 'Hello world', 3.14, ['the', 'meaning', 'of', 'life'], Lazy::create(fn () => 'Lazy'), Lazy::closure(fn () => 'Lazy'), SimpleData::from('Simple data'), new DataCollection(SimpleData::class, []), new DataCollection(SimpleData::class, []), new DataCollection(SimpleData::class, [])) extends Data {
        public function __construct(
            public null|int $nullable,
            public Optional|int $undefineable,
            public int $int,
            public bool $bool,
            public string $string,
            public float $float,
            /** @var string[] */
            public array $array,
            public Lazy|string $lazy,
            public ClosureLazy|string $closureLazy,
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

    assertMatchesSnapshot(transformData($data));
});

it('uses the correct types for data collection of attributes', function () {
    $collection = new DataCollection(SimpleData::class, []);

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

    assertMatchesSnapshot(transformData($data));
});

it('uses the correct types for paginated data collection for attributes ', function () {
    $collection = new PaginatedDataCollection(SimpleData::class, new LengthAwarePaginator([], 0, 15));

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

    assertMatchesSnapshot(transformData($data));
});

it('uses the correct types for cursor paginated data collection of attributes', function () {
    $collection = new CursorPaginatedDataCollection(SimpleData::class, new CursorPaginator([], 15));

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

    assertMatchesSnapshot(transformData($data));
});

it('outputs types with properties using their mapped name', function () {
    $data = new class ('Good job Ruben', 'Hi Ruben') extends Data {
        public function __construct(
            #[MapOutputName(SnakeCaseMapper::class)]
            public string $someCamelCaseProperty,
            #[MapOutputName('some:non:standard:property')]
            public string $someNonStandardProperty,
        ) {
        }
    };

    assertMatchesSnapshot(transformData($data));
});

it('it respects a TypeScript property optional attribute', function () {
    $data = new class (10, 'Ruben') extends Data {
        public function __construct(
            #[TypeScriptOptional]
            public int $id,
            public string $name,
        ) {
        }
    };

    assertMatchesSnapshot(transformData($data));
});

it('it respects a TypeScript class optional attribute', function () {
    #[TypeScriptOptional]
    class DummyTypeScriptOptionalClass extends Data
    {
        public function __construct(
            public int $id,
            public string $name,
        ) {
        }
    }

    $transformer = new DataTypeScriptTransformer($config);
    $reflection = new ReflectionClass(DummyTypeScriptOptionalClass::class);

    $this->assertTrue($transformer->canTransform($reflection));
    $this->assertEquals(
        <<<TXT
        {
        id?: number;
        name?: string;
        }
        TXT,
        $transformer->transform($reflection, 'DataObject')->transformed
    );
})->skip('Should be fixed in TS transformer');
