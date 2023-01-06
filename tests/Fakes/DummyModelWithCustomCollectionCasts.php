<?php

namespace Spatie\LaravelData\Tests\Fakes;

class DummyModelWithCustomCollectionCasts extends DummyModelWithCasts
{
    protected $table = 'dummy_model_with_casts';

    protected $casts = [
        'data' => SimpleData::class,
        'data_collection' => SimpleDataCollection::class.':'.SimpleData::class,
    ];
}
