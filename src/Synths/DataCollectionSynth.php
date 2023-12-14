<?php

namespace Spatie\LaravelData\Synths;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Spatie\LaravelData\DataCollection;

class DataCollectionSynth extends Synth
{
    public static $key = 'dtcl';

    public static function match($target)
    {
        return get_class($target) == DataCollection::class;
    }

    public function dehydrate(DataCollection $target, $dehydrateChild)
    {
        $data = $target->all();

        foreach ($data as $key => $child) {
            $data[$key] = $dehydrateChild($key, $child);
        }

        return [
            $data,
            ['class' => get_class($target)],
        ];
    }

    /**
     * @param  array<mixed>  $value
     * @param  array<string, class-string>  $meta
     * @param  mixed  $hydrateChild
     * @return \Spatie\LaravelData\DataCollection
     */
    public function hydrate($value, $meta, $hydrateChild)
    {
        foreach ($value as $key => $child) {
            $value[$key] = $hydrateChild($key, $child);
        }

        return $meta['class']::make($value);
    }

    public function get(&$target, $key)
    {
        return $target[$key] ?? null;
    }

    public function set(&$target, $key, $value)
    {
        $target[$key] = $value;
    }
}
