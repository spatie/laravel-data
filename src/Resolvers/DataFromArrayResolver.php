<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataParameter;
use Spatie\LaravelData\Support\DataProperty;

class DataFromArrayResolver
{
    public function __construct(protected DataConfig $dataConfig)
    {
    }

    public function execute(string $class, Collection $properties): Data
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
            ->pipe(fn (Collection $parameters) => new $dataClass->name(...$parameters));

        $dataClass
            ->properties
            ->filter(
                fn (DataProperty $property) => ! $property->isPromoted && $properties->has($property->name)
            )
            ->each(function (DataProperty $property) use ($properties, $data) {
                $data->{$property->name} = $properties->get($property->name);
            });

        return $data;
    }
}
