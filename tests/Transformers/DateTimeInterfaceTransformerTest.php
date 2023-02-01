<?php

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

it('can transform dates', function () {
    $transformer = new DateTimeInterfaceTransformer();

    $class = new class () {
        public Carbon $carbon;

        public CarbonImmutable $carbonImmutable;

        public DateTime $dateTime;

        public DateTimeImmutable $dateTimeImmutable;
    };

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'carbon')),
            new Carbon('19-05-1994 00:00:00')
        )
    )->toEqual('1994-05-19T00:00:00+00:00');

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'carbonImmutable')),
            new CarbonImmutable('19-05-1994 00:00:00')
        )
    )->toEqual('1994-05-19T00:00:00+00:00');

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'dateTime')),
            new DateTime('19-05-1994 00:00:00')
        )
    )->toEqual('1994-05-19T00:00:00+00:00');

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'dateTimeImmutable')),
            new DateTimeImmutable('19-05-1994 00:00:00')
        )
    )->toEqual('1994-05-19T00:00:00+00:00');
});

it('can transform dates with an alternative format', function () {
    $transformer = new DateTimeInterfaceTransformer(format: 'd-m-Y');

    $class = new class () {
        public Carbon $carbon;

        public CarbonImmutable $carbonImmutable;

        public DateTime $dateTime;

        public DateTimeImmutable $dateTimeImmutable;
    };

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'carbon')),
            new Carbon('19-05-1994 00:00:00'),
            []
        )
    )->toEqual('19-05-1994');

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'carbonImmutable')),
            new CarbonImmutable('19-05-1994 00:00:00'),
            []
        )
    )->toEqual('19-05-1994');

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'dateTime')),
            new DateTime('19-05-1994 00:00:00'),
            []
        )
    )->toEqual('19-05-1994');

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'dateTimeImmutable')),
            new DateTimeImmutable('19-05-1994 00:00:00'),
            []
        )
    )->toEqual('19-05-1994');
});

it('can change the timezone', function () {
    $transformer = new DateTimeInterfaceTransformer(setTimeZone: 'Europe/Brussels');

    $class = new class () {
        public Carbon $carbon;

        public CarbonImmutable $carbonImmutable;

        public DateTime $dateTime;

        public DateTimeImmutable $dateTimeImmutable;
    };

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'carbon')),
            new Carbon('19-05-1994 00:00:00')
        )
    )->toEqual('1994-05-19T02:00:00+02:00');

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'carbonImmutable')),
            new CarbonImmutable('19-05-1994 00:00:00')
        )
    )->toEqual('1994-05-19T02:00:00+02:00');

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'dateTime')),
            new DateTime('19-05-1994 00:00:00')
        )
    )->toEqual('1994-05-19T02:00:00+02:00');

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'dateTimeImmutable')),
            new DateTimeImmutable('19-05-1994 00:00:00')
        )
    )->toEqual('1994-05-19T02:00:00+02:00');
});

it('can transform dates with leading !', function () {
    $transformer = new DateTimeInterfaceTransformer();

    $class = new class () {
        public Carbon $carbon;
    };

    config(['data.date_format' => '!Y-m-d']);

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'carbon')),
            Carbon::createFromFormat('!Y-m-d', '1994-05-19')
        )
    )->toEqual('1994-05-19');
});
