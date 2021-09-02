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

    public function execute(DataProperty $property): Collection
    {
        if ($property->isData() || $property->isDataCollection()) {
            return $this->getNestedRules($property);
        }

        return collect([$property->name() => $this->getRulesForProperty($property)]);
    }

    private function getNestedRules(DataProperty $property): Collection
    {
        $prefix = match (true) {
            $property->isData() => "{$property->name()}.",
            $property->isDataCollection() => "{$property->name()}.*.",
            default => throw new TypeError()
        };

        $topLevelRules = match (true) {
            $property->isData() && $property->isNullable() => ['nullable', 'array'],
            $property->isData() => ['required', 'array'],
            $property->isDataCollection() && $property->isNullable() => ['nullable', 'array'],
            $property->isDataCollection() => ['required', 'array'],
            // no break
            default => throw new TypeError()
        };

        return $this->dataValidationRulesResolver
            ->execute($property->dataClassName())
            ->mapWithKeys(fn (array $rules, string $name) => [
                "{$prefix}{$name}" => $rules,
            ])
            ->prepend($topLevelRules, $property->name());
    }

    private function getRulesForProperty(DataProperty $property): array
    {
        return array_reduce(
            $this->dataConfig->getRuleInferrers(),
            fn (array $rules, RuleInferrer $ruleInferrer) => $ruleInferrer->handle($property, $rules),
            []
        );
    }
}
