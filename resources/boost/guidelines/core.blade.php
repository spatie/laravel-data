# Laravel Data Guidelines

These guidelines are for agents working in Laravel applications that use `spatie/laravel-data`. Keep the Data layer boring: typed boundaries, predictable serialization, no hidden persistence.

## 1. Installation And Setup

Install the package in consuming apps:

```shell
composer require spatie/laravel-data
```

Publish `config/data.php` only when defaults need to change:

```shell
php artisan vendor:publish --provider="Spatie\LaravelData\LaravelDataServiceProvider" --tag="data-config"
```

Use `php artisan make:data NameData` if the project uses generated stubs. Otherwise create the file manually in the existing namespace.

This repository is the package source, so Boost skills live under `resources/boost/skills/{skill-name}/`. In an application, custom skills usually live under `.ai/skills/{skill-name}/`.

Schema changes must go through Laravel migrations. Never suggest direct database edits.

## 2. Naming Conventions

Use the `Data` suffix. Keep names tied to the contract, not the transport.

| Purpose | Good names |
| --- | --- |
| Create input | `CreateUserData`, `CreateStaffData` |
| Update input | `UpdateSupplierData`, `UpdatePackageData` |
| Response output | `SupplierData`, `PackageData`, `BillData` |
| Nested value | `AddressData`, `MoneyData`, `ServiceTypeData` |
| Filters | `SupplierFilterData`, `BillSearchData` |

Avoid `Dto`, `DTO`, `Payload`, and `Resource` suffixes unless the existing app already uses them.

## 3. Folder Conventions

Follow the app first. If there is no convention:

- Request input: `App\Data\Requests`
- Response output: `App\Data\Resources`
- Shared objects: `App\Data`

If the app uses domain modules, keep Data classes with the domain:

- `Domain\Supplier\Data\SupplierData`
- `Domain\Package\Data\PackageData`
- `Domain\Bill\Data\BillData`

Do not create a global `App\Data` dumping ground in a domain-structured project.

## 4. Request Data Classes

Request Data classes validate and type incoming input before it reaches an action.

```php
<?php

declare(strict_types=1);

namespace App\Data\Requests;

use Spatie\LaravelData\Data;

final class UpdateSupplierData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public bool $is_active,
    ) {
    }

    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
```

Controller code should stay thin:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Suppliers\UpdateSupplier;
use App\Data\Requests\UpdateSupplierData;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class SupplierController
{
    public function update(Supplier $supplier, UpdateSupplierData $data, UpdateSupplier $updateSupplier): RedirectResponse
    {
        Gate::authorize('update', $supplier);

        $updateSupplier->handle($supplier, $data);

        return to_route('suppliers.show', $supplier);
    }
}
```

## 5. Response Data Classes

Response Data classes define what leaves the backend. Use them for APIs and Inertia props when the shape matters.

```php
<?php

declare(strict_types=1);

namespace App\Data\Resources;

use App\Models\Package;
use Spatie\LaravelData\Data;

final class PackageData extends Data
{
    public function __construct(
        public int $id,
        public string $tracking_number,
        public string $status,
        public string $created_at,
    ) {
    }

    public static function fromModel(Package $package): self
    {
        return new self(
            id: $package->id,
            tracking_number: $package->tracking_number,
            status: $package->status->value,
            created_at: $package->created_at->toDateString(),
        );
    }
}
```

Load relations before transformation. A `fromModel()` method should not quietly run extra queries.

## 6. Nested Data Objects

Use nested Data objects when a nested array has a stable meaning.

```php
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

final class SupplierData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public AddressData $address,
    ) {
    }
}
```

Do not leave `array $address` in a response contract that Vue, another controller, or an action must understand.

## 7. Collections

Collections need item types. Laravel Data can infer a lot, but humans and static analysis still need the annotation.

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

Use `ServiceTypeData::collect($models)` for repeated objects. Use `DataCollection`, `PaginatedDataCollection`, or `CursorPaginatedDataCollection` only when you need Laravel Data include/exclude behavior on the collection itself.

## 8. Validation

Let simple type rules be inferred where that is enough. Add explicit rules for domain constraints.

```php
use App\Models\Department;
use Illuminate\Validation\Rule;

public static function rules(): array
{
    return [
        'email' => ['required', 'email:rfc', 'max:255'],
        'department_id' => ['required', 'integer', Rule::exists(Department::class, 'id')],
    ];
}
```

Use array rule syntax. Pipe strings make regex and conditional rules harder to review.

Use `validateAndCreate()` in tests and non-request code when validation must run:

```php
$data = CreateUserData::validateAndCreate($payload);
```

## 9. Mapping And Name Mapping

Prefer matching the external contract. When names differ, map them where the difference occurs.

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

Use class-level mappers such as `SnakeCaseMapper` only when every property follows the same convention.

## 10. Casts And Transformers

Use casts for input. Use transformers for output.

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

```php
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

final class BillData extends Data
{
    public function __construct(
        public int $id,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d')]
        public CarbonImmutable $issued_at,
    ) {
    }
}
```

Use enums before custom casts when the value is a fixed domain set.

## 11. Lazy Properties

Use lazy properties for fields that are expensive, optional, or only needed on partial reloads.

```php
use Illuminate\Support\Collection;
use Spatie\LaravelData\Lazy;

final class SupplierData extends Data
{
    /**
     * @param Lazy|Collection<int, BillData> $recent_bills
     */
    public function __construct(
        public int $id,
        public string $name,
        public Lazy|Collection $recent_bills,
    ) {
    }

    public static function fromModel(Supplier $supplier): self
    {
        return new self(
            id: $supplier->id,
            name: $supplier->name,
            recent_bills: Lazy::whenLoaded(
                'recentBills',
                $supplier,
                fn () => BillData::collect($supplier->recentBills),
            ),
        );
    }
}
```

Use `Lazy::inertia()`, `Lazy::closure()`, or `Lazy::inertiaDeferred()` only when the Inertia behavior is intentional.

## 12. TypeScript Generation

For Laravel apps, use `spatie/laravel-typescript-transformer`.

```shell
composer require spatie/laravel-typescript-transformer
php artisan typescript:install
```

With TypeScript Transformer v3, add `Spatie\LaravelTypeScriptTransformer\LaravelData\LaravelDataTypeScriptTransformerExtension` in the app's `TypeScriptTransformerServiceProvider`. Older v2 setups use the Laravel Data transformer and collector in `config/typescript-transformer.php`.

Do not hand-copy DTO shapes into TypeScript. Generate them and import the generated type in Vue.

## 13. Inertia And Vue

Do not pass raw Eloquent models to pages when the page has a stable prop contract.

```php
return Inertia::render('Suppliers/Show', [
    'supplier' => SupplierData::fromModel($supplier),
]);
```

Keep shared props small. Use deferred or lazy props for tab-specific and expensive data.

## 14. Testing With Pest

Cover the contract, not implementation trivia:

- Valid payload creates the request Data object.
- Invalid payload throws validation errors.
- Model mapping returns the expected response shape.
- Nested Data serializes as expected.
- Inertia responses expose Data-shaped props, not raw models.

```php
it('creates data from a valid payload', function (): void {
    $data = CreateUserData::validateAndCreate([
        'name' => 'Taylor',
        'email' => 'taylor@example.com',
    ]);

    expect($data)
        ->toBeInstanceOf(CreateUserData::class)
        ->email->toBe('taylor@example.com');
});
```

Use factories and actions. Avoid `DB` facade assertions unless the feature is specifically about queries.

## 15. Refactoring Rules

Refactor to Data when:

- The same request shape is validated in more than one place.
- Arrays cross controller, action, job, or service boundaries.
- A controller manually builds Inertia props from models.
- Vue prop types repeat a backend payload shape.
- Nested arrays need validation or repeated serialization.

Leave simple local arrays alone.

## 16. Anti-Patterns

| Do | Do Not |
| --- | --- |
| Inject `CreateStaffData $data` into a controller action | Duplicate `$request->validate()` in several controllers |
| Return `SupplierData::fromModel($supplier)` to Inertia | Pass a raw `Supplier` model to Vue |
| Use `AddressData $address` for a stable nested shape | Use `array $address` and expect readers to guess keys |
| Annotate `Collection<int, ServiceTypeData>` | Use an untyped `Collection` for nested Data |
| Use an enum for fixed statuses | Accept arbitrary strings for domain states |
| Let an action persist | Call `Model::create()` inside a Data class |
| Authorize in controller, policy, or action | Put permission checks in Data classes |

## 17. Review Checklist

- Is this Data class crossing a real boundary?
- Are all properties explicitly typed?
- Is nullability deliberate?
- Should any string be an enum?
- Are nested shapes modeled as Data?
- Are collection item types documented?
- Are validation rules close to request input?
- Are mappings and date formats explicit?
- Are expensive relations eager loaded, lazy, or deferred?
- Does the Data class avoid authorization and persistence?
- Do tests cover validation and serialized shape?
