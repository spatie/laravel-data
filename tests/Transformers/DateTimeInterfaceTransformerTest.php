<?php

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

it('can transform dates', function () {
    $transformer = new DateTimeInterfaceTransformer();

    $class = new class () extends Data {
        public Carbon $carbon;

        public CarbonImmutable $carbonImmutable;

        public DateTime $dateTime;

        public DateTimeImmutable $dateTimeImmutable;
    };

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'carbon')),
            new Carbon('19-05-1994 00:00:00'),
            TransformationContextFactory::create()->get($class)
        )
    )->toEqual('1994-05-19T00:00:00+00:00');

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'carbonImmutable')),
            new CarbonImmutable('19-05-1994 00:00:00'),
            TransformationContextFactory::create()->get($class)
        )
    )->toEqual('1994-05-19T00:00:00+00:00');

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'dateTime')),
            new DateTime('19-05-1994 00:00:00'),
            TransformationContextFactory::create()->get($class)
        )
    )->toEqual('1994-05-19T00:00:00+00:00');

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'dateTimeImmutable')),
            new DateTimeImmutable('19-05-1994 00:00:00'),
            TransformationContextFactory::create()->get($class)
        )
    )->toEqual('1994-05-19T00:00:00+00:00');
});

it('can transform dates with an alternative format', function () {
    $transformer = new DateTimeInterfaceTransformer(format: 'd-m-Y');

    $class = new class () extends Data {
        public Carbon $carbon;

        public CarbonImmutable $carbonImmutable;

        public DateTime $dateTime;

        public DateTimeImmutable $dateTimeImmutable;
    };

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'carbon')),
            new Carbon('19-05-1994 00:00:00'),
            TransformationContextFactory::create()->get($class)
        )
    )->toEqual('19-05-1994');

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'carbonImmutable')),
            new CarbonImmutable('19-05-1994 00:00:00'),
            TransformationContextFactory::create()->get($class)
        )
    )->toEqual('19-05-1994');

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'dateTime')),
            new DateTime('19-05-1994 00:00:00'),
            TransformationContextFactory::create()->get($class)
        )
    )->toEqual('19-05-1994');

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'dateTimeImmutable')),
            new DateTimeImmutable('19-05-1994 00:00:00'),
            TransformationContextFactory::create()->get($class)
        )
    )->toEqual('19-05-1994');
});

it('can change the timezone', function () {
    $transformer = new DateTimeInterfaceTransformer(setTimeZone: 'Europe/Brussels');

    $class = new class () extends Data {
        public Carbon $carbon;

        public CarbonImmutable $carbonImmutable;

        public DateTime $dateTime;

        public DateTimeImmutable $dateTimeImmutable;
    };

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'carbon')),
            new Carbon('19-05-1994 00:00:00'),
            TransformationContextFactory::create()->get($class)
        )
    )->toEqual('1994-05-19T02:00:00+02:00');

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'carbonImmutable')),
            new CarbonImmutable('19-05-1994 00:00:00'),
            TransformationContextFactory::create()->get($class)
        )
    )->toEqual('1994-05-19T02:00:00+02:00');

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'dateTime')),
            new DateTime('19-05-1994 00:00:00'),
            TransformationContextFactory::create()->get($class)
        )
    )->toEqual('1994-05-19T02:00:00+02:00');

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'dateTimeImmutable')),
            new DateTimeImmutable('19-05-1994 00:00:00'),
            TransformationContextFactory::create()->get($class)
        )
    )->toEqual('1994-05-19T02:00:00+02:00');
});

it('can transform dates with leading !', function () {
    config(['data.date_format' => '!Y-m-d']);

    $transformer = new DateTimeInterfaceTransformer();

    $class = new class () extends Data {
        public Carbon $carbon;
    };

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'carbon')),
            Carbon::createFromFormat('!Y-m-d', '1994-05-19'),
            TransformationContextFactory::create()->get($class)
        )
    )->toEqual('1994-05-19');
});
