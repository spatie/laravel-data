<?php

namespace Spatie\LaravelData\Contracts;

interface ComparableData
{
    public function equalTo(TransformableData $other): bool;
}
