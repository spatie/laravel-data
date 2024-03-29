<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Str;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;

class LoadsModelRelationsDataPipe implements DataPipe
{
    public function handle(mixed $payload, DataClass $class, array $properties, CreationContext $creationContext): array
    {
        if ($payload instanceof Model) {
            foreach ($class->properties as $dataProperty) {
                if (isset($properties[$dataProperty->name])) {
                    continue;
                }
                $relation = $dataProperty->inputMappedName ?? $dataProperty->name;

                $relationRetriever = function () use ($payload, $relation) {
                    try {
                        $relationName = $payload::$snakeAttributes ? Str::camel($relation) : $relation;
                        return $payload->loadMissing($relationName)->{$relationName};
                    } catch (RelationNotFoundException) {
                        return $payload->{$relation};
                    }
                };

                $properties[$dataProperty->name] = match(true) {
                    (bool)$dataProperty->type->lazyType => Lazy::create($relationRetriever)->defaultIncluded(!$dataProperty->type->isOptional && !$dataProperty->type->isNullable),
                    $dataProperty->type->isOptional => Optional::create(),
                    !$dataProperty->type->isNullable => $relationRetriever(),
                    default => null,
                };
            }
        }

        return $properties;
    }
}
