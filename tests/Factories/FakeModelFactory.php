<?php

namespace Spatie\LaravelData\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\LaravelData\Tests\Fakes\Models\FakeModel;

class FakeModelFactory extends Factory
{
    protected $model = FakeModel::class;

    public function definition()
    {
        return [
            'string' => $this->faker->name,
            'nullable' => null,
            'date' => $this->faker->dateTime(),
        ];
    }
}
