<?php

namespace Spatie\LaravelData\Tests\Attributes\Validation;

use Spatie\LaravelData\Attributes\Validation\CurrentPassword;
use Spatie\LaravelData\Tests\TestCase;

class CurrentPasswordTest extends TestCase
{
    /** @test */
    public function it_can_get_the_correct_rules_for_the_attribute()
    {
        $this->assertValidationAttributeRules(
            ['current_password'],
            new CurrentPassword()
        );

        $this->assertValidationAttributeRules(
            ['current_password:api'],
            new CurrentPassword('api')
        );
    }
}
