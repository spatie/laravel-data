---
title: In Packages
weight: 18
---

## Testing

When you're developing a package, running into config access issues is quite common, especially when using the magic from factory method, which won't resolve the factory instance without the config. Simply include our Provider in your package's TestCase, and you're all set.

```php
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelDataServiceProvider::class,
            // Register additional service providers to use with your tests
        ];
    }
}
``` 
