it('ca
uses(TestCase::class);
n create a data method from a constructor', function () {
    $class = new class () extends Data {
        public function __construct(
            public string $promotedProperty = 'hello',
            string $property = 'hello',
        ) {
        }
    };

    $method = DataMethod::createConstructor(
        new ReflectionMethod($class, '__construct'),
        collect(['promotedProperty' => DataProperty::create(new ReflectionProperty($class, 'promotedProperty'))])
    );

    $this->assertEquals('__construct', $method->name);
    $this->assertCount(2, $method->parameters);
    $this->assertInstanceOf(DataProperty::class, $method->parameters[0]);
    $this->assertInstanceOf(DataParameter::class, $method->parameters[1]);
    $this->assertTrue($method->isPublic);
    $this->assertFalse($method->isStatic);
    $this->assertFalse($method->isCustomCreationMethod);
});

it('can create a data method from a magic method', function () {
    $class = new class () extends Data {
        public static function fromString(
            string $property,
        ) {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'fromString'));

    $this->assertEquals('fromString', $method->name);
    $this->assertCount(1, $method->parameters);
    $this->assertInstanceOf(DataParameter::class, $method->parameters[0]);
    $this->assertTrue($method->isPublic);
    $this->assertTrue($method->isStatic);
    $this->assertTrue($method->isCustomCreationMethod);
});

it('correctly accepts single values as magic creation method', function () {
    $class = new class () extends Data {
        public static function fromString(
            string $property,
        ) {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'fromString'));

    $this->assertTrue($method->accepts('Hello'));
    $this->assertFalse($method->accepts(3.14));
});

it('correctly accepts single inherited values as magic creation method', function () {
    $class = new class () extends Data {
        public static function fromString(
            Data $property,
        ) {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'fromString'));

    $this->assertTrue($method->accepts(new SimpleData('Hello')));
});

it('correctly accepts multiple values as magic creation method', function () {
    $method = DataMethod::create(new ReflectionMethod(DataWithMultipleArgumentCreationMethod::class, 'fromMultiple'));

    $this->assertTrue($method->accepts('Hello', 42));
    $this->assertTrue($method->accepts(...[
        'number' => 42,
        'string' => 'hello',
    ]));

    $this->assertFalse($method->accepts(42, 'Hello'));
});

it('correctly accepts mixed values as magic creation method', function () {
    $class = new class () extends Data {
        public static function fromString(
            mixed $property,
        ) {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'fromString'));

    $this->assertTrue($method->accepts(new SimpleData('Hello')));
    $this->assertTrue($method->accepts(null));
});

it('correctly accepts values with defaults as magic creation method', function () {
    $class = new class () extends Data {
        public static function fromString(
            string $property = 'Hello',
        ) {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'fromString'));

    $this->assertTrue($method->accepts('Hello'));
    $this->assertTrue($method->accepts());
});

it('needs a correct amount of parameters as magic creation method', function () {
    $class = new class () extends Data {
        public static function fromString(
            string $property,
            string $propertyWithDefault = 'Hello',
        ) {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'fromString'));

    $this->assertTrue($method->accepts('Hello'));
    $this->assertTrue($method->accepts('Hello', 'World'));

    $this->assertFalse($method->accepts());
    $this->assertFalse($method->accepts('Hello', 'World', 'Nope'));
});
