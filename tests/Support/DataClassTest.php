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
        $subject = new class(null) extends Data {
            public function __construct(public $property)
            {
            }

            public static function fromString(string $property): static
            {
                return new self($property);
            }

            public static function fromDummyDto(DummyDto $property): static
            {
                return new self($property);
            }

            public static function fromNumber(int | float $property): static
            {
                return new self($property);
            }

            public function fromDoNotInclude(string $other)
            {
            }

            private static function fromAnotherDoNotInclude(string $other)
            {
            }

            public static function fromYetAnotherDoNotInclude($other): static
            {
            }
        };

        $this->assertEquals([
            'string' => 'fromString',
            DummyDto::class => 'fromDummyDto',
            'int' => 'fromNumber',
            'float' => 'fromNumber',
        ], DataClass::create(new ReflectionClass($subject))->customFromMethods());
    }
}
