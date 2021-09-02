<?php

namespace Spatie\LaravelData\Tests\Attributes\Validation;

use Spatie\LaravelData\Attributes\Validation\Dimensions;
use Spatie\LaravelData\Exceptions\CannotBuildValidationRule;
use Spatie\LaravelData\Tests\TestCase;

class DimensionsTest extends TestCase
{
    /** @test */
    public function it_can_get_the_correct_rules_for_the_attribute()
    {
        $this->assertValidationAttributeRules(
            ['dimensions:min_width=15,min_height=10,max_width=150,max_height=100,ratio=1'],
            new Dimensions(minWidth: 15, minHeight: 10, maxWidth: 150, maxHeight: 100, ratio: 1)
        );

        $this->assertValidationAttributeRules(
            ['dimensions:max_width=150,max_height=100'],
            new Dimensions(maxWidth: 150, maxHeight: 100)
        );

        $this->assertValidationAttributeRules(
            ['dimensions:ratio=1.5'],
            new Dimensions(ratio: 1.5)
        );

        $this->assertValidationAttributeRules(
            ['dimensions:ratio=3/4'],
            new Dimensions(ratio: '3/4')
        );
    }

    /** @test */
    public function it_crashes_without_parameters()
    {
        $this->expectException(CannotBuildValidationRule::class);

        (new Dimensions())->getRules();
    }
}
