<?php

namespace Spatie\LaravelData\Support\EloquentCasts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Crypt;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\CannotCastData;
use Spatie\LaravelData\Support\DataConfig;

/**
 * @template TData of (BaseData&TransformableData)
 * @template TDataCollection of DataCollection
 *
 * @implements CastsAttributes<TDataCollection<TData>|null,TDataCollection<TData>|array|null>
 */
class DataCollectionEloquentCast implements CastsAttributes
{
    protected DataConfig $dataConfig;

    /**
     * @param class-string<TData> $dataClass
     * @param class-string<TDataCollection> $dataCollectionClass
     * @param array<string> $arguments
     */
    public function __construct(
        protected string $dataClass,
        protected string $dataCollectionClass = DataCollection::class,
        protected array $arguments = []
    ) {
        $this->dataConfig = app(DataConfig::class);
    }

    public function get($model, string $key, $value, array $attributes): ?DataCollection
    {
        if (is_string($value) && in_array('encrypted', $this->arguments)) {
            $value = Crypt::decryptString($value);
        }

        if ($value === null && in_array('default', $this->arguments)) {
            $value = '[]';
        }

        if ($value === null) {
            return null;
        }

        $data = json_decode($value, true, flags: JSON_THROW_ON_ERROR);

        $isAbstractClassCast = $this->isAbstractClassCast();

        $data = array_map(function (array $item) use ($isAbstractClassCast) {
            if ($isAbstractClassCast) {
                $morphedClass = $this->dataConfig->morphMap->getMorphedDataClass($item['type']) ?? $item['type'];

                return $morphedClass::from($item['data']);
            }

            return ($this->dataClass)::from($item);
        }, $data);

        return new ($this->dataCollectionClass)($this->dataClass, $data);
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof BaseDataCollectable && $value instanceof TransformableData) {
            $value = $value->all();
        }

        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        if (! is_array($value)) {
            throw CannotCastData::shouldBeArray($model::class, $key);
        }

        $isAbstractClassCast = $this->isAbstractClassCast();

        $data = array_map(function (array|BaseData $item) use ($isAbstractClassCast) {
            if ($isAbstractClassCast && $item instanceof TransformableData) {
                $class = get_class($item);

                return [
                    'type' => $this->dataConfig->morphMap->getDataClassAlias($class) ?? $class,
                    'data' => json_decode(json: $item->toJson(), associative: true, flags: JSON_THROW_ON_ERROR),
                ];
            }

            return is_array($item)
                ? ($this->dataClass)::from($item)
                : $item;
        }, $value);

        if ($isAbstractClassCast) {
            return json_encode($data);
        }

        $dataCollection = new ($this->dataCollectionClass)($this->dataClass, $data);

        $dataCollection = $dataCollection->toJson();

        if (in_array('encrypted', $this->arguments)) {
            return Crypt::encryptString($dataCollection);
        }

        return $dataCollection;
    }

    /**
     * @param TDataCollection<TData>|null $firstValue
     * @param TDataCollection<TData>|null $secondValue
     */
    public function compare($model, string $key, $firstValue, $secondValue): bool
    {
        return $this->get($model, $key, $firstValue, [])?->toArray() === $this->get($model, $key, $secondValue, [])?->toArray();
    }

    protected function isAbstractClassCast(): bool
    {
        $dataClass = $this->dataConfig->getDataClass($this->dataClass);

        return $dataClass->isAbstract && $dataClass->transformable && ! $dataClass->propertyMorphable;
    }
}
