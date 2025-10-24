<?php

namespace Spatie\LaravelData\Support\Annotations;

use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Nullable;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Resolvers\ContextResolver;
use Spatie\LaravelData\Support\Annotations\PhpDocumentorTypes\ArrayWithoutMixedDefault;
use Spatie\LaravelData\Support\Annotations\PhpDocumentorTypes\IterableWithoutMixedDefault;

/**
 * @note To myself, always use the fully qualified class names in pest tests when using anonymous classes
 */
class DataIterableAnnotationReader
{
    public function __construct(
        protected readonly ContextResolver $contextResolver,
    ) {
    }

    /** @return array<string, DataIterableAnnotation[]> */
    public function getForClass(ReflectionClass $class): array
    {
        return collect($this->get($class))
            ->groupBy(fn (DataIterableAnnotation $annotation) => $annotation->property)
            ->toArray();
    }

    /** @return DataIterableAnnotation[] */
    public function getForProperty(ReflectionProperty $property): array
    {
        return $this->get($property);
    }

    /** @return array<string, DataIterableAnnotation[]> */
    public function getForMethod(ReflectionMethod $method): array
    {
        return collect($this->get($method))
            ->groupBy(fn (DataIterableAnnotation $annotation) => $annotation->property)
            ->toArray();
    }

    /** @return DataIterableAnnotation[] */
    protected function get(
        ReflectionProperty|ReflectionClass|ReflectionMethod $reflection
    ): array {
        $comment = $reflection->getDocComment();
        if ($comment === false) {
            return [];
        }

        $hasType = preg_match_all('/(?:@var|@param|@property(?:-read)?)(.+)/uim', $comment, $matches);
        if (! $hasType) {
            return [];
        }

        $annotations = [];
        foreach ($matches[1] as $match) {
            [$valueTypeString, $propertyName] = explode('$', $match, 2) + [1 => null];

            $type = tap(new TypeResolver(), function (TypeResolver $t) {
                $t->addKeyword('array', ArrayWithoutMixedDefault::class);
                $t->addKeyword('iterable', IterableWithoutMixedDefault::class);
            })->resolve($valueTypeString);

            $getKeyTypeWithoutDefault = static function (AbstractList $type): ?Type {
                return(new class ($type) extends AbstractList {
                    public function __construct(AbstractList $victim)
                    {
                        parent::__construct($victim->valueType, $victim->keyType);
                    }
                    public function getKeyTypeWithoutDefault(): ?Type
                    {
                        return $this->keyType;
                    }
                })->getKeyTypeWithoutDefault();
            };

            /** @return string[] */
            $getValueTypeStrings = static function (Type $type, ?Type $key) use ($getKeyTypeWithoutDefault, &$getValueTypeStrings): array {
                if ($type instanceof Compound) {
                    return array_merge(...array_map(fn (Type $t) => $getValueTypeStrings($t, $key), iterator_to_array($type)));
                } elseif ($type instanceof ArrayWithoutMixedDefault || $type instanceof IterableWithoutMixedDefault) {
                    return $type->getOriginalValueType() === null
                        ? [[(string) $type, $key === null ? 'array-key' : (string) $key]]
                        : $getValueTypeStrings($type->getOriginalValueType(), $getKeyTypeWithoutDefault($type));
                } elseif ($type instanceof AbstractList) {
                    return $getValueTypeStrings($type->getValueType(), $getKeyTypeWithoutDefault($type));
                } elseif ($type instanceof Nullable) {
                    return $getValueTypeStrings($type->getActualType(), $key);
                } else {
                    return [[(string) $type, $key === null ? 'array-key' : (string) $key]];
                }
            };

            $valueTypeStrings = $getValueTypeStrings($type, null);
            foreach ($valueTypeStrings as [$valueTypeString, $keyString]) {
                $valueTypeString = ltrim($valueTypeString, '\\');
                if (is_subclass_of($valueTypeString, BaseData::class)) {
                    $annotations[] = new DataIterableAnnotation(
                        type: $valueTypeString,
                        isData: true,
                        keyType: $keyString,
                        property: $propertyName,
                    );

                    continue;
                }

                static $ignoredClasses = [Lazy::class, Optional::class];

                $fcqn = $this->resolveFcqn($reflection, $valueTypeString);
                if (class_exists($fcqn)) {
                    if (! in_array($fcqn, $ignoredClasses) && ! array_any($ignoredClasses, fn ($ignoredClass) => is_subclass_of($fcqn, $ignoredClass))) {
                        $annotations[] = new DataIterableAnnotation(
                            type: $fcqn,
                            isData: is_subclass_of($fcqn, BaseData::class),
                            keyType: $keyString,
                            property: $propertyName,
                        );
                    }

                    continue;
                }

                if (! in_array($valueTypeString, $ignoredClasses) && ! array_any($ignoredClasses, fn ($ignoredClass) => is_subclass_of($valueTypeString, $ignoredClass))) {
                    $annotations[] = new DataIterableAnnotation(
                        type: $valueTypeString,
                        isData: false,
                        keyType: $keyString,
                        property: $propertyName,
                    );
                }
            }
        }

        usort($annotations, fn (DataIterableAnnotation $a, DataIterableAnnotation $b) => $b->isData <=> $a->isData);

        return $annotations;
    }

    protected function resolveFcqn(
        ReflectionProperty|ReflectionClass|ReflectionMethod $reflection,
        string $class
    ): ?string {
        $context = $this->contextResolver->execute($reflection);

        $type = (new FqsenResolver())->resolve($class, $context);

        return ltrim((string) $type, '\\');
    }
}
