---
title: Quickstart
weight: 2
---

In this quickstart we'll quite you through the most important functionalities of the package, we start by installing the package:

```bash
composer require spatie/laravel-data
```

We're going to create a blog with different posts so let's get started with the `PostData` object. A post has a title, content and a date when it was published:

```php
class PostData extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        public CarbonImmutable $published_at
    ) {
    }
}
```

The only requirement for using the package is that you extend your data objects from the base `Data` object. We add the requirements for a post as public properties (in this example we use PHP 8.0 newest feature: constructor promoted properties but you can also use simple class properties) and a constructor method that can fill them in.

We store this `PostData` object as `app/Data/PostData.php` so we have all our data objects bundled in one directory, but you're free store them wherever you want within your application.

We can now create this object just like any plain PHP object:

```php
$post = new PostData(
    'Hello laravel-data',
    'This is an introduction post for the new package',
    CarbonImmutable::now()
);
```

The package also allows you to create these data objects from any type, for example an array:

