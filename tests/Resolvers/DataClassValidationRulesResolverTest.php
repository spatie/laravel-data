<?php

namespace Spatie\LaravelData\Tests\Resolvers;

use Illuminate\Http\Request;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\StringType;
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
            'name' => [new Nullable(), new StringType()],
            'age' => [new Numeric(),  new Nullable()],
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
        $this->app->bind(Request::class, fn() => $requestMock);

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
}
