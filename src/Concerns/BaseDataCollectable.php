<?php

namespace Spatie\LaravelData\Concerns;

trait BaseDataCollectable
{
    public function getDataClass(): string
    {
        return $this->dataClass;
    }
}
