<?php

namespace Spatie\LaravelData\Tests\Attributes\Validation;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\Validation\Before;
use Spatie\LaravelData\Tests\TestCase;
use Spatie\TestTime\TestTime;

class BeforeTest extends TestCase
{
    /** @test */
    public function it_can_get_the_correct_rules_for_the_attribute()
    {
        TestTime::freeze(Carbon::create(2020, 05, 16, 0, 0, 0));

        $this->assertValidationAttributeRules([
            'before:some_field',
        ], new Before('some_field'));

        $this->assertValidationAttributeRules([
            'before:2020-05-15T00:00:00+00:00',
        ], new Before(Carbon::yesterday()));
    }
}
