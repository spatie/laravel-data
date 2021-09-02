<?php

namespace Spatie\LaravelData\Tests\Attributes\Validation;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\Validation\After;
use Spatie\LaravelData\Attributes\Validation\AfterOrEqual;
use Spatie\LaravelData\Tests\TestCase;
use Spatie\TestTime\TestTime;

class AfterOrEqualTest extends TestCase
{
    /** @test */
    public function it_can_get_the_correct_rules_for_the_attribute()
    {
        TestTime::freeze(Carbon::create(2020, 05, 16, 0, 0, 0));

        $this->assertValidationAttributeRules([
            'after_or_equal:some_field',
        ], new AfterOrEqual('some_field'));

        $this->assertValidationAttributeRules([
            'after_or_equal:2020-05-15T00:00:00+00:00',
        ], new AfterOrEqual(Carbon::yesterday()));
    }
}
