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

        /** @var class-string<\Spatie\LaravelData\Data> $class */
        $overWrittenRules = $class::rules();

        return $this->dataConfig->getDataClass($class)
            ->properties()
            ->reject(fn (DataProperty $property) => array_key_exists($property->name(), $overWrittenRules))
            ->mapWithKeys(fn (DataProperty $property) => $resolver->execute($property, $nullable))
            ->merge($overWrittenRules);
    }
}
