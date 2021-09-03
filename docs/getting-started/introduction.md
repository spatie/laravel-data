---
title: Introduction
weight: 1
---

This package tries to be a layer between the backend and the frontend code. By creating a structured data model that both sides must follow.

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

By extending from `Data` you know enable a lot of new functionality like:

- Automatically transforming data objects into api resources
- Automatically creating data objects from request data
- Make it possible to construct a data object from any type you want
- Add support for automatically
- Automatically creating TypeScript definitions 

This documentation is split up in four chapters:

1. This page and a quickstart that gives you a concrete example on how to use data objects
2. A section on how to create a data object
3. How to transform a data object into a resource that can be sent to the frontend
4. An advanced chapter 
