<?php

namespace Spatie\LaravelData\Tests\RuleInferrers;

use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\RequiredIf as BaseRequiredIf;
use ReflectionClass;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Attributes\Validation\RequiredIf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\RuleInferrers\RequiredRuleInferrer;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\Rules\FoundationEnum;
use Spatie\LaravelData\Support\Validation\Rules\FoundationRequiredIf;
use Spatie\LaravelData\Support\Validation\RulesCollection;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;

class RequiredRuleInferrerTest extends TestCase
{
    private RequiredRuleInferrer $inferrer;

    public function setUp(): void
    {
        parent::setUp();

        $this->inferrer = new RequiredRuleInferrer();
    }

    /** @test */
    public function it_wont_add_a_required_rule_when_a_property_is_non_nullable()
    {
        $dataProperty = $this->getProperty(new class () extends Data {
            public string $string;
        });

        $rules = $this->inferrer->handle($dataProperty, new RulesCollection());

        $this->assertEqualsCanonicalizing(['required'], $rules->all());
    }

    /** @test */
    public function it_wont_add_a_required_rule_when_a_property_is_nullable()
    {
        $dataProperty = $this->getProperty(new class () extends Data {
            public ?string $string;
        });

        $rules = $this->inferrer->handle($dataProperty, new RulesCollection());

        $this->assertEqualsCanonicalizing([], $rules->all());
    }

    /** @test */
    public function it_wont_add_a_required_rule_when_a_property_already_contains_a_required_rule()
    {
        $dataProperty = $this->getProperty(new class () extends Data {
            public string $string;
        });

        $rules = $this->inferrer->handle($dataProperty, RulesCollection::create()->add(new RequiredIf('bla')));

        $this->assertEqualsCanonicalizing(['required_if:bla'], $rules->all());
    }

    /** @test */
    public function it_wont_add_a_required_rule_when_a_property_already_contains_a_required_object_rule()
    {
        $dataProperty = $this->getProperty(new class () extends Data {
            public string $string;
        });

        $rules = $this->inferrer->handle(
            $dataProperty,
            RulesCollection::create()->add(FoundationRequiredIf::create())
        );

        $this->assertEqualsCanonicalizing([new BaseRequiredIf(true)], $rules->normalize());
    }

    /** @test */
    public function it_wont_add_a_required_rule_when_a_property_already_contains_a_boolean_rule()
    {
        $dataProperty = $this->getProperty(new class () extends Data {
            public string $string;
        });

        $rules = $this->inferrer->handle(
            $dataProperty,
            RulesCollection::create()->add(BooleanType::create())
        );

        $this->assertEqualsCanonicalizing([new BooleanType()], $rules->normalize());
    }

    /** @test */
    public function it_wont_add_a_required_rule_when_a_property_already_contains_a_nullable_rule()
    {
        $dataProperty = $this->getProperty(new class () extends Data {
            public string $string;
        });

        $rules = $this->inferrer->handle(
            $dataProperty,
            RulesCollection::create()->add(Nullable::create())
        );

        $this->assertEqualsCanonicalizing([new Nullable()], $rules->normalize());
    }

    /** @test */
    public function it_has_support_for_rules_that_cannot_be_converted_to_string()
    {
        $dataProperty = $this->getProperty(new class () extends Data {
            public string $string;
        });

        $rules = $this->inferrer->handle(
            $dataProperty,
            RulesCollection::create()->add(new FoundationEnum(new Enum('SomeClass')))
        );

        $this->assertEqualsCanonicalizing(['required', new Enum('SomeClass')], $rules->normalize());
    }

    /** @test */
    public function it_wont_add_required_to_a_data_collection_since_it_is_already_present()
    {
        $dataProperty = $this->getProperty(new class () extends Data {
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection $collection;
        });

        $rules = $this->inferrer->handle(
            $dataProperty,
            RulesCollection::create()->add(new Present(), new ArrayType())
        );

        $this->assertEqualsCanonicalizing(['present', 'array'], $rules->normalize());
    }

    public function it_wont_add_required_rules_to_undefinable_properties()
    {
        $dataProperty = $this->getProperty(new class () extends Data {
            public string|Optional $string;
        });

        $rules = $this->inferrer->handle($dataProperty, []);

        $this->assertEqualsCanonicalizing([], $rules);
    }

    private function getProperty(object $class): DataProperty
    {
        $dataClass = DataClass::create(new ReflectionClass($class));

        return $dataClass->properties->first();
    }
}
