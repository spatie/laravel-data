<?php

namespace Spatie\LaravelData\Tests\Resolvers;

use ReflectionProperty;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\RequiredWith;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Resolvers\DataPropertyValidationRulesResolver;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\NestedData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;

class DataPropertyValidationRulesResolverTest extends TestCase
{
    /** @test */
    public function it_will_add_a_required_or_nullable_rule_based_upon_the_property_nullability()
    {
        $rules = $this->resolveRules(new class () {
            public int $property;
        });

        $this->assertEqualsCanonicalizing([
            'property' => ['required', 'numeric'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            public ?int $property;
        });

        $this->assertEqualsCanonicalizing([
            'property' => ['nullable', 'numeric'],
        ], $rules);
    }

    /** @test */
    public function it_will_add_basic_rules_for_certain_types()
    {
        $rules = $this->resolveRules(new class () {
            public string $property;
        });

        $this->assertEqualsCanonicalizing([
            'property' => ['required', 'string'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            public int $property;
        });

        $this->assertEqualsCanonicalizing([
            'property' => ['required', 'numeric'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            public bool $property;
        });

        $this->assertEqualsCanonicalizing([
            'property' => ['boolean'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            public float $property;
        });

        $this->assertEqualsCanonicalizing([
            'property' => ['required', 'numeric'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            public array $property;
        });

        $this->assertEqualsCanonicalizing([
            'property' => ['required', 'array'],
        ], $rules);
    }

    /** @test */
    public function it_will_take_validation_attributes_into_account()
    {
        $rules = $this->resolveRules(new class () {
            #[Max(10)]
            public string $property;
        });

        $this->assertEqualsCanonicalizing([
            'property' => ['required', 'string', 'max:10'],
        ], $rules);
    }

    /** @test */
    public function it_will_take_rules_from_nested_data_objects()
    {
        $rules = $this->resolveRules(new class () {
            public SimpleData $property;
        });

        $this->assertEqualsCanonicalizing([
            'property' => ['required', 'array'],
            'property.string' => ['required', 'string'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            public ?SimpleData $property;
        });

        $this->assertEqualsCanonicalizing([
            'property' => ['nullable', 'array'],
            'property.string' => ['nullable', 'string'],
        ], $rules);
    }

    /** @test */
    public function it_will_take_rules_from_nested_data_collections()
    {
        $rules = $this->resolveRules(new class () {
            /** @var \Spatie\LaravelData\Tests\Fakes\SimpleData[] */
            public DataCollection $property;
        });

        $this->assertEqualsCanonicalizing([
            'property' => ['required', 'array'],
            'property.*.string' => ['required', 'string'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            /** @var \Spatie\LaravelData\Tests\Fakes\SimpleData[]|null */
            public ?DataCollection $property;
        });

        $this->assertEqualsCanonicalizing([
            'property' => ['nullable', 'array'],
            'property.*.string' => ['required', 'string'],
        ], $rules);
    }

    /** @test */
    public function it_can_nest_validation_rules_event_further()
    {
        $rules = $this->resolveRules(new class () {
            public NestedData $property;
        });

        $this->assertEqualsCanonicalizing([
            'property' => ['required', 'array'],
            'property.simple' => ['required', 'array'],
            'property.simple.string' => ['required', 'string'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            public ?SimpleData $property;
        });

        $this->assertEqualsCanonicalizing([
            'property' => ['nullable', 'array'],
            'property.string' => ['nullable', 'string'],
        ], $rules);
    }

    /** @test */
    public function it_will_never_add_extra_require_rules_when_not_needed()
    {
        $rules = $this->resolveRules(new class () {
            public ?string $property;
        });

        $this->assertEqualsCanonicalizing([
            'property' => ['string', 'nullable'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            public bool $property;
        });

        $this->assertEqualsCanonicalizing([
            'property' => ['boolean'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            #[RequiredWith('other')]
            public string $property;
        });

        $this->assertEqualsCanonicalizing([
            'property' => ['string', 'required_with:other'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            #[Rule('required_with:other')]
            public string $property;
        });

        $this->assertEqualsCanonicalizing([
            'property' => ['string', 'required_with:other'],
        ], $rules);
    }

    private function resolveRules(object $class): array
    {
        $reflectionProperty = new ReflectionProperty($class, 'property');

        $property = DataProperty::create($reflectionProperty);

        return app(DataPropertyValidationRulesResolver::class)->execute($property)->toArray();
    }
}
