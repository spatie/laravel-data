<?php

namespace Spatie\LaravelData\Tests\Resolvers;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Tests\Fakes\DummyDto;
use Spatie\LaravelData\Tests\Fakes\DummyModel;
use Spatie\LaravelData\Tests\TestCase;

class DataFromSomethingResolverTest extends TestCase
{
    /** @test */
    public function it_can_create_data_from_a_custom_method()
    {
        $data = new class('') extends Data {
            public function __construct(public string $string)
            {
            }

            public static function fromString(string $string): static
            {
                return new self($string);
            }

            public static function fromDto(DummyDto $dto)
            {
                return new self($dto->artist);
            }

            public static function fromArray(array $payload)
            {
                return new self($payload['string']);
            }
        };

        $this->assertEquals(new $data('Hello World'), $data::from('Hello World'));
        $this->assertEquals(new $data('Rick Astley'), $data::from(DummyDto::rick()));
        $this->assertEquals(new $data('Hello World'), $data::from(['string' => 'Hello World']));
        $this->assertEquals(new $data('Hello World'), $data::from(DummyModel::make(['string' => 'Hello World'])));
    }

    /** @test */
    public function it_can_create_data_from_a_custom_optional_method()
    {
        $data = new class('') extends Data {
            public function __construct(public string $string)
            {
            }

            public static function optionalString(string $string): static
            {
                return new self($string);
            }

            public static function optionalDto(DummyDto $dto)
            {
                return new self($dto->artist);
            }

            public static function optionalArray(array $payload)
            {
                return new self($payload['string']);
            }
        };

        $this->assertEquals(new $data('Hello World'), $data::optional('Hello World'));
        $this->assertEquals(new $data('Rick Astley'), $data::optional(DummyDto::rick()));
        $this->assertEquals(new $data('Hello World'), $data::optional(['string' => 'Hello World']));
        $this->assertEquals(new $data('Hello World'), $data::optional(DummyModel::make(['string' => 'Hello World'])));

        $this->assertNull($data::optional(null));
    }
}
