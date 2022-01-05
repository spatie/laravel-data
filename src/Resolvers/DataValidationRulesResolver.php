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

        return $this->dataConfig->getDataClass($class)
            ->properties()
            ->mapWithKeys(
                fn(DataProperty $property) => $resolver->execute($property, $nullable)
            );
    }
}
