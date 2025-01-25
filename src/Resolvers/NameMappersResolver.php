<?php

namespace Spatie\LaravelData\Resolvers;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\NameMapper;
use Spatie\LaravelData\Mappers\ProvidedNameMapper;
use Spatie\LaravelData\Support\AttributeCollection;

class NameMappersResolver
{
    public static function create(array $ignoredMappers = []): self
    {
        return new self($ignoredMappers);
    }

    public function __construct(protected array $ignoredMappers = [])
    {
    }

    public function execute(
        AttributeCollection $attributes
    ): array {
        return [
            'inputNameMapper' => $this->resolveInputNameMapper($attributes),
            'outputNameMapper' => $this->resolveOutputNameMapper($attributes),
        ];
    }

    protected function resolveInputNameMapper(
        AttributeCollection $attributes
    ): ?NameMapper {
        $mapper = $attributes->getAttribute(MapInputName::class)
            ?? $attributes->getAttribute(MapName::class);

        if ($mapper) {
            return $this->resolveMapper($mapper->input);
        }

        return $this->resolveDefaultNameMapper(config('data.name_mapping_strategy.input'));
    }

    protected function resolveOutputNameMapper(
        AttributeCollection $attributes
    ): ?NameMapper {
        $mapper = $attributes->getAttribute(MapOutputName::class)
            ?? $attributes->getAttribute(MapName::class);

        if ($mapper) {
            return $this->resolveMapper($mapper->output);
        }

        return $this->resolveDefaultNameMapper(config('data.name_mapping_strategy.output'));
    }

    protected function resolveMapper(string|int|NameMapper $value): ?NameMapper
    {
        $mapper = $this->resolveMapperClass($value);

        foreach ($this->ignoredMappers as $ignoredMapper) {
            if ($mapper instanceof $ignoredMapper) {
                return null;
            }
        }

        return $mapper;
    }

    protected function resolveMapperClass(int|string|NameMapper $value): NameMapper
    {
        if (is_int($value)) {
            return new ProvidedNameMapper($value);
        }

        if ($value instanceof NameMapper) {
            return $value;
        }

        if (is_a($value, NameMapper::class, true)) {
            return resolve($value);
        }

        return new ProvidedNameMapper($value);
    }

    protected function resolveDefaultNameMapper(
        ?string $value,
    ): ?NameMapper {
        if ($value === null) {
            return null;
        }

        return $this->resolveMapperClass($value);
    }
}
