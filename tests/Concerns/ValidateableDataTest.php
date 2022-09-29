<?php

namespace Spatie\LaravelData\Tests\Concerns;

use Spatie\LaravelData\Tests\Fakes\MultiData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithExplicitValidationRuleAttributeData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithOverwrittenRules;
use Spatie\LaravelData\Tests\TestCase;

class ValidateableDataTest extends TestCase
{
    /** @test */
    public function it_will_detect_validation_rules_even_when_not_explicitly_set(): void
    {
        $this->assertSame([
            'first' => ['string', 'required'],
            'second' => ['string', 'required'],
        ], MultiData::getValidationRules());
    }

    /** @test */
    public function it_will_detect_validation_rules_when_set_via_the_static_rules_method(): void
    {
        $this->assertSame(SimpleDataWithOverwrittenRules::rules(), SimpleDataWithOverwrittenRules::getValidationRules());
    }

    /** @test */
    public function it_will_detect_validation_rules_set_via_the_validation_attributes(): void
    {
        $this->assertSame([
            'email' => ['string', 'email:rfc', 'required'],
        ], SimpleDataWithExplicitValidationRuleAttributeData::getValidationRules());
    }

    /** @test */
    public function it_can_selectively_choose_validation_rules(): void
    {
        $this->assertSame([
            'first' => ['string', 'required'],
        ], MultiData::getValidationRules('first'));
    }
}
