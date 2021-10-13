---
title: Quickstart weight: 2
---

In this quickstart we'll quite you through the most important functionalities of the package, we start by installing the
package:

```bash
composer require spatie/laravel-data
```

We're going to create a blog with different posts so let's get started with the `PostData` object. A post has a title,
content, status and a date when it was published:

```php
class PostData extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        public PostStatus $status,
        public ?CarbonImmutable $published_at
    ) {
    }
}
```

The only requirement for using the package is that you extend your data objects from the base `Data` object. We add the
requirements for a post as public properties (in this example we use PHP 8.0 newest feature: constructor promoted
properties, but you can also use simple class properties) and a constructor method that can fill them in.

The `PostStatus` is an enum using the [spatie/enum](https://github.com/spatie/enum) package:

```php
/**
 * @method static self draft()
 * @method static self published()
 * @method static self archived()
 */
class PostStatus extends Enum
{

}
```

We store this `PostData` object as `app/Data/PostData.php` so we have all our data objects bundled in one directory, but
you're free store them wherever you want within your application.

We can now create this object just like any plain PHP object:

```php
$post = new PostData(
    'Hello laravel-data',
    'This is an introduction post for the new package',
    PostStatus::published(),
    CarbonImmutable::now()
);
```

The package also allows you to create these data objects from any type, for example an array:

```php
$post = PostData::from([
    'title' => 'Hello laravel-data',
    'content' => 'This is an introduction post for the new package',
    'status' => PostStatus::published(),
    'published_at' => CarbonImmutable::now(),
]);
```

Now let's say we have a Laravel request coming from the front with these properties, our controller would then look like
this:

```php
public function __invoke(Request $request)
{
    $post = PostData::from([
        'title' => $request->input('title'),
        'content' => $request->input('content'),
        'status' => PostStatus::from($request->input('status')),
        'published_at' => CarbonImmutable::createFromFormat(DATE_ATOM, $request->input('published_at')),
    ]);
}
```

That's a lot of code just to fill a data object it would be a lot nicer if we could do this:

```php
public function __invoke(Request $request)
{
    $post = PostData::from($request);
}
```

But this throws the following exception:

> TypeError: App\Data\PostData::__construct(): Argument #3 ($status) must be of type App\Enums\PostStatus, string given

Because the status property expects an `PostStatus` enum object, but it gets a string. We can fix this by implementing a
cast for enums:

```php
class PostStatusCast implements Cast
{
    public function cast(DataProperty $property, mixed $value): PostStatus
    {
        return PostStatus::from($value);
    }
}
```

And tell the package to always use this cast when trying to create a `PostData` object:

```php
class PostData extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        #[WithCast(PostStatusCast::class)]
        public PostStatus $status,
        public ?CarbonImmutable $published_at
    ) {
    }
}
```

It is possible to write more powerful casts that would work for any type of enum but we're not going to handle this for
now in the quickstart.

Now when we send the following payload to the controller:

```json
{
    "title" : "Hello laravel-data",
    "content" : "This is an introduction post for the new package",
    "status" : "published",
    "published_at" : "2021-09-24T13:31:20+00:00"
}
```

And we get a filled in `PostData` object, neat! But how did the package convert the `published_at` string into a `CarbonImmutable` object? It is possible to define global casts within the `data.php` application config file. These casts will be used on data objects if no other casts can be found. By default the global casts list looks like this:

```php
'casts' => [
    DateTimeInterface::class => Spatie\LaravelData\Casts\DateTimeInterfaceCast::class,
],
```

Which means that if a class property is of type `DateTime`, `Carbon`, `CarbonImmutable`, ... it will be automatically converted.

There is a lot more to learn about casting further in this documentation!

Continuing with our example, what if we have a `Post` Eloquent model like this:

```php
class Post extends Model
{

    protected $dates = [
        'published_at'
    ];
}
```
