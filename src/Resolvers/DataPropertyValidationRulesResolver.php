<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Collection;
use Spatie\LaravelData\RuleInferrers\RuleInferrer;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use TypeError;

class DataPropertyValidationRulesResolver
{
    public function __construct(
        protected DataValidationRulesResolver $dataValidationRulesResolver,
        protected DataConfig $dataConfig
    ) {
    }

    public function execute(DataProperty $property, bool $nullable = false): Collection
    {
        $propertyName = $property->inputMappedName ?? $property->name;

        if ($property->isDataObject || $property->isDataCollection) {
            return $this->getNestedRules($property, $propertyName, $nullable);
        }

        return collect([$propertyName => $this->getRulesForProperty($property, $nullable)]);
    }

    private function getNestedRules(
        DataProperty $property,
        string $propertyName,
        bool $nullable
    ): Collection {
        $prefix = match (true) {
            $property->isDataObject => "{$propertyName}.",
            $property->isDataCollection => "{$propertyName}.*.",
            default => throw new TypeError()
        };

        $isNullable = $nullable || $property->isNullable;

        $toplevelRule = match (true) {
            $isNullable => 'nullable',
            $property->isDataObject => "required",
            $property->isDataCollection => "present",
            default => throw new TypeError()
        };

        return $this->dataValidationRulesResolver
            ->execute(
                $property->dataClass,
                $nullable || ($property->isDataObject && $property->isNullable)
            )
            ->mapWithKeys(fn (array $rules, string $name) => [
                "{$prefix}{$name}" => $rules,
            ])
            ->prepend([$toplevelRule, 'array'], $propertyName);
    }

    private function getRulesForProperty(DataProperty $property, bool $nullable): array
    {
        return array_reduce(
            $this->dataConfig->getRuleInferrers(),
            fn (array $rules, RuleInferrer $ruleInferrer) => $ruleInferrer->handle($property, $rules),
            $nullable ? ['nullable'] : []
        );
    }
}
