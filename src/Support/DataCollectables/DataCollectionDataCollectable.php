<?php

namespace Spatie\LaravelData\Support\DataCollectables;

use Spatie\LaravelData\DataCollection;

class DataCollectionDataCollectable extends DataCollectable
{
    /**
     * @param \Spatie\LaravelData\DataCollection $items
     */
    public function normalize($items): array
    {
        return $items->items();
    }

    public function denormalize(array $items, string $dataClass, string $collectableClass): DataCollection
    {
        return new $collectableClass($dataClass, $items);
    }
}
