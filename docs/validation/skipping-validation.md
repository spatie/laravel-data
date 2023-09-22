---
title: Skipping validation
weight: 7
---

Sometimes you don't want properties to be automatically validated, for instance when you're manually overwriting the
rules method like this:

```php
class SongData extends Data
{
    public function __construct(
        public string $name,
    ) {
    }
    
    public static function fromRequest(Request $request): static{
        return new self("{$request->input('first_name')} {$request->input('last_name')}")
    }
    
    public static function rules(): array
    {
        return [
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
        ];
    }
}
```

When a request is being validated, the rules will look like this:

```php
[
    'name' => ['required', 'string'],
    'first_name' => ['required', 'string'],
    'last_name' => ['required', 'string'],
]
```

We know we never want to validate the `name` property since it won't be in the request payload, this can be done as
such:

```php
class SongData extends Data
{
    public function __construct(
        #[WithoutValidation]
        public string $name,
    ) {
    }
}
```

Now the validation rules will look like this:

```php
[
    'first_name' => ['required', 'string'],
    'last_name' => ['required', 'string'],
]
```
