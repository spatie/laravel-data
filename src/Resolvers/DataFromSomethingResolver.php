<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Http\Request;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Enums\CustomCreationMethodType;
use Spatie\LaravelData\Exceptions\CannotCreateAbstractClass;
use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\ResolvedDataPipeline;

/**
 * @template TData of BaseData
 */
class DataFromSomethingResolver
{
    public function __construct(
        protected DataConfig $dataConfig,
        protected DataFromArrayResolver $dataFromArrayResolver,
        protected DataMorphClassResolver $dataMorphClassResolver,
    ) {
    }

    /**
     * @param class-string<TData> $class
     *
     * @return TData
     */
    public function execute(
        string $class,
        CreationContext $creationContext,
        mixed ...$payloads
    ): BaseData {
        if ($data = $this->createFromCustomCreationMethod($class, $creationContext, $payloads)) {
            return $data;
        }

        $pipeline = $this->dataConfig->getResolvedDataPipeline($class);

        $normalizedPayloads = [];

        foreach ($payloads as $i => $payload) {
            $normalizedPayloads[$i] = $pipeline->normalize($payload ?? []);
        }

        $dataClass = $this->dataConfig->getDataClass($class);

        if ($dataClass->isAbstract === true && $dataClass->propertyMorphable === true) {
            $morphDataClass = $this->dataMorphClassResolver->execute($dataClass, array_map(
                fn (Normalized|array $normalized) => $pipeline->transformNormalizedToArray($normalized, $creationContext),
                $normalizedPayloads
            ));

            $this->replaceDataClassWithMorphedVersion(
                $morphDataClass,
                $pipeline,
                $creationContext,
                $class
            );
        }

        $properties = $this->runPipeline(
            $creationContext,
            $pipeline,
            $payloads,
            $normalizedPayloads
        );

        return $this->dataFromArrayResolver->execute($class, $properties);
    }

    protected function replaceDataClassWithMorphedVersion(
        ?string $morphDataClass,
        ResolvedDataPipeline &$pipeline,
        CreationContext $creationContext,
        string &$class,
    ): void {
        if ($morphDataClass === null) {
            throw CannotCreateAbstractClass::morphClassWasNotResolved(originalClass: $class);
        }

        $pipeline = $this->dataConfig->getResolvedDataPipeline($morphDataClass);
        $creationContext->dataClass = $morphDataClass;
        $class = $morphDataClass;
    }

    protected function createFromCustomCreationMethod(
        string $class,
        CreationContext $creationContext,
        array $payloads
    ): ?BaseData {
        if ($creationContext->disableMagicalCreation) {
            return null;
        }

        $customCreationMethods = $this->dataConfig
            ->getDataClass($class)
            ->methods;

        $method = null;

        foreach ($customCreationMethods as $customCreationMethod) {
            if ($customCreationMethod->customCreationMethodType !== CustomCreationMethodType::Object) {
                continue;
            }

            if (
                $creationContext->ignoredMagicalMethods !== null
                && in_array($customCreationMethod->name, $creationContext->ignoredMagicalMethods)
            ) {
                continue;
            }

            if ($customCreationMethod->accepts(...$payloads)) {
                $method = $customCreationMethod;

                break;
            }
        }

        if ($method === null) {
            return null;
        }

        $pipeline = $this->dataConfig->getResolvedDataPipeline($class);

        foreach ($payloads as $payload) {
            if ($payload instanceof Request) {
                // Solely for the purpose of validation
                $pipeline->execute($payload, $creationContext);
            }
        }

        foreach ($method->parameters as $index => $parameter) {
            if ($parameter->type->type->isCreationContext()) {
                $payloads[$index] = $creationContext;
            }
        }

        $methodName = $method->name;

        return $class::$methodName(...$payloads);
    }

    /**
     * @param array<array<string, mixed>|Normalized> $normalizedPayloads
     */
    protected function runPipeline(
        CreationContext $creationContext,
        ResolvedDataPipeline $pipeline,
        array $payloads,
        array $normalizedPayloads
    ): array {
        $payloadCount = count($payloads);

        if ($payloadCount === 0) {
            return $pipeline->runPipelineOnNormalizedValue([], [], $creationContext);
        }

        if ($payloadCount === 1) {
            return $pipeline->runPipelineOnNormalizedValue($payloads[0], $normalizedPayloads[0], $creationContext);
        }

        $properties = [];

        for ($i = 0; $i < $payloadCount; $i++) {
            foreach ($pipeline->runPipelineOnNormalizedValue($payloads[$i], $normalizedPayloads[$i], $creationContext) as $key => $value) {
                if (array_key_exists($key, $properties) && ($value === null || $value instanceof Optional)) {
                    continue;
                }

                $properties[$key] = $value;
            }
        }

        return $properties;
    }
}
