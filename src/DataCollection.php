<?php

namespace Spatie\LaravelData;

use ArrayAccess;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\Concerns\BaseDataCollectable;
use Spatie\LaravelData\Concerns\EnumerableMethods;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Concerns\TransformableData;
use Spatie\LaravelData\Concerns\WrappableData;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\DataCollectable;
use Spatie\LaravelData\Contracts\IncludeableData as IncludeableDataContract;
use Spatie\LaravelData\Exceptions\CannotCastData;
use Spatie\LaravelData\Exceptions\InvalidDataCollectionOperation;
use Spatie\LaravelData\Support\EloquentCasts\DataCollectionEloquentCast;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements \ArrayAccess<TKey, TValue>
 * @implements  DataCollectable<TKey, TValue>
 */
class DataCollection implements DataCollectable, ArrayAccess
{
    /** @use \Spatie\LaravelData\Concerns\BaseDataCollectable<TKey, TValue> */
    use BaseDataCollectable;
    use ResponsableData;
    use IncludeableData;
    use WrappableData;
    use TransformableData;

    /** @use \Spatie\LaravelData\Concerns\EnumerableMethods<TKey, TValue> */
    use EnumerableMethods;

    /** @var Enumerable<TKey, TValue> */
    protected Enumerable $items;

    /**
     * @param class-string<TValue> $dataClass
     * @param array|Enumerable<TKey, TValue>|DataCollection $items
     */
    public function __construct(
        public readonly string $dataClass,
        Enumerable|array|DataCollection|null $items
    ) {
        if (is_array($items) || is_null($items)) {
            $items = new Collection($items);
        }

        if ($items instanceof DataCollection) {
            $items = $items->toCollection();
        }

        $this->items = $items->map(
            fn ($item) => $item instanceof $this->dataClass ? $item : $this->dataClass::from($item)
        );
    }

    /**
     * @return array<TKey, TValue>
     */
    public function items(): array
    {
        return $this->items->all();
    }

    /**
     * @return Enumerable<TKey, TValue>
     */
    public function toCollection(): Enumerable
    {
        return $this->items;
    }

    /**
     * @param TValue ...$values
     *
     * @return static
     */
    public function push(...$values): static
    {
        foreach ($values as $value) {
            $this[] = $value;
        }

        return $this;
    }

    /**
     * @param TKey $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        if (! $this->items instanceof ArrayAccess) {
            throw InvalidDataCollectionOperation::create();
        }

        return $this->items->offsetExists($offset);
    }

    /**
     * @param TKey $offset
     *
     * @return TValue
     */
    public function offsetGet($offset): mixed
    {
        if (! $this->items instanceof ArrayAccess) {
            throw InvalidDataCollectionOperation::create();
        }

        $data = $this->items->offsetGet($offset);

        if($data instanceof IncludeableDataContract) {
            $data->withPartialTrees($this->getPartialTrees());
        }

        return $data;
    }

    /**
     * @param TKey|null $offset
     * @param TValue $value
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (! $this->items instanceof ArrayAccess) {
            throw InvalidDataCollectionOperation::create();
        }

        $value = $value instanceof BaseData
            ? $value
            : $this->dataClass::from($value);

        $this->items->offsetSet($offset, $value);
    }

    /**
     * @param TKey $offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        if (! $this->items instanceof ArrayAccess) {
            throw InvalidDataCollectionOperation::create();
        }

        $this->items->offsetUnset($offset);
    }

    public static function castUsing(array $arguments)
    {
        if (count($arguments) < 1) {
            throw CannotCastData::dataCollectionTypeRequired();
        }

        return new DataCollectionEloquentCast($arguments[0], static::class, array_slice($arguments, 1));
    }
}
