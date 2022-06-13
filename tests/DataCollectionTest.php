
// Da
uses(TestCase::class);
tasets
dataset('arrayAccessCollections', function () {
    yield "array" => [
        fn () => SimpleData::collection([
            'A', 'B', SimpleData::from('C'), SimpleData::from('D'),
        ]),
    ];

    yield "collection" => [
        fn () => SimpleData::collection([
            'A', 'B', SimpleData::from('C'), SimpleData::from('D'),
        ]),
    ];
});

// Helpers
function it_can_reset_the_keys()
{
    $collection = SimpleData::collection([
        1 => SimpleData::from('a'),
        3 => SimpleData::from('b'),
    ]);

    test()->assertEquals(
        SimpleData::collection([
            0 => SimpleData::from('a'),
            1 => SimpleData::from('b'),
        ]),
        $collection->values()
    );
}
