<?php

namespace Spatie\LaravelData\Support\NameMapping;

class PartialTreesNameMapping
{
    /*
    * @param array<string, string> $mapped
    * @param array<string, class-string<\Spatie\LaravelData\Support\DataClass>> $mappedDataObjects
    */
    public function __construct(
        readonly PartialTreesNameMapper $mapper,
        readonly array $mapped,
        readonly array $mappedDataObjects,
    ) {
    }

    public function getOriginalName(string $mapped): ?string
    {
        return $this->mapped[$mapped] ?? null;
    }

    public function getNextMapping(string $mappedOrOriginal): ?self
    {
        $dataClass = $this->mappedDataObjects[$mappedOrOriginal] ?? null;

        if ($dataClass === null) {
            return null;
        }

        return $this->mapper->getMapping($dataClass);
    }
}
