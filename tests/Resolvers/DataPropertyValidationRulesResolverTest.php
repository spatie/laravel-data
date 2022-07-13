<?php

namespace Spatie\LaravelData\Tests\Resolvers;

use Illuminate\Validation\Rules\Enum as EnumRule;
use ReflectionProperty;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\RequiredWith;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Resolvers\DataPropertyValidationRulesResolver;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\DataWithMapper;
use Spatie\LaravelData\Tests\Fakes\FakeEnum;
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

        $this->assertEquals([
            'property' => ['numeric', 'required'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            public ?int $property;
        });

        $this->assertEquals([
            'property' => ['numeric', 'nullable'],
        ], $rules);
    }

    /** @test */
    public function it_will_add_basic_rules_for_certain_types()
    {
        $rules = $this->resolveRules(new class () {
            public string $property;
        });

        $this->assertEquals([
            'property' => ['string', 'required'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            public int $property;
        });

        $this->assertEquals([
            'property' => ['numeric', 'required'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            public bool $property;
        });

        $this->assertEquals([
            'property' => ['boolean'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            public float $property;
        });

        $this->assertEquals([
            'property' => ['numeric', 'required'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            public array $property;
        });

        $this->assertEquals([
            'property' => ['array', 'required'],
        ], $rules);
    }

    /** @test */
    public function it_will_add_rules_for_enums()
    {
        $this->onlyPHP81();

        $rules = $this->resolveRules(new class () {
            public FakeEnum $property;
        });

        $this->assertEquals([
            'property' => [new EnumRule(FakeEnum::class), 'required'],
        ], $rules);
    }

    /** @test */
    public function it_will_take_validation_attributes_into_account()
    {
        $rules = $this->resolveRules(new class () {
            #[Max(10)]
            public string $property;
        });

        $this->assertEquals([
            'property' => ['string', 'max:10', 'required'],
        ], $rules);
    }

    /** @test */
    public function it_will_take_rules_from_nested_data_objects()
    {
        $rules = $this->resolveRules(new class () {
            public SimpleData $property;
        });

        $this->assertEquals([
            'property' => ['required', 'array'],
            'property.string' => ['string', 'required'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            public ?SimpleData $property;
        });

        $this->assertEquals([
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

        $this->assertEquals([
            'property' => ['present', 'array'],
            'property.*.string' => ['string', 'required'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            /** @var \Spatie\LaravelData\Tests\Fakes\SimpleData[]|null */
            public ?DataCollection $property;
        });

        $this->assertEquals([
            'property' => ['nullable', 'array'],
            'property.*.string' => ['string', 'required'],
        ], $rules);
    }

    /** @test */
    public function it_can_nest_validation_rules_event_further()
    {
        $rules = $this->resolveRules(new class () {
            public NestedData $property;
        });

        $this->assertEquals([
            'property' => ['required', 'array'],
            'property.simple' => ['required', 'array'],
            'property.simple.string' => ['string', 'required'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            public ?SimpleData $property;
        });

        $this->assertEquals([
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

        $this->assertEquals([
            'property' => ['string', new Nullable()],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            public bool $property;
        });

        $this->assertEquals([
            'property' => ['boolean'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            #[RequiredWith('other')]
            public string $property;
        });

        $this->assertEquals([
            'property' => ['string', 'required_with:other'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            #[Rule('required_with:other')]
            public string $property;
        });

        $this->assertEquals([
            'property' => ['string', 'required_with:other'],
        ], $rules);
    }

    /** @test */
    public function it_will_work_with_non_string_rules()
    {
        $rules = $this->resolveRules(new class () {
            #[Enum(FakeEnum::class)]
            public string $property;
        });

        $this->assertEquals([
            'property' => ['string', new EnumRule(FakeEnum::class), 'required'],
        ], $rules);
    }

    /** @test */
    public function it_will_take_mapped_properties_into_account()
    {
        $rules = $this->resolveRules(new class () {
            #[MapName('other')]
            public int $property;
        });

        $this->assertEquals([
            'other' => ['numeric', 'required'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            #[MapName('other')]
            public SimpleData $property;
        });

        $this->assertEquals([
            'other' => ['required', 'array'],
            'other.string' => ['string', 'required'],
        ], $rules);

        $rules = $this->resolveRules(new class () {
            #[DataCollectionOf(SimpleData::class), MapName('other')]
            public DataCollection $property;
        });

        $this->assertEquals([
            'other' => ['present', 'array'],
            'other.*.string' => ['string', 'required'],
        ], $rules);


        $rules = $this->resolveRules(new class () {
            #[MapName('other')]
            public DataWithMapper $property;
        });

        $this->assertEquals([
            'other' => ['required', 'array'],
            'other.cased_property' => ['string', 'required'],
            'other.data_cased_property' => ['required', 'array'],
            'other.data_cased_property.string' => ['string', 'required'],
            'other.data_collection_cased_property' => ['present', 'array'],
            'other.data_collection_cased_property.*.string' => ['string', 'required'],
        ], $rules);
    }

    /** @test */
    public function it_will_nullify_nested_nullable_data_objects()
    {
        $data = new class () extends Data {
            public ?SimpleData $property;
        };

        $this->assertEquals([
            'property' => ['nullable', 'array'],
            'property.string' => ['nullable', 'string'],
        ], $this->resolveRules($data));
    }

    /** @test */
    public function it_will_nullify_optional_nested_data_objects()
    {
        $data = new class () extends Data {
            public Optional|SimpleData $property;
        };

        $this->assertEquals([
            'property' => ['nullable', 'array'],
            'property.string' => ['nullable', 'string'],
        ], $this->resolveRules($data));
    }

    private function resolveRules(object $class): array
    {
        $reflectionProperty = new ReflectionProperty($class, 'property');

        $property = DataProperty::create($reflectionProperty);

        return app(DataPropertyValidationRulesResolver::class)->execute($property)->toArray();
    }
}
