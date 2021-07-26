<?php

namespace Spatie\LaravelData\Attributes;

interface DataValidationAttribute
{
    public function getRules(): array;
}
