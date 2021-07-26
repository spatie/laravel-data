<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelData\DataCollection;

class DummyModel extends Model
{
    protected $casts = [
        'data' => SimpleData::class,
        'data_collection' => DataCollection::class.':'.SimpleData::class,
    ];

    public $timestamps = false;

    public static function migrate()
    {
        Schema::create('dummy_models', function (Blueprint $blueprint) {
            $blueprint->increments('id');

            $blueprint->text('data')->nullable();
            $blueprint->text('data_collection')->nullable();
        });
    }
}
