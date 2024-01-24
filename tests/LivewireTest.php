<?php

use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

it('works with livewire', function () {
    $class = new class ('') extends Data {
        use WireableData;

        public function __construct(
            public string $name,
        ) {
        }
    };

    $data = $class::fromLivewire(['name' => 'Freek']);

    expect($data)->toEqual(new $class('Freek'));

    expect($data->toLivewire())->toEqual(['name' => 'Freek']);
});
