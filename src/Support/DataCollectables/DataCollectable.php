<?php

namespace Spatie\LaravelData\Support\DataCollectables;

abstract class DataCollectable
{
    abstract public function normalize($items): array;

    abstract public function denormalize(array $items, string $dataClass, string $collectableClass): mixed;
}
