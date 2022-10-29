<?php

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Resolvers\DataValidatorResolver;

beforeEach(function () {
    $this->resolver = app(DataValidatorResolver::class);
});

it('can set the validator to stop on the first failure', function () {
    $dataClass = new class() extends Data
    {
        public static function stopOnFirstFailure(): bool
        {
            return true;
        }
    };

    $validator = $this->resolver->execute($dataClass::class, []);

    expect(invade($validator)->stopOnFirstFailure)->toBeTrue();
});
