<?php

namespace Spatie\LaravelData\Tests\Support;

use ReflectionClass;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Tests\Fakes\DummyDto;
use Spatie\LaravelData\Tests\TestCase;

class DataClassTest extends TestCase
{
    /** @test */
    public function it_can_find_from_methods_and_the_types_that_can_be_used_with_them()
    {
        $subject = new class (null) extends Data {
            public function __construct(public $property)
            {
            }

            public static function fromString(string $property): static
            {
            }

            public static function fromDummyDto(DummyDto $property): static
            {
            }

            public static function fromNumber(int | float $property): static
            {
            }

            public static function doNotInclude(string $property): static
            {
            }

            public function fromDoNotIncludeA(string $other)
            {
            }

            private static function fromDoNotIncludeB(string $other)
            {
            }

            protected static function fromDoNotIncludeC(string $other)
            {
            }

            public static function fromDoNotIncludeD($other): static
            {
            }

            public static function fromDoNotIncludeE(string $other, string $extra): static
            {
            }
        };

        $this->assertEquals([
            'string' => 'fromString',
            DummyDto::class => 'fromDummyDto',
            'int' => 'fromNumber',
            'float' => 'fromNumber',
        ], DataClass::create(new ReflectionClass($subject))->creationMethods());
    }

    /** @test */
    public function it_can_check_if_a_data_class_has_an_authorisation_method()
    {
        $withMethod = new class (null) extends Data {
            public static function authorize(): bool
            {
            }
        };

        $withNonStaticMethod = new class (null) extends Data {
            public function authorize(): bool
            {
            }
        };

        $withNonPublicMethod = new class (null) extends Data {
            protected static function authorize(): bool
            {
            }
        };

        $withoutMethod = new class (null) extends Data {
        };

        $this->assertTrue(DataClass::create(new ReflectionClass($withMethod))->hasAuthorizationMethod());
        $this->assertFalse(DataClass::create(new ReflectionClass($withNonPublicMethod))->hasAuthorizationMethod());
        $this->assertFalse(DataClass::create(new ReflectionClass($withNonStaticMethod))->hasAuthorizationMethod());
        $this->assertFalse(DataClass::create(new ReflectionClass($withoutMethod))->hasAuthorizationMethod());
    }
}
