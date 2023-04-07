<?php

namespace Spatie\LaravelData\Normalizers;

use Illuminate\Foundation\Http\FormRequest;

class FormRequestNormalizer implements Normalizer
{
    public function normalize(mixed $value): ?array
    {
        if (! $value instanceof FormRequest) {
            return null;
        }

        return $value->validated();
    }
}
