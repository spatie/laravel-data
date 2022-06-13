it('ca
uses(TestCase::class);
n get the cast attribute with arguments', function () {
    $helper = resolveHelper(new class () {
        #[WithCast(DateTimeInterfaceCast::class, 'd-m-y')]
        public SimpleData $property;
    });

    $this->assertEquals(new DateTimeInterfaceCast('d-m-y'), $helper->cast);
});

it('can get the transformer attribute', function () {
    $helper = resolveHelper(new class () {
        #[WithTransformer(DateTimeInterfaceTransformer::class)]
        public SimpleData $property;
    });

    $this->assertEquals(new DateTimeInterfaceTransformer(), $helper->transformer);
});

it('can get the transformer attribute with arguments', function () {
    $helper = resolveHelper(new class () {
        #[WithTransformer(DateTimeInterfaceTransformer::class, 'd-m-y')]
        public SimpleData $property;
    });

    $this->assertEquals(new DateTimeInterfaceTransformer('d-m-y'), $helper->transformer);
});

it('can get the mapped input name', function () {
    $helper = resolveHelper(new class () {
        #[MapInputName('other')]
        public SimpleData $property;
    });

    $this->assertEquals('other', $helper->inputMappedName);
});

it('can get the mapped output name', function () {
    $helper = resolveHelper(new class () {
        #[MapOutputName('other')]
        public SimpleData $property;
    });

    $this->assertEquals('other', $helper->outputMappedName);
});

it('can get all attributes', function () {
    $helper = resolveHelper(new class () {
        #[MapInputName('other')]
        #[WithTransformer(DateTimeInterfaceTransformer::class)]
        #[WithCast(DateTimeInterfaceCast::class)]
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection $property;
    });

    $this->assertCount(4, $helper->attributes);
});

it('can get the default value', function () {
    $helper = resolveHelper(new class () {
        public string $property;
    });

    $this->assertFalse($helper->hasDefaultValue);

    $helper = resolveHelper(new class () {
        public string $property = 'hello';
    });

    $this->assertTrue($helper->hasDefaultValue);
    $this->assertEquals('hello', $helper->defaultValue);
});

it('can check if the property is promoted', function () {
    $helper = resolveHelper(new class ('') {
        public function __construct(
            public string $property,
        ) {
        }
    });

    $this->assertTrue($helper->isPromoted);

    $helper = resolveHelper(new class () {
        public string $property;
    });

    $this->assertFalse($helper->isPromoted);
});

it('can check if a property should be validated', function () {
    $this->assertTrue(resolveHelper(new class () {
        public string $property;
    })->validate);

    $this->assertFalse(resolveHelper(new class () {
        #[WithoutValidation]
        public string $property;
    })->validate);
});

// Helpers
function resolveHelper(object $class, bool $hasDefaultValue = false, mixed $defaultValue = null): DataProperty
{
    $reflectionProperty = new ReflectionProperty($class, 'property');

    return DataProperty::create($reflectionProperty, $hasDefaultValue, $defaultValue);
}
