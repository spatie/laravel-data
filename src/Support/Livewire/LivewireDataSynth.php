<?php

namespace Spatie\LaravelData\Support\Livewire;

use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\Lazy\LivewireLostLazy;

class LivewireDataSynth extends Synth
{
    // TODO:
    //
    // Think about Lazy, we can always send them down?
    // Or only conditional lazy which are true -> probably a better default
    // What if we want to create a new data object and don't have the lazyvalue
    // -> we could create a new LiveWireMissingLazy object, which we then use as the lazy value
    // -> when resolving it would throw an error saying the value was lost in LiveWire
    //
    // Problem with computed properties should be sorted out
    //
    // DataCollections synth from the PR
    //
    // Do we want livewire as an dependency?
    //
    // Update docs
    //
    // Can we test this?
    //
    // Mapping property names, should we do this?


    protected DataConfig $dataConfig;

    public static string $key = 'laravel-data-object';

    public function __construct(ComponentContext $context, $path)
    {
        $this->dataConfig = app(DataConfig::class);

        parent::__construct($context, $path);
    }

    public static function match($target)
    {
        return $target instanceof BaseData && $target instanceof TransformableData;
    }

    public function get(&$target, $key): BaseData
    {
        return $target->{$key};
    }

    public function set(&$target, $key, $value): void
    {
        $target->{$key} = $value;
    }

    /**
     * @param BaseData&TransformableData $target
     * @param callable(mixed):mixed $dehydrateChild
     *
     * @return array
     */
    public function dehydrate(
        BaseData&TransformableData $target,
        callable $dehydrateChild
    ): array {
        $morph = $this->dataConfig->morphMap->getDataClassAlias($target::class) ?? $target::class;

        $payload = $target->all();

        ray($payload);

        foreach ($payload as $key => $value) {
            $payload[$key] = $dehydrateChild($key, $value);
        }

        return [
            $payload,
            ['morph' => $morph],
        ];
    }

    /**
     * @param mixed $value
     * @param array $meta
     * @param callable(mixed):mixed $hydrateChild
     *
     * @return BaseData
     */
    public function hydrate(
        array $value,
        array $meta,
        callable $hydrateChild
    ): BaseData {
        $morph = $meta['morph'];

        $dataClass = $this->dataConfig->morphMap->getMorphedDataClass($morph) ?? $morph;

        $payload = [];

        foreach ($this->dataConfig->getDataClass($dataClass)->properties as $name => $property) {
            if (array_key_exists($name, $value) === false && $property->type->lazyType) {
                $payload[$name] = new LivewireLostLazy($dataClass, $name);

                continue;
            }

            $payload[$name] = $hydrateChild($name, $value[$name]);
        }

        return $dataClass::factory()
            ->ignoreMagicalMethod('fromLivewire')
            ->from($payload);
    }
}
