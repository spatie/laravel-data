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

    public function hydrate($value, $meta)
    {
        return new DataCollection($meta['class'], $value);
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
