<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Collection;
use Illuminate\Validation\NestedRules;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\NestedRulesWithAdditional;
use Spatie\LaravelData\Support\Validation\RulesCollection;
use TypeError;

class DataPropertyValidationRulesResolver
{
    public function __construct(
        protected DataClassValidationRulesResolver $dataValidationRulesResolver,
        protected DataConfig $dataConfig
    ) {
    }

    public function execute(
        DataProperty $property,
        array $payload = [],
        bool $nullable = false,
        ?string $payloadPath = null
    ): Collection {
        $propertyName = $property->inputMappedName ?? $property->name;

        if ($property->type->isDataObject || $property->type->isDataCollectable) {
            return $this->getNestedRules(
                property: $property,
                propertyName: $propertyName,
                payload: $payload,
                nullable: $nullable,
                payloadPath: $payloadPath
            );
        }

        return collect([
            $this->resolveRulePath($payloadPath, $propertyName) => $this->getRulesForProperty($property, $nullable),
        ]);
    }

    protected function getNestedRules(
        DataProperty $property,
        string $propertyName,
        array $payload,
        bool $nullable,
        ?string $payloadPath = null
    ): Collection {
        $toplevelRules = $this->resolveDataClassOrCollectionTopLevelRules(
            isNullable: $nullable || $property->type->isNullable,
            isOptional: $property->type->isOptional,
            property: $property
        );

        $rulePath = $this->resolveRulePath($payloadPath, $propertyName);

        $nestedPayloadPath = match (true) {
            $property->type->isDataObject => $rulePath,
            $property->type->isDataCollectable => "{$rulePath}.*",
            default => throw new TypeError(),
        };

        $nestedRules = $this->dataValidationRulesResolver->execute(
            $property->type->dataClass,
            $payload,
            $this->isNestedDataNullable($nullable, $property),
            $nestedPayloadPath,
        );

        $topLevelRuleResolvedInNestedRule = $nestedRules->pull($rulePath);

        if ($topLevelRuleResolvedInNestedRule instanceof NestedRules) {
            return $nestedRules->prepend(
                NestedRulesWithAdditional::fromNestedRules(
                    $topLevelRuleResolvedInNestedRule,
                    [$rulePath => $toplevelRules]
                ),
                $rulePath
            );
        }

        return $nestedRules->prepend($toplevelRules, $rulePath);
    }

    private function resolveDataClassOrCollectionTopLevelRules(
        bool $isNullable,
        bool $isOptional,
        DataProperty $property
    ): array {
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

        return $toplevelRules->normalize();
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

    private function resolveRulePath(?string $payloadPath, string $propertyName): string
    {
        return $payloadPath ? "{$payloadPath}.{$propertyName}" : $propertyName;
    }
}
