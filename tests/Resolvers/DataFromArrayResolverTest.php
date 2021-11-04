<?php

namespace Spatie\LaravelData\Tests\Resolvers;

use Carbon\CarbonImmutable;
use DateTime;
use Spatie\LaravelData\Resolvers\DataFromArrayResolver;
use Spatie\LaravelData\Tests\Fakes\ComplicatedData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;

class DataFromArrayResolverTest extends TestCase
{
    private DataFromArrayResolver $action;

    public function setUp(): void
    {
        parent::setUp();

        $this->action = app(DataFromArrayResolver::class);
    }

    /** @test */
    public function it_maps_default_types()
    {
        /** @var \Spatie\LaravelData\Tests\Fakes\ComplicatedData $data */
        $data = $this->action->execute(
            ComplicatedData::class,
            [
                'withoutType' => 42,
                'int' => 42,
                'bool' => true,
                'float' => 3.14,
                'string' => 'Hello world',
                'array' => [1, 1, 2, 3, 5, 8],
                'nullable' => null,
                'mixed' => 42,
                'explicitCast' => '16-06-1994',
                'defaultCast' => '1994-05-16T12:00:00+01:00',
                'nestedData' => [
                    'string' => 'hello',
                ],
                'nestedCollection' => [
                    ['string' => 'never'],
                    ['string' => 'gonna'],
                    ['string' => 'give'],
                    ['string' => 'you'],
                    ['string' => 'up'],
                ],
            ]
        );

        $this->assertInstanceOf(ComplicatedData::class, $data);
        $this->assertEquals(42, $data->withoutType);
        $this->assertEquals(42, $data->int);
        $this->assertTrue($data->bool);
        $this->assertEquals(3.14, $data->float);
        $this->assertEquals('Hello world', $data->string);
        $this->assertEquals([1, 1, 2, 3, 5, 8], $data->array);
        $this->assertNull($data->nullable);
        $this->assertEquals(42, $data->mixed);
        $this->assertEquals(DateTime::createFromFormat(DATE_ATOM, '1994-05-16T12:00:00+01:00'), $data->defaultCast);
        $this->assertEquals(CarbonImmutable::createFromFormat('d-m-Y', '16-06-1994'), $data->explicitCast);
        $this->assertEquals(SimpleData::from('hello'), $data->nestedData);
        $this->assertEquals(SimpleData::collection([
            SimpleData::from('never'),
            SimpleData::from('gonna'),
            SimpleData::from('give'),
            SimpleData::from('you'),
            SimpleData::from('up'),
        ]), $data->nestedCollection);
    }

    /** @test */
    public function it_wont_cast_a_property_that_is_already_in_the_correct_type()
    {
        /** @var \Spatie\LaravelData\Tests\Fakes\ComplicatedData $data */
        $data = $this->action->execute(
            ComplicatedData::class,
            [
                'withoutType' => 42,
                'int' => 42,
                'bool' => true,
                'float' => 3.14,
                'string' => 'Hello world',
                'array' => [1, 1, 2, 3, 5, 8],
                'nullable' => null,
                'mixed' => 42,
                'explicitCast' => DateTime::createFromFormat('d-m-Y', '16-06-1994'),
                'defaultCast' => DateTime::createFromFormat(DATE_ATOM, '1994-05-16T12:00:00+02:00'),
                'nestedData' => SimpleData::from('hello'),
                'nestedCollection' => SimpleData::collection([
                    'never', 'gonna', 'give', 'you', 'up',
                ]),
            ]
        );

        $this->assertInstanceOf(ComplicatedData::class, $data);
        $this->assertEquals(42, $data->withoutType);
        $this->assertEquals(42, $data->int);
        $this->assertTrue($data->bool);
        $this->assertEquals(3.14, $data->float);
        $this->assertEquals('Hello world', $data->string);
        $this->assertEquals([1, 1, 2, 3, 5, 8], $data->array);
        $this->assertNull($data->nullable);
        $this->assertEquals(42, $data->mixed);
        $this->assertEquals(DateTime::createFromFormat(DATE_ATOM, '1994-05-16T12:00:00+02:00'), $data->defaultCast);
        $this->assertEquals(DateTime::createFromFormat('d-m-Y', '16-06-1994'), $data->explicitCast);
        $this->assertEquals(SimpleData::from('hello'), $data->nestedData);
        $this->assertEquals(SimpleData::collection([
            SimpleData::from('never'),
            SimpleData::from('gonna'),
            SimpleData::from('give'),
            SimpleData::from('you'),
            SimpleData::from('up'),
        ]), $data->nestedCollection);
    }
}
