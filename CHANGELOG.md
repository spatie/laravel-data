# Changelog

All notable changes to `laravel-data` will be documented in this file.

## 4.17.1 - 2025-09-04

### What's Changed

* Fix issue where toArray() is called on null collections by @oddvalue in https://github.com/spatie/laravel-data/pull/1054
* Update the annotations in the Eloquent casts

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.17.0...4.17.1

## 4.17.0 - 2025-06-25

### What's Changed

* Add support for external validation attribute references by @rubenvanassche in https://github.com/spatie/laravel-data/pull/1051

### Breaking changes

- While technically not breaking, some changes were made inside the validation attributes. You can check it here: https://github.com/spatie/laravel-data/pull/1051 in the headsup section.

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.16.1...4.17.0

## 4.16.1 - 2025-06-24

### What's Changed

* Fix issue where toArray() is called on null by @mdietger in https://github.com/spatie/laravel-data/pull/1046

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.16.0...4.16.1

## 4.16.0 - 2025-06-20

### What's Changed

* Add return type annotations to TransformableData trait by @cyppe in https://github.com/spatie/laravel-data/pull/1000
* Add the possibibilty to except key for empty data generation by @thoresuenert in https://github.com/spatie/laravel-data/pull/1035
* Enhance CannotCastEnum exception message to include property name by @rajmundtoth0 in https://github.com/spatie/laravel-data/pull/1039
* Fix issue in pr 1007 by @rubenvanassche in https://github.com/spatie/laravel-data/pull/1041
* Implement the compare method on the eloquent casts to improve the isDirty check  by @SanderSander in https://github.com/spatie/laravel-data/pull/1033
* Add support for default values on properties for morph by @bentleyo in https://github.com/spatie/laravel-data/pull/1017
* Fix problem with dynamic properties

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.15.3...4.16.0

## 4.15.3 - 2025-06-19

- Add support for only and except in enum rule

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.15.2...4.15.3

## 4.15.2 - 2025-06-12

- Fix: CannotCreateData exception when AutoWhenLoadedLazy relationship is not loaded  (#1009)
- Fix: Inertia deferred properties not being that flexible

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.15.1...4.15.2

## 4.15.1 - 2025-04-10

### What's Changed

* Fix #997 by @bentleyo in https://github.com/spatie/laravel-data/pull/998

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.15.0...4.15.1

## 4.15.0 - 2025-04-09

### What's Changed

* Add test and doc changes by @rubenvanassche in https://github.com/spatie/laravel-data/pull/996
* Add support for inertia deferred props by @matthiasweiss in https://github.com/spatie/laravel-data/pull/939
* Add EmptyString support for Formik compatibility by @igorleszczynski in https://github.com/spatie/laravel-data/pull/955
* Update ide.json by @Yi-pixel in https://github.com/spatie/laravel-data/pull/990
* feature property morphable by @bentleyo  in https://github.com/spatie/laravel-data/pull/995

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.14.1...4.15.0

## 4.14.1 - 2025-03-17

### What's Changed

* fix: Model with method name matching attribute names are not retrieved by @Tofandel in https://github.com/spatie/laravel-data/pull/979

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.14.0...4.14.1

## 4.14.0 - 2025-03-14

If you're using cached versions of your data objects and don't clear this cache on deploy, now is the time since we've updated some internal structures.

- Fix an issue where data classes could not be cached (a7d117e1224258a05c2d8e101928b6740a680a69)
- Fix retrieval of property to work for Astrotomic/translatable (#883)
- Refactored some internals for storing attribute information
- Add dataClass to normalize exception message (#968)
- Add Macroable Trait to Data Collection Classes (#971)

## 4.13.2 - 2025-03-03

### What's Changed

* Fix an issue where specific Lazy classes won't be recognized

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.13.1...4.13.2

## 4.13.1 - 2025-02-14

Allow Laravel 12

### What's Changed

* Bump dependabot/fetch-metadata from 2.2.0 to 2.3.0 by @dependabot in https://github.com/spatie/laravel-data/pull/940
* Add "laravel-data-json-schemas" to third party packages by @BasilLangevin in https://github.com/spatie/laravel-data/pull/943

### New Contributors

* @BasilLangevin made their first contribution in https://github.com/spatie/laravel-data/pull/943

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.13.0...4.13.1

## 4.13.0 - 2025-01-24

### What's Changed

* Auto lazy by @rubenvanassche in https://github.com/spatie/laravel-data/pull/831

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.12.0...4.13.0

## 4.12.0 - 2025-01-24

What a release! Probably to biggest minor release we've ever done!

Some cool highlights:

#### Disabling optional values

Optional values are great, but sometimes a `null` value is desirable from now on you can do the following:

```php
class SongData extends Data {
    public function __construct(
        public string $title,
        public string $artist,
        public Optional|null|string $album,
    ) {
    }
}

SongData::factory()
    ->withoutOptionalValues()
    ->from(['title' => 'Never gonna give you up', 'artist' => 'Rick Astley']); // album will `null` instead of `Optional`














```
#### Injecting property values

It was already possible to inject a Laravel route parameter when creating a data object, we've now extended this functionality quite a bit and also allow injecting dependencies from the container and the authenticated user.

```php
class SongData extends Data {
    #[FromAuthenticatedUser]
    public UserData $user;
}














```
#### Merging manual rules

In the past when the validation rules of a property were manually defined, the automatic validation rules for that property were omitted. From now on, you can define manual validation rules and merge them with the automatically generated validation rules:

```php
```php
#[MergeValidationRules]
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }

    public static function rules(): array
    {
        return [
            'title' => ['max:20'],
            'artist' => ['max:20'],
        ];
    }
}














```
#### New property mappers:

We now ship by default a `Uppercase` and `Lowercase` mapper for mapping property names.

### All changes:

* Fix GitHub action fail by @rust17 in https://github.com/spatie/laravel-data/pull/918
* Point to the right problem on ArgumentCountError exception by @nsvetozarevic in https://github.com/spatie/laravel-data/pull/884
* Fix an issue where anonymous classes in castables were serialized (#903) by @rubenvanassche in https://github.com/spatie/laravel-data/pull/923
* Add the ability to optionally merge automatically inferred rules with manual rules by @CWAscend in https://github.com/spatie/laravel-data/pull/848
* Implement enum json serialization by @dont-know-php in https://github.com/spatie/laravel-data/pull/896
* Use comments instead of docblocks in configuration file. by @edwinvdpol in https://github.com/spatie/laravel-data/pull/904
* Casting DateTimeInterface: Truncate nanoseconds to microseconds (first 6 digits) / with Tests by @yob-yob in https://github.com/spatie/laravel-data/pull/908
* Use container to call `Data::authorize()` to allow for dependencies by @cosmastech in https://github.com/spatie/laravel-data/pull/910
* Improve type for CreationContextFactory::alwaysValidate by @sanfair in https://github.com/spatie/laravel-data/pull/925
* Removed comma character from Data Rule stub by @andrey-helldar in https://github.com/spatie/laravel-data/pull/926
* Use BaseData contract i/o Data concrete in CollectionAnnotation by @riesjart in https://github.com/spatie/laravel-data/pull/928
* New mappers added: `LowerCaseMapper` and `UpperCaseMapper` by @andrey-helldar in https://github.com/spatie/laravel-data/pull/927
* Allow disabling default Optional values in CreationContext by @ragulka in https://github.com/spatie/laravel-data/pull/931
* Fix introduction.md by @pikant in https://github.com/spatie/laravel-data/pull/937
* General code health improvements by @xHeaven in https://github.com/spatie/laravel-data/pull/920
* Filling properties from current user by @c-v-c-v in https://github.com/spatie/laravel-data/pull/879

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.11.1...4.12.0

## 4.11.1 - 2024-10-23

- Fix an issue where the cache structures command did not work if the directory did not exist (#892)

## 4.11.0 - 2024-10-22

### What's Changed

* feat: support "null to optional" by @innocenzi in https://github.com/spatie/laravel-data/pull/881
* Register optimize commands by @erikgaal in https://github.com/spatie/laravel-data/pull/880

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.10.1...4.11.0

## 4.10.1 - 2024-10-07

- Fix an issue where optional default values would disable validation

## 4.10.0 - 2024-10-04

It has been a fews weeks, a mostly bugfix release with one new feature, enjoy!

- Fix an issue where required rules could not be combined with optional (#844)
- Fix Livewire return type to make sure it can return everything in the data object (#836)
- Fix issue where validation messages where ignored by collections nested in collections (#867)
- Fix Resource to include Contextable data inteface (#868)
- Stop NormalizedModel from initializing itself and try to lazy load properties when required (#870)
- Passing an enum to caster handle without an error (#841)
- Passing date objects to caster handle without an error (#842)
- Allow setting a default mapping strategy  (#846)

## 4.9.0 - 2024-09-10

- Move some interfaces around in order to avoid a circular chaos

## 4.8.2 - 2024-08-30

- Remove a circular dependency

## 4.81 - 2024-08-13

- Fix a missing dependency

## 4.8.0 - 2024-08-13

### What's Changed

* Detect data from collection by @clementbirkle in https://github.com/spatie/laravel-data/pull/812
* Fix an issue where dd or dump did not work

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.7.2...4.8.0

## 4.7.2 - 2024-07-25

- Fix issue where an exception was not always thrown while it should (#809)
- Solve an issue where an empty container with an iterable cast won't be cast (#810)
- Fix Parsing of Optional Types Annotations in DataIterableAnnotation (#808)
- Support TypeScript Hidden Properties (#820)
- Fix issue where abstract eloquent casts were not encrypted(#828)

## 4.7.1 - 2024-06-25

### What's Changed

* Fix some typos in docs by @Klaas058 in https://github.com/spatie/laravel-data/pull/794
* Provide a default timezone for casting date by @PhilippeThouvenot in https://github.com/spatie/laravel-data/pull/799
* Remove object rule caching
* Fix an issue where a normalized model attribute fetching a relation would not use the loaded relation

### New Contributors

* @PhilippeThouvenot made their first contribution in https://github.com/spatie/laravel-data/pull/799

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.7.0...4.7.1

## 4.7.0 - 2024-06-13

### What's Changed

* Fix typo in docs by @DvDty in https://github.com/spatie/laravel-data/pull/769
* Update wrapping.md by @kimulisiraj in https://github.com/spatie/laravel-data/pull/770
* Fix typos on "Use with Livewire" page by @vkarchevskyi in https://github.com/spatie/laravel-data/pull/777
* Fix dataCastUsing method signature by @shankhadevpadam in https://github.com/spatie/laravel-data/pull/781
* Avoid loading already loaded relations and allow loading non studly relation names by @Tofandel in https://github.com/spatie/laravel-data/pull/773
* Fix routing parameters filled in incorrectly using mapping property names by @guiqibusixin in https://github.com/spatie/laravel-data/pull/775
* Feature: add ability to store eloquent casts as an encrypted string by @eugen-stranz in https://github.com/spatie/laravel-data/pull/723

### New Contributors

* @DvDty made their first contribution in https://github.com/spatie/laravel-data/pull/769
* @vkarchevskyi made their first contribution in https://github.com/spatie/laravel-data/pull/777
* @shankhadevpadam made their first contribution in https://github.com/spatie/laravel-data/pull/781
* @guiqibusixin made their first contribution in https://github.com/spatie/laravel-data/pull/775
* @eugen-stranz made their first contribution in https://github.com/spatie/laravel-data/pull/723
* @yob-yob made their first contribution in https://github.com/spatie/laravel-data/pull/776

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.6.0...4.7.0

## 4.6.0 - 2024-05-03

### What's Changed

- Add initial support for casting union types
- Fix an issue with paginator includes not working
- Fix consistency of After, AfterOrEqual, Before, BeforeOrEquals rules
- Fix creation context issue (#749)
- Fix an performance issue where when creating a data object from models, the attributes were always called
- Add a #[LoadRelation] attribute which allows loading model relations when creating data objects on the fly

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.5.1...4.6.0

## 3.12.0 - 2024-04-24

### What's Changed

* Backport cache fix to v3 by @jameshulse in https://github.com/spatie/laravel-data/pull/671
* fix: adds environment variable to disable structure caching by @jaydublu2002 in https://github.com/spatie/laravel-data/pull/645
* v3 support for Laravel 11 by @jameshulse in https://github.com/spatie/laravel-data/pull/739

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.5.1...3.12.0

## 4.5.1 - 2024-04-10

### What's Changed

* Abstract data object as collection by @onursimsek in https://github.com/spatie/laravel-data/pull/741

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.5.0...4.5.1

## 4.5.0 - 2024-04-04

### What's Changed

* Cast value to string for createFromFormat by @kylekatarnls in https://github.com/spatie/laravel-data/pull/707
* fix: avoid mutation during `DataCollectable` transformation by @innocenzi in https://github.com/spatie/laravel-data/pull/696
* Add new section to the docs for package testing by @adam-code-labx in https://github.com/spatie/laravel-data/pull/711
* Change default cache variable by @Elnadrion in https://github.com/spatie/laravel-data/pull/717
* Improve performance of CastPropertiesDataPipe by using hash lookups instead of ->first by @LauJosefsen in https://github.com/spatie/laravel-data/pull/721
* Change method of creating object without magic methods by @daveroverts in https://github.com/spatie/laravel-data/pull/725
* Upgrade to inertia 1.0 by @kimulisiraj in https://github.com/spatie/laravel-data/pull/726
* Allow to pass multiple formats to Laravel's `date_format` rule by @riesjart in https://github.com/spatie/laravel-data/pull/727
* Add attribute for Laravel's `list` validation rule by @riesjart in https://github.com/spatie/laravel-data/pull/728
* chore(deps): bump dependabot/fetch-metadata from 1.6.0 to 2.0.0 by @dependabot in https://github.com/spatie/laravel-data/pull/718
* Update creating-a-cast.md by @AlexRegenbogen in https://github.com/spatie/laravel-data/pull/724
* Fix wrong link to using attributes page by @BernhardK91 in https://github.com/spatie/laravel-data/pull/733
* Add better support for serializing data by @rubenvanassche in https://github.com/spatie/laravel-data/pull/735
* Fix for the use of built-in class names in Collection annotations by @27pchrisl in https://github.com/spatie/laravel-data/pull/736
* Adds a config option to silently ignore when a Computed Property is being set by @erikaraujo in https://github.com/spatie/laravel-data/pull/714

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.4.1...4.5.0

## 4.4.1 - 2024-03-20

### What's Changed

* fix issue where DataCollection keys were missing in TypeScript by @rubenvanassche

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.4.0...4.4.1

## 4.4.0 - 2024-03-13

### What's Changed

* Add support for transformation max depths by @rubenvanassche in https://github.com/spatie/laravel-data/pull/699

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.3.2...4.4.0

## 4.3.2 - 2024-03-12

### What's Changed

* Provide fallbacks for config values by @sebastiandedeyne in https://github.com/spatie/laravel-data/pull/695

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.3.1...4.3.2

## 4.3.1 - 2024-03-12

### What's Changed

* Support duration in cache configuration by @sebastiandedeyne in https://github.com/spatie/laravel-data/pull/694
* Handle `null` for `fieldContext` within `resolvePotentialPartialArray` by @faustbrian in https://github.com/spatie/laravel-data/pull/693

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.3.0...4.3.1

## 4.3.0 - 2024-03-11

### What's Changed

* Update wrapping.md to clarify collections and paginated collections case by @santigarcor in https://github.com/spatie/laravel-data/pull/675
* Feature/cast and transformer iterables by @rubenvanassche in https://github.com/spatie/laravel-data/pull/686
* Add support for passing on partials when not transforming values by @rubenvanassche in https://github.com/spatie/laravel-data/pull/688
* chore(deps): bump ramsey/composer-install from 2 to 3 by @dependabot in https://github.com/spatie/laravel-data/pull/678
* Allow data context to be set to null by @sebastiandedeyne in https://github.com/spatie/laravel-data/pull/691
* Fix iterable casts when there's a global and local cast specified by @sebastiandedeyne in https://github.com/spatie/laravel-data/pull/690
* Fix iterable values with union types by @sebastiandedeyne in https://github.com/spatie/laravel-data/pull/692

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.2.0...4.3.0

## 4.2.0 - 2024-03-01

### What's Changed

* Add experimental support for Livewire synths @rubenvanassche in https://github.com/spatie/laravel-data/pull/669
* Add DataCollectionSynth by @edalzell in https://github.com/spatie/laravel-data/pull/591

**Full Changelog**: https://github.com/spatie/laravel-data/compare/4.1.0...4.2.0

## 3.11.2 - 2024-02-22

- fix: adds environment variable to disable structure caching (#645)

## 3.11.1 - 2024-02-20

### What's Changed

* Backport cache fix to v3 by @jameshulse in https://github.com/spatie/laravel-data/pull/671

**Full Changelog**: https://github.com/spatie/laravel-data/compare/3.11.0...3.11.1

## 4.1.0 - 2024-02-16

- Fix an issue where the cache was queried too much
- Move the creation process of object rules from constructor to `getRule` to fix issues with caching and typescript transformer
- Laravel 11 support

## 4.0.2 - 2024-02-14

- Fixes issue where casts could not return `null` or an empty array
- Prevent out of memory with multiple requests in a test (#665)
- Allow passing `null` to `collect` and return an empty version of the defined output type
- Fix templates for Laravel Idea plugin support (#666)
- Fix an issue where the cache could not be disabled

## 4.0.1 - 2024-02-09

Laravel-data 4.0.0 was released 5 hours ago, time for an update!

- Add support for a collection cast
- Make sure we check wether a value is Uncastable
- Fix issue where creating a data object from multiple payloads wasn't always consistent
- Allow disabling cache
- Refactor `PropertyRules`
- Fix issue where install on windows was impossible
- Fix types for map and through (#640)

## 4.0.0

- Allow arrays, Collections, Paginators, ... to be used as DataCollections
- Add support for magically creating data collections
- Rewritten transformation system with respect to includeable properties
- Addition of collect method
- Removal of collection method
- Add support for using Laravel Model attributes as data properties
- Allow creating data objects using `from` without parameters
- Add support for a Dto and Resource object
- It is now a lot easier to validate all the payloads added to laravel-data
- Added contexts to the creation and transformation process
- Allow creating a data object or collection using a factory
- Speed up the process of creating and transforming data objects
- Add support for BNF syntax
- Laravel 10 requirement
- Rewritten docs

**Some more "internal" changes**

- Restructured tests for the future we have ahead
- The Type system was completely rewritten, allowing for a better performance and more flexibility in the future
- Benchmarks added to make data even faster

## 3.11.0 - 2023-12-21

- Add support for data structure caching #622

## 3.10.1 - 2023-12-04

- Make ValidationPath Stringable
- Fix PHPStan
- Improve performance when optional property exists (#612)

## 3.10.0 - 2023-12-01

A fresh release after a month of vacation, enjoy!

- Add ExcludeWith validation rule (#584)
- Add DoesntEndWith and DoesntStartWith validation rules (#585)
- Add MinDigits and MaxDigits validation rules (#586)
- Add DataCollection reject() method (#593)
- Add generic type for WithData trait (#597)
- Fix issue where non set optional values were transformed (#602)
- Fix issue where parameters passed to Laravel Collection methods would sometimes provide alternative results (issue: #607)

## 1.5.3 - 2023-12-01

- MimeTypes validation fix on v1 (#596)

## 3.9.2 - 2023-10-20

- Fix breaking compatibility #590

## 3.9.1 - 2023-10-12

- Add Declined and DeclinedIf validation attributes (#572)
- Add MacAddress validation attribute (#573)
- Add RequiredArrayKeys validation attribute (#574)
- Support Arrayable when casting to DataCollection (#577)
- Fetch attributes from parent classes to allow reusability (#581)
- Fix issue where non-set optional values would be transformed

## 3.9.0 - 2023-09-15

- Fix an issue where computed values could not be set as null
- Fix for no rules created on optional|nullable Data object and Collection (#532)
- Add `CustomValidationAttribute`'s
- Copy partial trees when using array access on a collection

## 3.8.1 - 2023-08-11

- fix abstract json cast format

## 3.8.0 - 2023-08-09

- Add Hidden Attribute (#505)
- Add Null value support for RequiredUnless Validation (#525)
- Add abstract eloquent casts (#526)

## 3.7.1 - 2023-08-04

- fix target namespace when creating files with Laravel Idea (#497)
- allow collection to be created passing null (#507)
- add Ulid validation rule (#510)
  -add TARGET_PARAMETER to Attribute for improved Validation (#523)

## 3.7.0 - 2023-07-05

- Add support for better exception messages when parameters are missing
- Fix default properties generating validation rules when not provided
- Add last() method on DataCollection (#486)
- Add new manual written present attribute rule always overwrites a generated required rule
- Added the ability to create data files, casts, transformers and rules using Laravel Idea plugin #485

## 3.6.0 - 2023-06-02

- Add some config options to the `make:data` command (#449, #335)

## 1.5.2 - 2023-05-31

- Add laravel v10 support

## 3.5.1 - 2023-05-12

- Add ability to instantiate an eloquent cast when null value using null database values (#425)
- Revert only use validated data (https://github.com/spatie/laravel-data/pull/438) -> see #432

## 3.5.0 - 2023-05-05

- Add support for computed values
- Add merge method to Data Collections (#419)
- Allow field references in same validation attribute
- Generic return type of toResponse function (#437)
- Only use validated data (#438)
- Add missing constructor parameters to error message (#433)

## 3.4.4 - 2023-04-14

- Make Lazy Macroable

## 3.4.3 - 2023-04-12

- Update TypeScript Transformer with new closure lazy type

## 3.4.2 - 2023-04-12

- Add support for Lazy Closures which can be used with inertia

## 3.4.1 - 2023-04-07

- reverted the reverted #393 branch

## 3.4.0 - 2023-04-07

- Allow to fetch Lazy data properties in unified way (#403)

## 3.3.3 - 2023-04-03

- revert: Fix usage of properties' mapped output names in 'only', 'except', 'includes', and 'excludes' operations (https://github.com/spatie/laravel-data/pull/393)

## 3.2.2 - 2023-03-30

- Fix issue when validating and creating a data object was impossible to use magic creation methods
- Fix usage of properties' mapped output names in 'only', 'except', 'includes', and 'excludes' operations (#393)
- Add some extra documentation

## 3.2.1 - 2023-03-24

- Introduce FormRequestNormalizer (#380)
- Add support for models without all columns (#385)

## 3.2.0 - 2023-03-17

- Add benchmarks for testing data performance
- Add some performance improvements for data creation and transformation

## 3.1.3 - 2023-03-16

- Performance improvements

## 3.1.2 - 2023-03-10

- Fix issue where promoted protected constructor parameters were seen as possible property canidates
- Allow using closures within the with method (#372)
- Add support for Validation Rule Contract in Rule rule (#362)
- Allow laravel-data validation rules to be used in Laravel validator (#375)

## 3.1.1 - 2023-03-02

- Add WithCastable attribute (#349)
- Quote non-standard object keys when transforming to Typescript (#367)

## 3.1.0 - 2023-02-10

- Allow filling props from route parameters (#341)
- Better handing of custom regex validation rules(#342)
- Fix types (#348)
- Improve validation when strings are passed for nested data objects
- Add FieldReference to Before and After Validation attribute (#345)

## 3.0.0 - 2023-02-01

- The validation logic is completely rewritten
- Add support for nested nullable and optional data objects
- Allow referencing other fields in validation attributes when the data is nested
- Allow referencing url parameters (and even model properties) in validation attributes
- Allow circular dependencies when validating data
- Add support for nested validation messages and attributes
- Package specific properties are renamed to avoid conflicts
- Serialization logic updated to only serialize your properties
- Prevent fatal error when passing a string containing only an integer to JSON Normalizer (#322)
- Ignore leading ! in DateTimeInterfaceTransformer (#325)
- Extend the make:data command to be more flexible (#335)

## 2.2.3 - 2023-01-24

- Add Laravel 10.x support (#331)

## 2.2.2 - 2023-01-09

- Add a way to prepend DataPipes to the pipeline (#305)
- Better IDE completion (#307)
- Make eloquent collection cast respect collection (#308)

## 2.2.1 - 2022-12-21

- fix support to return 201 status code in Json Responses (#291)

## 2.2.0 - 2022-12-21

- Add generic return type for DataCollection::toCollection (#290)
- Improve ide.json completion (#294)
- Pass in payload relative to the data object when generating rules (#264)
- ignore phpstorm attributes when instantiating and add readonly property (#281)

## 2.1.0 - 2022-12-07

- Stop using custom pipeline when creating data magically from requests
- set timezones in datetime casts (#287 )

## 2.0.16 - 2022-11-18

- add support for optional properties in TypeScript transformer (#153)

## 2.0.15 - 2022-11-17

- refactor test suite to Pest (#239)
- update the docs
- add a `StudlyMapper`
- add better support for Livewire

## 2.0.14 - 2022-11-10

- accept 'float' as data type (#237)
- fix typo in mime type validation rule(#243)
- add support for enums in validation attributes
- add support for withoutTrashed to exists validation attribute (#248)
- add PHP 8.2 testing in GH actions
- add ability to modify properties before the data pipeline (#247)

## 2.0.13 - 2022-10-14

- fix first and last page url are never null (#215)
- add ability to statically retrieve a data models rules (#221)
- improved pattern matching in DataCollectionAnnotationReader (#225)
- add ExcludeWithout attribute rule (#230)
- improve getValidationRules to also retrieve wildcard rules (#231)
- return property with or without mapping the name (#199)

## 2.0.12 - 2022-09-29

- Improve pattern matching in DataCollectionAnnotationReader (#214)
- Add ability to retrieve rules for a data object (#221)

## 2.0.11 - 2022-09-28

- Use generics with Data::collection (#213)
- Improve pattern matching in DataCollectionAnnotationReader (#214)
- Fix TypeScript Transformer paginated collections, first and last page url are never null (#215)

## 2.0.10 - 2022-09-07

- Add support for a JsonSerializer
- Fix generic iterator definition for data collections
- Fix validation of DataCollection objects not working as expected

## 2.0.9 - 2022-08-23

- Add support for timezone manipulation in casts and transformers (#200)

## 2.0.8 - 2022-08-18

- use AbstractCloner instead of reflection (#196)

## 2.0.7 - 2022-08-18

- Add var dumper caster (#195)
- Allow direct pipeline call (#193)
- Rename ArrayableNormalizer (#191)
- Make normalizers configurable (#190)

## 2.0.6 - 2022-08-12

- Add cast lazy condition result to bool (#186)
- Add conditional return types for collections (#184)
- Fix windows tests

## 2.0.5 - 2022-08-10

- Fix validation rules not being used for data collections and objects

## 2.0.4 - 2022-08-01

- Add IDE completion support (#182)

## 2.0.3 - 2022-07-29

- Add support for invokable rules (#179)

## 2.0.2 - 2022-07-29

- more consistent use of transform through codebase for transforming collections and data

## 2.0.1 - 2022-07-13

- Add class defined partials (#164)
- Use mapped property names in TypeScript (#154)
- Add make:data command (#157)
- Better support for overwritten rules
- Add support for Lazy inertia props (#163)
- Add support for array query parameters as partials (#162)

## 2.0.0 - 2022-07-08

Version 2 of laravel-data is a complete overhaul, we've almost completely rewritten the package.

This is a (non-complete) list of the new features:

- A DataPipeline
- Data normalizers
- Mappable property names
- Wrapping of data in responses
- `only` and `except` methods on Data and DataCollections
- Multiple parameter magic methods
- Optional properties
- Split DataCollections
- Better support for TypeScript Transformer
- And a lot more ...

## 1.5.1 - 2022-07-08

- Fix optional parameters (#152)
- Use protected properties and methods (#147 )

## 1.5.0 - 2022-05-25

## What's Changed

- add values() on DataCollection by @Nielsvanpach in https://github.com/spatie/laravel-data/pull/135

## New Contributors

- @Nielsvanpach made their first contribution in https://github.com/spatie/laravel-data/pull/135

**Full Changelog**: https://github.com/spatie/laravel-data/compare/1.4.7...1.5.0

## 1.4.7 - 2022-05-16

- support $payload as a dependency in rules (#123)

## 1.4.6 - 2022-04-06

- Add Dependency Injection for rules, messages, and attributes methods (#114)

## 1.4.5 - 2022-03-18

- Add support for stdClass payload casting (#106)

## 1.4.4 - 2022-03-18

- use present validation rule instead of required for data collections

## 1.4.3 - 2022-02-16

- allow using default password config in password validation attribute (#94)
- solve binding issues on Laravel Octane (#101)
- fixes a bug where models nested by relation could not be created due to date casts
- add a `links` array to the paginated response
- stop execution of `lazy::whenLoaded` closure when the relation is `null`

## 1.4.2 - 2022-01-26

- fix for aborting value assignment after a false boolean (#80)
- add a `WithoutValidation` attribute
- allow transformers to target native types, data collections and data objects

## 1.4.1 - 2022-01-21

- Allow transformers to target Data and DataCollections

## 1.4.0 - 2022-01-20

- removes checks for built in types and `isBuiltIn` from `DataProperty`
- add better support for defaults

## 1.3.3 - 2022-01-20

## What's Changed

- Laravel 9.x by @aidan-casey in https://github.com/spatie/laravel-data/pull/77
- Removes Spatie's Laravel Enums from dev requirements by @aidan-casey in https://github.com/spatie/laravel-data/pull/76

## New Contributors

- @aidan-casey made their first contribution in https://github.com/spatie/laravel-data/pull/77

**Full Changelog**: https://github.com/spatie/laravel-data/compare/1.3.2...1.3.3

## 1.3.2 - 2022-01-19

- add support for json_encode to Data objects
- add support for json_encode to DataCollection objects

## 1.3.1 - 2022-01-07

- add basic support for intersection types
- allow casting of built in PHP types
- add support for inferring enum rules
- fix an issue where an Enum validation attribute would not work

## 1.2.5 - 2021-12-29

- fixes the RequiredRuleResolver to support custom rules like `Enum`
- add an Enum validation rule attribute

## 1.2.4 - 2021-12-16

- rename the `authorized` method to `authorize`
- disable the behavior were excluded conditional properties still could be included

## 1.2.3 - 2021-12-03

- fix return type notice message

## 1.2.1 - 2021-11-19

- fixes an issue where data object could not be created when it had lazy nested data object
- fixes windows test suite

## 1.2.0 - 2021-11-16

- when creating data objects, we now will always run validation when a `Request` object is given not only when a data object is injected
- removal of `DataFromRequestResolver`
- added `DataValidatorResolver`

## 1.1.0 - 2021-11-12

- change data property types collection checking procedure
- move `spatie/test-time` dependency to require-dev
- expand support for nested data object creation (#19)
- expand support for annotating data collections

## 1.0.4 - 2021-11-04

- revert allow ignoring with a closure within a unique rule

## 1.0.3 - 2021-11-02

- allow ignoring with a closure within a unique rule

## 1.0.2 - 2021-11-02

- add a `WithData` trait for quicker getting data from objects

## 1.0.1 - 2021-10-28

- fix required rules being added when not allowed

## 1.0.0 - 2021-10-27

- initial release
