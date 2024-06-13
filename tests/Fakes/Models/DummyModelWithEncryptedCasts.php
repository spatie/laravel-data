<?php

namespace Spatie\LaravelData\Tests\Fakes\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataCollection;

class DummyModelWithEncryptedCasts extends Model
{
    protected $casts = [
        'data' => SimpleData::class.':encrypted',
        'data_collection' => SimpleDataCollection::class.':'.SimpleData::class.',encrypted',
    ];

    protected $table = 'dummy_model_with_casts';

    public $timestamps = false;
}
