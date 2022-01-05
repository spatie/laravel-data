<?php

namespace Spatie\LaravelData\Tests\Transformers;

use ReflectionProperty;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\DummyBackedEnum;
use Spatie\LaravelData\Tests\TestCase;
use Spatie\LaravelData\Transformers\EnumTransformer;

class EnumTransformerTest extends TestCase
{
    /** @test */
    public function it_can_transform_enums()
    {
        if (version_compare(phpversion(), '8.1', '<')) {
            $this->markTestIncomplete('No enum support in PHP 8.1');
        }

        $transformer = new EnumTransformer();

        $class = new class () {
            public DummyBackedEnum $enum = DummyBackedEnum::FOO;
        };

        $this->assertEquals(
            DummyBackedEnum::FOO->value,
            $transformer->transform(DataProperty::create(new ReflectionProperty($class, 'enum')), $class->enum)
        );
    }
}
