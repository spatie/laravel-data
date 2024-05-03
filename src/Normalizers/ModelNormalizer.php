<?php

namespace Spatie\LaravelData\Normalizers;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Normalizers\Normalized\NormalizedModel;

class ModelNormalizer implements Normalizer
{
    public function normalize(mixed $value): null|array|Normalized
    {
        if (! $value instanceof Model) {
            return null;
        }

        return new NormalizedModel($value);
    }
}
