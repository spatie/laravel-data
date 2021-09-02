<?php

namespace Spatie\LaravelData\Tests\Attributes\Validation;

use Spatie\LaravelData\Attributes\Validation\AcceptedIf;
use Spatie\LaravelData\Tests\TestCase;

class AcceptedIfTest extends TestCase
{
    /** @test */
    public function it_can_get_the_rules_for_the_attribute()
    {
        $this->assertValidationAttributeRules([
            'accepted_if:value,string',
        ], new AcceptedIf('value', 'string'));

        $this->assertValidationAttributeRules([
            'accepted_if:value,true',
        ], new AcceptedIf('value', true));

        $this->assertValidationAttributeRules([
            'accepted_if:value,42',
        ], new AcceptedIf('value', 42));

        $this->assertValidationAttributeRules([
            'accepted_if:value,3.14',
        ], new AcceptedIf('value', 3.14));
    }
}
