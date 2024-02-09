<?php

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\WithData;

it('can add the WithData trait to a model', function () {
    $model = new class () extends Model {
        use WithData;

        protected string $dataClass = SimpleData::class;
    };

    $model->fill([
        'string' => 'Hello World',
    ]);

    $data = $model->getData();

    expect($data)->toEqual(SimpleData::from('Hello World'));
});

it('can define the WithData trait data class by method', function () {
    $arrayable = new class () implements Arrayable {
        use WithData;

        public function toArray()
        {
            return [
                'string' => 'Hello World',
            ];
        }

        protected function dataClass(): string
        {
            return SimpleData::class;
        }
    };

    $data = $arrayable->getData();

    expect($data)->toEqual(SimpleData::from('Hello World'));
});

it('can add the WithData trait to a request', function () {
    $formRequest = new class () extends FormRequest {
        use WithData;

        public string $dataClass = SimpleData::class;
    };

    $formRequest->replace([
        'string' => 'Hello World',
    ]);

    $data = $formRequest->getData();

    expect($data)->toEqual(SimpleData::from('Hello World'));
});
