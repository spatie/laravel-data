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

        if ($property->type->isDataObject || $property->type->isDataCollectable) {
            return $this->getNestedRules($property, $propertyName, $payload, $nullable);
        }

        return collect([$propertyName => $this->getRulesForProperty($property, $nullable)]);
    }

    protected function getNestedRules(
        DataProperty $property,
        string $propertyName,
        array $payload,
        bool $nullable
    ): Collection {
        $prefix = match (true) {
            $property->type->isDataObject => "{$propertyName}.",
            $property->type->isDataCollectable => "{$propertyName}.*.",
            default => throw new TypeError()
        };

        $isNullable = $nullable || $property->type->isNullable || $property->type->isOptional;

        $toplevelRule = match (true) {
            $isNullable => 'nullable',
            $property->type->isDataObject => "required",
            $property->type->isDataCollectable => "present",
            default => throw new TypeError()
        };

        return $this->dataValidationRulesResolver
            ->execute(
                $property->type->dataClass,
                $payload,
                $this->isNestedDataNullable($nullable, $property)
            )
            ->mapWithKeys(fn (array $rules, string $name) => [
                "{$prefix}{$name}" => $rules,
            ])
            ->prepend([$toplevelRule, 'array'], $propertyName);
    }

    protected function getRulesForProperty(DataProperty $property, bool $nullable): array
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

    protected function isNestedDataNullable(bool $nullable, DataProperty $property): bool
    {
        if ($nullable) {
            return true;
        }

        if ($property->type->isDataObject) {
            return $property->type->isNullable || $property->type->isOptional;
        }

        return false;
    }
}
