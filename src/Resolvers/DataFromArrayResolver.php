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
use Spatie\LaravelData\Support\DataParameter;
use Spatie\LaravelData\Support\DataProperty;

class DataFromArrayResolver
{
    public function __construct(protected DataConfig $dataConfig)
    {
    }

    public function execute(string $class, Collection $properties): BaseData
    {
        $dataClass = $this->dataConfig->getDataClass($class);

        $constructorParameters = $dataClass->constructorMethod?->parameters ?? collect();

        $data = $constructorParameters
            ->mapWithKeys(function (DataParameter|DataProperty $parameter) use ($properties) {
                if ($properties->has($parameter->name)) {
                    return [$parameter->name => $properties->get($parameter->name)];
                }

                if (! $parameter->isPromoted && $parameter->hasDefaultValue) {
                    return [$parameter->name => $parameter->defaultValue];
                }

                return [];
            })
            ->pipe(fn (Collection $parameters) => $this->createData($dataClass, $parameters));

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
        Collection $parameters,
    ) {
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
