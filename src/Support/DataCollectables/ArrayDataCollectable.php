<?php

namespace Spatie\LaravelData\Support\DataCollectables;

class ArrayDataCollectable extends DataCollectable
{
    public function normalize($items): array
    {
        return $items;
    }

    public function denormalize(array $items, string $dataClass, string $collectableClass): array
    {
        return $items;
    }
}
