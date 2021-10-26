<?php

namespace Spatie\LaravelData\Tests\Resolvers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Util\Exception;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Tests\Fakes\DummyDto;
use Spatie\LaravelData\Tests\Fakes\DummyModel;
use Spatie\LaravelData\Tests\Fakes\DummyModelWithCasts;
use Spatie\LaravelData\Tests\TestCase;

class DataFromSomethingResolverTest extends TestCase
{
    /** @test */
    public function it_can_create_data_from_a_custom_method()
    {
        $data = new class ('') extends Data {
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
        $this->assertEquals(new $data('Hello World'), $data::from(DummyModelWithCasts::make(['string' => 'Hello World'])));
    }

    /** @test */
    public function it_can_create_data_from_a_custom_method_with_an_interface_parameter()
    {
        $data = new class ('') extends Data {
            public function __construct(public string $string)
            {
            }

            public static function fromInterface(Arrayable $arrayable)
            {
                return new self($arrayable->toArray()['string']);
            }
        };

        $interfaceable = new class () implements Arrayable {
            public function toArray()
            {
                return [
                    'string' => 'Rick Astley',
                ];
            }
        };

        $this->assertEquals(new $data('Rick Astley'), $data::from($interfaceable));
    }

    /** @test */
    public function it_can_create_data_from_a_custom_method_with_an_inherited_parameter()
    {
        $data = new class ('') extends Data {
            public function __construct(public string $string)
            {
            }

            public static function fromModel(Model $model)
            {
                return new self($model->string);
            }
        };

        $inherited = new DummyModel(['string' => 'Rick Astley']);

        $this->assertEquals(new $data('Rick Astley'), $data::from($inherited));
    }

    /** @test */
    public function it_can_create_data_from_a_custom_method_and_always_takes_the_nearest_type()
    {
        $data = new class ('') extends Data {
            public function __construct(public string $string)
            {
            }

            public static function fromModel(Model $arrayable)
            {
                throw new Exception("Cannot be called");
            }

            public static function fromDummyModel(DummyModel $model)
            {
                return new self($model->string);
            }
        };

        $inherited = new DummyModel(['string' => 'Rick Astley']);

        $this->assertEquals(new $data('Rick Astley'), $data::from($inherited));
    }

    /** @test */
    public function it_can_create_data_from_a_custom_optional_method()
    {
        $data = new class ('') extends Data {
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
