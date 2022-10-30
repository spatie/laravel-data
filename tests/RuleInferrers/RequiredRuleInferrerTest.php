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
use Spatie\LaravelData\Support\Validation\RulesCollection;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

/**
 * @return \Spatie\LaravelData\Support\DataProperty;
 */
function getProperty(object $class)
{
    $dataClass = DataClass::create(new ReflectionClass($class));

    return $dataClass->properties->first();
}

beforeEach(function () {
    $this->inferrer = new RequiredRuleInferrer();
});

it("won't add a required rule when a property is non-nullable", function () {
    $dataProperty = getProperty(new class () extends Data {
        public string $string;
    });

    $rules = $this->inferrer->handle($dataProperty, new RulesCollection());

    expect($rules->all())->toEqualCanonicalizing([new Required()]);
});

it("won't add a required rule when a property is nullable", function () {
    $dataProperty = getProperty(new class () extends Data {
        public ?string $string;
    });

    $rules = $this->inferrer->handle($dataProperty, new RulesCollection());

    expect($rules->all())->toEqualCanonicalizing([]);
});

it("won't add a required rule when a property already contains a required rule", function () {
    $dataProperty = getProperty(new class () extends Data {
        public string $string;
    });

    $rules = $this->inferrer->handle(
        $dataProperty,
        RulesCollection::create()->add(new RequiredIf('bla'))
    );

    expect($rules->all())->toEqualCanonicalizing(['required_if:bla']);
});

it("won't add a required rule when a property already contains a required object rule ", function () {
    $dataProperty = getProperty(new class () extends Data {
        public string $string;
    });

    $rules = $this->inferrer->handle(
        $dataProperty,
        RulesCollection::create()->add(Required::create())
    );

    expect($rules->normalize())->toEqualCanonicalizing(['required']);
});

it(
    "won't add a required rule when a property already contains a boolean rule",
    function () {
        $dataProperty = getProperty(new class () extends Data {
            public string $string;
        });

        $rules = $this->inferrer->handle(
            $dataProperty,
            RulesCollection::create()->add(BooleanType::create())
        );

        expect($rules->normalize())->toEqualCanonicalizing([new BooleanType()]);
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
            RulesCollection::create()->add(Nullable::create())
        );

        expect($rules->normalize())->toEqualCanonicalizing([new Nullable()]);
    }
);

it('has support for rules that cannot be converted to string', function () {
    $dataProperty = getProperty(new class () extends Data {
        public string $string;
    });

    $rules = $this->inferrer->handle(
        $dataProperty,
        RulesCollection::create()->add(
            new \Spatie\LaravelData\Attributes\Validation\Enum(new BaseEnum('SomeClass'))
        )
    );

    expect($rules->normalize())->toEqualCanonicalizing([
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
        RulesCollection::create()->add(new Present(), new ArrayType())
    );

    expect($rules->normalize())->toEqualCanonicalizing(['present', 'array']);
});

it("won't add required rules to undefinable properties", function () {
    $dataProperty = getProperty(new class () extends Data {
        public string|Optional $string;
    });

    $rules = $this->inferrer->handle($dataProperty, []);

    expect($rules)->toEqualCanonicalizing([]);
})->throws(TypeError::class);
