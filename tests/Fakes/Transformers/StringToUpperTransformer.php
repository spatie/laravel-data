<?php

namespace Spatie\LaravelData\Tests\Fakes\Transformers;

use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Transformers\Transformer;

<<<<<<< HEAD
class StringToUpperTransformer implements Transformer
{
=======
class StringToUpperTransformer implements Transformer{
>>>>>>> Allow transformers to target built in types, data collections and data objects
    public function transform(DataProperty $property, mixed $value): string
    {
        return strtoupper($value);
    }
};
