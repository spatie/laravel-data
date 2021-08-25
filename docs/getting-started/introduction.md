---
title: Introduction
weight: 1
---

A `laravel-data` specific object is just a regular PHP object that extends from `Data`:

```php
use Spatie\LaravelData\Data;

class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
}
```

This documentation is split up in three chapters:

1. How to create a data object
2. How to transform a data object into a resource that can be sent to the frontend
3. Everything not covered in the two previous chapters
