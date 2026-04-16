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
    public function __construct(
        protected DataConfig $dataConfig,
        protected RuleNormalizer $ruleAttributesResolver,
        protected RuleDenormalizer $ruleDenormalizer,
        protected DataMorphClassResolver $dataMorphClassResolver,
        protected DataClassFromValidationPayloadResolver $dataClassFromValidationPayloadResolver,
    ) {
    }

    public function execute(
        string $class,
        array $fullPayload,
        ValidationPath $path,
        DataRules $dataRules
    ): array {
        $dataClass = $this->dataClassFromValidationPayloadResolver->execute($class, $fullPayload, $path);

        $withoutValidationProperties = [];

        foreach ($dataClass->properties as $dataProperty) {
            $propertyPath = $path->property($dataProperty->inputMappedName ?? $dataProperty->name);

            if ($this->shouldSkipPropertyValidation($dataProperty, $fullPayload, $propertyPath)) {
                $withoutValidationProperties[] = $dataProperty->name;

                continue;
            }

            $isOptionalAndEmpty = $dataProperty->type->isOptional && Arr::has($fullPayload, $propertyPath->get()) === false;
            $isNullableAndEmpty = $dataProperty->type->isNullable && Arr::get($fullPayload, $propertyPath->get()) === null;

            if ($dataProperty->type->kind->isDataRelated() && ($isOptionalAndEmpty || $isNullableAndEmpty)) {
                $this->resolveToplevelRules(
                    $dataProperty,
                    $fullPayload,
                    $path,
                    $propertyPath,
                    $dataRules
                );

                continue;
            }

            if ($dataProperty->type->kind->isDataObject()) {
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

                continue;
            }

            if ($dataProperty->type->kind->isDataCollectable()) {
                $this->resolveDataCollectionSpecificRules(
                    $dataClass,
                    $dataProperty,
                    $fullPayload,
                    $path,
                    $propertyPath,
                    $dataRules,
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

    protected function resolveDataCollectionSpecificRules(
        DataClass $dataClass,
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

        if (! is_array($collectionPayload)) {
            return;
        }

        $itemDataClass = $this->dataConfig->getDataClass($dataProperty->type->dataClass);

        if (! $itemDataClass->hasDynamicValidationRules && ! $dataClass->hasDynamicValidationRules) {
            $this->resolveStaticCollectionRules(
                $itemDataClass,
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
        DataClass $itemDataClass,
        array $collectionPayload,
        array $fullPayload,
        ValidationPath $propertyPath,
        DataRules $dataRules,
    ): void {
        foreach ($collectionPayload as $key => $value) {
            $itemPath = $propertyPath->property($key);

            if (! is_array($value)) {
                $dataRules->add($itemPath, ['array']);

                continue;
            }

            $this->execute(
                $itemDataClass->name,
                $fullPayload,
                $itemPath,
                $dataRules
            );
        }
    }

    protected function resolveDynamicCollectionRules(
        DataProperty $dataProperty,
        array $fullPayload,
        ValidationPath $propertyPath,
        DataRules $dataRules,
    ): void {
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
        if (! $class->hasDynamicValidationRules) {
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
}
