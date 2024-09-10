<?php

namespace Spatie\LaravelData\Commands;

use Illuminate\Console\Command;
use ReflectionClass;
use Spatie\LaravelData\Support\Caching\CachedDataConfig;
use Spatie\LaravelData\Support\Caching\DataClassFinder;
use Spatie\LaravelData\Support\Caching\DataStructureCache;
use Spatie\LaravelData\Support\Factories\DataClassFactory;

class DataStructuresCacheCommand extends Command
{
    protected $signature = 'data:cache-structures {--show-classes : Show the data classes cached}';

    protected $description = 'Cache the internal data structures';

    public function handle(
        DataStructureCache $dataStructureCache,
        DataClassFactory $dataClassFactory,
    ): void {
        if (config('data.structure_caching.enabled') === false) {
            $this->error('Data structure caching is not enabled');

            return;
        }

        $this->components->info('Caching data structures...');

        $dataClasses = DataClassFinder::fromConfig(config('data.structure_caching'))->classes();

        $cachedDataConfig = CachedDataConfig::createFromConfig(config('data'));

        $dataStructureCache->storeConfig($cachedDataConfig);

        $progressBar = $this->output->createProgressBar(count($dataClasses));

        foreach ($dataClasses as $dataClassString) {
            $dataClass = $dataClassFactory->build(new ReflectionClass($dataClassString));

            $dataClass->prepareForCache();

            $dataStructureCache->storeDataClass($dataClass);

            $progressBar->advance();
        }

        $progressBar->finish();

        $this->line(PHP_EOL);
        $this->line('Cached '.count($dataClasses).' data classes');

        if ($this->option('show-classes')) {
            $this->table(
                ['Data Class'],
                array_map(fn (string $dataClass) => [$dataClass], $dataClasses)
            );
        }
    }
}
