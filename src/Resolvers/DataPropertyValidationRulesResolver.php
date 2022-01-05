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
        if ($property->isData() || $property->isDataCollection()) {
            return $this->getNestedRules($property, $nullable);
        }

        return collect([$property->name() => $this->getRulesForProperty($property, $nullable)]);
    }

    private function getNestedRules(DataProperty $property, bool $nullable): Collection
    {
        $prefix = match (true) {
            $property->isData() => "{$property->name()}.",
            $property->isDataCollection() => "{$property->name()}.*.",
            default => throw new TypeError()
        };

        $isNullable = $nullable || $property->isNullable();

        $topLevelRules = [
            $isNullable ? 'nullable' : 'required',
            'array',
        ];

        return $this->dataValidationRulesResolver
            ->execute(
                $property->dataClassName(),
                $nullable || ($property->isData() && $property->isNullable())
            )
            ->mapWithKeys(fn(array $rules, string $name) => [
                "{$prefix}{$name}" => $rules,
            ])
            ->prepend($topLevelRules, $property->name());
    }

    private function getRulesForProperty(DataProperty $property, bool $nullable): array
    {
        return array_reduce(
            $this->dataConfig->getRuleInferrers(),
            fn(array $rules, RuleInferrer $ruleInferrer) => $ruleInferrer->handle($property, $rules),
            $nullable ? ['nullable'] : []
        );
    }
}
