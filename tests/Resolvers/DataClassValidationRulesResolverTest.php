<?php


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Exists as LaravelExists;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\mock;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Resolvers\DataClassValidationRulesResolver;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithOverwrittenRules;

beforeEach(function () {
    $this->resolver = app(DataClassValidationRulesResolver::class);
});

it('will resolve rules for a data object', function () {
    $data = new class () extends Data {
        public string $name;

        public ?int $age;
    };

    expect($this->resolver->execute($data::class)->all())
        ->toEqual([
            'name' => ['string', 'required'],
            'age' => ['numeric', 'nullable'],
        ]);
});

it('will make properties nullable if required', function () {
    $data = new class () extends Data {
        public string $name;

        public ?int $age;
    };

    expect(
        $this->resolver->execute($data::class, nullable: true)->all()
    )->toEqual([
        'name' => ['nullable', 'string'],
        'age' => ['nullable', 'numeric'],
    ]);
});

it('will merge overwritten rules on the data object', function () {
    $data = new class () extends Data {
        public string $name;

        public static function rules(): array
        {
            return [
                'name' => ['string', 'required', 'min:10', 'max:100'],
            ];
        }
    };

    expect(
        $this->resolver->execute($data::class)->all()
    )->toEqualCanonicalizing([
        'name' => ['string', 'required', 'min:10', 'max:100'],
    ]);
});

it(
    'can overwrite rules for the base collection object which will not affect the collected data object rules',
    function () {
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
            expect($exception->errors())->toHaveKey(('collection.1.string'));

            return;
        }

        $this->fail('We should not end up here');
    }
);

it('can skip certain properties from being validated', function () {
    $data = new class () extends Data {
        #[WithoutValidation]
        public string $skip_string;

        #[WithoutValidation]
        public SimpleData $skip_data;

        #[WithoutValidation, DataCollectionOf(SimpleData::class)]
        public DataCollection $skip_data_collection;

        public ?int $age;
    };

    expect(
        $this->resolver->execute($data::class)->all()
    )->toMatchArray([
        'age' => ['numeric', 'nullable'],
    ]);
});

it('can resolve dependencies when calling rules', function () {
    $requestMock = mock(Request::class);
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

    expect(
        $this->resolver->execute($data::class)->all()
    )->toMatchArray([
        'name' => ['required'],
    ]);
});

it('can resolve payload when calling rules', function () {
    $data = new class () extends Data {
        public string $name;

        public static function rules(array $payload): array
        {
            return [
                'name' => $payload['name'] === 'foo' ? ['required'] : ['sometimes'],
            ];
        }
    };

    expect(
        $this->resolver->execute($data::class, ['name' => 'foo'])->all()
    )->toMatchArray([
        'name' => ['required'],
    ]);
});

it('will transform overwritten data rules into plain Laravel rules', function () {
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

    expect(
        $this->resolver->execute($data::class)->all()
    )->toMatchArray([
        'property' => [
            (new LaravelExists('table'))->where(fn (Builder $builder) => $builder->is_admin),
        ],
    ]);
});
