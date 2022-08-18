<?php

namespace Spatie\LaravelData\Support\VarDumper;

use Spatie\LaravelData\Contracts\DataCollectable;
use Spatie\LaravelData\Contracts\DataObject;
use Symfony\Component\VarDumper\Cloner\Stub;

class DataVarDumperCaster
{
    public static function castDataObject(DataObject $data, array $a, Stub $stub, bool $isNested)
    {
        return $data->all();
    }

    public static function castDataCollectable(DataCollectable $data, array $a, Stub $stub, bool $isNested)
    {
        return [
            'items' => $data->all(),
        ];
    }
}
