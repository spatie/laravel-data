<?php

namespace Spatie\LaravelData\Tests\Fakes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Tests\Fakes\MultiData;

class DummyModelWithJson extends Model
{
    protected $casts = [
        'data' => MultiData::class,
        'data_collection' => DataCollection::class.':'.MultiData::class,
    ];

    public $timestamps = false;

    public static function migrate()
    {
        Schema::create('dummy_model_with_jsons', function (Blueprint $blueprint) {
            $blueprint->increments('id');

            $blueprint->json('data')->nullable();
            $blueprint->json('data_collection')->nullable();
        });
    }
}
