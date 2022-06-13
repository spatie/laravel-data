it('ca
uses(TestCase::class);
n cast enum', function () {
    $class = new class () {
        public DummyBackedEnum $enum;
    };

    $this->assertEquals(
        DummyBackedEnum::FOO,
        $this->caster->cast(DataProperty::create(new ReflectionProperty($class, 'enum')), 'foo', [])
    );
});

it('fails when it cannot cast an enum from value', function () {
    $class = new class () {
        public DummyBackedEnum $enum;
    };

    $this->expectException(Exception::class);

    $this->assertEquals(
        DummyBackedEnum::FOO,
        $this->caster->cast(DataProperty::create(new ReflectionProperty($class, 'enum')), 'bar', [])
    );
});

it('fails when casting a unit enum', function () {
    $class = new class () {
        public DummyUnitEnum $enum;
    };

    $this->assertEquals(
        Uncastable::create(),
        $this->caster->cast(DataProperty::create(new ReflectionProperty($class, 'enum')), 'foo', [])
    );
});

it('fails with other types', function () {
    $class = new class () {
        public int $int;
    };

    $this->assertEquals(
        Uncastable::create(),
        $this->caster->cast(DataProperty::create(new ReflectionProperty($class, 'int')), 'foo', [])
    );
});
