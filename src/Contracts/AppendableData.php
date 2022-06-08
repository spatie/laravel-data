<?php

namespace Spatie\LaravelData\Contracts;

interface AppendableData
{
    public function with(): array;

    public function additional(array $additional): object;

    public function getAdditionalData(): array;
}
