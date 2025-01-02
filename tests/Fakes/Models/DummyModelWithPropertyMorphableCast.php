<?php

namespace Spatie\LaravelData\Tests\Fakes\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Tests\Fakes\PropertyMorphableData\AbstractPropertyMorphableData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataCollection;

class DummyModelWithPropertyMorphableCast extends Model
{
    protected $casts = [
        'data' => AbstractPropertyMorphableData::class,
        'data_collection' => SimpleDataCollection::class.':'.AbstractPropertyMorphableData::class,
    ];

    protected $table = 'dummy_model_with_casts';

    public $timestamps = false;
}
