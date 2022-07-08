<?php

namespace Spatie\LaravelData\Tests\Casts;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use Exception;
use ReflectionProperty;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\TestCase;

class DateTimeInterfaceCastTest extends TestCase
{
    /** @test */
    public function it_can_cast_date_times()
    {
        $caster = new DateTimeInterfaceCast('d-m-Y H:i:s');

        $class = new class () {
            public Carbon $carbon;

            public CarbonImmutable $carbonImmutable;

            public DateTime $dateTime;

            public DateTimeImmutable $dateTimeImmutable;
        };

        $this->assertEquals(
            new Carbon('19-05-1994 00:00:00'),
            $caster->cast(DataProperty::create(new ReflectionProperty($class, 'carbon')), '19-05-1994 00:00:00', [])
        );

        $this->assertEquals(
            new CarbonImmutable('19-05-1994 00:00:00'),
            $caster->cast(DataProperty::create(new ReflectionProperty($class, 'carbonImmutable')), '19-05-1994 00:00:00', [])
        );

        $this->assertEquals(
            new DateTime('19-05-1994 00:00:00'),
            $caster->cast(DataProperty::create(new ReflectionProperty($class, 'dateTime')), '19-05-1994 00:00:00', [])
        );

        $this->assertEquals(
            new DateTimeImmutable('19-05-1994 00:00:00'),
            $caster->cast(DataProperty::create(new ReflectionProperty($class, 'dateTimeImmutable')), '19-05-1994 00:00:00', [])
        );
    }

    /** @test */
    public function it_fails_when_it_cannot_cast_a_date_into_the_correct_format()
    {
        $caster = new DateTimeInterfaceCast('d-m-Y H:i:s');

        $class = new class () {
            public DateTime $carbon;
        };

        $this->expectException(Exception::class);

        $this->assertEquals(
            new DateTime('19-05-1994 00:00:00'),
            $caster->cast(DataProperty::create(new ReflectionProperty($class, 'carbon')), '19-05-1994', [])
        );
    }

    /** @test */
    public function it_fails_with_other_types()
    {
        $caster = new DateTimeInterfaceCast('d-m-Y');

        $class = new class () {
            public int $int;
        };

        $this->assertEquals(
            Uncastable::create(),
            $caster->cast(DataProperty::create(new ReflectionProperty($class, 'int')), '1994-05-16 12:20:00', [])
        );
    }
}
