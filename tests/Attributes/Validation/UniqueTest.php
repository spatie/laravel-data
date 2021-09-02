<?php

namespace Spatie\LaravelData\Tests\Attributes\Validation;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rules\Unique as BaseUnique;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Tests\TestCase;

class UniqueTest extends TestCase
{

    /** @test */
    public function it_can_get_the_correct_rules_for_the_attribute()
    {
        $this->assertValidationAttributeRules(
            [new BaseUnique('users')],
            new Unique('users')
        );

        $this->assertValidationAttributeRules(
            [new BaseUnique('users', 'email')],
            new Unique('users', 'email')
        );

        $this->assertValidationAttributeRules(
            [new BaseUnique('tenant.users', 'email')],
            new Unique('users', 'email', connection: 'tenant')
        );

        $this->assertValidationAttributeRules(
            [(new BaseUnique('users', 'email'))->withoutTrashed()],
            new Unique('users', 'email', withoutTrashed: true)
        );

        $this->assertValidationAttributeRules(
            [(new BaseUnique('users', 'email'))->withoutTrashed('deleted_when')],
            new Unique('users', 'email', withoutTrashed: true, deletedAtColumn: 'deleted_when')
        );

        $this->assertValidationAttributeRules(
            [(new BaseUnique('users', 'email'))->ignore(5)],
            new Unique('users', 'email', ignore: 5)
        );

        $this->assertValidationAttributeRules(
            [(new BaseUnique('users', 'email'))->ignore(5, 'uuid')],
            new Unique('users', 'email', ignore: 5, ignoreColumn: 'uuid')
        );

        $closure = fn (Builder $builder) => $builder;

        $this->assertValidationAttributeRules(
            [(new BaseUnique('users', 'email'))->where($closure)],
            new Unique('users', 'email', where: $closure)
        );
    }
}
