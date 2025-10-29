<?php

namespace Spatie\LaravelData\Resolvers;

use ArgumentCountError;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Exceptions\CannotCreateData;
use Spatie\LaravelData\Exceptions\CannotSetComputedValue;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataParameter;
use Spatie\LaravelData\Support\DataProperty;

/**
 * @template TData of BaseData
 */
class DataFromArrayResolver
{
    public function __construct(protected DataConfig $dataConfig)
    {
    }

    /**
     * @param class-string<TData> $class
     *
     * @return TData
     */
    public function execute(string $class, array $properties, CreationContext $creationContext): BaseData
    {
        $dataClass = $this->dataConfig->getDataClass($class);

        $data = $this->createData($dataClass, $properties, $creationContext);

        foreach ($dataClass->properties as $property) {
            if (
                $property->isPromoted
                || $property->isReadonly
                || ! array_key_exists($property->name, $properties)
            ) {
                continue;
            }

            if ($property->type->isOptional
                && isset($data->{$property->name})
                && $properties[$property->name] instanceof Optional
            ) {
                continue;
            }

            if ($property->computed
                && $property->type->isNullable
                && $properties[$property->name] === null
            ) {
                continue; // Nullable properties get assigned null by default
            }

            if ($property->computed) {
                if (! config('data.features.ignore_exception_when_trying_to_set_computed_property_value')) {
                    throw CannotSetComputedValue::create($property);
                }

                continue; // Ignore the value being passed into the computed property and let it be recalculated
            }

            $data->{$property->name} = $properties[$property->name];
        }

        return $data;
    }

    protected function createData(
        DataClass $dataClass,
        array $properties,
        CreationContext $creationContext,
    ) {
        $constructorParameters = $dataClass->constructorMethod?->parameters;

        if ($constructorParameters === null) {
            return match (true) {
                method_exists($dataClass->name, 'makeWithContext') => $dataClass->name::makeWithContext($creationContext),
                method_exists($dataClass->name, 'make') => $dataClass->name::make(),
                default => new $dataClass->name(),
            };
        }

        $parameters = [];

        foreach ($constructorParameters as $parameter) {
            if (array_key_exists($parameter->name, $properties)) {
                $parameters[$parameter->name] = $properties[$parameter->name];

                continue;
            }

            if (! $parameter->isPromoted && $parameter->hasDefaultValue) {
                $parameters[$parameter->name] = $parameter->defaultValue;
            }
        }

        try {
            return match (true) {
                method_exists($dataClass->name, 'makeWithContext') => $dataClass->name::makeWithContext($creationContext, ...$parameters),
                method_exists($dataClass->name, 'make') => $dataClass->name::make(...$parameters),
                default => new $dataClass->name(...$parameters),
            };
        } catch (ArgumentCountError $error) {
            if ($this->isAnyParameterMissing($dataClass, array_keys($parameters))) {
                throw CannotCreateData::constructorMissingParameters(
                    $dataClass,
                    $parameters,
                    $error
                );
            } else {
                throw $error;
            }
        }
    }

    protected function isAnyParameterMissing(DataClass $dataClass, array $parameters): bool
    {
        return $dataClass
            ->constructorMethod
            ->parameters
            ->filter(fn (DataParameter|DataProperty $parameter) => ! $parameter->hasDefaultValue)
            ->pluck('name')
            ->diff($parameters)
            ->isNotEmpty();
    }
}
