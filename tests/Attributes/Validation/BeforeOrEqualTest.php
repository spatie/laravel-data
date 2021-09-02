<?php

namespace Spatie\LaravelData\Tests\Attributes\Validation;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\Validation\BeforeOrEqual;
use Spatie\LaravelData\Tests\TestCase;
use Spatie\TestTime\TestTime;

class BeforeOrEqualTest extends TestCase
{
    /** @test */
    public function it_can_get_the_correct_rules_for_the_attribute()
    {
        TestTime::freeze(Carbon::create(2020, 05, 16, 0, 0, 0));

        $this->assertValidationAttributeRules([
            'before_or_equal:some_field',
        ], new BeforeOrEqual('some_field'));

        $this->assertValidationAttributeRules([
            'before_or_equal:2020-05-15T00:00:00+00:00',
        ], new BeforeOrEqual(Carbon::yesterday()));
    }
}
