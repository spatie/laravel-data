<?php

namespace Spatie\LaravelData\Support\Creation\Actions;

use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Normalizers\Normalized\UnknownProperty;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class ReadDataPropertyAction
{
    /**
     * @param array<int, array|Normalized> $normalized
     *
     * @return array{0: mixed, 1: string}
     */
    public function execute(
        CreationContext $creationContext,
        DataProperty $property,
        array $normalized
    ): array {
        $mappedKey = $creationContext->mapPropertyNames
            ? $property->inputMappedName
            : null;

        if ($mappedKey === $property->name) {
            $mappedKey = null;
        }

        foreach ($normalized as $normalizedPayload) {
            if ($mappedKey !== null) {
                $value = $this->read($normalizedPayload, $mappedKey, $property);

                if (! $value instanceof UnknownProperty) {
                    return [$value, $mappedKey];
                }
            }

            $value = $this->read($normalizedPayload, $property->name, $property);

            if (! $value instanceof UnknownProperty) {
                return [$value, $property->name];
            }
        }

        return [UnknownProperty::$instance, $mappedKey ?? $property->name];
    }

    protected function read(
        array|Normalized $normalized,
        string|int $key,
        DataProperty $property
    ): mixed {
        if ($normalized instanceof Normalized) {
            return $normalized->getProperty((string) $key, $property);
        }

        if (! array_key_exists($key, $normalized)) {
            return UnknownProperty::$instance;
        }

        return $normalized[$key];
    }
}
