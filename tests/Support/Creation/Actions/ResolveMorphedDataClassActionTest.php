<?php

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\PropertyForMorph;
use Spatie\LaravelData\Contracts\PropertyMorphableData;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Exceptions\CannotCreateAbstractClass;
use Spatie\LaravelData\Support\Creation\Actions\ReadDataPropertyAction;
use Spatie\LaravelData\Support\Creation\Actions\ResolveMorphedDataClassAction;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Tests\Fakes\AbstractPropertyMorphableData;
use Spatie\LaravelData\Tests\Fakes\PropertyMorphableDataA;
use Spatie\LaravelData\Tests\Fakes\PropertyMorphableDataB;

function resolveMorph(string $dataClass, array $normalized): string
{
    $action = new ResolveMorphedDataClassAction(
        app(DataConfig::class),
        new ReadDataPropertyAction(),
    );

    return $action->execute(
        CreationContextFactory::createFromConfig($dataClass)->get(),
        app(DataConfig::class)->getDataClass($dataClass),
        $normalized
    )->name;
}

it('coerces a backed enum discriminator before morphing', function () {
    expect(resolveMorph(AbstractPropertyMorphableData::class, [['variant' => 'a']]))
        ->toBe(PropertyMorphableDataA::class)
        ->and(resolveMorph(AbstractPropertyMorphableData::class, [['variant' => 'b']]))
        ->toBe(PropertyMorphableDataB::class);
});

it('reads the discriminator from the first payload that has it', function () {
    expect(resolveMorph(AbstractPropertyMorphableData::class, [[], ['variant' => 'b']]))
        ->toBe(PropertyMorphableDataB::class);
});

it('throws when the discriminator is absent and has no default', function () {
    resolveMorph(AbstractPropertyMorphableData::class, [[]]);
})->throws(CannotCreateAbstractClass::class);

it('throws when morph returns no class', function () {
    resolveMorph(AbstractPropertyMorphableData::class, [['variant' => 'unknown']]);
})->throws(CannotCreateAbstractClass::class);

it('falls back to the property default when the discriminator is absent', function () {
    abstract class MorphActionDataWithDefault extends Data implements PropertyMorphableData
    {
        #[PropertyForMorph]
        public string $kind = 'defaulted';

        public static function morph(array $properties): ?string
        {
            return $properties['kind'] === 'defaulted'
                ? MorphActionDefaultedData::class
                : null;
        }
    }

    class MorphActionDefaultedData extends MorphActionDataWithDefault
    {
    }

    expect(resolveMorph(MorphActionDataWithDefault::class, [[]]))
        ->toBe(MorphActionDefaultedData::class);
});

it('reads the discriminator through its mapped input name', function () {
    abstract class MorphActionMappedData extends Data implements PropertyMorphableData
    {
        #[PropertyForMorph]
        #[MapInputName('kind_on_the_wire')]
        public string $kind;

        public static function morph(array $properties): ?string
        {
            return $properties['kind'] === 'mapped'
                ? MorphActionMappedChildData::class
                : null;
        }
    }

    class MorphActionMappedChildData extends MorphActionMappedData
    {
    }

    expect(resolveMorph(MorphActionMappedData::class, [['kind_on_the_wire' => 'mapped']]))
        ->toBe(MorphActionMappedChildData::class);
});
