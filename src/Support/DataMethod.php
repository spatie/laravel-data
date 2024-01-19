<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Enums\CustomCreationMethodType;

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
        public readonly CustomCreationMethodType $customCreationMethodType,
        public readonly ?DataReturnType $returnType,
    ) {
    }

    public function accepts(mixed ...$input): bool
    {
        /** @var Collection<array-key, \Spatie\LaravelData\Support\DataParameter|\Spatie\LaravelData\Support\DataProperty> $parameters */
        $parameters = array_is_list($input)
            ? $this->parameters
            : $this->parameters->mapWithKeys(fn (DataParameter|DataProperty $parameter) => [$parameter->name => $parameter]);

        $parameters = $parameters->reject(
            fn (DataParameter|DataProperty $parameter) => $parameter instanceof DataParameter && $parameter->type->type->isCreationContext()
        );

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

            if (
                $parameter instanceof DataProperty
                && ! $parameter->type->acceptsValue($input[$index])
            ) {
                return false;
            }

            if (
                $parameter instanceof DataParameter
                && ! $parameter->type->acceptsValue($input[$index])
            ) {
                return false;
            }
        }

        return true;
    }

    public function returns(string $type): bool
    {
        return $this->returnType?->acceptsType($type) ?? false;
    }
}
