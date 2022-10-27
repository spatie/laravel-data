<?php

use Spatie\LaravelData\Tests\Fakes\DataWithMapper;
use Spatie\LaravelData\Tests\Fakes\FakeEnum;
use Spatie\LaravelData\Tests\Fakes\NestedData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Illuminate\Validation\Rules\Enum as EnumRule;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Bail;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\RequiredWith;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\Size;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Optional;

it('will add a required or nullable rule based upon the property nullability', function () {
    $rules = resolveRules(new class()
    {
        public int $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['numeric', 'required'],
    ]);

    $rules = resolveRules(new class()
    {
        public ?int $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['numeric', 'nullable'],
    ]);
});

it('will add basic rules for certain types', function () {
    $rules = resolveRules(new class()
    {
        public string $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['string', 'required'],
    ]);

    $rules = resolveRules(new class()
    {
        public int $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['numeric', 'required'],
    ]);

    $rules = resolveRules(new class()
    {
        public bool $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['boolean'],
    ]);

    $rules = resolveRules(new class()
    {
        public float $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['numeric', 'required'],
    ]);

    $rules = resolveRules(new class()
    {
        public array $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['array', 'required'],
    ]);
});

it('will add rules for enums', function () {
    $this->onlyPHP81();

    $rules = resolveRules(new class()
    {
        public FakeEnum $property;
    });

    expect($rules)->toMatchArray([
        'property' => [new EnumRule(FakeEnum::class), 'required'],
    ]);
});

it('will take validation attributes into account', function () {
    $rules = resolveRules(new class()
    {
        #[Max(10)]
        public string $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['string', 'max:10', 'required'],
    ]);
});

it('will take rules from nested data objects', function () {
    $rules = resolveRules(new class()
    {
        public SimpleData $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['required', 'array'],
        'property.string' => ['string', 'required'],
    ]);

    $rules = resolveRules(new class()
    {
        public ?SimpleData $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['nullable', 'array'],
        'property.string' => ['nullable', 'string'],
    ]);

    $rules = resolveRules(new class()
    {
        public Optional|SimpleData $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['sometimes', 'array'],
        'property.string' => ['nullable', 'string'],
    ]);

    $rules = resolveRules(new class()
    {
        public null|Optional|SimpleData $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['nullable', 'sometimes', 'array'],
        'property.string' => ['nullable', 'string'],
    ]);
});

it('will take rules from nested data collections', function () {
    $rules = resolveRules(new class()
    {
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['present', 'array'],
        'property.*.string' => ['string', 'required'],
    ]);

    $rules = resolveRules(new class()
    {
        #[DataCollectionOf(SimpleData::class)]
        public ?DataCollection $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['nullable', 'array'],
        'property.*.string' => ['string', 'required'],
    ]);

    $rules = resolveRules(new class()
    {
        #[DataCollectionOf(SimpleData::class)]
        public Optional|DataCollection $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['sometimes', 'array'],
        'property.*.string' => ['string', 'required'],
    ]);

    $rules = resolveRules(new class()
    {
        #[DataCollectionOf(SimpleData::class)]
        public null|Optional|DataCollection $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['nullable', 'sometimes', 'array'],
        'property.*.string' => ['string', 'required'],
    ]);
});

it('can nest validation rules even further', function () {
    $rules = resolveRules(new class()
    {
        public NestedData $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['required', 'array'],
        'property.simple' => ['required', 'array'],
        'property.simple.string' => ['string', 'required'],
    ]);

    $rules = resolveRules(new class()
    {
        public ?SimpleData $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['nullable', 'array'],
        'property.string' => ['nullable', 'string'],
    ]);
});

it('will never add extra require rules when not needed', function () {
    $rules = resolveRules(new class()
    {
        public ?string $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['string', new Nullable()],
    ]);

    $rules = resolveRules(new class()
    {
        public bool $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['boolean'],
    ]);

    $rules = resolveRules(new class()
    {
        #[RequiredWith('other')]
        public string $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['string', 'required_with:other'],
    ]);

    $rules = resolveRules(new class()
    {
        #[Rule('required_with:other')]
        public string $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['string', 'required_with:other'],
    ]);
});

it('will work with non-string rules', function () {
    $rules = resolveRules(new class()
    {
        #[Enum(FakeEnum::class)]
        public string $property;
    });

    expect($rules)->toMatchArray([
        'property' => ['string', new EnumRule(FakeEnum::class), 'required'],
    ]);
});

it('will take mapped properties into account', function () {
    $rules = resolveRules(new class()
    {
        #[MapName('other')]
        public int $property;
    });

    expect($rules)->toMatchArray([
        'other' => ['numeric', 'required'],
    ]);

    $rules = resolveRules(new class()
    {
        #[MapName('other')]
        public SimpleData $property;
    });

    expect($rules)->toMatchArray([
        'other' => ['required', 'array'],
        'other.string' => ['string', 'required'],
    ]);

    $rules = resolveRules(new class()
    {
        #[DataCollectionOf(SimpleData::class), MapName('other')]
        public DataCollection $property;
    });

    expect($rules)->toMatchArray([
        'other' => ['present', 'array'],
        'other.*.string' => ['string', 'required'],
    ]);

    $rules = resolveRules(new class()
    {
        #[MapName('other')]
        public DataWithMapper $property;
    });

    expect($rules)->toMatchArray([
        'other' => ['required', 'array'],
        'other.cased_property' => ['string', 'required'],
        'other.data_cased_property' => ['required', 'array'],
        'other.data_cased_property.string' => ['string', 'required'],
        'other.data_collection_cased_property' => ['present', 'array'],
        'other.data_collection_cased_property.*.string' => ['string', 'required'],
    ]);
});

it('will nullify nested nullable data objects', function () {
    $data = new class() extends Data
    {
        public ?SimpleData $property;
    };

    expect(resolveRules($data))->toMatchArray([
        'property' => ['nullable', 'array'],
        'property.string' => ['nullable', 'string'],
    ]);
});

it('will nullify optional nested data objects', function () {
    $data = new class() extends Data
    {
        public Optional|SimpleData $property;
    };

    expect(resolveRules($data))->toEqual([
        'property' => ['sometimes', 'array'],
        'property.string' => ['nullable', 'string'],
    ]);
});

it(
    'will apply rule inferrers on data collections and data objects',
    function () {
        $data = new class() extends Data
        {
            #[Bail]
            public SimpleData $property;
        };

        expect(resolveRules($data))->toMatchArray([
            'property' => ['required', 'array', 'bail'],
            'property.string' => ['string', 'required'],
        ]);

        $data = new class() extends Data
        {
            #[DataCollectionOf(SimpleData::class)]
            #[Size(10)]
            public DataCollection $property;
        };

        expect(resolveRules($data))->toMatchArray([
            'property' => ['present', 'array', 'size:10'],
            'property.*.string' => ['string', 'required'],
        ]);
    }
);
