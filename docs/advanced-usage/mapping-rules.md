---
title: Mapping rules
weight: 13
---

It is possible to map the names properties going in and out of your data objects using: `MapOutputName`, `MapInputName`
and `MapName` attributes. But sometimes it can be quite hard to follow where which name can be used. Let's go through
some case:

In the data object:

```php
class UserData extends Data
 {
     public function __construct(
         #[MapName('favorite_song')] // name mapping
         public Lazy|SongData $song,
         #[RequiredWith('song')] // In validation rules, use the original name
         public string $title,
     ) {
     }

     public static function allowedRequestExcept(): ?array
     {
         return [
             'song', // Use the original name when defining includes, excludes, excepts and only
         ];
     }
     
     public function rules(ValidContext $context): array {
        return  [
            'song' => 'required', // Use the original name when defining validation rules
        ];
    }

    // ...
 }
 ```

When creating a data object:

```php
UserData::from([
    'favorite_song' => ..., // You can use the mapped or original name here
    'title' => 'some title'
]);
```

When adding an include, exclude, except or only:

```php
 UserData::from(User::first())->except('song'); // Always use the original name here
```

Within a request query, you can use the mapped or original name:

```
https://spatie.be/my-account?except[]=favorite_song 
```

When validating a data object or getting rules for a data object, always use the original name:

```php
$data = [
    'favorite_song' => 123,
    'title' => 'some title',
];

UserData::validate($data)
UserData::getValidationRules($data)
```

