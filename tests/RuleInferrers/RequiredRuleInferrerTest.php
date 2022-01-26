<?php

namespace Spatie\LaravelData\Tests\RuleInferrers;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use ReflectionClass;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\RuleInferrers\RequiredRuleInferrer;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;
use Spatie\LaravelData\Undefined;

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

        $rules = $this->inferrer->handle($dataProperty, []);

        $this->assertEqualsCanonicalizing(['required'], $rules);
    }

    /** @test */
    public function it_wont_add_a_required_rule_when_a_property_is_nullable()
    {
        $dataProperty = $this->getProperty(new class () extends Data {
            public ?string $string;
        });

        $rules = $this->inferrer->handle($dataProperty, []);

        $this->assertEqualsCanonicalizing([], $rules);
    }

    /** @test */
    public function it_wont_add_a_required_rule_when_a_property_already_contains_a_required_rule()
    {
        $dataProperty = $this->getProperty(new class () extends Data {
            public string $string;
        });

        $rules = $this->inferrer->handle($dataProperty, ['required_if:bla']);

        $this->assertEqualsCanonicalizing(['required_if:bla'], $rules);
    }

    /** @test */
    public function it_wont_add_a_required_rule_when_a_property_already_contains_a_required_object_rule()
    {
        $dataProperty = $this->getProperty(new class () extends Data {
            public string $string;
        });

        $rules = $this->inferrer->handle($dataProperty, [Rule::requiredIf(true)]);

        $this->assertEqualsCanonicalizing([Rule::requiredIf(true)], $rules);
    }

    /** @test */
    public function it_wont_add_a_required_rule_when_a_property_already_contains_a_boolean_rule()
    {
        $dataProperty = $this->getProperty(new class () extends Data {
            public string $string;
        });

        $rules = $this->inferrer->handle($dataProperty, ['boolean']);

        $this->assertEqualsCanonicalizing(['boolean'], $rules);
    }

    /** @test */
    public function it_wont_add_a_required_rule_when_a_property_already_contains_a_nullable_rule()
    {
        $dataProperty = $this->getProperty(new class () extends Data {
            public string $string;
        });

        $rules = $this->inferrer->handle($dataProperty, ['nullable']);

        $this->assertEqualsCanonicalizing(['nullable'], $rules);
    }

    /** @test */
    public function it_has_support_for_rules_that_cannot_be_converted_to_string()
    {
        $dataProperty = $this->getProperty(new class () extends Data {
            public string $string;
        });

        $rules = $this->inferrer->handle($dataProperty, [new Enum('SomeClass')]);

        $this->assertEqualsCanonicalizing(['required', new Enum('SomeClass')], $rules);
    }

    /** @test */
    public function it_wont_add_required_to_a_data_collection_since_it_is_already_present()
    {
        $dataProperty = $this->getProperty(new class () extends Data {
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection $collection;
        });

        $rules = $this->inferrer->handle($dataProperty, ['present', 'array']);

        $this->assertEqualsCanonicalizing(['present', 'array'], $rules);
    }

    public function it_wont_add_required_rules_to_undefinable_properties()
    {
        $dataProperty = $this->getProperty(new class () extends Data {
            public string|Undefined $string;
        });

        $rules = $this->inferrer->handle($dataProperty, []);

        $this->assertEqualsCanonicalizing([], $rules);
    }

    private function getProperty(object $class): DataProperty
    {
        $dataClass = DataClass::create(new ReflectionClass($class));

        return $dataClass->properties()[0];
    }
}
