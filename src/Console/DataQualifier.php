<?php

namespace Spatie\LaravelData\Console;

interface DataQualifier
{
    public function qualify(string $model): string;
}
