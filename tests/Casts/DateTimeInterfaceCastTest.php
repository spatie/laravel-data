<?php

namespace Spatie\LaravelData\Tests\Casts;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonTimeZone;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use ReflectionProperty;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\TestCase;

it('can cast date times', function () {
    $caster = new DateTimeInterfaceCast('d-m-Y H:i:s');

    $class = new class()
    {
        public Carbon $carbon;

        public CarbonImmutable $carbonImmutable;

        public DateTime $dateTime;

        public DateTimeImmutable $dateTimeImmutable;
    };

    expect(
        $caster->cast(
            DataProperty::create(new ReflectionProperty($class, 'carbon')),
            '19-05-1994 00:00:00',
            []
        )
    )->toEqual(new Carbon('19-05-1994 00:00:00'));

    expect(
        $caster->cast(
            DataProperty::create(new ReflectionProperty($class, 'carbonImmutable')),
            '19-05-1994 00:00:00',
            []
        )
    )->toEqual(new CarbonImmutable('19-05-1994 00:00:00'));

    expect(
        $caster->cast(
            DataProperty::create(new ReflectionProperty($class, 'dateTime')),
            '19-05-1994 00:00:00',
            []
        )
    )->toEqual(new DateTime('19-05-1994 00:00:00'));

    expect(
        $caster->cast(
            DataProperty::create(new ReflectionProperty($class, 'dateTimeImmutable')),
            '19-05-1994 00:00:00',
            []
        )
    )->toEqual(new DateTimeImmutable('19-05-1994 00:00:00'));
});

it('fails when it cannot cast a date into the correct format', function () {
    $caster = new DateTimeInterfaceCast('d-m-Y H:i:s');

    $class = new class()
    {
        public DateTime $carbon;
    };

    expect(
        $caster->cast(
            DataProperty::create(new ReflectionProperty($class, 'carbon')),
            '19-05-1994',
            []
        )
    )->toEqual(new DateTime('19-05-1994 00:00:00'));
})->throws(Exception::class);

it('fails with other types', function () {
    $caster = new DateTimeInterfaceCast('d-m-Y');

    $class = new class()
    {
        public int $int;
    };

    expect(
        $caster->cast(
            DataProperty::create(new ReflectionProperty($class, 'int')),
            '1994-05-16 12:20:00',
            []
        )
    )->toEqual(Uncastable::create());
});

it('can set an alternative timezone', function () {
    $caster = new DateTimeInterfaceCast('d-m-Y H:i:s', setTimeZone: 'Europe/Brussels');

    $class = new class()
    {
        public Carbon $carbon;

        public CarbonImmutable $carbonImmutable;

        public DateTime $dateTime;

        public DateTimeImmutable $dateTimeImmutable;
    };

    expect(
        $caster->cast(
            DataProperty::create(new ReflectionProperty($class, 'carbon')),
            '19-05-1994 00:00:00',
            []
        )->getTimezone()
    )->toEqual(CarbonTimeZone::create('Europe/Brussels'));

    expect(
        $caster->cast(
            DataProperty::create(new ReflectionProperty($class, 'carbonImmutable')),
            '19-05-1994 00:00:00',
            []
        )->getTimezone()
    )->toEqual(CarbonTimeZone::create('Europe/Brussels'));

    expect(
        $caster->cast(
            DataProperty::create(new ReflectionProperty($class, 'dateTime')),
            '19-05-1994 00:00:00',
            []
        )->getTimezone()
    )->toEqual(new DateTimeZone('Europe/Brussels'));

    expect(
        $caster->cast(
            DataProperty::create(new ReflectionProperty($class, 'dateTimeImmutable')),
            '19-05-1994 00:00:00',
            []
        )->getTimezone()
    )->toEqual(new DateTimeZone('Europe/Brussels'));
});
