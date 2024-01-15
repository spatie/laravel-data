<?php

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataPipeline;
use Spatie\LaravelData\DataPipes\AuthorizedDataPipe;
use Spatie\LaravelData\DataPipes\CastPropertiesDataPipe;
use Spatie\LaravelData\DataPipes\DefaultValuesDataPipe;

it('can prepend a data pipe at the beginning of the pipeline', function () {
    $pipeline = DataPipeline::create()
        ->through(DefaultValuesDataPipe::class)
        ->through(CastPropertiesDataPipe::class)
        ->firstThrough(AuthorizedDataPipe::class);

    $reflectionProperty = tap(
        new ReflectionProperty(DataPipeline::class, 'pipes'),
        static fn (ReflectionProperty $r) => $r->setAccessible(true),
    );

    $pipes = $reflectionProperty->getValue($pipeline);

    expect($pipes)
        ->toHaveCount(3)
        ->toMatchArray([
            AuthorizedDataPipe::class,
            DefaultValuesDataPipe::class,
            CastPropertiesDataPipe::class,
        ]);
});

it('can restructure payload before entering the pipeline', function () {
    $class = new class () extends Data {
        public function __construct(
            public string|null $name = null,
            public string|null $address = null,
        ) {
        }

        public static function prepareForPipeline(Collection $properties): Collection
        {
            $properties->put('address', $properties->only(['line_1', 'city', 'state', 'zipcode'])->join(','));

            return $properties;
        }
    };

    $instance = $class::from([
        'name' => 'Freek',
        'line_1' => '123 Sesame St',
        'city' => 'New York',
        'state' => 'NJ',
        'zipcode' => '10010',
    ]);

    expect($instance->toArray())->toMatchArray([
        'name' => 'Freek',
        'address' => '123 Sesame St,New York,NJ,10010',
    ]);
});
