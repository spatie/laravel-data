---
title: Quickstart
weight: 1
---

In this quickstart, we'll guide you through the most important functionalities of the package and how to use them. 

First, you should [install the package](https://spatie.be/docs/laravel-data/v3/installation-setup).

We're going to create a blog with different posts so let's get started with the `PostData` object. A post has a title, some content, a status and a date when it was published:

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

The only requirement for using the package is extending your data objects from the base `Data` object. We add the requirements for a post as public properties.

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

We store this `PostData` object as `app/Data/PostData.php`, so we have all our data objects bundled in one directory, but you're free to store them wherever you want within your application.

We can now create this a `PostData` object just like any plain PHP object:

```php
$post = new PostData(
    'Hello laravel-data',
    'This is an introduction post for the new package',
    PostStatus::published(),
    CarbonImmutable::now()
);
```

The package also allows you to create these data objects from any type, for example, an array:

```php
$post = PostData::from([
    'title' => 'Hello laravel-data',
    'content' => 'This is an introduction post for the new package',
    'status' => PostStatus::published(),
    'published_at' => CarbonImmutable::now(),
]);
```

## The make:data command

You can easily generate new data objects with the artisan command `make:data`:

```shell
php artisan make:data PostData
```

By default, this command puts data objects in the `App\Data` namespace, this can be changed as such:

```shell
php artisan make:data PostData --namespace=DataTransferObjects
```

## Using requests and casts

Now let's say we have a Laravel request coming from the front with these properties. Our controller would then look like
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

That's a lot of code just to fill a data object. It would be a lot nicer if we could do this:

```php
public function __invoke(Request $request)
{
    $post = PostData::from($request);
}
```

But this throws the following exception:

```
TypeError: App\Data\PostData::__construct(): Argument #3 ($status) must be of type App\Enums\PostStatus, string given
```

That's because the status property expects a `PostStatus` enum object, but it gets a string. We can fix this by implementing a cast for enums:

```php
class PostStatusCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $context): PostStatus
    {
        return PostStatus::from($value);
    }
}
```

And tell the package always to use this cast when trying to create a `PostData` object:

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

## Using global casts

Let's send the following payload to the controller:

```json
{
    "title" : "Hello laravel-data",
    "content" : "This is an introduction post for the new package",
    "status" : "published",
    "published_at" : "2021-09-24T13:31:20+00:00"
}
```

We get the `PostData` object populated with the values in the JSON payload, neat! But how did the package convert the `published_at` string into a `CarbonImmutable` object?

It is possible to define global casts within the `data.php` config file. These casts will be used on data objects if no other casts can be found.

By default, the global casts list looks like this:

```php
'casts' => [
    DateTimeInterface::class => Spatie\LaravelData\Casts\DateTimeInterfaceCast::class,
],
```

This means that if a class property is of type `DateTime`, `Carbon`, `CarbonImmutable`, ... it will be automatically converted.

You can read more about casting [here](/docs/laravel-data/v3/as-a-data-transfer-object/casts).

## Validation using form requests

Since we're working with requests, wouldn't it be cool to validate the data coming in from the request using the data object? Typically, you would create a request with a validator like this:

```php
class PostDataRequest extends FormRequest
{
    public function authorize()
    {
        return false;
    }

    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string'],
            'status' => ['required', 'string', 'in:draft,published,archived'],
            'published_at' => ['nullable', 'date']
        ];
    }
}
```

Thanks to PHP 8.0 attributes, we can completely omit this `PostDataRequest` and use the data object instead:

```php
class PostData extends Data
{
    public function __construct(
        #[Required, StringType, Max(200)]
        public string $title,
        #[Required, StringType]
        public string $content,
        #[Required, StringType, In(['draft', 'published', 'archived'])]
        public PostStatus $status,
        #[Nullable, Date]
        public ?CarbonImmutable $published_at
    ) {
    }
}
```

You can now inject the data object into your application, just like a Laravel form request:

```php
public function __invoke(PostData $data)
{
    dd($data); // a filled in data object
}
```

When the given data is invalid, a user will be redirected back with the validation errors in the error bag. If a validation occurs when making a JSON request, a 422 response will be returned with the validation errors.

Because our data object is so well-typed, we can even drop some validation rules since they can be automatically deduced:

```php
class PostData extends Data
{
    public function __construct(
        #[Max(200)]
        public string $title,
        public string $content,
        #[StringType, In(['draft', 'published', 'archived'])]
        public PostStatus $status,
        #[Date]
        public ?CarbonImmutable $published_at
    ) {
    }
}
```

There's still much more you can do with validating data objects. Read more about it [here](/docs/laravel-data/v3/as-a-data-transfer-object/request-to-data-object#validating-a-request).

## Working with Eloquent models

In our application, we have a `Post` Eloquent model:

```php
class Post extends Model
{
    protected $fillable = '*';

    protected $casts = [
        'status' => PostStatus::class
    ];

    protected $dates = [
        'published_at'
    ];
}
```

Thanks to the casts we added earlier, this can be quickly transformed into a `PostData` object:

```php
PostData::from(Post::findOrFail($id));
```

## Customizing the creation of a data object

It is even possible to manually define how such a model is mapped onto a data object. To demonstrate that, we will take a completely different example that shows the strength of the `from` method.

What if we would like to support to create posts via an email syntax like this:

```
title|status|content
```

Creating a `PostData` object would then look like this:

```php
PostData::from('Hello laravel-data|draft|This is an introduction post for the new package');
```

To make this work, we need to add a magic creation function within our data class:

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
    
    public static function fromString(string $post): PostData
    {
        $fields = explode('|', $post);
    
        return new self(
            $fields[0],
            $fields[2],
            PostStatus::from($fields[1]),
            null
        );
    }
}
```

Magic creation methods allow you to create data objects from any type by passing them to the `from` method of a data
object, you can read more about it [here](/laravel-data/v3/as-a-data-transfer-object/creating-a-data-object#magical-creation).

It can be convenient to transform more complex models than our `Post` into data objects because you can decide how a model
would be mapped onto a data object.

## Nesting data objects and collections

Now that we have a fully functional post data object. We're going to create a new data object, `AuthorData` that will store the name of an author and a collection of posts the author wrote:

```php
class AuthorData extends Data
{
    public function __construct(
        public string $name,
        /** @var \App\Data\PostData[] */
        public DataCollection $posts
    ) {
    }
}
```

Instead of using an array to store all the posts, we use a `DataCollection`. This will be very useful later on! We now can create an author object as such:

```php
new AuthorData(
    'Ruben Van Assche',
    PostData::collection([
        new PostData('Hello laravel-data', 'This is an introduction post for the new package', PostStatus::draft(), null),
        new PostData('What is a data object', 'How does it work?', PostStatus::draft(), null),
    ])
);
```

But it is also possible to create an author using the `from` method:

```php
AuthorData::from([
    'name' => 'Ruben Van Assche',
    'posts' => [
        [
            'title' => 'Hello laravel-data',
            'content' => 'This is an introduction post for the new package',
            'status' => 'draft',
            'published_at' => null,
        ],
        [
            'title' => 'What is a data object',
            'content' => 'How does it work',
            'status' => 'draft',
            'published_at' => null,
        ],
    ],
]);
```

The data object is smart enough to convert an array of posts into a data collection of post data. Mapping data coming from the frontend was never that easy!

You can do a lot more with data collections. Read more about it [here](/docs/laravel-data/v3/as-a-data-transfer-object/collections).

## Usage in controllers

We've been creating many data objects from all sorts of values, time to change course and go the other way around and start transforming data objects into arrays.

Let's say we have an API controller that returns a post:

```php
public function __invoke()
{
    return new PostData(
        'Hello laravel-data',
        'This is an introduction post for the new package',
        PostStatus::published(),
        CarbonImmutable::create(2020, 05, 16),
    );
}
```

By returning a data object in a controller, it is automatically converted to JSON:

```json
{
    "title" : "Hello laravel-data",
    "content" : "This is an introduction post for the new package",
    "status" : "published",
    "published_at" : "2021-09-24T13:31:20+00:00"
}
```

You can also easily convert a data object into an array as such:

```php
$postData->toArray();
```

Which gives you an array like this:

```php
[
    'title' => 'Hello laravel-data',
    'content' => 'This is an introduction post for the new package',
    'status' => 'published',
    'published_at' => '2021-09-24T13:31:20+00:00',
]
```

It is possible to transform a data object into an array and keep complex types like the `PostStatus` and `CarbonImmutable`:

```php
$postData->all();
```

This will give the following array:

```php
[
    'title' => 'Hello laravel-data',
    'content' => 'This is an introduction post for the new package',
    'status' => PostStatus::published(),
    'published_at' => CarbonImmutable::create(2020, 05, 16),
]
```

As you can see, if we transform a data object to JSON, the `CarbonImmutable` published at date is transformed into a string.

## Using transformers

A few sections ago, we used casts to convert simple types into complex types. Transformers work the other way around. They transform complex types into simple ones and transform a data object into a simpler structure like an array or JSON.

Just like the `DateTimeInterfaceCast` we also have a `DateTimeInterfaceTransformer` that will convert `DateTime`, `Carbon`, ... objects into strings.

This `DateTimeInterfaceTransformer` is registered in the `data.php` config file and will automatically be used when a data object needs to transform a `DateTimeInterface` object:

```php
'transformers' => [
    DateTimeInterface::class => \Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer::class,
    \Illuminate\Contracts\Support\Arrayable::class => \Spatie\LaravelData\Transformers\ArrayableTransformer::class,
],
```

The value of the `PostStatus` enum is automatically transformed to a string because it implements `JsonSerializable`, but it is perfectly possible to write a custom transformer for it just like we built our custom cast a few sections ago:

```php
class PostStatusTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value): string
    {
        /** @var \App\Enums\PostStatus $value */
        return $value->value;
    }
}
```

We now can use this transformer in the data object like this:

```php
class PostData extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        #[WithTransformer(PostStatusTransformer::class)]
        public PostStatus $status,
        public ?CarbonImmutable $published_at
    ) {
    }
}
```

You can read a lot more about transformers [here](/docs/laravel-data/v3/as-a-resource/transformers).

## Generating a blueprint

We now can send our posts as JSON to the front, but what if we want to create a new post? When using Inertia, for example, we might need an empty blueprint object like this that the user could fill in:

```json
{
    "title" : null,
    "content" : null,
    "status" : null,
    "published_at" : null
}
```

This can be done with the `empty` method, which will return an empty array following the structure of your data object:

```php
PostData::empty();
```

This will return the following array:

```php
[
  'title' => null,
  'content' => null,
  'status' => null,
  'published_at' => null,
]
```

It is possible to set the status of the post to draft by default:

```php
PostData::empty([
    'status' => 'draft';
]);
```

## Lazy properties

For the last section of this quickstart, we're going to take a look at the `AuthorData` object again:

```php
class AuthorData extends Data
{
    public function __construct(
        public string $name,
        /** @var \App\Data\PostData[] */
        public DataCollection $posts
    ) {
    }
}
```

Let's say that we want to compose a list of all the authors. What if we had 100+ authors who have all written more than 100+ posts:

```json
[
    {
        "name" : "Ruben Van Assche",
        "posts" : [
            {
                "title" : "Hello laravel-data",
                "content" : "This is an introduction post for the new package",
                "status" : "published",
                "published_at" : "2021-09-24T13:31:20+00:00"
            }

            // ...
        ]
    },
    {
        "name" : "Freek van der Herten",
        "posts" : [
            {
                "title" : "Hello laravel-event-sourcing",
                "content" : "This is an introduction post for the new package",
                "status" : "published",
                "published_at" : "2021-09-24T13:31:20+00:00"
            }

            // ...
        ]
    }

    // ...
]
```

As you can see, this will quickly be a large set of data we would send over JSON, which we don't want to do.  Since each author includes not only his name but also all the posts he has written.

In the end, we only want something like this:

```json
[
    {
        "name" : "Ruben Van Assche"
    },
    {
        "name" : "Freek van der Herten"
    }

    // ...
]
```

This can be achieved with lazy properties. Lazy properties are only added to a payload when we explicitly ask it. They work with closures that are executed only when this is required:

```php
class AuthorData extends Data
{
    public function __construct(
        public string $name,
        /** @var \App\Data\PostData[] */
        public DataCollection|Lazy $posts
    ) {
    }

    public static function fromModel(Author $author)
    {
        return new self(
            $author->name,
            Lazy::create(fn() => PostData::collection($author->posts))
        );
    }
}
```

When we now create a new author:

```php
$author = Author::create([
    'name' => 'Ruben Van Assche'
]);

$author->posts()->create([        
    [
        'title' => 'Hello laravel-data',
        'content' => 'This is an introduction post for the new package',
        'status' => 'draft',
        'published_at' => null,
    ]
]);

AuthorData::from($author);
```

Transforming it into JSON looks like this:

```json
{
    "name" : "Ruben Van Assche"
}
```

If we want to include the posts, the only thing we need to do is this:

```php
$postData->include('posts')->toJson();
```

This will result in this JSON:

```json
{
    "name" : "Ruben Van Assche",
    "posts" : [
        {
            "title" : "Hello laravel-data",
            "content" : "This is an introduction post for the new package",
            "status" : "published",
            "published_at" : "2021-09-24T13:31:20+00:00"
        }
    ]
}
```

Let's take this one step further. What if we want to be able only to include the title of each post? We can do this by making all the other properties within the post data object also lazy:

```php
class PostData extends Data
{
    public function __construct(
        public string|Lazy $title,
        public string|Lazy $content,
        #[WithCast(PostStatusCast::class)]
        public PostStatus|Lazy $status,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'd-M')]
        public CarbonImmutable|Lazy|null $published_at
    ) {
    }

    public static function fromModel(Post $post): PostData
    {
        return new self(
            Lazy::create(fn() => $post->title),
            Lazy::create(fn() => $post->content),
            Lazy::create(fn() => $post->status),
            Lazy::create(fn() => $post->published_at)
        );
    }
}
```

Now the only thing we need to do is including the title:

```php
$postData->include('posts.title')->toJson();
```

This will result in this JSON:

```json
{
    "name" : "Ruben Van Assche",
    "posts" : [
        {
            "title" : "Hello laravel-data"
        }
    ]
}
```

If we also want to include the status, we can do the following:

```php
$postData->include('posts.{title,status}')->toJson();
```

It is also possible to include all properties of the posts like this:

```php
$postData->include('posts.*')->toJson();
```

You can do quite a lot with lazy properties like including them:

- when a model relation is loaded like Laravel API resources
- when they are requested in the URL query
- by default with an option to exclude them

And a lot more. You can read all about it [here](/docs/laravel-data/v3/as-a-resource/lazy-properties).

So that's it, a quick overview of this package. There's still a lot more you can do with data objects like:

- [casting](/docs/laravel-data/v3/advanced-usage/eloquent-casting) them into Eloquent models
- [transforming](/docs/laravel-data/v3/advanced-usage/typescript) the structure to typescript
- [working](https://spatie.be/docs/laravel-data/v3/as-a-data-transfer-object/collections) with `DataCollections`
- you find it all in these docs
