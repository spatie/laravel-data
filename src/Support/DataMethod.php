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

    public static function createConstructor(?ReflectionMethod $method, Collection $properties): ?static
    {
        if ($method === null) {
            return null;
        }

        $parameters = collect($method->getParameters())->map(function (ReflectionParameter $parameter) use ($properties) {
            if ($parameter->isPromoted()) {
                return $properties->get($parameter->name);
            }

            return DataParameter::create($parameter);
        });

        return new self(
            '__construct',
            $parameters,
            false,
            $method->isPublic(),
            false
        );
    }

    public function accepts(mixed ...$input): bool
    {
        /** @var \Spatie\LaravelData\Support\DataType[] $types */
        $types = array_is_list($input)
            ? $this->parameters->map(fn (DataParameter|DataProperty $parameter) => $parameter->type)
            : $this->parameters->mapWithKeys(fn (DataParameter|DataProperty $parameter) => [$parameter->name => $parameter->type]);

        if (count($input) !== $this->parameters->count()) {
            return false;
        }

        foreach ($types as $index => $type) {
            $currentInput = $input[$index] ?? null;

            if (! $type->acceptsValue($currentInput)) {
                return false;
            }
        }

        return true;
    }
}
