---
name: laravel-data-development
description: Build Laravel features with spatie/laravel-data using typed request data, response data, validation, nested payloads, Inertia props, and frontend-safe contracts.
---

# Laravel Data Development

Use this skill when a Laravel feature moves structured data across a boundary: HTTP request input, an action call, a model-to-response transformation, an Inertia prop, or a generated TypeScript type.

Laravel Data is not a blanket replacement for every array. Use it where a named, typed contract makes the code easier to change and review.

## Writing Bar

Keep skill content concrete enough that a Laravel developer would keep it in the repo:

- Use complete snippets when imports matter.
- Use real Laravel concepts: actions, policies, factories, relationships, Inertia props.
- Avoid generic filler such as "improves maintainability" unless the rule says exactly how.
- Do not invent relations, folders, or frontend paths without saying they are examples.
- Prefer one strong example over three shallow examples.
- Remove advice that only restates what every Laravel developer already knows.

## Reference Files

- `../../guidelines/core.blade.php`: conventions, review rules, and implementation guardrails.
- `examples/examples.md`: concrete Laravel, Inertia, Vue, and Pest examples.
- `references/references.md`: official links and syntax reminders.

## Use Laravel Data For

- Request data that needs validation before reaching an action.
- Action input that would otherwise be a loose array.
- Response objects built from Eloquent models.
- Inertia props that should not expose raw models.
- Nested payloads with a stable shape.
- Repeated nested objects that need typed collections.
- Date, enum, value-object, or name-mapped fields.
- PHP data contracts consumed by generated TypeScript types.

## Do Not Use It For

- Tiny local arrays inside one method.
- One-off query option arrays that Laravel already expresses clearly.
- Authorization checks.
- Persistence or transaction orchestration.
- General domain behavior.
- Large relation graphs that would make serialization expensive.

If a Data class only renames an obvious two-item array used once, skip it.

## Required Standards

- Extend `Spatie\LaravelData\Data`.
- Prefer `final` classes unless the project has a reason to extend them.
- Use promoted constructor properties.
- Give every property an explicit type.
- Use nullable types only for genuinely nullable values.
- Use enums for fixed domain values.
- Use nested Data classes for stable nested payloads.
- Document collection item types with PHPDoc generics.
- Keep formatting and mapping logic small and local to the Data class.
- Keep business decisions in actions, services, models, or policies.
- Eager load relations before model-to-data transformation.
- Read only already-loaded relations inside `fromModel()` unless the method name makes loading explicit.
- Prefer explicit mapping attributes over hidden key conversion.
- Use `spatie/laravel-data`, not the old `spatie/data-transfer-object` package.

## Project Conventions

This repository is the `spatie/laravel-data` package source. It has no consuming app `app/` or `Domain/` folder. The examples in this skill use app-level namespaces because they are meant to guide consumers of the package.

In consuming apps, follow existing structure first. If there is no established convention, use:

- Request data: `App\Data\Requests`
- Response data: `App\Data\Resources`
- Shared data: `App\Data`

If the app uses domain modules, prefer domain-local classes:

- `Domain\Supplier\Data\SupplierData`
- `Domain\Package\Data\PackageData`
- `Domain\Bill\Data\BillData`

Use clear names: `CreateUserData`, `UpdateSupplierData`, `PackageData`, `BillData`, `AddressData`.

## Before Coding

Identify:

- Request Data classes needed.
- Response Data classes needed.
- Nested Data classes and collection item types.
- Validation rules and route/user context needed by those rules.
- Relations that must be eager loaded before transformation.
- Pest coverage for validation, model mapping, nested serialization, and Inertia shape.

Also decide whether Laravel Data is actually worth it. If the answer is no, use the simpler Laravel code.

## Before Final Response

Check:

- Data classes are typed and constructor-promoted.
- Nullability is intentional.
- Enums are used for fixed values.
- Relation access is N+1-safe.
- Data classes do not authorize or persist.
- Inertia props do not leak raw Eloquent models where Data would provide the contract.
- Pest tests were added or updated where behavior changed.
- Pint, static analysis, and frontend builds were run when relevant, or explicitly documented as not run.
