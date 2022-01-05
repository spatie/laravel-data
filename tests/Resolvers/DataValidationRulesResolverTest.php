<?php

namespace Spatie\LaravelData\Tests\Resolvers;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Resolvers\DataValidationRulesResolver;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithOverwrittenRules;
use Spatie\LaravelData\Tests\TestCase;

class DataValidationRulesResolverTest extends TestCase
{
    private DataValidationRulesResolver $resolver;

    public function setUp(): void
    {
        parent::setUp();

        $this->resolver = app(DataValidationRulesResolver::class);
    }

    /** @test */
    public function it_will_resolve_rules_for_a_data_object()
    {
        $data = new class () extends Data {
            public string $name;

            public ?int $age;
        };

        $this->assertEquals([
            'name' => ['string', 'required'],
            'age' => ['numeric', 'nullable'],
        ], $this->resolver->execute($data::class)->all());
    }

    /** @test */
    public function it_will_make_properties_nullable_if_required()
    {
        $data = new class () extends Data {
            public string $name;

            public ?int $age;
        };

        $this->assertEqualsCanonicalizing([
            'name' => ['string', 'nullable'],
            'age' => ['numeric', 'nullable'],
        ], $this->resolver->execute($data::class, nullable: true)->all());
    }

    /** @test */
    public function it_will_merge_overwritten_rules_on_the_data_object()
    {
        $data = new class () extends Data {
            public string $name;

            public static function rules(): array
            {
                return [
                    'name' => ['string', 'required', 'min:10', 'max:100'],
                ];
            }
        };

        $this->assertEqualsCanonicalizing([
            'name' => ['string', 'required', 'min:10', 'max:100'],
        ], $this->resolver->execute($data::class)->all());
    }

    /** @test */
    public function it_will_merge_overwritten_rules_on_nested_data_objects()
    {
        $data = new class () extends Data {
            public SimpleDataWithOverwrittenRules $nested;

            /** @var DataCollection<\Spatie\LaravelData\Tests\Fakes\SimpleDataWithOverwrittenRules> */
            public DataCollection $collection;
        };

        $this->assertEqualsCanonicalizing([
            'nested' => ['array', 'required'],
            'nested.string' => ['string', 'required', 'min:10', 'max:100'],
            'collection' => ['array', 'required'],
            'collection.*.string' => ['string', 'required', 'min:10', 'max:100'],
        ], $this->resolver->execute($data::class)->all());
    }
}
