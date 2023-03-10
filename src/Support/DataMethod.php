<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Collection;
use ReflectionMethod;
use ReflectionParameter;

/**
 * @property Collection<DataParameter> $parameters
 */
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

    public static function create(ReflectionMethod $method): self
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

    public static function createConstructor(?ReflectionMethod $method, Collection $properties): ?self
    {
        if ($method === null) {
            return null;
        }

        $parameters = collect($method->getParameters())
            ->map(function (ReflectionParameter $parameter) use ($properties) {
                if (! $parameter->isPromoted()) {
                    return DataParameter::create($parameter);
                }

                if ($properties->has($parameter->name)) {
                    return $properties->get($parameter->name);
                }

                return null;
            })
            ->filter()
            ->values();

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
        /** @var Collection<\Spatie\LaravelData\Support\DataParameter|\Spatie\LaravelData\Support\DataProperty> $parameters */
        $parameters = array_is_list($input)
            ? $this->parameters
            : $this->parameters->mapWithKeys(fn (DataParameter|DataProperty $parameter) => [$parameter->name => $parameter]);

        if (count($input) > $parameters->count()) {
            return false;
        }

        foreach ($parameters as $index => $parameter) {
            $parameterProvided = array_key_exists($index, $input);

            if (! $parameterProvided && $parameter->hasDefaultValue === false) {
                return false;
            }

            if (! $parameterProvided && $parameter->hasDefaultValue) {
                continue;
            }

            if (! $parameter->type->acceptsValue($input[$index])) {
                return false;
            }
        }

        return true;
    }
}
