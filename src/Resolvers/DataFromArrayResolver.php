<?php

namespace Spatie\LaravelData\Resolvers;

use ArgumentCountError;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Exceptions\CannotCreateData;
use Spatie\LaravelData\Exceptions\CannotSetComputedValue;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
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
    public function execute(string $class, Collection $properties): BaseData
    {
        $dataClass = $this->dataConfig->getDataClass($class);

        $data = $this->createData($dataClass, $properties);

        $dataClass
            ->properties
            ->reject(
                fn (DataProperty $property) => $property->isPromoted
                    || $property->isReadonly
                    || ! $properties->has($property->name)
            )
            ->each(function (DataProperty $property) use ($properties, $data) {
                if ($property->type->isOptional
                    && isset($data->{$property->name})
                    && $properties->get($property->name) instanceof Optional
                ) {
                    return;
                }

                if ($property->computed
                    && $property->type->isNullable
                    && $properties->get($property->name) === null
                ) {
                    return; // Nullable properties get assigned null by default
                }

                if ($property->computed) {
                    throw CannotSetComputedValue::create($property);
                }

                $data->{$property->name} = $properties->get($property->name);
            });

        return $data;
    }

    protected function createData(
        DataClass $dataClass,
        Collection $properties,
    ) {
        $constructorParameters = $dataClass->constructorMethod?->parameters;

        if ($constructorParameters === null) {
            return new $dataClass->name();
        }

        $parameters = [];

        foreach ($constructorParameters as $parameter) {
            if ($properties->has($parameter->name)) {
                $parameters[$parameter->name] = $properties->get($parameter->name);

                continue;
            }

            if (! $parameter->isPromoted && $parameter->hasDefaultValue) {
                $parameters[$parameter->name] = $parameter->defaultValue;
            }
        }

        try {
            return new $dataClass->name(...$parameters);
        } catch (ArgumentCountError $error) {
            throw CannotCreateData::constructorMissingParameters(
                $dataClass,
                $parameters,
                $error
            );
        }
    }
}
