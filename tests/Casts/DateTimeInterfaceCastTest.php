<?php

namespace Spatie\LaravelData\Tests\Casts;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\Traits\Creator;
use DateTime;
use DateTimeImmutable;
use ReflectionProperty;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Tests\TestCase;

class DateTimeInterfaceCastTest extends TestCase
{
    /** @test */
    public function it_can_cast_date_times()
    {
        $caster = new DateTimeInterfaceCast();

        $class = new class {
            public Carbon $carbon;

            public CarbonImmutable $carbonImmutable;

            public DateTime $dateTime;

            public DateTimeImmutable $dateTimeImmutable;
        };

        $this->assertEquals(
            new Carbon('16 may 1994 12:20:00'),
            $caster->cast((new ReflectionProperty($class, 'carbon'))->getType(), '1994-05-16 12:20:00')
        );

        $this->assertEquals(
            new CarbonImmutable('16 may 1994 12:20:00'),
            $caster->cast((new ReflectionProperty($class, 'carbonImmutable'))->getType(), '1994-05-16 12:20:00')
        );

        $this->assertEquals(
            new DateTime('16 may 1994 12:20:00'),
            $caster->cast((new ReflectionProperty($class, 'dateTime'))->getType(), '1994-05-16 12:20:00')
        );

        $this->assertEquals(
            new DateTimeImmutable('16 may 1994 12:20:00'),
            $caster->cast((new ReflectionProperty($class, 'dateTimeImmutable'))->getType(), '1994-05-16 12:20:00')
        );
    }

    /** @test */
    public function it_fails_with_other_types()
    {
        $caster = new DateTimeInterfaceCast();

        $class = new class {
            public int $int;
        };

        $this->assertEquals(
            Uncastable::create(),
            $caster->cast((new ReflectionProperty($class, 'int'))->getType(), '1994-05-16 12:20:00')
        );
    }
}
