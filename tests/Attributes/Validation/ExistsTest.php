<?php

namespace Spatie\LaravelData\Tests\Attributes\Validation;

use Illuminate\Validation\Rules\Exists as BaseExists;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Tests\TestCase;

class ExistsTest extends TestCase
{
    /** @test */
    public function it_can_get_the_correct_rules_for_the_attribute()
    {
        $this->assertValidationAttributeRules(
            [new BaseExists('posts')],
            new Exists('posts')
        );

        $this->assertValidationAttributeRules(
            [new BaseExists('posts', 'uuid')],
            new Exists('posts', 'uuid')
        );
    }
}
