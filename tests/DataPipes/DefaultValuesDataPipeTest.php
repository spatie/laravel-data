<?php

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

it('can create a data object with defaults empty', function () {
    $dataClass = new class ('', '', '') extends Data {
        public function __construct(
            public ?string $string,
            public Optional|string $optionalString,
            public string $stringWithDefault = 'Hi',
        ) {
        }
    };

    expect(new $dataClass(null, new Optional(), 'Hi'))
        ->toEqual($dataClass::from([]));
});

it('can create a data object with defined defaults', function () {
    $dataClass = new class ('', '', '') extends Data {
        public function __construct(
            public string $stringWithDefault,
        ) {
        }

        public static function defaults(): array
        {
            return [
                'stringWithDefault' => 'Hi',
            ];
        }
    };

    expect(new $dataClass('Hi'))->toEqual($dataClass::from([]));
});

