<?php

namespace Spatie\LaravelData\Support;

class PartialTreesMapping
{
    public function __construct(
        readonly public string $original,
        readonly public string $mapped,
        readonly public array $children = [],
    ) {
    }

    public function getChild(string $mapped): ?PartialTreesMapping
    {
        foreach ($this->children as $child) {
            if ($child->mapped === $mapped || $child->original === $mapped) {
                return $child;
            }
        }

        return null;
    }

    public static function fromRootDataClass(DataClass $dataClass): self
    {
        return self::fromDataClass('root', 'root', $dataClass);
    }

    public static function fromDataClass(
        string $original,
        string $mapped,
        DataClass $dataClass
    ): self {
        $children = [];

        $dataClass->properties->each(function (DataProperty $dataProperty) use (&$children) {
            if ($dataProperty->type->isDataObject || $dataProperty->type->isDataCollectable) {
                $children[] = self::fromDataClass(
                    $dataProperty->name,
                    $dataProperty->outputMappedName ?? $dataProperty->name,
                    app(DataConfig::class)->getDataClass($dataProperty->type->dataClass),
                );

                return;
            }

            if ($dataProperty->outputMappedName === null) {
                return;
            }

            $children[] = new self(
                $dataProperty->name,
                $dataProperty->outputMappedName,
            );
        });

        return new self(
            $original,
            $mapped,
            $children
        );
    }
}
