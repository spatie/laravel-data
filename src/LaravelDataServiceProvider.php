<?php

namespace Spatie\LaravelData;

use Livewire\Livewire;
use ReflectionMethod;
use Spatie\LaravelData\Attributes\DataValidationContext;
use Spatie\LaravelData\Commands\DataMakeCommand;
use Spatie\LaravelData\Commands\DataStructuresCacheCommand;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Resolvers\ContextResolver;
use Spatie\LaravelData\Support\Caching\DataStructureCache;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\Livewire\LivewireDataCollectionSynth;
use Spatie\LaravelData\Support\Livewire\LivewireDataSynth;
use Spatie\LaravelData\Support\Validation\ValidationContextManager;
use Spatie\LaravelData\Support\VarDumper\VarDumperManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelDataServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-data')
            ->hasCommand(DataMakeCommand::class)
            ->hasCommand(DataStructuresCacheCommand::class)
            ->hasConfigFile('data');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(
            DataStructureCache::class,
            fn () => new DataStructureCache(config('data.structure_caching.cache'))
        );

        $this->app->singleton(
            DataConfig::class,
            function () {
                if (config('data.structure_caching.enabled') === false || $this->app->runningUnitTests()) {
                    return DataConfig::createFromConfig(config('data'));
                }

                return $this->app->make(DataStructureCache::class)->getConfig() ?? DataConfig::createFromConfig(config('data'));
            }
        );

        $this->app->singleton(ContextResolver::class);

        $this->app->singleton(ValidationContextManager::class);

        $this->app->beforeResolving(BaseData::class, function ($class, $parameters, $app) {
            if ($app->has($class)) {
                return;
            }

            $this->resolveValidationContextFromRoute($app);

            $app->bind(
                $class,
                fn ($container) => $class::from($container['request'])
            );
        });

        if (config('data.livewire.enable_synths') && class_exists(Livewire::class)) {
            $this->registerLivewireSynths();
        }
    }

    protected function registerLivewireSynths(): void
    {
        Livewire::propertySynthesizer(LivewireDataSynth::class);
        Livewire::propertySynthesizer(LivewireDataCollectionSynth::class);
    }

    protected function resolveValidationContextFromRoute($app): void
    {
        $route = $app['request']->route();

        if ($route === null) {
            return;
        }

        $action = $route->getAction('uses');

        if (! is_string($action) || ! str_contains($action, '@')) {
            return;
        }

        [$controller, $method] = explode('@', $action);

        if (! class_exists($controller) || ! method_exists($controller, $method)) {
            return;
        }

        $reflectionMethod = new ReflectionMethod($controller, $method);
        $attributes = $reflectionMethod->getAttributes(DataValidationContext::class);

        if (empty($attributes)) {
            return;
        }

        $contextAttribute = $attributes[0]->newInstance();

        $app->make(ValidationContextManager::class)->setContext($contextAttribute->context);
    }

    public function packageBooted(): void
    {
        $enableVarDumperCaster = match (config('data.var_dumper_caster_mode')) {
            'enabled' => true,
            'development' => $this->app->environment('local', 'testing'),
            default => false,
        };

        if ($enableVarDumperCaster) {
            (new VarDumperManager)->initialize();
        }

        if (method_exists($this, 'optimizes')) {
            $this->optimizes(
                optimize: 'data:cache-structures',
                key: 'laravel-data',
            );
        }
    }
}
