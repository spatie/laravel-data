<?php

namespace Spatie\LaravelData\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\LaravelData\Tests\Fakes\Models\FakeModel;
use Spatie\LaravelData\Tests\Fakes\Models\FakeNestedModel;

class FakeNestedModelFactory extends Factory
{
    protected $model = FakeNestedModel::class;

    public function definition()
    {
        return [
            'string' => $this->faker->name,
            'nullable' => null,
            'date' => $this->faker->dateTime(),
            'fake_model_id' => FakeModel::factory(),
        ];
    }
}
