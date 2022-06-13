it('ca
uses(TestCase::class);
n save a data collection', function () {
    DummyModelWithCasts::create([
        'data_collection' => SimpleData::collection([
            new SimpleData('Hello'),
            new SimpleData('World'),
        ]),
    ]);

    $this->assertDatabaseHas(DummyModelWithCasts::class, [
        'data_collection' => json_encode([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]),
    ]);
});

it('can save a data object as an array', function () {
    DummyModelWithCasts::create([
        'data_collection' => [
            ['string' => 'Hello'],
            ['string' => 'World'],
        ],
    ]);

    $this->assertDatabaseHas(DummyModelWithCasts::class, [
        'data_collection' => json_encode([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]),
    ]);
});

it('can load a data object', function () {
    DB::table('dummy_model_with_casts')->insert([
        'data_collection' => json_encode([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]),
    ]);

    /** @var \Spatie\LaravelData\Tests\Fakes\DummyModelWithCasts $model */
    $model = DummyModelWithCasts::first();

    $this->assertEquals(
        SimpleData::collection([
            new SimpleData('Hello'),
            new SimpleData('World'),
        ]),
        $model->data_collection
    );
});

it('can save a null as a value', function () {
    DummyModelWithCasts::create([
        'data_collection' => null,
    ]);

    $this->assertDatabaseHas(DummyModelWithCasts::class, [
        'data_collection' => null,
    ]);
});

it('can load null as a value', function () {
    DB::table('dummy_model_with_casts')->insert([
        'data_collection' => null,
    ]);

    /** @var \Spatie\LaravelData\Tests\Fakes\DummyModelWithCasts $model */
    $model = DummyModelWithCasts::first();

    $this->assertNull($model->data_collection);
});
