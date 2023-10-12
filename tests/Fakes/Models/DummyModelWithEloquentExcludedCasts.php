<?php

namespace Spatie\LaravelData\Tests\Fakes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnumWithOptions;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithEloquentExcludedTransformerData;

class DummyModelWithEloquentExcludedCasts extends Model
{
    protected $casts = [
        'data' => SimpleDataWithEloquentExcludedTransformerData::class,
        'eloquent_excluded_enum' => DummyBackedEnumWithOptions::class,
    ];

    public $timestamps = false;

    public static function migrate()
    {
        Schema::create('dummy_model_with_eloquent_excluded_casts', function (Blueprint $blueprint) {
            $blueprint->increments('id');

            $blueprint->text('data')->nullable();
            $blueprint->string('eloquent_excluded_enum');
        });
    }
}
