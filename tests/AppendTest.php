<?php

use Spatie\LaravelData\Data;

it('can append data via method overwriting', function () {
    $data = new class ('Freek') extends Data {
        public function __construct(public string $name)
        {
        }

        public function with(): array
        {
            return ['alt_name' => "{$this->name} from Spatie"];
        }
    };

    expect($data->toArray())->toMatchArray([
        'name' => 'Freek',
        'alt_name' => 'Freek from Spatie',
    ]);
});

it('can append data via method overwriting with closures', function () {
    $data = new class ('Freek') extends Data {
        public function __construct(public string $name)
        {
        }

        public function with(): array
        {
            return [
                'alt_name' => static function (self $data) {
                    return $data->name.' from Spatie via closure';
                },
            ];
        }
    };

    expect($data->toArray())->toMatchArray([
        'name' => 'Freek',
        'alt_name' => 'Freek from Spatie via closure',
    ]);
});

it('can append data via method call', function () {
    $data = new class ('Freek') extends Data {
        public function __construct(public string $name)
        {
        }
    };

    $transformed = $data->additional([
        'company' => 'Spatie',
        'alt_name' => fn (Data $data) => "{$data->name} from Spatie",
    ])->toArray();

    expect($transformed)->toMatchArray([
        'name' => 'Freek',
        'company' => 'Spatie',
        'alt_name' => 'Freek from Spatie',
    ]);
});


test('when using additional method and with method the additional method will be prioritized', function () {
    $data = new class ('Freek') extends Data {
        public function __construct(public string $name)
        {
        }

        public function with(): array
        {
            return [
                'alt_name' => static function (self $data) {
                    return $data->name.' from Spatie via closure';
                },
            ];
        }
    };

    expect($data->additional(['alt_name' => 'I m Freek from additional'])->toArray())->toMatchArray([
        'name' => 'Freek',
        'alt_name' => 'I m Freek from additional',
    ]);
});

