<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Contracts\TransformableData as TransformableDataContract;

trait ComparableData
{
    public function equalTo(TransformableDataContract $other): bool
    {
        return $this->toArray() === $other->toArray();
    }
}
