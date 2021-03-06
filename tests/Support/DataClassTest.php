<?php

namespace Spatie\LaravelData\Tests\Support;

use ReflectionClass;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataMethod;
use Spatie\LaravelData\Tests\DataWithDefaults;
use Spatie\LaravelData\Tests\Fakes\DataWithMapper;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;

class DataClassTest extends TestCase
{
    /** @test */
    public function it_keeps_track_of_a_global_map_from_attribute()
    {
        $dataClass = DataClass::create(new ReflectionClass(DataWithMapper::class));

        $this->assertEquals(
            'cased_property',
            $dataClass->properties->get('casedProperty')->inputMappedName
        );

        $this->assertEquals(
            'cased_property',
            $dataClass->properties->get('casedProperty')->outputMappedName
        );
    }

    /** @test */
    public function it_will_provide_information_about_special_methods()
    {
        $class = DataClass::create(new ReflectionClass(SimpleData::class));

        $this->assertArrayHasKey('fromString', $class->methods);
        $this->assertInstanceOf(DataMethod::class, $class->methods->get('fromString'));
    }

    /** @test */
    public function it_will_provide_information_about_the_constrcutor()
    {
        $class = DataClass::create(new ReflectionClass(SimpleData::class));

        $this->assertNotNull($class->constructorMethod);
        $this->assertInstanceOf(DataMethod::class, $class->constructorMethod);
    }

    /** @test */
    public function it_will_populate_defaults_to_properties_when_they_exist()
    {
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
    }
}
