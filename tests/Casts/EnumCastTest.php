<?php

namespace Spatie\LaravelData\Tests\Casts;

use Exception;
use ReflectionProperty;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\DummyUnitEnum;
use Spatie\LaravelData\Tests\TestCase;

class EnumCastTest extends TestCase
{
    protected EnumCast $caster;

    public function setUp(): void
    {
        parent::setUp();

        if (version_compare(phpversion(), '8.1', '<')) {
            $this->markTestIncomplete('No enum support in PHP 8.1');
        }

        $this->caster = new EnumCast();
    }

    /** @test */
    public function it_can_cast_enum()
    {
        $class = new class () {
            public DummyBackedEnum $enum;
        };

        $this->assertEquals(
            DummyBackedEnum::FOO,
            $this->caster->cast(DataProperty::create(new ReflectionProperty($class, 'enum')), 'foo')
        );
    }

    /** @test */
    public function it_fails_when_it_cannot_cast_an_enum_from_value()
    {
        $class = new class () {
            public DummyBackedEnum $enum;
        };

        $this->expectException(Exception::class);

        $this->assertEquals(
            DummyBackedEnum::FOO,
            $this->caster->cast(DataProperty::create(new ReflectionProperty($class, 'enum')), 'bar')
        );
    }

    /** @test */
    public function it_fails_when_casting_a_unit_enum()
    {
        $class = new class () {
            public DummyUnitEnum $enum;
        };

        $this->assertEquals(
            Uncastable::create(),
            $this->caster->cast(DataProperty::create(new ReflectionProperty($class, 'enum')), 'foo')
        );
    }

    /** @test */
    public function it_fails_with_other_types()
    {
        $class = new class () {
            public int $int;
        };

        $this->assertEquals(
            Uncastable::create(),
            $this->caster->cast(DataProperty::create(new ReflectionProperty($class, 'int')), 'foo')
        );
    }
}
