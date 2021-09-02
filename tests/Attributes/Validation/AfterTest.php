<?php

namespace Spatie\LaravelData\Tests\Attributes\Validation;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\After;
use Spatie\LaravelData\Tests\TestCase;
use Spatie\TestTime\TestTime;

class AfterTest extends TestCase
{
    /** @test */
    public function it_can_get_the_correct_rules_for_the_attribute()
    {
        TestTime::freeze(Carbon::create(2020, 05, 16, 0, 0, 0));

        $this->assertValidationAttributeRules([
            'after:some_field',
        ], new After('some_field'));

        $this->assertValidationAttributeRules([
            'after:2020-05-15T00:00:00+00:00',
        ], new After(Carbon::yesterday()));
    }
}
