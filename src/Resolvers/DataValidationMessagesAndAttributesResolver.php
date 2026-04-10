<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\ValidationPath;

class DataValidationMessagesAndAttributesResolver
{
    public function __construct(
        protected DataConfig $dataConfig,
        protected DataMorphClassResolver $dataMorphClassResolver,
        protected DataClassFromValidationPayloadResolver $dataClassFromValidationPayloadResolver,
    ) {
    }

    public function execute(
        string $class,
        array $fullPayload,
        ValidationPath $path,
        array $nestingChain = [],
    ): array {
        $messages = [];
        $attributes = [];

        $dataClass = $this->dataConfig->getDataClass($class);

        if ($dataClass->isAbstract && $dataClass->propertyMorphable && $path->containsWildcards()) {
            // We need to know paths without wildcards to be able to resolve the correct data class
            return $this->resolveWildcardMessagesAndAttributes($class, $fullPayload, $path, $nestingChain);
        }

        if ($dataClass->isAbstract && $dataClass->propertyMorphable) {
            $dataClass = $this->dataClassFromValidationPayloadResolver->execute($class, $fullPayload, $path);
        }

        foreach ($dataClass->properties as $dataProperty) {
            $propertyPath = $path->property($dataProperty->inputMappedName ?? $dataProperty->name);

            if (
                $dataProperty->type->kind->isNonDataRelated()
                && $dataProperty->validate === false
            ) {
                continue;
            }

            if ($dataProperty->type->kind->isDataObject()) {
                if (in_array($dataProperty->type->dataClass, $nestingChain)) {
                    continue;
                }

                $nested = $this->execute(
                    $dataProperty->type->dataClass,
                    $fullPayload,
                    $propertyPath,
                    [...$nestingChain, $dataProperty->type->dataClass],
                );


                $messages[] = $nested['messages'];
                $attributes[] = $nested['attributes'];

                continue;
            }

            if ($dataProperty->type->kind->isDataCollectable()) {
                if (in_array($dataProperty->type->dataClass, $nestingChain)) {
                    continue;
                }

                $this->resolveCollectableMessagesAndAttributes(
                    $fullPayload,
                    $dataProperty,
                    $propertyPath,
                    $this->dataConfig->getDataClass($dataProperty->type->dataClass),
                    $nestingChain,
                    $messages,
                    $attributes
                );
            }
        }

        $messages = array_merge(...$messages);
        $attributes = array_merge(...$attributes);

        if (method_exists($dataClass->name, 'messages')) {
            $messages = collect(app()->call([$dataClass->name, 'messages']))
                ->keyBy(
                    fn (mixed $messages, string $key) => ! str_contains($key, '.') && is_string($messages)
                        ? $path->property("*.{$key}")->get()
                        : $path->property($key)->get()
                )
                ->merge($messages)
                ->all();
        }

        if (method_exists($dataClass->name, 'attributes')) {
            $attributes = collect(app()->call([$dataClass->name, 'attributes']))
                ->keyBy(fn (mixed $messages, string $key) => $path->property($key)->get())
                ->merge($attributes)
                ->all();
        }

        return ['messages' => $messages, 'attributes' => $attributes];
    }

    protected function resolveCollectableMessagesAndAttributes(
        array $fullPayload,
        DataProperty $dataProperty,
        ValidationPath $propertyPath,
        DataClass $collectedDataClass,
        array $nestingChain,
        array &$messages,
        array &$attributes,
    ): void {
        if (! $collectedDataClass->isAbstract || ! $collectedDataClass->propertyMorphable) {
            $collected = $this->execute(
                $dataProperty->type->dataClass,
                $fullPayload,
                $propertyPath->property('*'),
                [...$nestingChain, $dataProperty->type->dataClass],
            );

            $messages[] = $collected['messages'];
            $attributes[] = $collected['attributes'];

            return;
        }

        $items = Arr::get($fullPayload, $propertyPath->get());

        if (! is_array($items)) {
            return;
        }

        foreach ($items as $index => $item) {
            $morphedClass = $this->dataMorphClassResolver->execute(
                $collectedDataClass,
                [$item],
            );

            if (! $morphedClass) {
                $morphedClass = $dataProperty->type->dataClass;
            }

            $collected = $this->execute(
                $morphedClass,
                $fullPayload,
                $propertyPath->property($index),
                [...$nestingChain, $dataProperty->type->dataClass, $morphedClass],
            );

            $messages[] = $collected['messages'];
            $attributes[] = $collected['attributes'];
        }
    }

    protected function resolveWildcardMessagesAndAttributes(
        string $class,
        array $fullPayload,
        ValidationPath $path,
        array $nestingChain,
    ): array {
        $messages = [];
        $attributes = [];

        foreach ($path->matchingWildcardPayloadValidationPaths($fullPayload) as $resolvedPath) {
            $nested = $this->execute(
                $class,
                $fullPayload,
                $resolvedPath,
                $nestingChain,
            );

            $messages[] = $nested['messages'];
            $attributes[] = $nested['attributes'];
        }

        return [
            'messages' => array_merge(...$messages),
            'attributes' => array_merge(...$attributes),
        ];
    }
}
