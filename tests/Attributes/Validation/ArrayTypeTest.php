<?php

namespace Spatie\LaravelData\Tests\Attributes\Validation;

use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Tests\TestCase;

class ArrayTypeTest extends TestCase
{
    /** @test */
    public function it_can_get_the_correct_rules_for_the_attribute()
    {
        $this->assertValidationAttributeRules(
            ['array'],
            new ArrayType()
        );

        $this->assertValidationAttributeRules(
            ['array:a,b,c'],
            new ArrayType(['a', 'b', 'c'])
        );
    }
}
