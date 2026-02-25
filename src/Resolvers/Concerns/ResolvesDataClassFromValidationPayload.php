<?php

namespace Spatie\LaravelData\Resolvers\Concerns;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Resolvers\DataMorphClassResolver;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\Validation\ValidationPath;

trait ResolvesDataClassFromValidationPayload
{
    public function dataClassFromValidationPayload(
        DataConfig $dataConfig,
        DataMorphClassResolver $dataMorphClassResolver,
        string $class,
        array $fullPayload,
        ValidationPath $path,
    ): DataClass {
        $dataClass = $dataConfig->getDataClass($class);

        if (! $dataClass->isAbstract || ! $dataClass->propertyMorphable) {
            return $dataClass;
        }

        $payload = $path->isRoot()
            ? $fullPayload
            : Arr::get($fullPayload, $path->get()) ?? [];

        $morphedClass = $dataMorphClassResolver->execute(
            $dataClass,
            [$payload],
        );

        return $morphedClass
            ? $dataConfig->getDataClass($morphedClass)
            : $dataClass;
    }
}
