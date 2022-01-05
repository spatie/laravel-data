<?php

namespace Spatie\LaravelData\Tests\Transformer;

use ReflectionProperty;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\DummyBackedEnum;
use Spatie\LaravelData\Tests\TestCase;
use Spatie\LaravelData\Transformers\EnumTransformer;

/** @requires PHP >= 8.1 */
class EnumTransformerTest extends TestCase
{
    /** @test */
    public function it_can_transform_enum()
    {
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
