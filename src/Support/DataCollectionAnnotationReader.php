<?php

namespace Spatie\LaravelData\Support;

use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Types\ContextFactory;
use ReflectionProperty;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\DataObject;

class DataCollectionAnnotationReader
{
    public function getClass(
        ReflectionProperty $reflectionProperty
    ): ?string {
        $comment = $reflectionProperty->getDocComment();

        if ($comment === false) {
            return null;
        }

        preg_match(
            '/\??(?<array>[\\\\A-Za-z0-9]*)\[\]|([\\\\A-Za-z0-9?]*)<(?<collection>[\\\\A-Za-z0-9]*)>/',
            $comment,
            $matches,
        );

        $class = $matches['collection'] ?? $matches['array'] ?? null;

        if ($class === null) {
            return null;
        }

        $class = ltrim($class, '\\');

        if (is_subclass_of($class, BaseData::class)) {
            return $class;
        }

        $context = (new ContextFactory())->createFromReflector($reflectionProperty);
        $type = (new FqsenResolver())->resolve($class, $context);

        $class = ltrim((string) $type, '\\');

        if (is_subclass_of($class, BaseData::class)) {
            return $class;
        }

        return null;
    }
}
