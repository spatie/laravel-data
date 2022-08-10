<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
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

        $isNullable = $nullable || $property->type->isNullable;
        $isOptional = $property->type->isOptional;

        $toplevelRules = RulesCollection::create();

        if ($isNullable) {
            $toplevelRules->add(new Nullable());
        }

        if ($isOptional) {
            $toplevelRules->add(new Sometimes());
        }

        if (! $isNullable && ! $isOptional && $property->type->isDataObject) {
            $toplevelRules->add(new Required());
        }

        if (! $isNullable && ! $isOptional && $property->type->isDataCollectable) {
            $toplevelRules->add(new Present());
        }

        $toplevelRules->add(ArrayType::create());

        foreach ($this->dataConfig->getRuleInferrers() as $inferrer) {
            $inferrer->handle($property, $toplevelRules);
        }

        return $this->dataValidationRulesResolver
            ->execute(
                $property->type->dataClass,
                $payload,
                $this->isNestedDataNullable($nullable, $property)
            )
            ->mapWithKeys(fn (array $rules, string $name) => [
                "{$prefix}{$name}" => $rules,
            ])
            ->prepend($toplevelRules->normalize(), $propertyName);
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
