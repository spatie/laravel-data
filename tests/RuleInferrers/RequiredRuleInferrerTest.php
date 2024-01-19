<?php

use Illuminate\Validation\Rules\Enum as BaseEnum;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\RequiredIf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\RuleInferrers\RequiredRuleInferrer;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\Validation\PropertyRules;
use Spatie\LaravelData\Support\Validation\RuleDenormalizer;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Spatie\LaravelData\Support\Validation\ValidationPath;
use Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

/**
 * @return \Spatie\LaravelData\Support\DataProperty;
 */
function getProperty(object $class)
{
    $dataClass = FakeDataStructureFactory::class($class);

    return $dataClass->properties->first();
}

beforeEach(function () {
    $this->inferrer = new RequiredRuleInferrer();
});

it("won't add a required rule when a property is non-nullable", function () {
    $dataProperty = getProperty(new class () extends Data {
        public string $string;
    });

    $rules = $this->inferrer->handle($dataProperty, new PropertyRules(), new ValidationContext([], [], new ValidationPath(null)));

    expect($rules->all())->toEqualCanonicalizing([new Required()]);
});

it("won't add a required rule when a property is nullable", function () {
    $dataProperty = getProperty(new class () extends Data {
        public ?string $string;
    });

    $rules = $this->inferrer->handle($dataProperty, new PropertyRules(), new ValidationContext([], [], new ValidationPath(null)));

    expect($rules->all())->toEqualCanonicalizing([]);
});

it("won't add a required rule when a property already contains a required rule", function () {
    $dataProperty = getProperty(new class () extends Data {
        public string $string;
    });

    $rules = $this->inferrer->handle(
        $dataProperty,
        PropertyRules::create()->add(new RequiredIf('bla')),
        new ValidationContext([], [], new ValidationPath(null))
    );

    expect($rules->all())->toEqualCanonicalizing(['required_if:bla']);
});

it("won't add a required rule when a property already contains a required object rule ", function () {
    $dataProperty = getProperty(new class () extends Data {
        public string $string;
    });

    $rules = $this->inferrer->handle(
        $dataProperty,
        PropertyRules::create()->add(Required::create()),
        new ValidationContext([], [], new ValidationPath(null))
    );

    expect(app(RuleDenormalizer::class)->execute($rules->all(), ValidationPath::create()))
        ->toEqualCanonicalizing(['required']);
});

it(
    "won't add a required rule when a property already contains a boolean rule",
    function () {
        $dataProperty = getProperty(new class () extends Data {
            public string $string;
        });

        $rules = $this->inferrer->handle(
            $dataProperty,
            PropertyRules::create()->add(BooleanType::create()),
            new ValidationContext([], [], new ValidationPath(null))
        );

        expect(app(RuleDenormalizer::class)->execute($rules->all(), ValidationPath::create()))
            ->toEqualCanonicalizing([new BooleanType()]);
    }
);

it(
    "won't add a required rule when a property already contains a nullable rule",
    function () {
        $dataProperty = getProperty(new class () extends Data {
            public string $string;
        });

        $rules = $this->inferrer->handle(
            $dataProperty,
            PropertyRules::create()->add(Nullable::create()),
            new ValidationContext([], [], new ValidationPath(null))
        );

        expect(app(RuleDenormalizer::class)->execute($rules->all(), ValidationPath::create()))
            ->toEqualCanonicalizing([new Nullable()]);
    }
);

it('has support for rules that cannot be converted to string', function () {
    $dataProperty = getProperty(new class () extends Data {
        public string $string;
    });

    $rules = $this->inferrer->handle(
        $dataProperty,
        PropertyRules::create()->add(
            new \Spatie\LaravelData\Attributes\Validation\Enum(new BaseEnum('SomeClass'))
        ),
        new ValidationContext([], [], new ValidationPath(null))
    );

    expect(app(RuleDenormalizer::class)->execute($rules->all(), ValidationPath::create()))->toEqualCanonicalizing([
        'required', new BaseEnum('SomeClass'),
    ]);
});

it("won't add required to a data collection since it is already present", function () {
    $dataProperty = getProperty(new class () extends Data {
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection $collection;
    });

    $rules = $this->inferrer->handle(
        $dataProperty,
        PropertyRules::create()->add(new Present(), new ArrayType()),
        new ValidationContext([], [], new ValidationPath(null))
    );

    expect(app(RuleDenormalizer::class)->execute($rules->all(), ValidationPath::create()))
        ->toEqualCanonicalizing(['present', 'array']);
});

it("won't add required rules to undefinable properties", function () {
    $dataProperty = getProperty(new class () extends Data {
        public string|Optional $string;
    });

    $rules = $this->inferrer->handle($dataProperty, [], new ValidationContext([], [], new ValidationPath(null)));

    expect($rules)->toEqualCanonicalizing([]);
})->throws(TypeError::class);
