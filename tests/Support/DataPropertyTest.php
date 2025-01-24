<?php

use Spatie\LaravelData\Attributes\AutoClosureLazy;
use Spatie\LaravelData\Attributes\AutoInertiaLazy;
use Spatie\LaravelData\Attributes\AutoLazy;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Hidden;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Factories\DataPropertyFactory;
use Spatie\LaravelData\Tests\Fakes\CastTransformers\FakeCastTransformer;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModel;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

function resolveHelper(
    object $class,
    bool $hasDefaultValue = false,
    mixed $defaultValue = null,
    ?AutoLazy $classAutoLazy = null,
): DataProperty {
    $reflectionProperty = new ReflectionProperty($class, 'property');
    $reflectionClass = new ReflectionClass($class);

    return app(DataPropertyFactory::class)->build(
        $reflectionProperty,
        $reflectionClass,
        $hasDefaultValue,
        $defaultValue,
        classAutoLazy: $classAutoLazy
    );
}

it('can get the cast attribute with arguments', function () {
    $helper = resolveHelper(new class () {
        #[WithCast(DateTimeInterfaceCast::class, 'd-m-y')]
        public SimpleData $property;
    });

    expect($helper->cast)->toEqual(new DateTimeInterfaceCast('d-m-y'));
});

it('can get the transformer attribute', function () {
    $helper = resolveHelper(new class () {
        #[WithTransformer(DateTimeInterfaceTransformer::class)]
        public SimpleData $property;
    });

    expect($helper->transformer)->toEqual(new DateTimeInterfaceTransformer());
});

it('can get the transformer attribute with arguments', function () {
    $helper = resolveHelper(new class () {
        #[WithTransformer(DateTimeInterfaceTransformer::class, 'd-m-y')]
        public SimpleData $property;
    });

    expect($helper->transformer)->toEqual(new DateTimeInterfaceTransformer('d-m-y'));
});

it('can get the cast with transformer attribute', function () {
    $helper = resolveHelper(new class () {
        #[WithCastAndTransformer(FakeCastTransformer::class)]
        public SimpleData $property;
    });

    expect($helper->transformer)->toEqual(new FakeCastTransformer());
    expect($helper->cast)->toEqual(new FakeCastTransformer());
});

it('can get the mapped input name', function () {
    $helper = resolveHelper(new class () {
        #[MapInputName('other')]
        public SimpleData $property;
    });

    expect($helper->inputMappedName)->toEqual('other');
});

it('can get the mapped output name', function () {
    $helper = resolveHelper(new class () {
        #[MapOutputName('other')]
        public SimpleData $property;
    });

    expect($helper->outputMappedName)->toEqual('other');
});

it('can get all attributes', function () {
    $helper = resolveHelper(new class () {
        #[MapInputName('other')]
        #[WithTransformer(DateTimeInterfaceTransformer::class)]
        #[WithCast(DateTimeInterfaceCast::class)]
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection $property;
    });

    expect($helper->attributes)->toHaveCount(4);
});

it('can get the default value', function () {
    $helper = resolveHelper(new class () {
        public string $property;
    });

    expect($helper->hasDefaultValue)->toBeFalse();

    $helper = resolveHelper(new class () {
        public string $property = 'hello';
    });

    expect($helper)
        ->hasDefaultValue->toBeTrue()
        ->defaultValue->toEqual('hello');
});

it('will ignore an Optional value as a default value', function () {
    $helper = resolveHelper(new class () {
        public function __construct(
            public string|Optional $property = new Optional(),
        ) {
        }
    });

    expect($helper)
        ->hasDefaultValue->toBeFalse()
        ->defaultValue->toBeNull();
});

it('can check if the property is promoted', function () {
    $helper = resolveHelper(new class ('') {
        public function __construct(
            public string $property,
        ) {
        }
    });

    expect($helper->isPromoted)->toBeTrue();

    $helper = resolveHelper(new class () {
        public string $property;
    });

    expect($helper->isPromoted)->toBeFalse();
});

it('can check if a property should be validated', function () {
    expect(
        resolveHelper(new class () {
            public string $property;
        })->validate
    )->toBeTrue();

    expect(
        resolveHelper(new class () {
            #[WithoutValidation]
            public string $property;
        })->validate
    )->toBeFalse();

    expect(
        resolveHelper(new class () {
            #[Computed]
            public string $property;
        })->validate
    )->toBeFalse();
});

it('can check if a property is computed', function () {
    expect(
        resolveHelper(new class () {
            public string $property;
        })->computed
    )->toBeFalse();

    expect(
        resolveHelper(new class () {
            #[Computed]
            public string $property;
        })->computed
    )->toBeTrue();
});

it('can check if a property is hidden', function () {
    expect(
        resolveHelper(new class () {
            public string $property;
        })->hidden
    )->toBeFalse();

    expect(
        resolveHelper(new class () {
            #[Hidden]
            public string $property;
        })->hidden
    )->toBeTrue();
});

it('can check if a property is auto-lazy', function () {
    expect(
        resolveHelper(new class () {
            public string $property;
        })->autoLazy
    )->toBeNull();

    expect(
        resolveHelper(new class () {
            #[AutoLazy]
            public string $property;
        })->autoLazy
    )->toBeInstanceOf(AutoLazy::class);

    expect(
        resolveHelper(new class () {
            #[AutoInertiaLazy]
            public string|Lazy $property;
        })->autoLazy
    )->toBeInstanceOf(AutoInertiaLazy::class);

    expect(
        resolveHelper(new class () {
            #[AutoWhenLoadedLazy('relation')]
            public string|Lazy $property;
        })->autoLazy
    )->toBeInstanceOf(AutoWhenLoadedLazy::class);

    expect(
        resolveHelper(new class () {
            #[AutoClosureLazy]
            public string $property;
        })->autoLazy
    )->toBeInstanceOf(AutoClosureLazy::class);
});

it('will set a property as auto-lazy when the class is auto-lazy and a lazy type is allowed', function () {
    expect(
        resolveHelper(new class () {
            public string $property;
        }, classAutoLazy: new AutoLazy())->autoLazy
    )->toBeNull();

    expect(
        resolveHelper(new class () {
            public string|Lazy $property;
        }, classAutoLazy: new AutoLazy())->autoLazy
    )->toBeInstanceOf(AutoLazy::class);
});

it('wont throw an error if non existing attribute is used on a data class property', function () {
    expect(NonExistingPropertyAttributeData::from(['property' => 'hello'])->property)->toEqual('hello')
        ->and(PhpStormAttributeData::from(['property' => 'hello'])->property)->toEqual('hello')
        ->and(PhpStormAttributeData::from('{"property": "hello"}')->property)->toEqual('hello')
        ->and(PhpStormAttributeData::from((object) ['property' => 'hello'])->property)->toEqual('hello')
        ->and(ModelWithPhpStormAttributePropertyData::from((new DummyModel())->fill(['id' => 1]))->id)->toEqual(1)
        ->and(ModelWithPromotedPhpStormAttributePropertyData::from((new DummyModel())->fill(['id' => 1]))->id)->toEqual(1);
});

class NonExistingPropertyAttributeData extends Data
{
    #[\Foo\Bar]
    public readonly string $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }
}

class PhpStormAttributeData extends Data
{
    #[\JetBrains\PhpStorm\Immutable]
    public readonly string $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }
}

class PromotedPhpStormAttributeData extends Data
{
    public function __construct(
        #[\JetBrains\PhpStorm\Immutable]
        public readonly string $property
    ) {
        //
    }
}

class ModelWithPhpStormAttributePropertyData extends Data
{
    #[\JetBrains\PhpStorm\Immutable]
    public int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public static function fromDummyModel(DummyModel $model)
    {
        return new self($model->id);
    }
}

class ModelWithPromotedPhpStormAttributePropertyData extends Data
{
    public function __construct(
        #[\JetBrains\PhpStorm\Immutable]
        public int $id
    ) {
    }

    public static function fromDummyModel(DummyModel $model)
    {
        return new self($model->id);
    }
}
