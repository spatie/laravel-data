<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\RulesCollection;
use TypeError;

class DataPropertyValidationRulesResolver
{
    public function __construct(
        protected DataClassValidationRulesResolver $dataValidationRulesResolver,
        protected DataConfig $dataConfig
    ) {
    }

    public function execute(DataProperty $property, array $payload = [], bool $nullable = false): Collection
    {
        $propertyName = $property->inputMappedName ?? $property->name;

        if ($property->type->isDataObject || $property->type->isDataCollection) {
            return $this->getNestedRules($property, $propertyName, $payload, $nullable);
        }

        return collect([$propertyName => $this->getRulesForProperty($property, $nullable)]);
    }

    private function getNestedRules(
        DataProperty $property,
        string $propertyName,
        array $payload,
        bool $nullable
    ): Collection {
        $prefix = match (true) {
            $property->type->isDataObject => "{$propertyName}.",
            $property->type->isDataCollection => "{$propertyName}.*.",
            default => throw new TypeError()
        };

        $isNullable = $nullable || $property->type->isNullable;

        $toplevelRule = match (true) {
            $isNullable => 'nullable',
            $property->type->isDataObject => "required",
            $property->type->isDataCollection => "present",
            default => throw new TypeError()
        };

        return $this->dataValidationRulesResolver
            ->execute(
                $property->type->dataClass,
                $payload,
                $nullable || ($property->type->isDataObject && $property->type->isNullable)
            )
            ->mapWithKeys(fn (array $rules, string $name) => [
                "{$prefix}{$name}" => $rules,
            ])
            ->prepend([$toplevelRule, 'array'], $propertyName);
    }

    private function getRulesForProperty(DataProperty $property, bool $nullable): array
    {
        $rules = new RulesCollection();

        if ($nullable) {
            $rules->add(new Nullable());
        }

        foreach ($this->dataConfig->getRuleInferrers() as $inferrer) {
            $rules = $inferrer->handle($property, $rules);
        }

        return $rules->normalize();
    }
}
