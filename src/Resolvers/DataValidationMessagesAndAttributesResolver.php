<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\Validation\ValidationPath;

class DataValidationMessagesAndAttributesResolver
{
    public function __construct(
        protected DataConfig $dataConfig,
        protected DataMorphClassResolver $dataMorphClassResolver,
    ) {
    }

    public function execute(
        string $class,
        array $fullPayload,
        ValidationPath $path,
        array $nestingChain = [],
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

        $messages = [];
        $attributes = [];

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

                $collectedDataClass = $this->dataConfig->getDataClass($dataProperty->type->dataClass);

                if ($collectedDataClass->isAbstract && $collectedDataClass->propertyMorphable) {
                    $items = Arr::get($fullPayload, $propertyPath->get(), []);
                    foreach ($items as $index => $item) {
                        $morphedClass = $this->dataMorphClassResolver->execute(
                            $collectedDataClass,
                            [$item],
                        );

                        if (!$morphedClass) {
                            continue;
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

                } else {
                    $collected = $this->execute(
                        $dataProperty->type->dataClass,
                        $fullPayload,
                        $propertyPath->property('*'),
                        [...$nestingChain, $dataProperty->type->dataClass],
                    );

                    $messages[] = $collected['messages'];
                    $attributes[] = $collected['attributes'];

                }
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
}
