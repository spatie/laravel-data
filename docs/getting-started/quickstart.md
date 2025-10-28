---
title: Quickstart
weight: 1
---

In this quickstart, we'll guide you through the most important functionalities of the package and how to use them.

First, you should [install the package](/docs/laravel-data/v4/installation-setup).

We will create a blog with different posts, so let's start with the `PostData` object. A post has a title, some content, a status and a date when it was published:

```php
use Spatie\LaravelData\Data;

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

Extending your data objects from the base `Data` object is the only requirement for using the package. We add the requirements for a post as public properties.

The `PostStatus` is a native enum:

```php
enum PostStatus: string
{
    case draft = 'draft';
    case published = 'published';
    case archived = 'archived';
}
```

We store this `PostData` object as `app/Data/PostData.php`, so we have all our data objects bundled in one directory, but you're free to store them wherever you want within your application.

Tip: you can also quickly make a data object using the CLI: `php artisan make:data Post`, it will create a file `app/Data/PostData.php`.

We can now create a `PostData` object just like any plain PHP object:

```php
$post = new PostData(
    'Hello laravel-data',
    'This is an introduction post for the new package',
    PostStatus::published,
    CarbonImmutable::now()
);
```

The package also allows you to create these data objects from any type, for example, an array:

```php
$post = PostData::from([
    'title' => 'Hello laravel-data',
    'content' => 'This is an introduction post for the new package',
    'status' => PostStatus::published,
    'published_at' => CarbonImmutable::now(),
]);
```

Or a `Post` model with the required properties:

```php
class Post extends Model
{
    protected $guarded = [];

    protected $casts = [
        'status' => PostStatus::class,
        'published_at' => 'immutable_datetime',
    ];
}
```

Can be quickly transformed into a `PostData` object:

```php
PostData::from(Post::findOrFail($id));
```

## Using requests

Let's say we have a Laravel request coming from the frontend with these properties. Our controller would then validate these properties, and then it would store them in a model; this can be done as such:

```php
class DataController
{
    public function __invoke(Request $request)
    {
        $request->validate($this->rules());

        $postData = PostData::from([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'status' => $request->enum('status', PostStatus::class),
            'published_at' => $request->has('published_at')
                ? CarbonImmutable::createFromFormat(DATE_ATOM, $request->input('published_at'))
                : null,
        ]);

        Post::create($postData->toArray());

        return redirect()->back();
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'content' => ['required', 'string'],
            'status' => ['required', new Enum(PostStatus::class)],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
```

That's a lot of code to fill a data object, using laravel data we can remove a lot of code:

```php
class DataController
{
    public function __invoke(PostData $postData)
    {
        Post::create($postData->toArray());

        return redirect()->back();
    }
}
```

Let's see what's happening:

1) Laravel boots up, and the router directs to the `DataController`
2) Because we're injecting `PostData`, two things happen
    - `PostData` will generate validation rules based on the property types and validate the request
    - The `PostData` object is automatically created from the request
3) We're now in the `__invoke` method with a valid `PostData` object

You can always check the generated validation rules of a data object like this:

```php
class DataController
{
    public function __invoke(Request $request)
    {
        dd(PostData::getValidationRules($request->toArray()));
    }
}
```

Which provides us with the following set of rules:

```php
array:4 [
  "title" => array:2 [
    0 => "required"
    1 => "string"
  ]
  "content" => array:2 [
    0 => "required"
    1 => "string"
  ]
  "status" => array:2 [
    0 => "required"
    1 => Illuminate\Validation\Rules\Enum {
      #type: "App\Enums\PostStatus"
    }
  ]
  "published_at" => array:1 [
    0 => "nullable"
  ]
]
```

As you can see, we're missing the `date` rule on the `published_at` property. By default, this package will automatically generate the following rules:

- `required` when a property cannot be `null`
- `nullable` when a property can be `null`
- `numeric` when a property type is `int`
- `string` when a property type is `string`
- `boolean` when a property type is `bool`
- `numeric` when a property type is `float`
- `array` when a property type is `array`
- `enum:*` when a property type is a native enum

You can read more about the process of automated rule generation [here](/docs/laravel-data/v4/as-a-data-transfer-object/request-to-data-object#content-automatically-inferring-rules-for-properties-1).

We can easily add the date rule by using an attribute to our data object:

```php
class PostData extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        public PostStatus $status,
        #[Date]
        public ?CarbonImmutable $published_at
    ) {
    }
}
```

Now our validation rules look like this:

```php
array:4 [
  "title" => array:2 [
    0 => "required"
    1 => "string"
  ]
  "content" => array:2 [
    0 => "required"
    1 => "string"
  ]
  "status" => array:2 [
    0 => "required"
    1 => Illuminate\Validation\Rules\Enum {
      #type: "App\Enums\PostStatus"
    }
  ]
  "published_at" => array:2 [
    0 => "nullable"
    1 => "date"
  ]
]
```

There are [tons](/docs/laravel-data/v4/advanced-usage/validation-attributes) of validation rule attributes you can add to data properties. There's still much more you can do with validating data objects. Read more about it [here](/docs/laravel-data/v4/as-a-data-transfer-object/request-to-data-object#validating-a-request).

Tip: By default, when creating a data object in a non request context, no validation is executed:

```php
$post = PostData::from([
	// As long as PHP accepts the values for the properties, the object will be created
]);
```

You can create validated objects without requests like this:

```php
$post = PostData::validateAndCreate([
	// Before creating the object, each value will be validated
]);
```

## Casting data

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

It is possible to define casts within the `data.php` config file. By default, the casts list looks like this:

```php
'casts' => [
    DateTimeInterface::class => Spatie\LaravelData\Casts\DateTimeInterfaceCast::class,
],
```

This code means that if a class property is of type `DateTime`, `Carbon`, `CarbonImmutable`, ... it will be automatically cast.

You can create your own casts; read more about it [here](/docs/laravel-data/v4/advanced-usage/creating-a-cast).

### Local casts

Sometimes you need one specific cast in one specific data object; in such a case defining a local cast specific for the data object is a good option.

Let's say we have an `Image` class:

```php
class Image
{
    public function __construct(
        public string $file,
        public int $size,
    ) {
    }
}
```

There are two options how an `Image` can be created:

a) From a file upload
b) From an array when the image has been stored in the database

Let's create a cast for this:

```php
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Support\DataProperty;
use Str;

class ImageCast implements Cast
{
        public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): Image|Uncastable
    {
        // Scenario A
        if ($value instanceof UploadedFile) {
            $filename = $value->store('images', 'public');

            return new Image(
                $filename,
                $value->getSize(),
            );
        }

        // Scenario B
        if (is_array($value)) {
            return new Image(
                $value['filename'],
                $value['size'],
            );
        }

        return Uncastable::create();
    }
}

```

Ultimately, we return `Uncastable`, telling the package to try other casts (if available) because this cast cannot cast the value.

The last thing we need to do is add the cast to our property. We use the `WithCast` attribute for this:

```php
class PostData extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        public PostStatus $status,
        #[WithCast(ImageCast::class)]
        public ?Image $image,
        #[Date]
        public ?CarbonImmutable $published_at
    ) {
    }
}
```

You can read more about casting [here](/docs/laravel-data/v4/as-a-data-transfer-object/casts).

## Customizing the creation of a data object

We've seen the powerful `from` method on data objects, you can throw anything at it, and it will cast the value into a data object. But what if it can't cast a specific type, or what if you want to change how a type is precisely cast into a data object?

It is possible to manually define how a type is converted into a data object. What if we would like to support to create posts via an email syntax like this:

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
        public PostStatus $status,
        #[WithCast(ImageCast::class)]
        public ?Image $image,
        #[Date]
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
            null,
            null
        );
    }
}
```

Magic creation methods allow you to create data objects from any type by passing them to the `from` method of a data
object, you can read more about it [here](/docs/laravel-data/v4/as-a-data-transfer-object/creating-a-data-object#content-magical-creation).

It can be convenient to transform more complex models than our `Post` into data objects because you can decide how a model
would be mapped onto a data object.

## Nesting data objects and arrays of data objects

Now that we have a fully functional post-data object. We're going to create a new data object, `AuthorData`, that will store the name of an author and an array of posts the author wrote:

```php
use Spatie\LaravelData\Attributes\DataCollectionOf;

class AuthorData extends Data
{
    /**
    * @param array<int, PostData> $posts
    */
    public function __construct(
        public string $name,
        public array $posts
    ) {
    }
}
```

Notice that we've typed the `$posts` property as an array of `PostData` objects using a docblock.  This will be very useful later on! The package always needs to know what type of data objects are stored in an array. Of course, when you're storing other types then data objects this is not required but recommended.

We can now create an author object as such:

```php
new AuthorData(
    'Ruben Van Assche',
    PostData::collect([
        [
            'title' => 'Hello laravel-data',
            'content' => 'This is an introduction post for the new package',
            'status' => PostStatus::draft,
        ],
        [
            'title' => 'What is a data object',
            'content' => 'How does it work',
            'status' => PostStatus::published,
        ],
    ])
);
```

As you can see, the `collect` method can create an array of the `PostData` objects.

But there's another way; thankfully, our `from` method makes this process even more straightforward:

```php
AuthorData::from([
    'name' => 'Ruben Van Assche',
    'posts' => [
        [
            'title' => 'Hello laravel-data',
            'content' => 'This is an introduction post for the new package',
            'status' => PostStatus::draft,
        ],
        [
            'title' => 'What is a data object',
            'content' => 'How does it work',
            'status' => PostStatus::published,
        ],
    ],
]);
```

The data object is smart enough to convert an array of posts into an array of post data. Mapping data coming from the front end was never that easy!

### Nesting objects

Nesting an individual data object into another data object is perfectly possible. Remember the `Image` class we created? We needed a cast for it, but it is a perfect fit for a data object; let's create it:

```php
class ImageData extends Data
{
    public function __construct(
        public string $filename,
        public string $size,
    ) {
    }

    public static function fromUploadedImage(UploadedFile $file): self
    {
        $stored = $file->store('images', 'public');

        return new ImageData(
            url($stored),
            $file->getSize(),
        );
    }
}
```

In our `ImageCast`, the image could be created from a file upload or an array; we'll handle that first case with the `fromUploadedImage` magic method. Because `Image` is now `ImageData,` the second case is automatically handled by the package, neat!

We'll update our `PostData` object as such:

```php
class PostData extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        public PostStatus $status,
        public ?ImageData $image,
        #[Date]
        public ?CarbonImmutable $published_at
    ) {
    }
}
```

Creating a `PostData` object now can be done as such:

```php
return PostData::from([
    'title' => 'Hello laravel-data',
    'content' => 'This is an introduction post for the new package',
    'status' => PostStatus::published,
    'image' => [
        'filename' => 'images/8JQtgd0XaPtt9CqkPJ3eWFVV4BAp6JR9ltYAIKqX.png',
        'size' => 16524
    ],
    'published_at' => CarbonImmutable::create(2020, 05, 16),
]);
```

When we create the `PostData` object in a controller as such:

```php
public function __invoke(PostData $postData)
{
    return $postData;
}
```

We get a validation error:

```json
{
    "message": "The image must be an array. (and 2 more errors)",
    "errors": {
        "image": [
            "The image must be an array."
        ],
        "image.filename": [
            "The image.filename field is required."
        ],
        "image.size": [
            "The image.size field is required."
        ]
    }
}
```

This is a neat feature of data; it expects a nested `ImageData` data object when being created from the request, an array with the keys `filename` and `size`.

We can avoid this by manually defining the validation rules for this property:

```php
class PostData extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        public PostStatus $status,
        #[WithoutValidation]
        public ?ImageData $image,
        #[Date]
        public ?CarbonImmutable $published_at
    ) {
    }

    public static function rules(ValidationContext $context): array
    {
        return [
            'image' => ['nullable', 'image'],
        ];
    }
}
```

In the `rules` method, we explicitly define the rules for `image .`Due to how this package validates data, the nested fields `image.filename` and `image.size` would still generate validation rules, thus failing the validation. The `#[WithoutValidation]` explicitly tells the package only the use the custom rules defined in the `rules` method.

## Usage in controllers

We've been creating many data objects from all sorts of values, time to change course and go the other way around and start transforming data objects into arrays.

Let's say we have an API controller that returns a post:

```php
public function __invoke()
{
    return PostData::from([
        'title' => 'Hello laravel-data',
        'content' => 'This is an introduction post for the new package',
        'status' => PostStatus::published,
        'published_at' => CarbonImmutable::create(2020, 05, 16),
    ]);
}
```

By returning a data object in a controller, it is automatically converted to JSON:

```json
{
    "title": "Hello laravel-data",
    "content": "This is an introduction post for the new package",
    "status": "published",
    "image": null,
    "published_at": "2020-05-16T00:00:00+00:00"
}
```

You can also easily convert a data object into an array as such:

```php
$postData->toArray();
```

Which gives you an array like this:

```php
array:5 [
  "title" => "Hello laravel-data"
  "content" => "This is an introduction post for the new package"
  "status" => "published"
  "image" => null
  "published_at" => "2020-05-16T00:00:00+00:00"
]
```

It is possible to transform a data object into an array and keep complex types like the `PostStatus` and `CarbonImmutable`:

```php
$postData->all();
```

This will give the following array:

```php
array:5 [ 
  "title" => "Hello laravel-data"
  "content" => "This is an introduction post for the new package"
  "status" => App\Enums\PostStatus {
    +name: "published"
    +value: "published"
  }
  "image" => null
  "published_at" => Carbon\CarbonImmutable {
  		... 
  }
]

```

As you can see, if we transform a data object to JSON, the `CarbonImmutable` published at date is transformed into a string.

## Using transformers

A few sections ago, we used casts to cast simple types into complex types. Transformers work the other way around. They transform complex types into simple ones and transform a data object into a simpler structure like an array or JSON.

Like the `DateTimeInterfaceCast`, we also have a `DateTimeInterfaceTransformer` that converts `DateTime,` `Carbon,`... objects into strings.

This `DateTimeInterfaceTransformer` is registered in the `data.php` config file and will automatically be used when a data object needs to transform a `DateTimeInterface` object:

```php
'transformers' => [
    DateTimeInterface::class => \Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer::class,
    \Illuminate\Contracts\Support\Arrayable::class => \Spatie\LaravelData\Transformers\ArrayableTransformer::class,
],
```

Remember the image object we created earlier; we stored a file size and filename in the object. But that could be more useful; let's provide the URL to the file when transforming the object. Just like casts, transformers also can be local. Let's implement one for `Image`:

```php
class ImageTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): string
    {
        if (! $value instanceof Image) {
            throw new Exception("Not an image");
        }

        return url($value->filename);
    }
}
```

We can now use this transformer in the data object like this:

```php
class PostData extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        public PostStatus $status,
        #[WithCast(ImageCast::class)]
        #[WithTransformer(ImageTransformer::class)]
        public ?Image $image,
        #[Date]
        public ?CarbonImmutable $published_at
    ) {
    }
}
```

In our controller, we return the object as such:

```php
public function __invoke()
{
    return PostData::from([
        'title' => 'Hello laravel-data',
        'content' => 'This is an introduction post for the new package',
        'status' => PostStatus::published,
        'image' => [
            'filename' => 'images/8JQtgd0XaPtt9CqkPJ3eWFVV4BAp6JR9ltYAIKqX.png',
            'size' => 16524
        ],
        'published_at' => CarbonImmutable::create(2020, 05, 16),
    ]);
}
```

Which leads to the following JSON:

```php
{
    "title": "Hello laravel-data",
    "content": "This is an introduction post for the new package",
    "status": "published",
    "image": "http://laravel-playbox.test/images/8JQtgd0XaPtt9CqkPJ3eWFVV4BAp6JR9ltYAIKqX.png",
    "published_at": "2020-05-16T00:00:00+00:00"
}
```

You can read more about transformers [here](/docs/laravel-data/v4/as-a-resource/transformers).

## Generating a blueprint

We can now send our posts as JSON to the front, but what if we want to create a new post? When using Inertia, for example, we might need an empty blueprint object like this that the user could fill in:

```json
{
    "title" : null,
    "content" : null,
    "status" : null,
    "image": null,
    "published_at" : null
}
```

Such an array can be generated with the `empty` method, which will return an empty array following the structure of your data object:

```php
PostData::empty();
```

Which will return the following array:

```php
[
  'title' => null,
  'content' => null,
  'status' => null,
  'image' => null,
  'published_at' => null,
]
```

It is possible to set the status of the post to draft by default:

```php
PostData::empty([
    'status' => PostStatus::draft;
]);
```

## Lazy properties

For the last section of this quickstart, we will look at the `AuthorData` object again; let's say that we want to compose a list of all the authors. What if we had 100+ authors who have all written more than 100+ posts:

```json
[
    {
        "name" : "Ruben Van Assche",
        "posts" : [
            {
                "title" : "Hello laravel-data",
                "content" : "This is an introduction post for the new package",
                "status" : "published",
                "image" : "http://laravel-playbox.test/images/8JQtgd0XaPtt9CqkPJ3eWFVV4BAp6JR9ltYAIKqX.png",
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
                "image" : "http://laravel-playbox.test/images/8JQtgd0XaPtt9CqkPJ3eWFVV4BAp6JR9ltYAIKqX.png"
                "published_at" : "2021-09-24T13:31:20+00:00"
            }

            // ...
        ]
    }

    // ...
]
```

As you can see, this will quickly be a large set of data we would send over JSON, which we don't want to do. Since each author includes his name and all the posts, he has written.

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

This functionality can be achieved with lazy properties. Lazy properties are only added to a payload when we explicitly ask it. They work with closures that are executed only when this is required:

```php
class AuthorData extends Data
{
    /**
    * @param Collection<PostData>|Lazy $posts
    */
    public function __construct(
        public string $name,
        public Collection|Lazy $posts
    ) {
    }

    public static function fromModel(Author $author)
    {
        return new self(
            $author->name,
            Lazy::create(fn() => PostData::collect($author->posts))
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

Which will result in this JSON:

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

Let's take this one step further. What if we want to only include the title of each post? We can do this by making all the other properties within the post data object also lazy:

```php
class PostData extends Data
{
    public function __construct(
        public string|Lazy $title,
        public string|Lazy $content,
        public PostStatus|Lazy $status,
        #[WithoutValidation]
        #[WithCast(ImageCast::class)]
        #[WithTransformer(ImageTransformer::class)]
        public ImageData|Lazy|null $image,
        #[Date]
        public CarbonImmutable|Lazy|null $published_at
    ) {
    }
    
    public static function fromModel(Post $post): PostData
    {
        return new self(
            Lazy::create(fn() => $post->title),
            Lazy::create(fn() => $post->content),
            Lazy::create(fn() => $post->status),
            Lazy::create(fn() => $post->image),
            Lazy::create(fn() => $post->published_at)
        );
    }

    public static function rules(ValidationContext $context): array
    {
        return [
            'image' => ['nullable', 'image'],
        ];
    }
}
```

Now the only thing we need to do is include the title:

```php
$postData->include('posts.title')->toJson();
```

Which will result in this JSON:

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
- by default, with an option to exclude them

And a lot more. You can read all about it [here](/docs/laravel-data/v4/as-a-resource/lazy-properties).

## Conclusion

So that's it, a quick overview of this package. We barely scratched the surface of what's possible with the package. There's still a lot more you can do with data objects like:

- [casting](/docs/laravel-data/v4/advanced-usage/eloquent-casting) them into Eloquent models
- [transforming](/docs/laravel-data/v4/advanced-usage/typescript) the structure to typescript
- [working](/docs/laravel-data/v4/as-a-data-transfer-object/collections) with `DataCollections`
- [optional properties](/docs/laravel-data/v4/as-a-data-transfer-object/optional-properties) not always required when creating a data object
- [wrapping](/docs/laravel-data/v4/as-a-resource/wrapping) transformed data into keys
- [mapping](/docs/laravel-data/v4/as-a-data-transfer-object/mapping-property-names) property names when creating or transforming a data object
- [appending](/docs/laravel-data/v4/as-a-resource/appending-properties) extra data
- [including](/docs/laravel-data/v4/as-a-resource/lazy-properties#content-using-query-strings) properties using the URL query string
- [inertia](https://spatie.be/docs/laravel-data/v4/advanced-usage/use-with-inertia) support for lazy properties
- and so much more ... you'll find all the information here in the docs
