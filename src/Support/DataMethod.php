<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Collection;
use ReflectionMethod;
use ReflectionParameter;

class DataMethod
{
    public function __construct(
        public readonly string $name,
        public readonly Collection $parameters,
        public readonly bool $isStatic,
        public readonly bool $isPublic,
        public readonly bool $isCustomCreationMethod,
    ) {
    }

    public static function create(ReflectionMethod $method): static
    {
        $isCustomCreationMethod = $method->isStatic()
            && $method->isPublic()
            && str_starts_with($method->getName(), 'from')
            && $method->name !== 'from'
            && $method->name !== 'optional';

        return new self(
            $method->name,
            collect($method->getParameters())->map(
                fn (ReflectionParameter $parameter) => DataParameter::create($parameter),
            ),
            $method->isStatic(),
            $method->isPublic(),
            $isCustomCreationMethod
        );
    }

    public function accepts(mixed ...$input): bool
    {
        $types = array_is_list($input)
            ? $this->parameters->map(fn (DataParameter $parameter) => $parameter->types)
            : $this->parameters->mapWithKeys(fn (DataParameter $parameter) => [$parameter->name => $parameter->types]);

        if (count($input) !== $this->parameters->count()) {
            return false;
        }

        foreach ($types as $index => $type) {
            $currentInput = $input[$index] ?? null;

            if (! $type->accepts($currentInput)) {
                return false;
            }
        }

        return true;
    }
}
