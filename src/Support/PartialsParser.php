<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Str;

class PartialsParser
{
    public function execute(array $partials): array
    {
        $payload = [];

        foreach ($partials as $directive) {
            $directive = str_replace(' ', '', $directive);

            $nested = Str::after($directive, '.');
            $propertyDefinition = Str::before($directive, '.');

            if ($propertyDefinition === '*') {
                return ['*'];
            }

            if (Str::startsWith($propertyDefinition, '{') && Str::endsWith($propertyDefinition, '}')) {
                $properties = explode(',', substr($propertyDefinition, 1, -1));

                foreach ($properties as $property) {
                    if (! array_key_exists($property, $payload)) {
                        $payload[$property] = [];
                    }
                }

                continue;
            }

            if (array_key_exists($propertyDefinition, $payload) && $nested !== $propertyDefinition) {
                $payload[$propertyDefinition][] = $nested;
            } else {
                $payload[$propertyDefinition] = $nested === $propertyDefinition
                    ? []
                    : [$nested];
            }
        }

        foreach ($payload as $property => $nested) {
            $payload[$property] = $this->execute($nested);
        }

        return $payload;
    }
}
