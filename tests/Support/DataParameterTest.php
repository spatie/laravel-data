<?php

namespace Spatie\LaravelData\Tests\Support;

use ReflectionParameter;
use Spatie\BetterTypes\Type;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataParameter;
use Spatie\LaravelData\Tests\TestCase;

class DataParameterTest extends TestCase
{
    /** @test */
    public function it_can_create_a_data_parameter()
    {
        $class = new class ('', '', '') extends Data {
            public function __construct(
                string $nonPromoted,
                public $withoutType,
                public string $property,
                public string $propertyWithDefault = 'hello',
            ) {
            }
        };

        $reflection = new ReflectionParameter([$class::class, '__construct'], 'nonPromoted');
        $parameter = DataParameter::create($reflection);

        $this->assertEquals('nonPromoted', $parameter->name);
        $this->assertFalse($parameter->isPromoted);
        $this->assertFalse($parameter->hasDefaultValue);
        $this->assertNull($parameter->defaultValue);
        $this->assertEquals(new Type($reflection->getType()), $parameter->types);

        $reflection = new ReflectionParameter([$class::class, '__construct'], 'withoutType');
        $parameter = DataParameter::create($reflection);

        $this->assertEquals('withoutType', $parameter->name);
        $this->assertTrue($parameter->isPromoted);
        $this->assertFalse($parameter->hasDefaultValue);
        $this->assertNull($parameter->defaultValue);
        $this->assertEquals(new Type($reflection->getType()), $parameter->types);

        $reflection = new ReflectionParameter([$class::class, '__construct'], 'property');
        $parameter = DataParameter::create($reflection);

        $this->assertEquals('property', $parameter->name);
        $this->assertTrue($parameter->isPromoted);
        $this->assertFalse($parameter->hasDefaultValue);
        $this->assertNull($parameter->defaultValue);
        $this->assertEquals(new Type($reflection->getType()), $parameter->types);

        $reflection = new ReflectionParameter([$class::class, '__construct'], 'propertyWithDefault');
        $parameter = DataParameter::create($reflection);

        $this->assertEquals('propertyWithDefault', $parameter->name);
        $this->assertTrue($parameter->isPromoted);
        $this->assertTrue($parameter->hasDefaultValue);
        $this->assertEquals('hello', $parameter->defaultValue);
        $this->assertEquals(new Type($reflection->getType()), $parameter->types);
    }
}
