<?php

namespace Spatie\LaravelData\Tests\Transformers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use ReflectionProperty;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\TestCase;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

class DateTimeInterfaceTransformerTest extends TestCase
{
    /** @test */
    public function it_can_transform_dates()
    {
        $transformer = new DateTimeInterfaceTransformer();

        $class = new class () {
            public Carbon $carbon;

            public CarbonImmutable $carbonImmutable;

            public DateTime $dateTime;

            public DateTimeImmutable $dateTimeImmutable;
        };

        $this->assertEquals(
            '1994-05-19T00:00:00+00:00',
            $transformer->transform(DataProperty::create(new ReflectionProperty($class, 'carbon')), new Carbon('19-05-1994 00:00:00'))
        );

        $this->assertEquals(
            '1994-05-19T00:00:00+00:00',
            $transformer->transform(DataProperty::create(new ReflectionProperty($class, 'carbonImmutable')), new CarbonImmutable('19-05-1994 00:00:00'))
        );

        $this->assertEquals(
            '1994-05-19T00:00:00+00:00',
            $transformer->transform(DataProperty::create(new ReflectionProperty($class, 'dateTime')), new DateTime('19-05-1994 00:00:00'))
        );

        $this->assertEquals(
            '1994-05-19T00:00:00+00:00',
            $transformer->transform(DataProperty::create(new ReflectionProperty($class, 'dateTimeImmutable')), new DateTimeImmutable('19-05-1994 00:00:00'))
        );
    }

    /** @test */
    public function it_can_transform_dates_with_an_alternative_format()
    {
        $transformer = new DateTimeInterfaceTransformer(format: 'd-m-Y');

        $class = new class () {
            public Carbon $carbon;

            public CarbonImmutable $carbonImmutable;

            public DateTime $dateTime;

            public DateTimeImmutable $dateTimeImmutable;
        };

        $this->assertEquals(
            '19-05-1994',
            $transformer->transform(DataProperty::create(new ReflectionProperty($class, 'carbon')), new Carbon('19-05-1994 00:00:00'), [])
        );

        $this->assertEquals(
            '19-05-1994',
            $transformer->transform(DataProperty::create(new ReflectionProperty($class, 'carbonImmutable')), new CarbonImmutable('19-05-1994 00:00:00'), [])
        );

        $this->assertEquals(
            '19-05-1994',
            $transformer->transform(DataProperty::create(new ReflectionProperty($class, 'dateTime')), new DateTime('19-05-1994 00:00:00'), [])
        );

        $this->assertEquals(
            '19-05-1994',
            $transformer->transform(DataProperty::create(new ReflectionProperty($class, 'dateTimeImmutable')), new DateTimeImmutable('19-05-1994 00:00:00'), [])
        );
    }

    /** @test */
    public function it_can_change_the_timezone()
    {
        $transformer = new DateTimeInterfaceTransformer(setTimeZone: 'Europe/Brussels');

        $class = new class () {
            public Carbon $carbon;

            public CarbonImmutable $carbonImmutable;

            public DateTime $dateTime;

            public DateTimeImmutable $dateTimeImmutable;
        };

        $this->assertEquals(
            '1994-05-19T02:00:00+02:00',
            $transformer->transform(DataProperty::create(new ReflectionProperty($class, 'carbon')), new Carbon('19-05-1994 00:00:00'))
        );

        $this->assertEquals(
            '1994-05-19T02:00:00+02:00',
            $transformer->transform(DataProperty::create(new ReflectionProperty($class, 'carbonImmutable')), new CarbonImmutable('19-05-1994 00:00:00'))
        );

        $this->assertEquals(
            '1994-05-19T02:00:00+02:00',
            $transformer->transform(DataProperty::create(new ReflectionProperty($class, 'dateTime')), new DateTime('19-05-1994 00:00:00'))
        );

        $this->assertEquals(
            '1994-05-19T02:00:00+02:00',
            $transformer->transform(DataProperty::create(new ReflectionProperty($class, 'dateTimeImmutable')), new DateTimeImmutable('19-05-1994 00:00:00'))
        );
    }
}
