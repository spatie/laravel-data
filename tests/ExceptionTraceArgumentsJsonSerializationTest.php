<?php

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Exceptions\CannotCreateData;
use Spatie\LaravelData\Tests\Fakes\NotExtendedDataClass;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

// enable args in exception trace
beforeEach(function () {
    ini_set('zend.exception_ignore_args', 'Off');
});

it('can json_encode exception trace with args when not all required properties passed', function () {
    try {
        SimpleData::from([]);
    } catch (CannotCreateData $e) {
        $trace = $e->getTrace();
        $encodedJson = json_encode($trace, JSON_THROW_ON_ERROR);

        expect($encodedJson)->toBeJson();
    }
});

it('can json_encode exception trace with args when nested property not extends Data or Dto class', function () {
    $dataClass = new class ('', new NotExtendedDataClass('')) extends Data {
        public function __construct(
            public string $string,
            public NotExtendedDataClass $nested
        ) {
        }
    };

    try {
        $dataClass::from(['string' => '', 'nested' => ['name' => '']]);
    } catch (TypeError $e) {
        $trace = $e->getTrace();
        $encodedJson = json_encode($trace, JSON_THROW_ON_ERROR);

        expect($encodedJson)->toBeJson();
    }
});
