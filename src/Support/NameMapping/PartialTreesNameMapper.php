<?php

namespace Spatie\LaravelData\Support\NameMapping;

use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;

class PartialTreesNameMapper
{
    /** @var array<class-string<\Spatie\LaravelData\Support\DataClass>, \Spatie\LaravelData\Support\NameMapping\PartialTreesNameMapping> */
    protected static array $mappings = [];

    public function __construct(
        protected DataConfig $dataConfig,
    ) {
    }

    public function getMapping(
        string|DataClass $dataClass
    ): PartialTreesNameMapping {
        $dataClass = $dataClass instanceof DataClass
            ? $dataClass
            : $this->dataConfig->getDataClass($dataClass);

        if (! array_key_exists($dataClass->name, static::$mappings)) {
            static::$mappings[$dataClass->name] = $this->build($dataClass);
        }

        return static::$mappings[$dataClass->name];
    }

    protected function build(
        DataClass $dataClass,
    ): PartialTreesNameMapping {
        $mapped = [];
        $mappedDataObjects = [];

        $dataClass->properties->each(function (DataProperty $dataProperty) use (&$mapped, &$mappedDataObjects) {
            if ($dataProperty->type->isDataObject || $dataProperty->type->isDataCollectable) {
                $mappedDataObjects[$dataProperty->name] = $dataProperty->type->dataClass;
            }

            if ($dataProperty->outputMappedName === null) {
                return;
            }

            $mapped[$dataProperty->outputMappedName] = $dataProperty->name;
        });

        return new PartialTreesNameMapping(
            $this,
            $mapped,
            $mappedDataObjects
        );
    }
}
