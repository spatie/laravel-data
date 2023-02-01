<?php

namespace Spatie\LaravelData\Tests\Fakes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DummyModel extends Model
{
    protected $casts = [
        'date' => 'datetime',
        'nullable_date' => 'datetime',
        'boolean' => 'boolean',
    ];

    public static function migrate()
    {
        Schema::create('dummy_models', function (Blueprint $blueprint) {
            $blueprint->increments('id');

            $blueprint->string('string');
            $blueprint->dateTime('date');
            $blueprint->dateTime('nullable_date')->nullable();
            $blueprint->boolean('boolean');

            $blueprint->timestamps();
        });
    }
}
