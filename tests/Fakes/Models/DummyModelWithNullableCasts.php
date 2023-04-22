<?php

namespace Spatie\LaravelData\Tests\Fakes\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataCollection;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithDefaultValue;

class DummyModelWithNullableCasts extends Model
{
    protected $casts = [
        'data' => SimpleDataWithDefaultValue::class.':nullable',
        'data_collection' => SimpleDataCollection::class.':'.SimpleData::class.',nullable',
    ];

    protected $table = 'dummy_model_with_casts';

    public $timestamps = false;
}
