<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\DataCollection;

class SimpleDataCollection extends DataCollection
{
    public function toJson($options = 0): string
    {
        return parent::toJson(JSON_PRETTY_PRINT);
    }
}
