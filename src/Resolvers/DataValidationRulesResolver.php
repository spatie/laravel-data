<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;

class DataValidationRulesResolver
{
    /** @var array<string, \Illuminate\Support\Collection> */
    protected static $cachedDataObjectRules = [];

    public function __construct(protected DataConfig $dataConfig)
    {
    }

    public function execute(string $class): Collection
    {
        if (array_key_exists($class, self::$cachedDataObjectRules)) {
            return self::$cachedDataObjectRules[$class];
        }

        $resolver = app(DataPropertyValidationRulesResolver::class);

        $rules = collect($this->dataConfig->getDataProperties($class))
            ->mapWithKeys(
                fn (DataProperty $property) => $resolver->execute($property)
            );

        return self::$cachedDataObjectRules[$class] = $rules;
    }
}
