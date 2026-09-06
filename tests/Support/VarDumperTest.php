<?php

use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Support\VarDumper\DataVarDumperCaster;
use Spatie\LaravelData\Support\VarDumper\VarDumperManager;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Symfony\Component\VarDumper\Cloner\AbstractCloner;

it('registers distinct casters for data objects and data collectables', function () {
    (new VarDumperManager())->initialize();

    $data = SimpleData::from('Hello');
    $collection = new DataCollection(SimpleData::class, [SimpleData::from('World')]);

    $dataCaster = AbstractCloner::$defaultCasters[BaseData::class] ?? null;
    $collectableCaster = AbstractCloner::$defaultCasters[BaseDataCollectable::class] ?? null;

    expect($dataCaster)->toBe([DataVarDumperCaster::class, 'castDataObject'])
        ->and($collectableCaster)->toBe([DataVarDumperCaster::class, 'castDataCollectable']);
});
