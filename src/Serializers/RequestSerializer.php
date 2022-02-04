<?php

namespace Spatie\LaravelData\Serializers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Resolvers\DataFromArrayResolver;
use Spatie\LaravelData\Resolvers\DataValidatorResolver;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\PropertiesMapper;

class RequestSerializer implements DataSerializer
{
    public function __construct(
        protected DataConfig $dataConfig,
        protected DataValidatorResolver $dataValidatorResolver,
        protected DataFromArrayResolver $dataFromArrayResolver,
        protected MagicMethodSerializer $magicMethodSerializer,
        protected PropertiesMapper $propertiesMapper,
    ) {
    }

    public function serialize(string $class, mixed $payload): ?Data
    {
        if (! $payload instanceof Request) {
            return null;
        }

        /** @var \Spatie\LaravelData\Data|string $class */
        if ($this->dataConfig->getDataClass($class)->hasAuthorizationMethod()) {
            $this->ensureRequestIsAuthorized($class);
        }

        $properties = $this->propertiesMapper->execute(
            $payload->toArray(),
            $class,
        );

        $this->dataValidatorResolver->execute($class, $properties)->validate();

        if ($data = $this->magicMethodSerializer->serialize($class, $payload)) {
            return $data;
        }

        return $this->dataFromArrayResolver->execute($class, $properties);
    }

    private function ensureRequestIsAuthorized(string $class): void
    {
        /** @psalm-suppress UndefinedMethod */
        if (method_exists($class, 'authorize') && $class::authorize() === false) {
            throw new AuthorizationException();
        }
    }
}
