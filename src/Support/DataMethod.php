<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Enums\CustomCreationMethodType;

/**
 * @property Collection<DataParameter|DataProperty> $parameters
 */
class DataMethod
{
    public function __construct(
        public readonly string $name,
        public readonly Collection $parameters,
        public readonly bool $isStatic,
        public readonly bool $isPublic,
        public readonly CustomCreationMethodType $customCreationMethodType,
        public readonly ?DataType $returnType,
    ) {
    }

    public function accepts(mixed ...$input): bool
    {
        $requiredParameterCount = 0;

        foreach ($this->parameters as $parameter) {
            if ($parameter->type->type->isCreationContext()) {
                continue;
            }

            $requiredParameterCount++;
        }

        if (count($input) > $requiredParameterCount) {
            return false;
        }

        $useNameAsIndex = ! array_is_list($input);

        foreach ($this->parameters as $index => $parameter) {
            if ($parameter->type->type->isCreationContext()) {
                continue;
            }

            if ($useNameAsIndex) {
                $index = $parameter->name;
            }

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
