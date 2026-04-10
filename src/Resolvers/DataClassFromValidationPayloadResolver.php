<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\Validation\ValidationPath;

class DataClassFromValidationPayloadResolver
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
