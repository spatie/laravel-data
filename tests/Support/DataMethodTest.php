<?php

namespace Spatie\LaravelData\Tests\Support;

use Dflydev\DotAccessData\Data;
use ReflectionMethod;
use Spatie\LaravelData\Support\DataMethod;
use Spatie\LaravelData\Support\DataParameter;
use Spatie\LaravelData\Tests\TestCase;

class DataMethodTest extends TestCase
{
    /** @test */
    public function it_can_create_a_data_method_from_a_constructor()
    {
        $class = new class () extends Data {
            public function __construct(
                public string $property = 'hello',
            ) {
            }
        };

        $method = DataMethod::create(new ReflectionMethod($class, '__construct'));

        $this->assertEquals('__construct', $method->name);
        $this->assertCount(1, $method->parameters);
        $this->assertInstanceOf(DataParameter::class, $method->parameters[0]);
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
    public function it_correctly_accepts_values()
    {
    }
}
