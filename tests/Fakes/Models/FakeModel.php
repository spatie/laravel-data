<?php

namespace Spatie\LaravelData\Tests\Fakes\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\LaravelData\Tests\Factories\FakeModelFactory;

class FakeModel extends Model
{
    use HasFactory;

    protected $casts = [
        'date' => 'immutable_datetime',
    ];

    public function fakeNestedModels(): HasMany
    {
        return $this->hasMany(FakeNestedModel::class);
    }

    public function accessor(): Attribute
    {
        return Attribute::get(fn () => "accessor_{$this->string}");
    }

    public function getOldAccessorAttribute()
    {
        return "old_accessor_{$this->string}";
    }

    protected static function newFactory()
    {
        return FakeModelFactory::new();
    }
}
