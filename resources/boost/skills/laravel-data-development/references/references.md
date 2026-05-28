# Laravel Data References

## What This Package Does

`spatie/laravel-data` gives Laravel apps typed Data classes that can be created from requests, arrays, models, and collections. A good Data class names the shape of data crossing a boundary: validated input, action input, response output, nested payloads, or TypeScript-facing props.

The package is not the deprecated `spatie/data-transfer-object` package.

## Official Links

- GitHub: https://github.com/spatie/laravel-data
- Documentation: https://spatie.be/docs/laravel-data
- Installation: https://spatie.be/docs/laravel-data/v4/installation-setup
- Quickstart: https://spatie.be/docs/laravel-data/v4/getting-started/quickstart
- Request to Data object: https://spatie.be/docs/laravel-data/v4/as-a-data-transfer-object/request-to-data-object
- Casts: https://spatie.be/docs/laravel-data/v4/as-a-data-transfer-object/casts
- TypeScript transformation: https://spatie.be/docs/laravel-data/v4/advanced-usage/typescript
- TypeScript Transformer Laravel setup: https://spatie.be/docs/typescript-transformer/v3/laravel/installation-and-setup
- TypeScript Transformer Laravel Data integration: https://spatie.be/docs/typescript-transformer/v3/laravel/laravel-data
- Laravel Boost: https://laravel.com/docs/13.x/boost

## Core API

- `Data::from($payload)`: create a Data object from an array, request, model, or supported normalizer.
- `Data::validateAndCreate($payload)`: validate an array payload and create the Data object.
- `Data::collect($items)`: transform arrays, Laravel collections, Eloquent collections, and paginators into Data objects while preserving the collection shape where possible.
- `rules()`: add explicit Laravel validation rules.
- `fromModel()`: a project convention for deliberate model mapping when computed fields, formatting, or relations are involved.

## Common Syntax

### Basic Data Class

```php
<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class AddressData extends Data
{
    public function __construct(
        public string $line_1,
        public ?string $line_2,
        public string $city,
        public string $postal_code,
    ) {
    }
}
```

### Request Validation

```php
use App\Models\User;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;

final class CreateUserData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
    ) {
    }

    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique(User::class, 'email')],
        ];
    }
}
```

### Model Mapping

```php
final class SupplierData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $created_at,
    ) {
    }

    public static function fromModel(Supplier $supplier): self
    {
        return new self(
            id: $supplier->id,
            name: $supplier->name,
            created_at: $supplier->created_at->toDateString(),
        );
    }
}
```

### Collections

```php
use Illuminate\Support\Collection;

final class SupplierData extends Data
{
    /**
     * @param Collection<int, ServiceTypeData> $service_types
     */
    public function __construct(
        public int $id,
        public string $name,
        public Collection $service_types,
    ) {
    }
}
```

### Casts

```php
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

final class CreateStaffData extends Data
{
    public function __construct(
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public CarbonImmutable $date_of_joining,
    ) {
    }
}
```

### Transformers

```php
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

final class BillData extends Data
{
    public function __construct(
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d')]
        public CarbonImmutable $issued_at,
    ) {
    }
}
```

### Mapping

```php
use Spatie\LaravelData\Attributes\MapInputName;

final class BillData extends Data
{
    public function __construct(
        #[MapInputName('bill_no')]
        public string $bill_number,
    ) {
    }
}
```

## Reminders For Agents

- Search existing Data classes first.
- Use project namespaces and date formats; examples are not policy.
- Prefer explicit `fromModel()` methods when a response shape is more than a direct property list.
- Do not query inside Data mapping just to make an example convenient.
- Do not pass raw Eloquent models to Vue/Inertia when a Data class is the response contract.
- Use generated TypeScript types instead of copying DTO shapes into `.ts` files.
- Use `spatie/laravel-typescript-transformer` for Laravel TypeScript generation.
