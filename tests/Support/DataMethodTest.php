<?php

namespace Spatie\LaravelData\Tests\Support;

use ReflectionMethod;
use ReflectionProperty;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataMethod;
use Spatie\LaravelData\Support\DataParameter;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\DataWithMultipleArgumentCreationMethod;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;

class DataMethodTest extends TestCase
{
    /** @test */
    public function it_can_create_a_data_method_from_a_constructor()
    {
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
    }

    /** @test */
    public function it_can_create_a_data_method_from_a_magic_method()
    {
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
    }

    /** @test */
    public function it_correctly_accepts_single_values_as_magic_creation_method()
    {
        $class = new class () extends Data {
            public static function fromString(
                string $property,
            ) {
            }
        };

        $method = DataMethod::create(new ReflectionMethod($class, 'fromString'));

        $this->assertTrue($method->accepts('Hello'));
        $this->assertFalse($method->accepts(3.14));
    }

    /** @test */
    public function it_correctly_accepts_single_inherited_values_as_magic_creation_method()
    {
        $class = new class () extends Data {
            public static function fromString(
                Data $property,
            ) {
            }
        };

        $method = DataMethod::create(new ReflectionMethod($class, 'fromString'));

        $this->assertTrue($method->accepts(new SimpleData('Hello')));
    }

    /** @test */
    public function it_correctly_accepts_multiple_values_as_magic_creation_method()
    {
        $method = DataMethod::create(new ReflectionMethod(DataWithMultipleArgumentCreationMethod::class, 'fromMultiple'));

        $this->assertTrue($method->accepts('Hello', 42));
        $this->assertTrue($method->accepts(...[
            'number' => 42,
            'string' => 'hello',
        ]));

        $this->assertFalse($method->accepts(42, 'Hello'));
    }

    /** @test */
    public function it_correctly_accepts_mixed_values_as_magic_creation_method()
    {
        $class = new class () extends Data {
            public static function fromString(
                mixed $property,
            ) {
            }
        };

        $method = DataMethod::create(new ReflectionMethod($class, 'fromString'));

        $this->assertTrue($method->accepts(new SimpleData('Hello')));
        $this->assertTrue($method->accepts(null));
    }

    /** @test */
    public function it_correctly_accepts_values_with_defaults_as_magic_creation_method()
    {
        $class = new class () extends Data {
            public static function fromString(
                string $property = 'Hello',
            ) {
            }
        };

        $method = DataMethod::create(new ReflectionMethod($class, 'fromString'));

        $this->assertTrue($method->accepts('Hello'));
        $this->assertTrue($method->accepts());
    }

    /** @test */
    public function it_needs_a_correct_amount_of_parameters_as_magic_creation_method()
    {
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
    }
}
