<?php

namespace Spatie\LaravelData\Support\Annotations;

use Illuminate\Support\Arr;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Nullable;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Resolvers\ContextResolver;
use Spatie\LaravelData\Support\Types\Storage\AcceptedTypesStorage;

/**
 * @note To myself, always use the fully qualified class names in pest tests when using anonymous classes
 */
class DataIterableAnnotationReader
{
    public function __construct(
        protected readonly ContextResolver $contextResolver,
    ) {
    }

    /** @return array<string, DataIterableAnnotation> */
    public function getForClass(ReflectionClass $class): array
    {
        return collect($this->get($class))->reverse()->keyBy(fn (DataIterableAnnotation $annotation) => $annotation->property)->all();
    }

    public function getForProperty(ReflectionProperty $property): ?DataIterableAnnotation
    {
        $annotations = $this->get($property);

        return Arr::first($annotations, fn (DataIterableAnnotation $a) => $a->isData) ?? Arr::first($annotations);
    }

    /** @return DataIterableAnnotation[] */
    public function getAllForProperty(ReflectionProperty $property): array
    {
        return $this->get($property);
    }

    /** @return array<string, DataIterableAnnotation> */
    public function getForMethod(ReflectionMethod $method): array
    {
        return collect($this->get($method))->keyBy(fn (DataIterableAnnotation $annotation) => $annotation->property)->all();
    }

    /** @return DataIterableAnnotation[] */
    protected function get(
        ReflectionProperty|ReflectionClass|ReflectionMethod $reflection
    ): array {
        $comment = $reflection->getDocComment();
        if ($comment === false) {
            return [];
        }

        $hasType = preg_match_all('/(?:@var|@param|@property(?:-read)?)\s+((?:\\\*[a-z_][a-z0-9_\-]*)*(?:\s*<\s*(?1)(?:\s*,\s*(?1))?\s*>)?(?:\s*\|\s*(?1))?)(?:\s*(\$[a-z_][a-z0-9_]*))?/im', $comment, $matches);
        if (! $hasType) {
            return [];
        }

        $annotations = [];
        foreach ($matches[1] ?? [] as $index => $type) {
            $property = empty($matches[2][$index]) ? null : ltrim($matches[2][$index], '$');
            $type = (new TypeResolver())->resolve($type); // , (new ContextFactory())->createFromReflector($reflection));

            $getOriginalKeyType = function(AbstractList $type): ?Type {
                $list = new class($type) extends AbstractList {
                    public function __construct(AbstractList $victim) {
                        parent::__construct($victim->valueType, $victim->keyType);
                    }
                    public function getOriginalKeyType(): ?Type {
                        return $this->keyType;
                    }
                };

                return $list->getOriginalKeyType();
            };
            /** @return string[] */
            $commentToValueTypeStrings = function (Type $type, ?Type $key) use ($getOriginalKeyType, &$commentToValueTypeStrings): array {
                if ($type instanceof Compound) {
                    return array_merge(...array_map(fn (Type $t) => $commentToValueTypeStrings($t, $key), iterator_to_array($type)));
                } elseif ($type instanceof AbstractList) {
                    return $commentToValueTypeStrings($type->getValueType(), $getOriginalKeyType($type));
                } elseif ($type instanceof Nullable) {
                    return $commentToValueTypeStrings($type->getActualType(), $key);
                } else {
                    return [[(string) $type, $key === null ? 'array-key' : (string) $key]];
                }
            };
            $typeStrings = $commentToValueTypeStrings($type, null);
            foreach ($typeStrings as [$typeString, $keyString]) {
                if (in_array($typeString, ['int', 'string', 'bool', 'float', 'array', 'object', 'callable', 'iterable', 'mixed'])) {
                    $annotations[] = new DataIterableAnnotation(
                        type: $typeString,
                        isData: false,
                        keyType: $keyString,
                        property: $property,
                    );

                    continue;
                }

                $typeString = ltrim($typeString, '\\');
                if (is_subclass_of($typeString, BaseData::class)) {
                    $annotations[] = new DataIterableAnnotation(
                        type: $typeString,
                        isData: true,
                        keyType: $keyString,
                        property: $property,
                    );

                    continue;
                }

                $fcqn = $this->resolveFcqn($reflection, $typeString);
                if (class_exists($fcqn)) {
                    $annotations[] = new DataIterableAnnotation(
                        type: $fcqn,
                        isData: is_subclass_of($fcqn, BaseData::class),
                        keyType: $keyString,
                        property: $property,
                    );

                    continue;
                }

                $annotations[] = new DataIterableAnnotation(
                    type: $typeString,
                    isData: false,
                    keyType: $keyString,
                    property: $property,
                );
            }
        }

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
