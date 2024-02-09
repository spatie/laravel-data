<?php

namespace Spatie\LaravelData\Casts;

use Illuminate\Support\Enumerable;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class EnumerableCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if($property->type->kind->isDataCollectable()) {
            return Uncastable::create();
        }

        if ($value instanceof Enumerable) {
            return $value;
        }

        /** @var class-string<Enumerable>|null $collectionType */
        $collectionType = $property->type->findAcceptedTypeForBaseType(Enumerable::class);

        if ($collectionType === null) {
            return collect($value);
        }

        return $collectionType::make($value);
    }
}
