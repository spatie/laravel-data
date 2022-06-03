<?php

namespace Spatie\LaravelData\Tests\Support\Validation;

use Illuminate\Contracts\Validation\Rule as CustomRuleContract;
use Illuminate\Validation\Rules\Dimensions as BaseDimensions;
use Illuminate\Validation\Rules\Exists as BaseExists;
use Spatie\LaravelData\Attributes\Validation\Dimensions;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Support\Validation\RulesMapper;
use Spatie\LaravelData\Tests\TestCase;

class RulesMapperTest extends TestCase
{
    private RulesMapper $mapper;

    public function setUp() : void
    {
        parent::setUp();

        $this->mapper = resolve(RulesMapper::class);
    }

    /** @test */
    public function it_can_map_string_rules()
    {
        $this->assertEquals(
            [new Required()],
            $this->mapper->execute(['required'])
        );
    }

    /** @test */
    public function it_can_map_string_rules_with_arguments()
    {
        $this->assertEquals(
            [new Exists(rule: new BaseExists('users'))],
            $this->mapper->execute(['exists:users'])
        );
    }

    /** @test */
    public function it_can_map_string_rules_with_key_value_arguments()
    {
        $this->assertEquals(
            [new Dimensions(minWidth: 100, minHeight: 200)],
            $this->mapper->execute(['dimensions:min_width=100,min_height=200'])
        );
    }

    /** @test */
    public function it_can_map_multiple_rules()
    {
        $this->assertEquals(
            [new Required(), new Min(0)],
            $this->mapper->execute(['required', 'min:0'])
        );
    }

    /** @test */
    public function it_can_map_multiple_concatenated_rules()
    {
        $this->assertEquals(
            [new Required(), new Min(0)],
            $this->mapper->execute(['required|min:0'])
        );
    }

    /** @test */
    public function it_can_map_faulty_rules()
    {
        $this->assertEquals(
            [new Rule('min:')],
            $this->mapper->execute(['min:'])
        );
    }

    /** @test */
    public function it_can_map_laravel_rule_objects()
    {
        $this->assertEquals(
            [new Exists('users')],
            $this->mapper->execute([new BaseExists('users')])
        );
    }

    /** @test */
    public function it_can_map_custom_laravel_rule_objects()
    {
        $rule = new class implements CustomRuleContract {

            public function passes($attribute, $value)
            {

            }

            public function message()
            {

            }
        };

        $this->assertEquals(
            [new Rule($rule)],
            $this->mapper->execute([$rule])
        );
    }
}
