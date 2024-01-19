<?php

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonTimeZone;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory;

it('can cast date times', function () {
    $caster = new DateTimeInterfaceCast('d-m-Y H:i:s');

    $class = new class () {
        public Carbon $carbon;

        public CarbonImmutable $carbonImmutable;

        public DateTime $dateTime;

        public DateTimeImmutable $dateTimeImmutable;
    };


    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'carbon'),
            '19-05-1994 00:00:00',
            collect(),
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toEqual(new Carbon('19-05-1994 00:00:00'));

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'carbonImmutable'),
            '19-05-1994 00:00:00',
            collect(),
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toEqual(new CarbonImmutable('19-05-1994 00:00:00'));

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'dateTime'),
            '19-05-1994 00:00:00',
            collect(),
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toEqual(new DateTime('19-05-1994 00:00:00'));

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'dateTimeImmutable'),
            '19-05-1994 00:00:00',
            collect(),
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toEqual(new DateTimeImmutable('19-05-1994 00:00:00'));
});

it('fails when it cannot cast a date into the correct format', function () {
    $caster = new DateTimeInterfaceCast('d-m-Y H:i:s');

    $class = new class () {
        public DateTime $carbon;
    };

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'carbon'),
            '19-05-1994',
            collect(),
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toEqual(new DateTime('19-05-1994 00:00:00'));
})->throws(Exception::class);

it('fails with other types', function () {
    $caster = new DateTimeInterfaceCast('d-m-Y');

    $class = new class () {
        public int $int;
    };

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'int'),
            '1994-05-16 12:20:00',
            collect(),
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toEqual(Uncastable::create());
});

it('can set an alternative timezone', function () {
    $caster = new DateTimeInterfaceCast('d-m-Y H:i:s', setTimeZone: 'Europe/Brussels');

    $class = new class () {
        public Carbon $carbon;

        public CarbonImmutable $carbonImmutable;

        public DateTime $dateTime;

        public DateTimeImmutable $dateTimeImmutable;
    };

    expect($caster->cast(
        FakeDataStructureFactory::property($class, 'carbon'),
        '19-05-1994 00:00:00',
        collect(),
        CreationContextFactory::createFromConfig($class::class)->get()
    ))
        ->format('Y-m-d H:i:s')->toEqual('1994-05-19 02:00:00')
        ->getTimezone()->toEqual(CarbonTimeZone::create('Europe/Brussels'));

    expect($caster->cast(
        FakeDataStructureFactory::property($class, 'carbonImmutable'),
        '19-05-1994 00:00:00',
        collect(),
        CreationContextFactory::createFromConfig($class::class)->get()
    ))
        ->format('Y-m-d H:i:s')->toEqual('1994-05-19 02:00:00')
        ->getTimezone()->toEqual(CarbonTimeZone::create('Europe/Brussels'));

    expect($caster->cast(
        FakeDataStructureFactory::property($class, 'dateTime'),
        '19-05-1994 00:00:00',
        collect(),
        CreationContextFactory::createFromConfig($class::class)->get()
    ))
        ->format('Y-m-d H:i:s')->toEqual('1994-05-19 02:00:00')
        ->getTimezone()->toEqual(new DateTimeZone('Europe/Brussels'));

    expect($caster->cast(
        FakeDataStructureFactory::property($class, 'dateTimeImmutable'),
        '19-05-1994 00:00:00',
        collect(),
        CreationContextFactory::createFromConfig($class::class)->get()
    ))
        ->format('Y-m-d H:i:s')->toEqual('1994-05-19 02:00:00')
        ->getTimezone()->toEqual(new DateTimeZone('Europe/Brussels'));
});

it('can cast date times with a timezone', function () {
    $caster = new DateTimeInterfaceCast('d-m-Y H:i:s', timeZone: 'Europe/Brussels');

    $class = new class () {
        public Carbon $carbon;

        public CarbonImmutable $carbonImmutable;

        public DateTime $dateTime;

        public DateTimeImmutable $dateTimeImmutable;
    };

    expect($caster->cast(
        FakeDataStructureFactory::property($class, 'carbon'),
        '19-05-1994 00:00:00',
        collect(),
        CreationContextFactory::createFromConfig($class::class)->get()
    ))
        ->format('Y-m-d H:i:s')->toEqual('1994-05-19 00:00:00')
        ->getTimezone()->toEqual(CarbonTimeZone::create('Europe/Brussels'));

    expect($caster->cast(
        FakeDataStructureFactory::property($class, 'carbonImmutable'),
        '19-05-1994 00:00:00',
        collect(),
        CreationContextFactory::createFromConfig($class::class)->get()
    ))
        ->format('Y-m-d H:i:s')->toEqual('1994-05-19 00:00:00')
        ->getTimezone()->toEqual(CarbonTimeZone::create('Europe/Brussels'));

    expect($caster->cast(
        FakeDataStructureFactory::property($class, 'dateTime'),
        '19-05-1994 00:00:00',
        collect(),
        CreationContextFactory::createFromConfig($class::class)->get()
    ))
        ->format('Y-m-d H:i:s')->toEqual('1994-05-19 00:00:00')
        ->getTimezone()->toEqual(new DateTimeZone('Europe/Brussels'));

    expect($caster->cast(
        FakeDataStructureFactory::property($class, 'dateTimeImmutable'),
        '19-05-1994 00:00:00',
        collect(),
        CreationContextFactory::createFromConfig($class::class)->get()
    ))
        ->format('Y-m-d H:i:s')->toEqual('1994-05-19 00:00:00')
        ->getTimezone()->toEqual(new DateTimeZone('Europe/Brussels'));
});

it('can define multiple date formats to be used', function () {
    $data = new class () extends Data {
        public function __construct(
            #[WithCast(DateTimeInterfaceCast::class, ['Y-m-d\TH:i:sP', 'Y-m-d H:i:s'])]
            public ?DateTime $date = null
        ) {
        }
    };

    expect($data::from(['date' => '2022-05-16T14:37:56+00:00']))->toArray()
        ->toMatchArray(['date' => '2022-05-16T14:37:56+00:00'])
        ->and($data::from(['date' => '2022-05-16 17:00:00']))->toArray()
        ->toMatchArray(['date' => '2022-05-16T17:00:00+00:00']);
});
