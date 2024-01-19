<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Http\Request;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\Creation\ValidationType;
use Spatie\LaravelData\Support\DataClass;

class ValidatePropertiesDataPipe implements DataPipe
{
    public function handle(
        mixed $payload,
        DataClass $class,
        array $properties,
        CreationContext $creationContext
    ): array {
        if ($creationContext->validationType === ValidationType::Disabled) {
            return $properties;
        }

        if ($creationContext->validationType === ValidationType::OnlyRequests && ! $payload instanceof Request) {
            return $properties;
        }

        ($class->name)::validate($properties);

        return $properties;
    }
}
