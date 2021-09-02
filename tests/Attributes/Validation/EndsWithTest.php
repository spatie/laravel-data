<?php

namespace Spatie\LaravelData\Tests\Attributes\Validation;

use Spatie\LaravelData\Attributes\Validation\EndsWith;
use Spatie\LaravelData\Tests\TestCase;

class EndsWithTest extends TestCase
{
    /** @test */
    public function it_can_get_the_correct_rules_for_the_attribute()
    {
        $this->assertValidationAttributeRules(
            ['ends_with:x'],
            new EndsWith('x'),
        );

        $this->assertValidationAttributeRules(
            ['ends_with:x,y'],
            new EndsWith(['x', 'y']),
        );
    }
}
