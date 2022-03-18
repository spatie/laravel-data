<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataPipeline;
use Spatie\LaravelData\DataPipes\AuthorizedDataPipe;
use Spatie\LaravelData\DataPipes\ValidatePropertiesDataPipe;
use Spatie\LaravelData\Normalizers\ArraybleNormalizer;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataMethod;

class DataFromSomethingResolver
{
    public function __construct(
        protected DataConfig $dataConfig,
        protected DataFromArrayResolver $dataFromArrayResolver,
    ) {
    }

    public function execute(string $class, mixed ...$payloads): Data
    {
        if ($data = $this->createFromCustomCreationMethod($class, $payloads)) {
            return $data;
        }

        $properties = array_reduce(
            $payloads,
            function (Collection $carry, mixed $payload) use ($class) {
                /** @var \Spatie\LaravelData\DataPipeline $pipeline */
                $pipeline = $class::pipeline();

                return $carry->merge($pipeline->using($payload)->execute());
            },
            collect(),
        );

        return $this->dataFromArrayResolver->execute($class, $properties);
    }

    private function createFromCustomCreationMethod(string $class, array $payloads): ?Data
    {
        /** @var Collection<\Spatie\LaravelData\Support\DataMethod> $customCreationMethods */
        $customCreationMethods = $this->dataConfig
            ->getDataClass($class)
            ->methods
            ->filter(fn (DataMethod $method) => $method->isCustomCreationMethod);

        $methodName = null;

        foreach ($customCreationMethods as $customCreationMethod) {
            if ($customCreationMethod->accepts(...$payloads)) {
                $methodName = $customCreationMethod->name;

                break;
            }
        }

        if ($methodName === null) {
            return null;
        }

        foreach ($payloads as $payload) {
            if ($payload instanceof Request) {
                DataPipeline::create()
                    ->normalizer(ArraybleNormalizer::class)
                    ->into($class)
                    ->through(AuthorizedDataPipe::class)
                    ->through(ValidatePropertiesDataPipe::class)
                    ->using($payload)
                    ->execute();
            }
        }

        return $class::$methodName(...$payloads);
    }
}
