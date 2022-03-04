<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Tests\Fakes\Models\FakeNestedModel;
use Spatie\LaravelData\Undefined;

class FakeNestedModelData extends Data
{
    public function __construct(
        public string $string,
        public ?string $nullable,
        public CarbonImmutable $date,
        public Undefined|Lazy|FakeModelData|null $fake_model
    ) {
    }

    public static function createWithLazyWhenLoaded(FakeNestedModel $model)
    {
        return new self(
            $model->string,
            $model->nullable,
            $model->date,
            Lazy::whenLoaded('fakeModel', $model, fn () => FakeModelData::from($model->fakeModel)),
        );
    }
}
