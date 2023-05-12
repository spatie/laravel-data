<?php

namespace Spatie\LaravelData\Tests\Fakes\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataCollection;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithDefaultValue;

class DummyModelWithDefaultCasts extends Model
{
    protected $casts = [
        'data' => SimpleDataWithDefaultValue::class.':default',
        'data_collection' => SimpleDataCollection::class.':'.SimpleData::class.',default',
    ];

    protected $table = 'dummy_model_with_casts';

    public $timestamps = false;
}
