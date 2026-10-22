<?php

use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Livewire\LivewireServiceProvider;

use function Pest\Livewire\livewire;

use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Exceptions\CannotCreateData;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\Livewire\LivewireDataCollectionSynth;
use Spatie\LaravelData\Support\Livewire\LivewireDataSynth;
use Spatie\LaravelData\Support\Lazy\LivewireLostLazy;
use Spatie\LaravelData\Tests\Fakes\Livewire\ComputedDataComponent;
use Spatie\LaravelData\Tests\Fakes\Livewire\DataCollectionComponent;
use Spatie\LaravelData\Tests\Fakes\Livewire\MappedDataComponent;
use Spatie\LaravelData\Tests\Fakes\Livewire\NestedDataComponent;
use Spatie\LaravelData\Tests\Fakes\Livewire\SimpleDataComponent;
use Spatie\LaravelData\Tests\Fakes\LazyData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithDefaultValue;

uses()->group('livewire');

it('works with livewire', function () {
    $class = new class ('') extends Data {
        use WireableData;

        public function __construct(
            public string $name,
        ) {
        }
    };

    $data = $class::fromLivewire(['name' => 'Freek']);

    expect($data)->toEqual(new $class('Freek'));

    expect($data->toLivewire())->toEqual(['name' => 'Freek']);
});

describe('synth tests', function () {
    beforeEach(function () {
        app()->register(LivewireServiceProvider::class);

        Livewire::propertySynthesizer(LivewireDataSynth::class);
        Livewire::propertySynthesizer(LivewireDataCollectionSynth::class);
    });

    it('can initialize a data object', function () {
        livewire(SimpleDataComponent::class, ['name' => 'Hello World'])
            ->assertSet('data.name', 'Hello World');
    });

    it('can set a data object property', function () {
        livewire(SimpleDataComponent::class, ['name' => 'Hello World'])
            ->set('data.name', 'Hello World from Livewire')
            ->assertSet('data.name', 'Hello World from Livewire')
            ->assertSee('Hello World from Livewire');
    });

    it('will not send lazy data to the front when not included', function () {
        livewire(SimpleDataComponent::class, ['name' => Lazy::create(fn () => 'Hello World')])
            ->assertDontSee('Hello World');
    });

    it('is possible to set included lazy data', function () {
        livewire(SimpleDataComponent::class, ['name' => Lazy::create(fn () => 'Hello World'), 'includes' => ['name']])
            ->assertDontSee('Hello World')
            ->set('data.name', 'Hello World from Livewire')
            ->assertSet('data.name', 'Hello World from Livewire')
            ->assertSee('Hello World from Livewire');
    });

    it('can initialize a nested data object', function () {
        livewire(NestedDataComponent::class, ['nested' => new SimpleData('Hello World')])
            ->assertSet('data.simple.string', 'Hello World');
    });

    it('can set a nested data object property', function () {
        livewire(NestedDataComponent::class, ['nested' => new SimpleData('Hello World')])
            ->set('data.simple.string', 'Hello World from Livewire')
            ->assertSet('data.simple.string', 'Hello World from Livewire')
            ->assertSee('Hello World from Livewire');
    });

    it('will not map property names', function () {
        livewire(MappedDataComponent::class)
            ->set('data.string', 'Hello World from Livewire')
            ->assertSet('data.string', 'Hello World from Livewire')
            ->assertSee('Hello World from Livewire');
    });

    it('can use computed properties', function () {
        livewire(ComputedDataComponent::class)
            ->set('data.first_name', 'Ruben')
            ->assertSet('data.first_name', 'Ruben')
            ->assertSet('data.name', ' ') // Computed properties only rerender after constructor calls
            ->assertSee(' ')
            ->set('data.last_name', 'Van Assche')
            ->assertSet('data.last_name', 'Van Assche')
            ->call('save');
    });

    it('can use data collections', function () {
        livewire(DataCollectionComponent::class)
            ->assertSee('a')
            ->assertSee('b')
            ->assertSee('c')
            ->set('collection.0.string', 'Hello World')
            ->assertSet('collection.0.string', 'Hello World')
            ->assertSee('Hello World')
            ->call('save');
    });

    it('can hydrate data with a missing property that has a default value', function () {
        $synth = new LivewireDataSynth(Mockery::mock(ComponentContext::class), 'data');

        $data = $synth->hydrate(
            [],
            ['morph' => SimpleDataWithDefaultValue::class, 'context' => encrypt((new SimpleDataWithDefaultValue())->getDataContext())],
            function () {
                throw new Exception('The missing property should not be hydrated');
            }
        );

        expect($data->string)->toBe('default');
    });

    it('hydrates properties that are present in the snapshot', function () {
        $synth = new LivewireDataSynth(Mockery::mock(ComponentContext::class), 'data');
        $hydratedProperties = [];

        $data = $synth->hydrate(
            ['string' => 'updated'],
            ['morph' => SimpleDataWithDefaultValue::class, 'context' => encrypt((new SimpleDataWithDefaultValue())->getDataContext())],
            function ($name, $value) use (&$hydratedProperties) {
                $hydratedProperties[$name] = $value;

                return $value;
            }
        );

        expect($hydratedProperties)->toBe(['string' => 'updated'])
            ->and($data->string)->toBe('updated');
    });

    it('still creates a lost lazy property when it is missing from the snapshot', function () {
        $synth = new LivewireDataSynth(Mockery::mock(ComponentContext::class), 'data');

        $data = $synth->hydrate(
            [],
            ['morph' => LazyData::class, 'context' => encrypt((new LazyData('name'))->getDataContext())],
            function ($name, $value) {
                return $value;
            }
        );

        expect($data->name)->toBeInstanceOf(LivewireLostLazy::class);
    });

    it('still fails when a required property is missing from the snapshot', function () {
        $synth = new LivewireDataSynth(Mockery::mock(ComponentContext::class), 'data');

        expect(fn () => $synth->hydrate(
            [],
            ['morph' => SimpleData::class, 'context' => encrypt([])],
            fn ($name, $value) => $value
        ))->toThrow(CannotCreateData::class);
    });
});
