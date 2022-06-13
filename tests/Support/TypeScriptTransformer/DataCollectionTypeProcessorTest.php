it('us
uses(TestCase::class);
es the correct types for data collection of attributes', function () {
    $config = TypeScriptTransformerConfig::create();

    $data = new class (SimpleData::collection([]), SimpleData::collection([]), SimpleData::collection([])) extends Data {
        public function __construct(
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection $dataCollection,
            #[DataCollectionOf(SimpleData::class)]
            public ?DataCollection $dataCollectionWithNull,
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection|null $dataCollectionWithNullable,
        ) {
        }
    };

    $transformer = new DataTypeScriptTransformer($config);

    $reflection = new ReflectionClass($data);

    $this->assertTrue($transformer->canTransform($reflection));
    $this->assertMatchesSnapshot($transformer->transform($reflection, 'DataObject')->transformed);
});
