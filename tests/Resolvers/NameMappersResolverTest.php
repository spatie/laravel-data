it('ca
uses(TestCase::class);
n get an input and output mapper', function () {
    $attributes = getAttributes(new class () {
        #[MapInputName('input'), MapOutputName('output')]
        public $property;
    });

    $this->assertEquals(
        [
            'inputNameMapper' => new ProvidedNameMapper('input'),
            'outputNameMapper' => new ProvidedNameMapper('output'),
        ],
        $this->resolver->execute($attributes)
    );
});

it('can have no mappers', function () {
    $attributes = getAttributes(new class () {
        public $property;
    });

    $this->assertEquals(
        [
            'inputNameMapper' => null,
            'outputNameMapper' => null,
        ],
        $this->resolver->execute($attributes)
    );
});

it('can have a single map attribute', function () {
    $attributes = getAttributes(new class () {
        #[MapName('input', 'output')]
        public $property;
    });

    $this->assertEquals(
        [
            'inputNameMapper' => new ProvidedNameMapper('input'),
            'outputNameMapper' => new ProvidedNameMapper('output'),
        ],
        $this->resolver->execute($attributes)
    );
});

it('can overwrite a general map attribute', function () {
    $attributes = getAttributes(new class () {
        #[MapName('input', 'output'), MapInputName('input_overwritten')]
        public $property;
    });

    $this->assertEquals(
        [
            'inputNameMapper' => new ProvidedNameMapper('input_overwritten'),
            'outputNameMapper' => new ProvidedNameMapper('output'),
        ],
        $this->resolver->execute($attributes)
    );
});

it('can map an int', function () {
    $attributes = getAttributes(new class () {
        #[MapName(0, 3)]
        public $property;
    });

    $this->assertEquals(
        [
            'inputNameMapper' => new ProvidedNameMapper(0),
            'outputNameMapper' => new ProvidedNameMapper(3),
        ],
        $this->resolver->execute($attributes)
    );
});

it('can map a string', function () {
    $attributes = getAttributes(new class () {
        #[MapName('hello', 'world')]
        public $property;
    });

    $this->assertEquals(
        [
            'inputNameMapper' => new ProvidedNameMapper('hello'),
            'outputNameMapper' => new ProvidedNameMapper('world'),
        ],
        $this->resolver->execute($attributes)
    );
});

it('can map a mapper class', function () {
    $attributes = getAttributes(new class () {
        #[MapName(CamelCaseMapper::class, SnakeCaseMapper::class)]
        public $property;
    });

    $this->assertEquals(
        [
            'inputNameMapper' => new CamelCaseMapper(),
            'outputNameMapper' => new SnakeCaseMapper(),
        ],
        $this->resolver->execute($attributes)
    );
});

it('can ignore certain mapper types', function () {
    $attributes = getAttributes(new class () {
        #[MapInputName('input'), MapOutputName(CamelCaseMapper::class)]
        public $property;
    });

    $this->assertEquals(
        [
            'inputNameMapper' => null,
            'outputNameMapper' => new CamelCaseMapper(),
        ],
        NameMappersResolver::create([ProvidedNameMapper::class])->execute($attributes)
    );
});

// Helpers
function getAttributes(object $class): Collection
{
    return collect((new ReflectionProperty($class, 'property'))->getAttributes())
        ->map(fn (ReflectionAttribute $attribute) => $attribute->newInstance());
}
