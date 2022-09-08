<?php

namespace Spatie\LaravelData\Tests\Resolvers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Exists as LaravelExists;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Resolvers\DataClassValidationRulesResolver;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithOverwrittenRules;
use Spatie\LaravelData\Tests\TestCase;

class DataClassValidationRulesResolverTest extends TestCase
{
    private DataClassValidationRulesResolver $resolver;

    public function setUp(): void
    {
        parent::setUp();

        $this->resolver = app(DataClassValidationRulesResolver::class);
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

        $this->assertEquals([
            'name' => ['nullable', 'string'],
            'age' => ['nullable', 'numeric'],
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
            'collection' => ['array', 'present'],
            'collection.*.string' => ['string', 'required', 'min:10', 'max:100'],
        ], $this->resolver->execute($data::class)->all());
    }

    /** @test */
    public function it_can_overwrite_rules_for_the_base_collection_object_which_will_not_affect_the_collected_data_object_rules()
    {
        $dataClass = new class () extends Data {
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection $collection;

            public static function rules(): array
            {
                return [
                    'collection' => ['array', 'required'],
                ];
            }
        };

        try {
            $dataClass::validate([
                'collection' => [
                    ['string' => 'A'],
                    ['invalid' => 'B'],
                ],
            ]);
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('collection.1.string', $exception->errors());

            return;
        }

        $this->fail('We should not end up here');
    }

    /** @test */
    public function it_can_skip_certain_properties_from_being_validated()
    {
        $data = new class () extends Data {
            #[WithoutValidation]
            public string $skip_string;

            #[WithoutValidation]
            public SimpleData $skip_data;

            #[WithoutValidation, DataCollectionOf(SimpleData::class)]
            public DataCollection $skip_data_collection;

            public ?int $age;
        };

        $this->assertEquals([
            'age' => ['numeric', 'nullable'],
        ], $this->resolver->execute($data::class)->all());
    }

    /** @test */
    public function it_can_resolve_dependencies_when_calling_rules()
    {
        $requestMock = $this->mock(Request::class);
        $requestMock->expects('input')->andReturns('value');
        $this->app->bind(Request::class, fn () => $requestMock);

        $data = new class () extends Data {
            public string $name;

            public static function rules(Request $request): array
            {
                return [
                    'name' => $request->input('key') === 'value' ? ['required'] : ['bail'],
                ];
            }
        };

        $this->assertEquals([
            'name' => ['required'],
        ], $this->resolver->execute($data::class)->all());
    }

    /** @test */
    public function it_can_resolve_payload_when_calling_rules()
    {
        $data = new class () extends Data {
            public string $name;

            public static function rules(array $payload): array
            {
                return [
                    'name' => $payload['name'] === 'foo' ? ['required'] : ['sometimes'],
                ];
            }
        };

        $this->assertEquals([
            'name' => ['required'],
        ], $this->resolver->execute($data::class, ['name' => 'foo'])->all());
    }

    /** @test */
    public function it_will_transform_overwritten_data_rules_into_plain_laravel_rules()
    {
        $data = new class () extends Data {
            public int $property;

            public static function rules(): array
            {
                return [
                    'property' => [
                        new Exists('table', where: fn (Builder $builder) => $builder->is_admin),
                    ],
                ];
            }
        };

        $this->assertEquals([
            'property' => [(new LaravelExists('table'))->where(fn (Builder $builder) => $builder->is_admin)],
        ], $this->resolver->execute($data::class)->all());
    }
}
