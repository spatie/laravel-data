<?php

namespace Spatie\LaravelData\Support\VarDumper;

use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Symfony\Component\VarDumper\Cloner\AbstractCloner;

class VarDumperManager
{
    public function initialize(): void
    {
        AbstractCloner::$defaultCasters[BaseData::class] = [DataVarDumperCaster::class, 'castDataObject'];
        AbstractCloner::$defaultCasters[BaseDataCollectable::class] = [DataVarDumperCaster::class, 'castDataCollectable'];
    }
}
