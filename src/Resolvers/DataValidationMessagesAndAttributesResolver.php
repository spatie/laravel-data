<?php

namespace Spatie\LaravelData\Resolvers;

use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\Validation\ValidationPath;

class DataValidationMessagesAndAttributesResolver
{
    public function __construct(
        protected DataConfig $dataConfig,
    ) {
    }

    public function execute(
        string $class,
        array $fullPayload,
        ValidationPath $path,
        array $nestingChain = [],
    ): array {
        $dataClass = $this->dataConfig->getDataClass($class);

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

        $messages = array_merge(...$messages);
        $attributes = array_merge(...$attributes);

        if (method_exists($class, 'messages')) {
            $messages = collect(app()->call([$class, 'messages']))
                ->keyBy(
                    fn (mixed $messages, string $key) => ! str_contains($key, '.') && is_string($messages)
                        ? $path->property("*.{$key}")->get()
                        : $path->property($key)->get()
                )
                ->merge($messages)
                ->all();
        }

        if (method_exists($class, 'attributes')) {
            $attributes = collect(app()->call([$class, 'attributes']))
                ->keyBy(fn (mixed $messages, string $key) => $path->property($key)->get())
                ->merge($attributes)
                ->all();
        }

        return ['messages' => $messages, 'attributes' => $attributes];
    }
}
