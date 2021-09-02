<?php

namespace Spatie\LaravelData\Tests\Attributes\Validation;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Tests\TestCase;

class RuleTest extends TestCase
{
    /** @test */
    public function it_can_create_a_rule_attribute()
    {
        $attribute = new Rule('test', ['a', 'b', 'c'], 'x|y');

        $this->assertEquals(['test', 'a', 'b', 'c', 'x', 'y'], $attribute->getRules());
    }
}
