<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;
use Illuminate\Support\Str;
use Spatie\LaravelData\Contracts\AppendableData;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\IncludeableData;
use Spatie\LaravelData\Contracts\ResponsableData;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\Contracts\ValidateableData;
use Spatie\LaravelData\Contracts\WrappableData;

class DataMissingFeature extends Exception
{
    public static function create(
        string $dataClass,
        string $featureClass,
    ) {
        $implemented = collect([
            AppendableData::class,
            BaseData::class,
            IncludeableData::class,
            ResponsableData::class,
            TransformableData::class,
            ValidateableData::class,
            WrappableData::class,
        ])
            ->filter(fn (string $interface) => in_array($interface, class_implements($dataClass)))
            ->map(fn (string $interface) => Str::afterLast($interface, '\\'))
            ->map(fn (string $interface) => "`{$interface}`")
            ->join(', ');

        return new self("Feature `{$featureClass}` missing in data object `{$dataClass}` implementing {$implemented}");
    }
}
