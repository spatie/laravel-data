<?php

namespace Spatie\LaravelData\Resolvers;

use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Lazy\ConditionalLazy;
use Spatie\LaravelData\Support\Lazy\RelationalLazy;
use Spatie\LaravelData\Support\Transformation\TransformationContext;

class VisibleDataFieldsResolver
{
    /**
     * @param DataClass $dataClass
     *
     * @return array<string, TransformationContext|null>
     */
    public function execute(
        BaseData $data,
        DataClass $dataClass,
        TransformationContext $transformationContext,
    ): array {
        $dataInitializedFields = get_object_vars($data);

        $fields = $dataClass->transformationFields->resolve();

        foreach ($fields as $field => $next) {
            if (! array_key_exists($field, $dataInitializedFields)) {
                unset($fields[$field]);

                continue;
            }

            if ($next === true) {
                $fields[$field] = new TransformationContext(
                    $transformationContext->transformValues,
                    $transformationContext->mapPropertyNames,
                    $transformationContext->wrapExecutionType,
                );
            }
        }

        if ($transformationContext->exceptPartials) {
            $this->performExcept($fields, $transformationContext);
        }

        if (empty($fields)) {
            return [];
        }

        if ($transformationContext->onlyPartials) {
            $this->performOnly($fields, $transformationContext);
        }

        $includedFields = $transformationContext->includedPartials ? $this->resolveIncludedFields(
            $fields,
            $transformationContext,
            $dataClass,
        ) : [];

        $excludedFields = $transformationContext->excludedPartials ? $this->resolveExcludedFields(
            $fields,
            $transformationContext,
            $dataClass,
        ) : [];

        foreach ($fields as $field => $fieldTransFormationContext) {
            $value = $data->{$field};

            if ($value instanceof Optional) {
                unset($fields[$field]);

                continue;
            }

            if (! $value instanceof Lazy) {
                continue;
            }

            if ($value instanceof RelationalLazy || $value instanceof ConditionalLazy) {
                if (! $value->shouldBeIncluded()) {
                    unset($fields[$field]);
                }

                continue;
            }

            if (in_array($field, $excludedFields)) {
                unset($fields[$field]);

                continue;
            }

            if ($value->isDefaultIncluded() || in_array($field, $includedFields)) {
                continue;
            }

            unset($fields[$field]);
        }

        return $fields;
    }

    /**
     * @param array<string, TransformationContext|null> $fields
     */
    protected function performExcept(
        array &$fields,
        TransformationContext $transformationContext
    ): void {
        $exceptFields = [];

        foreach ($transformationContext->exceptPartials as $exceptPartial) {
            if ($exceptPartial->isUndefined()) {
                continue;
            }

            if ($exceptPartial->isAll()) {
                $fields = [];

                return;
            }

            if ($nested = $exceptPartial->getNested()) {
                $fields[$nested]->addExceptResolvedPartial($exceptPartial->next());

                continue;
            }

            if ($selectedFields = $exceptPartial->getFields()) {
                array_push($exceptFields, ...$selectedFields);
            }
        }

        foreach ($exceptFields as $exceptField) {
            unset($fields[$exceptField]);
        }
    }

    /**
     * @param array<string, TransformationContext|null> $fields
     */
    protected function performOnly(
        array &$fields,
        TransformationContext $transformationContext
    ): void {
        $onlyFields = null;

        foreach ($transformationContext->onlyPartials as $onlyPartial) {
            if ($onlyPartial->isUndefined() || $onlyPartial->isAll()) {
                // maybe filtered by next steps
                continue;
            }

            $onlyFields ??= [];

            if ($nested = $onlyPartial->getNested()) {
                $fields[$nested]->addOnlyResolvedPartial($onlyPartial->next());
                $onlyFields[] = $nested;

                continue;
            }

            if ($selectedFields = $onlyPartial->getFields()) {
                array_push($onlyFields, ...$selectedFields);
            }
        }

        if ($onlyFields === null) {
            return;
        }

        foreach ($fields as $fieldName => $fieldContext) {
            if (in_array($fieldName, $onlyFields)) {
                continue;
            }

            unset($fields[$fieldName]);
        }
    }

    /**
     * @param array<string, TransformationContext|null> $fields
     */
    protected function resolveIncludedFields(
        array &$fields,
        TransformationContext $transformationContext,
        DataClass $dataClass
    ): array {
        $includedFields = [];

        foreach ($transformationContext->includedPartials as $includedPartial) {
            if ($includedPartial->isUndefined()) {
                continue;
            }

            if ($includedPartial->isAll()) {
                $includedFields = $dataClass
                    ->properties
                    ->filter(fn (DataProperty $property) => $property->type->lazyType !== null && array_key_exists($property->name, $fields))
                    ->keys()
                    ->all();

                foreach ($includedFields as $includedField) {
                    // can be null when field is a non data object/collectable or array
                    $fields[$includedField]?->addIncludedResolvedPartial($includedPartial->next());
                }

                break;
            }

            if ($nested = $includedPartial->getNested()) {
                $fields[$nested]->addIncludedResolvedPartial($includedPartial->next());
                $includedFields[] = $nested;

                continue;
            }

            if ($selectedFields = $includedPartial->getFields()) {
                array_push($includedFields, ...$selectedFields);
            }
        }

        return $includedFields;
    }

    /**
     * @param array<string, TransformationContext|null> $fields
     */
    protected function resolveExcludedFields(
        array &$fields,
        TransformationContext $transformationContext,
        DataClass $dataClass
    ): array {
        $excludedFields = [];

        foreach ($transformationContext->excludedPartials as $excludedPartial) {
            if ($excludedPartial->isUndefined()) {
                continue;
            }

            if ($excludedPartial->isAll()) {
                $excludedFields = $dataClass
                    ->properties
                    ->filter(fn (DataProperty $property) => $property->type->lazyType !== null && array_key_exists($property->name, $fields))
                    ->keys()
                    ->all();

                foreach ($excludedFields as $excludedField) {
                    $fields[$excludedField]?->addExcludedResolvedPartial($excludedPartial->next());
                }

                break;
            }

            if ($nested = $excludedPartial->getNested()) {
                $fields[$nested]->addExcludedResolvedPartial($excludedPartial->next());
                $excludedFields[] = $nested;

                continue;
            }

            if ($selectedFields = $excludedPartial->getFields()) {
                array_push($excludedFields, ...$selectedFields);
            }
        }

        return $excludedFields;
    }
}
