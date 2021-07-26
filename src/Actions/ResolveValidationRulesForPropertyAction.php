<?php

namespace Spatie\LaravelData\Actions;

use Exception;
use Illuminate\Support\Collection;
use Spatie\LaravelData\AutoRules\AutoRule;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;

class ResolveValidationRulesForPropertyAction
{
    public function __construct(
        protected ResolveValidationRulesForDataAction $resolveValidationRulesForDataAction,
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
            default => throw new Exception('Unknown data property')
        };

        $topLevelRules = match (true) {
            $property->isData() && $property->isNullable() => ['nullable', 'array'],
            $property->isData() => ['required', 'array'],
            $property->isDataCollection() && $property->isNullable() => ['nullable', 'array'],
            $property->isDataCollection() => ['present', 'array'],
            // no break
            default => throw new Exception('Could not resolve rules for data property')
        };

        return $this->resolveValidationRulesForDataAction
            ->execute($property->getDataClass())
            ->mapWithKeys(fn (array $rules, string $name) => [
                "{$prefix}{$name}" => $rules,
            ])
            ->prepend($topLevelRules, $property->name());
    }

    private function getRulesForProperty(DataProperty $property): array
    {
        return array_reduce(
            $this->dataConfig->getAutoRules(),
            fn (array $rules, AutoRule $autoRule) => $autoRule->handle($property, $rules),
            []
        );
    }
}
