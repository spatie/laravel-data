---
title: Performance
weight: 15
---

Laravel Data is a powerful package that leverages PHP reflection to infer as much information as possible. While this approach provides a lot of benefits, it does come with a minor performance overhead. This overhead is typically negligible during development, but it can become noticeable in a production environment with a large number of data objects.

Fortunately, Laravel Data is designed to operate efficiently without relying on reflection. It achieves this by allowing you to cache the results of its complex analysis. This means that the performance cost is incurred only once, rather than on every request. By caching the analysis results before deploying your application to production, you ensure that a pre-analyzed, cached version of the data objects is used, significantly improving performance.

## Caching

Laravel Data provides a command to cache the analysis results of your data objects. This command will analyze all of your data objects and store the results in a Laravel cache of your choice:

```
php artisan data:cache-structures
```

That's it, the command will search for all the data objects in your application and cache the analysis results. Be sure to always run this command after creating or modifying a data object or when deploying your application to production.

## Configuration

The caching mechanism can be configured in the `data.php` config file. By default, the cache store is set to the default cache store of your application. You can change this to any other cache driver supported by Laravel. A prefix can also be set for the cache keys stored:

```php
'structure_caching' => [
    'cache' => [
        'store' => 'redis',
        'prefix' => 'laravel-data',
    ],
],
```

To find the data classes within your application, we're using the [php-structure-discoverer](https://github.com/spatie/php-structure-discoverer) package. This package allows you to configure the directories that will be searched for data objects. By default, the `app/data` directory is searched recursively. You can change this to any other directory or directories:

```php
'structure_caching' => [
    'directories' => [
        app_path('Data'),
    ],
],
```

Structure discoverer uses reflection (enabled by default) or a PHP parser to find the data objects. You can disable the reflection-based discovery and thus use the PHP parser discovery as such:

```php
'structure_caching' => [
    'reflection_discovery' => [
        'enabled' => false,
    ],
],
```

Since we cannot depend on reflection, we need to tell the parser what data objects are exactly and where to find them. This can be done by adding the laravel-data directory to the config directories:

```php
'structure_caching' => [
    'directories' => [
        app_path('Data'),
        base_path('vendor/spatie/laravel-data/src'),
    ],
],
```

When using reflection discovery, the base directory and root namespace can be configured as such if you're using a non-standard directory structure or namespace

```php
'structure_caching' => [
    'reflection_discovery' => [
        'enabled' => true,
        'base_path' => base_path(),
        'root_namespace' => null,
    ],
],
```

The caching mechanism can be disabled by setting the `enabled` option to `false`:

```php
'structure_caching' => [
    'enabled' => false,
],
```

You can read more about reflection discovery [here](https://github.com/spatie/php-structure-discoverer#parsers).

## Testing

When running tests, the cache is automatically disabled. This ensures that the analysis results are always up-to-date during development and testing. And that the cache won't interfere with your caching mocks.
