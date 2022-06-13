it('ke
uses(TestCase::class);
eps track of a global map from attribute', function () {
    $dataClass = DataClass::create(new ReflectionClass(DataWithMapper::class));

    $this->assertEquals(
        'cased_property',
        $dataClass->properties->get('casedProperty')->inputMappedName
    );

    $this->assertEquals(
        'cased_property',
        $dataClass->properties->get('casedProperty')->outputMappedName
    );
});

it('will provide information about special methods', function () {
    $class = DataClass::create(new ReflectionClass(SimpleData::class));

    $this->assertArrayHasKey('fromString', $class->methods);
    $this->assertInstanceOf(DataMethod::class, $class->methods->get('fromString'));
});

it('will provide information about the constrcutor', function () {
    $class = DataClass::create(new ReflectionClass(SimpleData::class));

    $this->assertNotNull($class->constructorMethod);
    $this->assertInstanceOf(DataMethod::class, $class->constructorMethod);
});

it('will populate defaults to properties when they exist', function () {
    /** @var \Spatie\LaravelData\Support\DataProperty[] $properties */
    $properties = DataClass::create(new ReflectionClass(DataWithDefaults::class))->properties->values();

    $this->assertEquals('property', $properties[0]->name);
    $this->assertFalse($properties[0]->hasDefaultValue);

    $this->assertEquals('default_property', $properties[1]->name);
    $this->assertTrue($properties[1]->hasDefaultValue);
    $this->assertEquals('Hello', $properties[1]->defaultValue);

    $this->assertEquals('promoted_property', $properties[2]->name);
    $this->assertFalse($properties[2]->hasDefaultValue);

    $this->assertEquals('default_promoted_property', $properties[3]->name);
    $this->assertTrue($properties[3]->hasDefaultValue);
    $this->assertEquals('Hello Again', $properties[3]->defaultValue);
});
