<?php

namespace Spatie\LaravelData\Support\Creation;

class ConstructionState
{
    protected array $payload = [];

    protected array $structure;

    /** @var array<int, array{payloadKey: string|int, structureKey: ?string, isIndex: bool}> */
    protected array $path = [];

    public function __construct(
        public readonly CreationContext $creationContext,
        string $class,
    ) {
        $this->structure = static::newNode($class);
    }

    public function enterProperty(string $name, ?string $sourceKey = null): void
    {
        $this->path[] = [
            'payloadKey' => $sourceKey ?? $name,
            'structureKey' => $name,
            'isIndex' => false,
        ];
    }

    public function enterIndex(string|int $index): void
    {
        $this->path[] = [
            'payloadKey' => $index,
            'structureKey' => null,
            'isIndex' => true,
        ];
    }

    public function leave(): void
    {
        array_pop($this->path);
    }

    public function depth(): int
    {
        return count($this->path);
    }

    public function dotPath(string|int|null $key = null): string
    {
        $segments = array_map(
            fn (array $segment) => $segment['payloadKey'],
            $this->path
        );

        if ($key !== null) {
            $segments[] = $key;
        }

        return implode('.', $segments);
    }

    public function writePayload(string|int $key, mixed $value): void
    {
        $slot = &$this->payloadSlot();

        $slot[$key] = $value;
    }

    public function hasPayload(string|int $key): bool
    {
        $slot = $this->currentPayload();

        return is_array($slot) && array_key_exists($key, $slot);
    }

    public function getPayload(string|int $key): mixed
    {
        $slot = $this->currentPayload();

        if (! is_array($slot) || ! array_key_exists($key, $slot)) {
            return null;
        }

        return $slot[$key];
    }

    public function payload(): array
    {
        return $this->payload;
    }

    protected function currentPayload(): mixed
    {
        $slot = $this->payload;

        foreach ($this->path as $segment) {
            $key = $segment['payloadKey'];

            if (! is_array($slot) || ! array_key_exists($key, $slot)) {
                return null;
            }

            $slot = $slot[$key];
        }

        return $slot;
    }

    protected function &payloadSlot(): array
    {
        $slot = &$this->payload;

        foreach ($this->path as $segment) {
            $key = $segment['payloadKey'];

            if (! array_key_exists($key, $slot) || ! is_array($slot[$key])) {
                $slot[$key] = [];
            }

            $slot = &$slot[$key];
        }

        return $slot;
    }

    protected static function newNode(?string $class): array
    {
        return [
            'class' => $class,
            'mappings' => [],
            'children' => [],
        ];
    }
}
