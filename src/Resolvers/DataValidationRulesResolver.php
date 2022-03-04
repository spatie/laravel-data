<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;

class DataValidationRulesResolver
{
    public function __construct(protected DataConfig $dataConfig)
    {
    }

    public function execute(string $class, bool $nullable = false): Collection
    {
        $resolver = app(DataPropertyValidationRulesResolver::class);

        $overWrittenRules = [];
        /** @var class-string<\Spatie\LaravelData\Data> $class */
        if (method_exists($class, 'rules')) {
            $overWrittenRules = app()->call([$class, 'rules']);
        }

        return $this->dataConfig->getDataClass($class)
            ->properties
            ->reject(fn (DataProperty $property) => array_key_exists($property->name, $overWrittenRules) || ! $property->validate)
            ->mapWithKeys(fn (DataProperty $property) => $resolver->execute($property, $nullable))
            ->merge($overWrittenRules);
    }
}
