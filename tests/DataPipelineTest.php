<?php

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
