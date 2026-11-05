# Laravel Data Examples

These examples assume a Laravel app with Inertia, Vue, Pest, policies, factories, and action classes. Adapt namespaces to the project.

## 1. Request Data

`CreateStaffData` is useful because the payload crosses from HTTP into an action and has real validation rules.

```php
<?php

declare(strict_types=1);

namespace App\Data\Requests;

use App\Models\Department;
use App\Models\Staff;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

final class CreateStaffData extends Data
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $email,
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public CarbonImmutable $date_of_joining,
        public int $department_id,
    ) {
    }

    public static function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique(Staff::class, 'email')],
            'date_of_joining' => ['required', 'date_format:Y-m-d'],
            'department_id' => ['required', 'integer', Rule::exists(Department::class, 'id')],
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Staff\CreateStaff;
use App\Data\Requests\CreateStaffData;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class StaffController
{
    public function store(CreateStaffData $data, CreateStaff $createStaff): RedirectResponse
    {
        Gate::authorize('create', Staff::class);

        $staff = $createStaff->handle($data);

        return to_route('staff.show', $staff);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Actions\Staff;

use App\Data\Requests\CreateStaffData;
use App\Models\Staff;

final class CreateStaff
{
    public function handle(CreateStaffData $data): Staff
    {
        return Staff::create([
            'first_name' => $data->first_name,
            'last_name' => $data->last_name,
            'email' => $data->email,
            'date_of_joining' => $data->date_of_joining->toDateString(),
            'department_id' => $data->department_id,
        ]);
    }
}
```

## 2. Response Data From A Model

Map models deliberately. If relations are needed, load them before calling `fromModel()`.

```php
<?php

declare(strict_types=1);

namespace App\Data\Resources;

use App\Models\Supplier;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

final class SupplierData extends Data
{
    /**
     * @param Collection<int, ServiceTypeData> $service_types
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public bool $is_active,
        public string $created_at,
        public string $display_name,
        public Collection $service_types,
    ) {
    }

    public static function fromModel(Supplier $supplier): self
    {
        return new self(
            id: $supplier->id,
            name: $supplier->name,
            email: $supplier->email,
            is_active: $supplier->is_active,
            created_at: $supplier->created_at->toDateString(),
            display_name: "{$supplier->name} ({$supplier->code})",
            service_types: $supplier->relationLoaded('serviceTypes')
                ? ServiceTypeData::collect($supplier->serviceTypes)
                : collect(),
        );
    }
}
```

```php
$supplier->loadMissing('serviceTypes');

return Inertia::render('Suppliers/Show', [
    'supplier' => SupplierData::fromModel($supplier),
]);
```

## 3. Nested Data

Use a nested Data class for stable nested payloads.

```php
<?php

declare(strict_types=1);

namespace App\Data\Resources;

use Spatie\LaravelData\Data;

final class AddressData extends Data
{
    public function __construct(
        public string $line_1,
        public ?string $line_2,
        public string $city,
        public string $state,
        public string $postal_code,
    ) {
    }
}
```

```php
final class StaffData extends Data
{
    public function __construct(
        public int $id,
        public string $full_name,
        public string $email,
        public AddressData $address,
    ) {
    }

    public static function fromModel(Staff $staff): self
    {
        return new self(
            id: $staff->id,
            full_name: "{$staff->first_name} {$staff->last_name}",
            email: $staff->email,
            address: new AddressData(
                line_1: $staff->address_line_1,
                line_2: $staff->address_line_2,
                city: $staff->city,
                state: $staff->state,
                postal_code: $staff->postal_code,
            ),
        );
    }
}
```

## 4. Data Collections

```php
<?php

declare(strict_types=1);

namespace App\Data\Resources;

use App\Models\ServiceType;
use Spatie\LaravelData\Data;

final class ServiceTypeData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }

    public static function fromModel(ServiceType $serviceType): self
    {
        return new self(
            id: $serviceType->id,
            name: $serviceType->name,
        );
    }
}
```

```php
$suppliers = Supplier::query()
    ->with('serviceTypes')
    ->latest()
    ->paginate(15);

return Inertia::render('Suppliers/Index', [
    'suppliers' => SupplierData::collect($suppliers),
]);
```

## 5. Inertia Props

Use Data classes for the page contract. Use deferred props for data the first render does not need.

```php
<?php

declare(strict_types=1);

namespace App\Data\Resources;

use App\Models\Supplier;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class SupplierShowData extends Data
{
    /**
     * @param Lazy|Collection<int, BillData> $recent_bills
     */
    public function __construct(
        public SupplierData $supplier,
        public Lazy|Collection $recent_bills,
    ) {
    }

    public static function fromModel(Supplier $supplier): self
    {
        return new self(
            supplier: SupplierData::fromModel($supplier),
            recent_bills: Lazy::inertiaDeferred(
                fn () => BillData::collect(
                    $supplier->bills()
                        ->latest()
                        ->limit(10)
                        ->get()
                ),
                'supplier-bills',
            ),
        );
    }
}
```

```php
public function show(Supplier $supplier): Response
{
    Gate::authorize('view', $supplier);

    $supplier->loadMissing('serviceTypes');

    return Inertia::render('Suppliers/Show', SupplierShowData::fromModel($supplier));
}
```

## 6. TypeScript

Install and configure the Laravel transformer package in apps that generate frontend types:

```shell
composer require spatie/laravel-typescript-transformer
php artisan typescript:install
```

```php
use Spatie\LaravelTypeScriptTransformer\LaravelData\LaravelDataTypeScriptTransformerExtension;

$config->extension(new LaravelDataTypeScriptTransformerExtension());
```

Some projects discover all Data classes. Others opt in:

```php
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class PackageData extends Data
{
    public function __construct(
        public int $id,
        public string $tracking_number,
        public string $status,
    ) {
    }
}
```

Vue should import the generated type, not rewrite it:

```vue
<script setup lang="ts">
import type { PackageData } from '@/types/generated';

defineProps<{
    package: PackageData;
}>();
</script>
```

## 7. Pest Tests

```php
use App\Data\Requests\CreateStaffData;
use App\Models\Department;
use Illuminate\Validation\ValidationException;

it('creates staff data from a valid payload', function (): void {
    $department = Department::factory()->create();

    $data = CreateStaffData::validateAndCreate([
        'first_name' => 'Asha',
        'last_name' => 'Rao',
        'email' => 'asha.rao@example.com',
        'date_of_joining' => '2026-05-01',
        'department_id' => $department->id,
    ]);

    expect($data)
        ->toBeInstanceOf(CreateStaffData::class)
        ->date_of_joining->toDateString()->toBe('2026-05-01');
});

it('rejects invalid staff data', function (): void {
    CreateStaffData::validateAndCreate([
        'first_name' => '',
        'last_name' => 'Rao',
        'email' => 'invalid',
        'date_of_joining' => '2026/05/01',
        'department_id' => null,
    ]);
})->throws(ValidationException::class);
```

```php
use App\Data\Resources\AddressData;
use App\Data\Resources\StaffData;

it('serializes nested address data', function (): void {
    $data = new StaffData(
        id: 1,
        full_name: 'Asha Rao',
        email: 'asha.rao@example.com',
        address: new AddressData(
            line_1: '100 Market Street',
            line_2: null,
            city: 'Mumbai',
            state: 'MH',
            postal_code: '400001',
        ),
    );

    expect($data->toArray())->toMatchArray([
        'address' => [
            'line_1' => '100 Market Street',
            'line_2' => null,
            'city' => 'Mumbai',
            'state' => 'MH',
            'postal_code' => '400001',
        ],
    ]);
});
```
