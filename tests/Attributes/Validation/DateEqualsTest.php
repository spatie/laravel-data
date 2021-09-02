<?php

namespace Spatie\LaravelData\Tests\Attributes\Validation;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\Validation\DateEquals;
use Spatie\LaravelData\Tests\TestCase;
use Spatie\TestTime\TestTime;

class DateEqualsTest extends TestCase
{
    /** @test */
    public function it_can_get_the_correct_rules_for_the_attribute()
    {
        TestTime::freeze(Carbon::create(2020, 05, 16, 0, 0, 0));

        $this->assertValidationAttributeRules([
            'date_equals:tomorrow',
        ], new DateEquals('tomorrow'));

        $this->assertValidationAttributeRules([
            'date_equals:2020-05-15T00:00:00+00:00',
        ], new DateEquals(Carbon::yesterday()));
    }
}
