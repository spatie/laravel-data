**✏️ Describe the bug**
A clear and concise description of what the bug is.

**↪️ To Reproduce**
Provide us a pest test like this one which shows the problem:

```php

it('cannot validate nested data', function () {
    class ChildData extends Data
    {
        public function __construct(
            #[Min(10)]
            public string $name,
        ) {
        }
    }

    class BaseData extends Data
    {
        public function __construct(
            public ChildData $child,
        ) {
        }
    }

    // Validation exception is not thrown (off course it is but for documentation purposes it is not)
    dd(BaseData::validateAndCreate(['child' => ['name' => 'Ruben']]));
});
```

Assertions aren't required, a simple dump or dd statement of what's going wrong is good enough 😄

**✅ Expected behavior**
A clear and concise description of what you expected to happen.

**🖥️ Versions**

Laravel:
Laravel Data:
PHP:
