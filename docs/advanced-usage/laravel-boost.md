---
title: Laravel Boost
weight: 19
---

## Laravel Boost Support

This package includes Laravel Boost AI guidelines and skills.

After installing `laravel/boost` in a Laravel application, run:

```shell
php artisan boost:install
```

To refresh existing Boost resources later:

```shell
php artisan boost:update
```

To discover newly installed package Boost resources:

```shell
php artisan boost:update --discover
```

Use `boost:update --discover` after installing new packages so Boost can prompt for newly available third-party guidelines and skills.

The package-provided skill is located in:

```txt
resources/boost/skills/laravel-data-development
```
