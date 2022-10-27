<?php

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\DataPropertyCanOnlyHaveOneType;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

function assertEmptyPropertyValue(
    mixed $expected,
    object $class,
    array $extra = [],
    string $propertyName = 'property',
) {
    $resolver = app(EmptyDataResolver::class);

    $empty = $resolver->execute($class::class, $extra);

    expect($empty)->toHaveKey($propertyName)
        ->and($empty[$propertyName])->toEqual($expected);
}

it('will return null if the property has no type', function () {
    assertEmptyPropertyValue(null, new class()
    {
        public $property;
    });
});

it('will return null if the property has a basic type', function () {
    assertEmptyPropertyValue(null, new class()
    {
        public int $property;
    });

    assertEmptyPropertyValue(null, new class()
    {
        public bool $property;
    });

    assertEmptyPropertyValue(null, new class()
    {
        public float $property;
    });

    assertEmptyPropertyValue(null, new class()
    {
        public string $property;
    });

    assertEmptyPropertyValue(null, new class()
    {
        public mixed $property;
    });
});

it('will return an array for collection types', function () {
    assertEmptyPropertyValue([], new class()
    {
        public array $property;
    });

    assertEmptyPropertyValue([], new class()
    {
        public Collection $property;
    });

    assertEmptyPropertyValue([], new class()
    {
        public EloquentCollection $property;
    });

    assertEmptyPropertyValue([], new class()
    {
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection $property;
    });
});

it('will further transform resources', function () {
    assertEmptyPropertyValue(['string' => null], new class()
    {
        public SimpleData $property;
    });
});

it('will return the base type for lazy types', function () {
    //        $this->assertEmptyPropertyValue(null, new class() {
    //            public Lazy | string $property;
    //        });

    assertEmptyPropertyValue([], new class()
    {
        public Lazy|array $property;
    });

    assertEmptyPropertyValue(['string' => null], new class()
    {
        public Lazy|SimpleData $property;
    });
});

it('will return the base type for lazy types that can be null', function () {
    assertEmptyPropertyValue(null, new class()
    {
        public Lazy|string|null $property;
    });

    assertEmptyPropertyValue([], new class()
    {
        public Lazy|array|null $property;
    });

    assertEmptyPropertyValue(['string' => null], new class()
    {
        public Lazy|SimpleData|null $property;
    });
});

it('will return the base type for lazy types that can be optional', function () {
    assertEmptyPropertyValue(null, new class()
    {
        public Lazy|string|Optional $property;
    });

    assertEmptyPropertyValue([], new class()
    {
        public Lazy|array|Optional $property;
    });

    assertEmptyPropertyValue(['string' => null], new class()
    {
        public Lazy|SimpleData|Optional $property;
    });
});

it('will return the base type for undefinable types', function () {
    assertEmptyPropertyValue(null, new class()
    {
        public Optional|string $property;
    });

    assertEmptyPropertyValue([], new class()
    {
        public Optional|array $property;
    });

    assertEmptyPropertyValue(['string' => null], new class()
    {
        public Optional|SimpleData $property;
    });
});

it('cannot have multiple types', function () {
    assertEmptyPropertyValue(null, new class()
    {
        public int|string $property;
    });
})->throws(DataPropertyCanOnlyHaveOneType::class);

it('cannot have multiple types with a lazy', function () {
    assertEmptyPropertyValue(null, new class()
    {
        public int|string|Lazy $property;
    });
})->throws(DataPropertyCanOnlyHaveOneType::class);

it('cannot have multiple types with a nullable lazy', function () {
    assertEmptyPropertyValue(null, new class()
    {
        public int|string|Lazy|null $property;
    });
})->throws(DataPropertyCanOnlyHaveOneType::class);

it('cannot have multiple types with an optional', function () {
    assertEmptyPropertyValue(null, new class()
    {
        public int|string|Optional $property;
    });
})->throws(DataPropertyCanOnlyHaveOneType::class);

it('cannot have multiple types with a nullable optional', function () {
    assertEmptyPropertyValue(null, new class()
    {
        public int|string|Optional|null $property;
    });
})->throws(DataPropertyCanOnlyHaveOneType::class);

it('can overwrite empty properties', function () {
    assertEmptyPropertyValue('Hello', new class()
    {
        public string $property;
    }, ['property' => 'Hello']);
});

it('can use the property default value', function () {
    assertEmptyPropertyValue('Hello', new class()
    {
        public string $property = 'Hello';
    });
});

it('can use the constructor property default value', function () {
    assertEmptyPropertyValue('Hello', new class()
    {
        public function __construct(
            public string $property = 'Hello',
        ) {
        }
    });
});

it('has support for mapping property names', function () {
    assertEmptyPropertyValue(null, new class()
    {
        #[MapOutputName('other_property')]
        public string $property;
    }, propertyName: 'other_property');
});
