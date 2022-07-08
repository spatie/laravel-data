<?php

namespace Spatie\LaravelData\Tests\Support\Validation;

use Illuminate\Validation\Rules\Enum as BaseEnum;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Prohibited;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Support\Validation\RulesCollection;
use Spatie\LaravelData\Tests\Fakes\FakeEnum;
use Spatie\LaravelData\Tests\TestCase;

class RulesCollectionTest extends TestCase
{
    /** @test */
    public function it_can_add_rules()
    {
        $collection = RulesCollection::create()
            ->add(new Required())
            ->add(new Prohibited(), new Min(0));

        $this->assertEquals(
            [new Required(), new Prohibited(), new Min(0)],
            $collection->all()
        );
    }

    /** @test */
    public function it_will_remove_the_previous_rule_if_a_new_version_is_added()
    {
        $collection = RulesCollection::create()
            ->add(new Min(10))
            ->add(new Min(314));

        $this->assertEquals([new Min(314)], $collection->all());
    }

    /** @test */
    public function it_can_remove_rules_by_type()
    {
        $collection = RulesCollection::create()
            ->add(new Min(10))
            ->removeType(new Min(314));

        $this->assertEquals([], $collection->all());
    }

    /** @test */
    public function it_can_remove_rules_by_class()
    {
        $collection = RulesCollection::create()
            ->add(new Min(10))
            ->removeType(Min::class);

        $this->assertEquals([], $collection->all());
    }

    /** @test */
    public function it_can_normalize_rules()
    {
        $collection = RulesCollection::create()
            ->add(new Min(10))
            ->add(new Required())
            ->add(new Enum(FakeEnum::class));

        $this->assertEquals([new Min(10), new Required(), new Enum(FakeEnum::class)], $collection->all());
        $this->assertEquals([new Min(10), new Required(), new BaseEnum(FakeEnum::class)], $collection->normalize());
    }
}
