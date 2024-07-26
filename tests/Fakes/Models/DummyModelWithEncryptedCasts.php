<?php

namespace Spatie\LaravelData\Tests\Fakes\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Tests\Fakes\AbstractData\AbstractData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataCollection;

class DummyModelWithEncryptedCasts extends Model
{
    protected $casts = [
        'data' => SimpleData::class.':encrypted',
        'data_collection' => SimpleDataCollection::class.':'.SimpleData::class.',encrypted',
        'abstract_data' => AbstractData::class.':encrypted',
        'abstract_collection' => DataCollection::class . ':' . AbstractData::class.',encrypted',
    ];

    protected $table = 'dummy_model_with_casts';

    public $timestamps = false;
}
