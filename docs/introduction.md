---
title: Introduction
weight: 1
---

This package enables the creation of rich data objects which can be used in various ways. Using this package you only need to describe your data once:

- instead of a form request, you can use a data object
- instead of an API transformer, you can use a data object
- instead of manually writing a typescript definition, you can use... 🥁 a data object

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

By extending from `Data` you enable a lot of new functionality like:

- Automatically transforming data objects into resources (like the Laravel API resources)
- Transform only the requested parts of data objects with lazy properties
- Automatically creating data objects from request data and validating them
- Automatically resolve validation rules for properties within a data object
- Make it possible to construct a data object from any type you want
- Add support for automatically validating data objects when creating them
- Generate TypeScript definitions from your data objects you can use on the frontend
- Save data objects as properties of an Eloquent model
- And a lot more ...

Why would you be using this package?

- You can be sure that data is typed when it leaves your app and comes back again from the frontend which makes a lot less errors
- You don't have to write the same properties three times (in a resource, in a data transfer object and in request validation)
- You need to write a lot less of validation rules because they are obvious through PHP's type system
- You get TypeScript versions of the data objects for free

Let's dive right into it!

## Are you a visual learner?

In this talk, given at Laracon US, you'll see [an introduction to Laravel Data](https://www.youtube.com/watch?v=CrO_7Df1cBc).

## We have badges!

<section class="article_badges">
    <a href="https://github.com/spatie/laravel-data/releases"><img src="https://img.shields.io/github/release/spatie/laravel-data.svg?style=flat-square" alt="Latest Version"></a>
    <a href="https://github.com/spatie/laravel-data/blob/main/LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License"></a>
    <a href="https://packagist.org/packages/spatie/laravel-data"><img src="https://img.shields.io/packagist/dt/spatie/laravel-data.svg?style=flat-square" alt="Total Downloads"></a>
</section>
