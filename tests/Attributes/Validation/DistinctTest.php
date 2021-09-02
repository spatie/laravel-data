<?php

namespace Spatie\LaravelData\Tests\Attributes\Validation;

use Spatie\LaravelData\Attributes\Validation\Distinct;
use Spatie\LaravelData\Exceptions\CannotBuildValidationRule;
use Spatie\LaravelData\Tests\TestCase;

class DistinctTest extends TestCase
{
    /** @test */
    public function it_can_get_the_correct_rules_for_the_attribute()
    {
        $this->assertValidationAttributeRules(
            ['distinct'],
            new Distinct()
        );

        $this->assertValidationAttributeRules(
            ['distinct:strict'],
            new Distinct(Distinct::Strict)
        );

        $this->assertValidationAttributeRules(
            ['distinct:ignore_case'],
            new Distinct(Distinct::IgnoreCase)
        );
    }

    /** @test */
    public function it_fails_with_other_modes()
    {
        $this->expectException(CannotBuildValidationRule::class);

        (new Distinct('fake'))->getRules();
    }
}
