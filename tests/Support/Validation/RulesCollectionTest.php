it('ca
uses(TestCase::class);
n add rules', function () {
    $collection = RulesCollection::create()
        ->add(new Required())
        ->add(new Prohibited(), new Min(0));

    $this->assertEquals(
        [new Required(), new Prohibited(), new Min(0)],
        $collection->all()
    );
});

it('will remove the previous rule if a new version is added', function () {
    $collection = RulesCollection::create()
        ->add(new Min(10))
        ->add(new Min(314));

    $this->assertEquals([new Min(314)], $collection->all());
});

it('can remove rules by type', function () {
    $collection = RulesCollection::create()
        ->add(new Min(10))
        ->removeType(new Min(314));

    $this->assertEquals([], $collection->all());
});

it('can remove rules by class', function () {
    $collection = RulesCollection::create()
        ->add(new Min(10))
        ->removeType(Min::class);

    $this->assertEquals([], $collection->all());
});

it('can normalize rules', function () {
    $collection = RulesCollection::create()
        ->add(new Min(10))
        ->add(new Required())
        ->add(new Enum(FakeEnum::class));

    $this->assertEquals([new Min(10), new Required(), new Enum(FakeEnum::class)], $collection->all());
    $this->assertEquals([new Min(10), new Required(), new BaseEnum(FakeEnum::class)], $collection->normalize());
});
