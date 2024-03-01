<?php

namespace Spatie\LaravelData\Support\Livewire;

use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\ContextableData;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\Lazy\LivewireLostLazy;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;

class LivewireDataSynth extends Synth
{
    protected DataConfig $dataConfig;

    public static string $key = 'ldo';

    public function __construct(ComponentContext $context, $path)
    {
        $this->dataConfig = app(DataConfig::class);

        parent::__construct($context, $path);
    }

    public static function match($target): bool
    {
        return $target instanceof BaseData && $target instanceof TransformableData;
    }

    public function get(&$target, $key): BaseData
    {
        return $target->{$key};
    }

    public function set(&$target, $key, $value): void
    {
        $target->{$key} = $value;
    }

    /**
     * @param callable(array-key, mixed):mixed $dehydrateChild
     */
    public function dehydrate(
        BaseData&TransformableData&ContextableData $target,
        callable $dehydrateChild
    ): array {
        $morph = $this->dataConfig->morphMap->getDataClassAlias($target::class) ?? $target::class;

        $payload = $target->transform(
            TransformationContextFactory::create()
                ->withPropertyNameMapping(false)
                ->withoutWrapping()
                ->withoutPropertyNameMapping()
                ->withoutValueTransformation()
        );

        foreach ($payload as $key => $value) {
            $payload[$key] = $dehydrateChild($key, $value);
        }

        return [
            $payload,
            ['morph' => $morph, 'context' => encrypt($target->getDataContext())],
        ];
    }

    /**
     * @param callable(array-key, mixed):mixed $hydrateChild
     */
    public function hydrate(
        array $value,
        array $meta,
        callable $hydrateChild
    ): BaseData {
        $morph = $meta['morph'];
        $context = decrypt($meta['context']);

        $dataClass = $this->dataConfig->morphMap->getMorphedDataClass($morph) ?? $morph;

        $payload = [];

        foreach ($this->dataConfig->getDataClass($dataClass)->properties as $name => $property) {
            if (array_key_exists($name, $value) === false && $property->type->lazyType) {
                $payload[$name] = new LivewireLostLazy($dataClass, $name);

                continue;
            }

            $payload[$name] = $hydrateChild($name, $value[$name]);
        }

        /** @var CreationContextFactory $factory */
        $factory = $dataClass::factory();

        $data = $factory
            ->withPropertyNameMapping(false)
            ->ignoreMagicalMethod('fromLivewire')
            ->withoutValidation()
            ->from($payload);

        $data->setDataContext($context);

        return $data;
    }
}
