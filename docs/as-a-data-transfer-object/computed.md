---
title: Computed values
weight: 7
---

Earlier we saw how default values can be set for a data object, the same approach can be used to set computed values, although slightly different:

```php
use Spatie\LaravelData\Attributes\Computed;

class SongData extends Data
{
    #[Computed]
    public string $full_name;

    public function __construct(
        public string $first_name,
        public string $last_name,
    ) {
        $this->full_name = "{$this->first_name} {$this->last_name}";
    }
}
```

You can now do the following:

```php
SongData::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche']);
SongData::validateAndCreate(['first_name' => 'Ruben', 'last_name' => 'Van Assche']);
```

Again there are a few conditions for this approach:

- You must always use a sole property, a property within the constructor definition won't work
- Computed properties cannot be defined in the payload, a `CannotSetComputedValue` will be thrown if this is the case

