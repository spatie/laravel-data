<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Resolvers\DataClassValidationRulesResolver;

/**
 * @mixin \Spatie\LaravelData\Data
 */
trait DynamicDataTrait
{
    /**
     * Properties that are required to determine the appropriate class
     * @return array
     */
    abstract public static function requiredPropertiesForResolving(): array;

    /**
     * @param array $properties
     * @return class-string<DynamicData::class>
     */
    abstract public static function dynamicClassName(array $properties): string;

    public static function from(...$payloads): static
    {
        // Skip overriding the from method for classes that extend the abstract one
        if (static::class !== self::class) {
            return parent::from(...$payloads);
        }

        $properties = static::propertiesFromPayloads(...$payloads);

        // Create the appropriate concrete data object from the properties
        /* @var class-string<DynamicData::class> $dataClass */
        $dataClass = static::dynamicClassName(Arr::only($properties, static::requiredPropertiesForResolving()));

        return $dataClass::from(...$payloads);
    }

    protected static function propertiesFromPayloads(mixed ...$payloads): array
    {
        // TODO: Remove this method and find an alternative. We can't use the data from
        // something resolver as it creates an instance and the dynamic data classes are abstract

        /** @var Collection $properties */
        $properties = array_reduce(
            $payloads,
            function (Collection $carry, mixed $payload) {
                /** @var BaseData $class */
                $pipeline = self::pipeline();

                foreach (self::normalizers() as $normalizer) {
                    $pipeline->normalizer($normalizer);
                }

                return $carry->merge($pipeline->using($payload)->execute());
            },
            collect(),
        );

        return $properties->all();
    }

    public static function rules(array $relativePayload, ?string $path)
    {
        // Don't add the rules if we're not inside the abstract class or required properties aren't available
        if (static::class !== self::class || ! Arr::has($relativePayload, static::requiredPropertiesForResolving())) {
            return [];
        }

        /* @var class-string<DynamicData::class> $dataClass */
        $dataClass = static::dynamicClassName(Arr::only($relativePayload, static::requiredPropertiesForResolving()));

        // Override the rules by adding the concrete classes rules
        return app(DataClassValidationRulesResolver::class)
            ->execute($dataClass, $relativePayload, false, '')
            ->all();
    }
}
