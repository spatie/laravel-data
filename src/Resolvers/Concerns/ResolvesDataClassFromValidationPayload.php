<?php

namespace Spatie\LaravelData\Resolvers\Concerns;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\Validation\ValidationPath;

trait ResolvesDataClassFromValidationPayload
{
    public function dataClassFromValidationPayload(
        string $class,
        array $fullPayload,
        ValidationPath $path,
    ): DataClass {
        $dataClass = $this->dataConfig->getDataClass($class);

        if (! $dataClass->isAbstract || ! $dataClass->propertyMorphable) {
            return $dataClass;
        }

        $payload = $path->isRoot()
            ? $fullPayload
            : Arr::get($fullPayload, $path->get()) ?? [];

        $morphedClass = $this->dataMorphClassResolver->execute(
            $dataClass,
            [$payload],
        );

        return $morphedClass
            ? $this->dataConfig->getDataClass($morphedClass)
            : $dataClass;
    }
}
