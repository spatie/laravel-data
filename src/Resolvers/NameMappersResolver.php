<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\NameMapper;
use Spatie\LaravelData\Mappers\ProvidedNameMapper;

class NameMappersResolver
{
    public static function create(array $ignoredMappers = []): self
    {
        return new self($ignoredMappers);
    }

    public function __construct(private array $ignoredMappers = [])
    {
    }

    public function execute(
        Collection $attributes
    ): array {
        return [
            'inputNameMapper' => $this->resolveInputNameMapper($attributes),
            'outputNameMapper' => $this->resolveOutputNameMapper($attributes),
        ];
    }

    private function resolveInputNameMapper(
        Collection $attributes
    ): ?NameMapper {
        /** @var \Spatie\LaravelData\Attributes\MapInputName|\Spatie\LaravelData\Attributes\MapName|null $mapper */
        $mapper = $attributes->first(fn (object $attribute) => $attribute instanceof MapInputName)
            ?? $attributes->first(fn (object $attribute) => $attribute instanceof MapName);

        if ($mapper) {
            return $this->resolveMapper($mapper->input);
        }

        return null;
    }

    private function resolveOutputNameMapper(
        Collection $attributes
    ): ?NameMapper {
        /** @var \Spatie\LaravelData\Attributes\MapOutputName|\Spatie\LaravelData\Attributes\MapName|null $mapper */
        $mapper = $attributes->first(fn (object $attribute) => $attribute instanceof MapOutputName)
            ?? $attributes->first(fn (object $attribute) => $attribute instanceof MapName);

        if ($mapper) {
            return $this->resolveMapper($mapper->output);
        }

        return null;
    }

    private function resolveMapper(string|int $value): ?NameMapper
    {
        $mapper = $this->resolveMapperClass($value);

        foreach ($this->ignoredMappers as $ignoredMapper) {
            if ($mapper instanceof $ignoredMapper) {
                return null;
            }
        }

        return $mapper;
    }

    private function resolveMapperClass(int|string $value): NameMapper
    {
        if (is_int($value)) {
            return new ProvidedNameMapper($value);
        }

        if (is_a($value, NameMapper::class, true)) {
            return resolve($value);
        }

        return new ProvidedNameMapper($value);
    }
}
