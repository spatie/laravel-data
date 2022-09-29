<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Str;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Types\ContextFactory;
use ReflectionProperty;
use Spatie\LaravelData\Contracts\BaseData;

class DataCollectionAnnotationReader
{
    public function getClass(
        ReflectionProperty $reflectionProperty
    ): ?string {
        $comment = $reflectionProperty->getDocComment();

        if ($comment === false) {
            return null;
        }

        $fqsenPattern = '[\\\\a-z0-9_]+';
        $keyPattern = '(?:int|string|\(int\|string\)|array-key)';

        $array = fn () => (string) Str::of($comment)->match("/({$fqsenPattern})\[\]/i");
        $collection = fn () => (string) Str::of($comment)->match("/{$fqsenPattern}<(?:{$keyPattern},\s*)?({$fqsenPattern})>/i");

        $class = $collection() ?: $array() ?: null;

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
