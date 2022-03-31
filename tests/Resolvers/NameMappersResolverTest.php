<?php

namespace Spatie\LaravelData\Tests\Resolvers;

use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionProperty;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Mappers\ProvidedNameMapper;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Resolvers\NameMappersResolver;
use Spatie\LaravelData\Tests\TestCase;

class NameMappersResolverTest extends TestCase
{
    private NameMappersResolver $resolver;

    public function setUp(): void
    {
        parent::setUp();

        $this->resolver = new NameMappersResolver();
    }

    /** @test */
    public function it_can_get_an_input_and_output_mapper()
    {
        $attributes = $this->getAttributes(new class () {
            #[MapInputName('input'), MapOutputName('output')]
            public $property;
        });

        $this->assertEquals(
            [
                'inputNameMapper' => new ProvidedNameMapper('input'),
                'outputNameMapper' => new ProvidedNameMapper('output'),
            ],
            $this->resolver->execute($attributes)
        );
    }

    /** @test */
    public function it_can_have_no_mappers()
    {
        $attributes = $this->getAttributes(new class () {
            public $property;
        });

        $this->assertEquals(
            [
                'inputNameMapper' => null,
                'outputNameMapper' => null,
            ],
            $this->resolver->execute($attributes)
        );
    }

    /** @test */
    public function it_can_have_a_single_map_attribute()
    {
        $attributes = $this->getAttributes(new class () {
            #[MapName('input', 'output')]
            public $property;
        });

        $this->assertEquals(
            [
                'inputNameMapper' => new ProvidedNameMapper('input'),
                'outputNameMapper' => new ProvidedNameMapper('output'),
            ],
            $this->resolver->execute($attributes)
        );
    }

    /** @test */
    public function it_can_overwrite_a_general_map_attribute()
    {
        $attributes = $this->getAttributes(new class () {
            #[MapName('input', 'output'), MapInputName('input_overwritten')]
            public $property;
        });

        $this->assertEquals(
            [
                'inputNameMapper' => new ProvidedNameMapper('input_overwritten'),
                'outputNameMapper' => new ProvidedNameMapper('output'),
            ],
            $this->resolver->execute($attributes)
        );
    }

    /** @test */
    public function it_can_map_an_int()
    {
        $attributes = $this->getAttributes(new class () {
            #[MapName(0, 3)]
            public $property;
        });

        $this->assertEquals(
            [
                'inputNameMapper' => new ProvidedNameMapper(0),
                'outputNameMapper' => new ProvidedNameMapper(3),
            ],
            $this->resolver->execute($attributes)
        );
    }

    /** @test */
    public function it_can_map_a_string()
    {
        $attributes = $this->getAttributes(new class () {
            #[MapName('hello', 'world')]
            public $property;
        });

        $this->assertEquals(
            [
                'inputNameMapper' => new ProvidedNameMapper('hello'),
                'outputNameMapper' => new ProvidedNameMapper('world'),
            ],
            $this->resolver->execute($attributes)
        );
    }

    /** @test */
    public function it_can_map_a_mapper_class()
    {
        $attributes = $this->getAttributes(new class () {
            #[MapName(CamelCaseMapper::class, SnakeCaseMapper::class)]
            public $property;
        });

        $this->assertEquals(
            [
                'inputNameMapper' => new CamelCaseMapper(),
                'outputNameMapper' => new SnakeCaseMapper(),
            ],
            $this->resolver->execute($attributes)
        );
    }

    /** @test */
    public function it_can_ignore_certain_mapper_types()
    {
        $attributes = $this->getAttributes(new class () {
            #[MapInputName('input'), MapOutputName(CamelCaseMapper::class)]
            public $property;
        });

        $this->assertEquals(
            [
                'inputNameMapper' => null,
                'outputNameMapper' => new CamelCaseMapper(),
            ],
            NameMappersResolver::create([ProvidedNameMapper::class])->execute($attributes)
        );
    }

    private function getAttributes(object $class): Collection
    {
        return collect((new ReflectionProperty($class, 'property'))->getAttributes())
            ->map(fn (ReflectionAttribute $attribute) => $attribute->newInstance());
    }
}
