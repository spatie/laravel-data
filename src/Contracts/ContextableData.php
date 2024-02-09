<?php

namespace Spatie\LaravelData\Contracts;

use Spatie\LaravelData\Support\Transformation\DataContext;

interface ContextableData
{
    public function getDataContext(): DataContext;
}
