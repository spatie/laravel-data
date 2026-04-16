<?php

namespace Spatie\LaravelData\Tests\Fakes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Tests\Fakes\AbstractData\AbstractData;
use Spatie\LaravelData\Tests\Fakes\LazyData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

class DummyModelWithCasts extends Model
{
    protected $casts = [
        'data' => SimpleData::class,
        'lazy_data' => LazyData::class,
        'data_collection' => DataCollection::class.':'.SimpleData::class,
        'lazy_data_collection' => DataCollection::class.':'.LazyData::class,
        'abstract_data' => AbstractData::class,
        'abstract_collection' => DataCollection::class . ':' . AbstractData::class,
    ];

    public $timestamps = false;

    public static function migrate()
    {
        Schema::create('dummy_model_with_casts', function (Blueprint $blueprint) {
            $blueprint->increments('id');

            $blueprint->text('data')->nullable();
            $blueprint->text('lazy_data')->nullable();
            $blueprint->text('data_collection')->nullable();
            $blueprint->text('lazy_data_collection')->nullable();
            $blueprint->text('abstract_data')->nullable();
            $blueprint->text('abstract_collection')->nullable();
        });
    }
}
