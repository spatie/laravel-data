it('ca
uses(TestCase::class);
n covert a data object to typescript', function () {
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
//              Types package is not smart enough
//                /** @var DataCollection<\Spatie\LaravelData\Tests\Fakes\SimpleData> */
            #[DataCollectionOf(SimpleData::class)]
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
});
