<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MergeValidationRules;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\DataRules;
use Spatie\LaravelData\Support\Validation\EnsurePropertyMorphable;
use Spatie\LaravelData\Support\Validation\PropertyRules;
use Spatie\LaravelData\Support\Validation\RuleDenormalizer;
use Spatie\LaravelData\Support\Validation\RuleNormalizer;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Spatie\LaravelData\Support\Validation\ValidationPath;

class DataValidationRulesResolver
{
    /** @var array<string, array|false> Cache of static validation rules. False means not cacheable. */
    protected array $staticRulesCache = [];

    public function __construct(
        protected DataConfig $dataConfig,
        protected RuleNormalizer $ruleAttributesResolver,
        protected RuleDenormalizer $ruleDenormalizer,
        protected DataMorphClassResolver $dataMorphClassResolver,
    ) {
    }

    public function execute(
        string $class,
        array $fullPayload,
        ValidationPath $path,
        DataRules $dataRules
    ): array {
        $dataClass = $this->dataConfig->getDataClass($class);

        if ($dataClass->isAbstract && $dataClass->propertyMorphable) {
            $payload = $path->isRoot()
                ? $fullPayload
                : Arr::get($fullPayload, $path->get(), []);

            $morphedClass = $this->dataMorphClassResolver->execute(
                $dataClass,
                [$payload],
            );

            $dataClass = $morphedClass
                ? $this->dataConfig->getDataClass($morphedClass)
                : $dataClass;
        }

        $withoutValidationProperties = [];

        foreach ($dataClass->properties as $dataProperty) {
            $propertyPath = $path->property($dataProperty->inputMappedName ?? $dataProperty->name);

            if ($this->shouldSkipPropertyValidation($dataProperty, $fullPayload, $propertyPath)) {
                $withoutValidationProperties[] = $dataProperty->name;

                continue;
            }

            if ($dataProperty->type->kind->isDataObject() || $dataProperty->type->kind->isDataCollectable()) {
                $this->resolveDataSpecificRules(
                    $dataProperty,
                    $fullPayload,
                    $path,
                    $propertyPath,
                    $dataRules
                );

                continue;
            }

            $rules = $this->inferRulesForDataProperty(
                $dataProperty,
                PropertyRules::create(),
                $fullPayload,
                $path,
            );

            if ($dataProperty->morphable) {
                $rules[] = new EnsurePropertyMorphable($dataClass);
            }

            $dataRules->add($propertyPath, $rules);
        }

        $this->resolveOverwrittenRules(
            $dataClass,
            $fullPayload,
            $path,
            $dataRules,
            $withoutValidationProperties
        );

        return $dataRules->rules;
    }

    protected function shouldSkipPropertyValidation(
        DataProperty $dataProperty,
        array $fullPayload,
        ValidationPath $propertyPath,
    ): bool {
        if ($dataProperty->validate === false) {
            return true;
        }

        if ($dataProperty->hasDefaultValue && Arr::has($fullPayload, $propertyPath->get()) === false) {
            return true;
        }

        return false;
    }

    protected function resolveDataSpecificRules(
        DataProperty $dataProperty,
        array $fullPayload,
        ValidationPath $path,
        ValidationPath $propertyPath,
        DataRules $dataRules,
    ): void {
        $isOptionalAndEmpty = $dataProperty->type->isOptional && Arr::has($fullPayload, $propertyPath->get()) === false;
        $isNullableAndEmpty = $dataProperty->type->isNullable && Arr::get($fullPayload, $propertyPath->get()) === null;

        if ($isOptionalAndEmpty || $isNullableAndEmpty) {
            $this->resolveToplevelRules(
                $dataProperty,
                $fullPayload,
                $path,
                $propertyPath,
                $dataRules
            );

            return;
        }

        if ($dataProperty->type->kind->isDataObject()) {
            $this->resolveDataObjectSpecificRules(
                $dataProperty,
                $fullPayload,
                $path,
                $propertyPath,
                $dataRules
            );

            return;
        }

        if ($dataProperty->type->kind->isDataCollectable()) {
            $this->resolveDataCollectionSpecificRules(
                $dataProperty,
                $fullPayload,
                $path,
                $propertyPath,
                $dataRules
            );
        }
    }

    protected function resolveDataObjectSpecificRules(
        DataProperty $dataProperty,
        array $fullPayload,
        ValidationPath $path,
        ValidationPath $propertyPath,
        DataRules $dataRules,
    ): void {
        $this->resolveToplevelRules(
            $dataProperty,
            $fullPayload,
            $path,
            $propertyPath,
            $dataRules
        );

        $this->execute(
            $dataProperty->type->dataClass,
            $fullPayload,
            $propertyPath,
            $dataRules,
        );
    }

    protected function resolveDataCollectionSpecificRules(
        DataProperty $dataProperty,
        array $fullPayload,
        ValidationPath $path,
        ValidationPath $propertyPath,
        DataRules $dataRules,
    ): void {
        $this->resolveToplevelRules(
            $dataProperty,
            $fullPayload,
            $path,
            $propertyPath,
            $dataRules,
            shouldBePresent: true
        );

        $collectionPayload = Arr::get($fullPayload, $propertyPath->get());

        // If collection payload is null or not an array, no nested rules needed
        if (! is_array($collectionPayload)) {
            return;
        }

        $nestedDataClass = $this->dataConfig->getDataClass($dataProperty->type->dataClass);

        // Optimization: If nested class has NO dynamic rules method, use direct approach
        if (! $nestedDataClass->hasDynamicValidationRules) {
            $this->resolveStaticCollectionRules(
                $nestedDataClass,
                $collectionPayload,
                $fullPayload,
                $propertyPath,
                $dataRules
            );

            return;
        }

        $this->resolveDynamicCollectionRules(
            $dataProperty,
            $fullPayload,
            $propertyPath,
            $dataRules
        );
    }

    protected function resolveStaticCollectionRules(
        DataClass $nestedDataClass,
        array $collectionPayload,
        array $fullPayload,
        ValidationPath $propertyPath,
        DataRules $dataRules,
    ): void {
        $cacheKey = $nestedDataClass->name;

        // Check if we've already determined cacheability for this class
        if (! array_key_exists($cacheKey, $this->staticRulesCache)) {
            if ($this->canSafelyCacheRules($nestedDataClass)) {
                // Generate static rules once for caching
                $staticRules = $this->generateStaticRules($nestedDataClass);
                $this->staticRulesCache[$cacheKey] = $staticRules;
            } else {
                // Mark as not cacheable
                $this->staticRulesCache[$cacheKey] = false;
            }
        }

        $cachedRules = $this->staticRulesCache[$cacheKey];

        if ($cachedRules !== false) {
            // Use cached static rules
            foreach ($collectionPayload as $collectionItemKey => $collectionItemValue) {
                $itemPath = $propertyPath->property($collectionItemKey);

                if (! is_array($collectionItemValue)) {
                    $dataRules->add($itemPath, ['array']);
                    continue;
                }

                // Apply cached rules with proper path adjustment
                foreach ($cachedRules as $ruleKey => $ruleValue) {
                    $adjustedPath = $itemPath->property($ruleKey);
                    $dataRules->add($adjustedPath, $ruleValue);
                }
            }
        } else {
            // Fallback to full context-aware rule generation
            foreach ($collectionPayload as $collectionItemKey => $collectionItemValue) {
                $itemPath = $propertyPath->property($collectionItemKey);

                if (! is_array($collectionItemValue)) {
                    $dataRules->add($itemPath, ['array']);
                    continue;
                }

                $this->execute(
                    $nestedDataClass->name,
                    $fullPayload,
                    $itemPath,
                    $dataRules
                );
            }
        }
    }

    protected function resolveDynamicCollectionRules(
        DataProperty $dataProperty,
        array $fullPayload,
        ValidationPath $propertyPath,
        DataRules $dataRules,
    ): void {
        // Use Rule::forEach for dynamic rules (classes with rules() method)
        $dataRules->addCollection($propertyPath, Rule::forEach(function (mixed $value, mixed $attribute) use ($fullPayload, $dataProperty) {
            if (! is_array($value)) {
                return ['array'];
            }

            $rules = $this->execute(
                $dataProperty->type->dataClass,
                $fullPayload,
                ValidationPath::create($attribute),
                DataRules::create()
            );

            return collect($rules)->keyBy(
                fn (mixed $rules, string $key) => Str::after($key, "{$attribute}.") // TODO: let's do this better
            )->all();
        }));
    }

    protected function resolveToplevelRules(
        DataProperty $dataProperty,
        array $fullPayload,
        ValidationPath $path,
        ValidationPath $propertyPath,
        DataRules $dataRules,
        bool $shouldBePresent = false
    ): void {
        $rules = [];

        if ($shouldBePresent) {
            $rules[] = Present::create();
        }

        $rules[] = ArrayType::create();

        $toplevelRules = $this->inferRulesForDataProperty(
            $dataProperty,
            PropertyRules::create(...$rules),
            $fullPayload,
            $path,
        );

        $dataRules->add($propertyPath, $toplevelRules);
    }


    protected function resolveOverwrittenRules(
        DataClass $class,
        array $fullPayload,
        ValidationPath $path,
        DataRules $dataRules,
        array $withoutValidationProperties
    ): void {
        if (! method_exists($class->name, 'rules')) {
            return;
        }

        $validationContext = new ValidationContext(
            $path->isRoot() ? $fullPayload : Arr::get($fullPayload, $path->get(), []),
            $fullPayload,
            $path
        );

        $overwrittenRules = app()->call([$class->name, 'rules'], ['context' => $validationContext]);
        $shouldMergeRules = $class->attributes->has(MergeValidationRules::class);

        foreach ($overwrittenRules as $key => $rules) {
            if (in_array($key, $withoutValidationProperties)) {
                continue;
            }

            $rules = collect(Arr::wrap($rules))
                ->map(fn (mixed $rule) => $this->ruleDenormalizer->execute($rule, $path))
                ->flatten()
                ->all();

            $shouldMergeRules
                ? $dataRules->merge($path->property($key), $rules)
                : $dataRules->add($path->property($key), $rules);
        }
    }

    protected function inferRulesForDataProperty(
        DataProperty $property,
        PropertyRules $rules,
        array $fullPayload,
        ValidationPath $path,
    ): array {
        $context = new ValidationContext(
            $path->isRoot() ? $fullPayload : Arr::get($fullPayload, $path->get(), null),
            $fullPayload,
            $path
        );

        foreach ($this->dataConfig->ruleInferrers as $inferrer) {
            $inferrer->handle($property, $rules, $context);
        }

        return $this->ruleDenormalizer->execute(
            $rules->all(),
            $path
        );
    }

    protected function canSafelyCacheRules(DataClass $dataClass): bool
    {
        // Very conservative approach - only cache if ALL properties are safe
        foreach ($dataClass->properties as $property) {
            // Check for common validation attributes that might cause context dependencies
            $potentiallyUnsafeAttributes = [
                'Spatie\LaravelData\Attributes\Validation\RequiredIf',
                'Spatie\LaravelData\Attributes\Validation\RequiredUnless',
                'Spatie\LaravelData\Attributes\Validation\RequiredWith',
                'Spatie\LaravelData\Attributes\Validation\Same',
                'Spatie\LaravelData\Attributes\Validation\Different',
                'Spatie\LaravelData\Attributes\Validation\ConfirmedIf',
                'Spatie\LaravelData\Attributes\Validation\AcceptedIf',
                'Spatie\LaravelData\Attributes\Validation\DeclinedIf',
                'Spatie\LaravelData\Attributes\Validation\ProhibitedIf',
                'Spatie\LaravelData\Attributes\Validation\ProhibitedUnless',
                'Spatie\LaravelData\Attributes\Validation\ExcludeIf',
                'Spatie\LaravelData\Attributes\Validation\ExcludeUnless',
            ];

            // If any potentially unsafe validation attributes exist, don't cache
            foreach ($potentiallyUnsafeAttributes as $attributeClass) {
                if ($property->attributes->has($attributeClass)) {
                    return false;
                }
            }

            // Don't cache if property is morphable - complex logic
            if ($property->morphable) {
                return false;
            }

            // Don't cache nested data objects/collections - they may have their own complex rules
            if ($property->type->kind->isDataObject() || $property->type->kind->isDataCollectable()) {
                return false;
            }
        }

        // Don't cache abstract or morphable classes
        if ($dataClass->isAbstract || $dataClass->propertyMorphable) {
            return false;
        }

        return true;
    }

    protected function generateStaticRules(DataClass $dataClass): array
    {
        // Generate rules using an empty payload and root path to get only structural rules
        $rules = $this->execute(
            $dataClass->name,
            [], // Empty payload - only structural rules, no context dependencies
            ValidationPath::create(),
            DataRules::create()
        );

        return $rules;
    }
}
