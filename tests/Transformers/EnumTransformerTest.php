it('ca
n transform enums', function () {
    $this->onlyPHP81();

    $transformer = new EnumTransformer();

    $class = new class () {
        public DummyBackedEnum $enum = DummyBackedEnum::FOO;
    };

    $this->assertEquals(
        DummyBackedEnum::FOO->value,
        $transformer->transform(DataProperty::create(new ReflectionProperty($class, 'enum')), $class->enum)
    );
});
