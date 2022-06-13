it('ca
n cast date times', function () {
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
});

it('fails when it cannot cast a date into the correct format', function () {
    $caster = new DateTimeInterfaceCast('d-m-Y H:i:s');

    $class = new class () {
        public DateTime $carbon;
    };

    $this->expectException(Exception::class);

    $this->assertEquals(
        new DateTime('19-05-1994 00:00:00'),
        $caster->cast(DataProperty::create(new ReflectionProperty($class, 'carbon')), '19-05-1994', [])
    );
});

it('fails with other types', function () {
    $caster = new DateTimeInterfaceCast('d-m-Y');

    $class = new class () {
        public int $int;
    };

    $this->assertEquals(
        Uncastable::create(),
        $caster->cast(DataProperty::create(new ReflectionProperty($class, 'int')), '1994-05-16 12:20:00', [])
    );
});
