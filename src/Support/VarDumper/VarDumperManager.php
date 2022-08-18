<?php

namespace Spatie\LaravelData\Support\VarDumper;

use Spatie\LaravelData\Contracts\DataCollectable;
use Spatie\LaravelData\Contracts\DataObject;
use Symfony\Component\VarDumper\Cloner\AbstractCloner;

class VarDumperManager
{
    public function initialize(): void
    {
        AbstractCloner::$defaultCasters[DataObject::class] = [DataVarDumperCaster::class, 'castDataObject'];
        AbstractCloner::$defaultCasters[DataCollectable::class] = [DataVarDumperCaster::class, 'castDataCollectable'];
    }
}
