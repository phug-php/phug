
Phug Dependency Injection
=========================

What is Phug Dependency Injection?
----------------------------------

This project allow to provide helpers functions and values,
to require them and to dump all required dependencies as
a PHP array export.

Installation
------------

Install via Composer

```bash
composer require phug/dependency-injection
```

Usage
-----

```php
use Phug\DependencyInjection;

$dependencies = new DependencyInjection();
$dependencies->register('limit', 42);
$dependencies->provider('clock', static function () {
    return new Clock();
});

$dependencies->provider('expiration', ['clock', 'limit', static function (ClockInterface $clock, $limit) {
    return static function ($margin) use ($clock, $limit) {
        $delta = $limit - $margin;

        return $clock->now()->modify("$delta days");
    };
}]);

$expiration = $dependencies->call('expiration'); // return new DateTimeImmutable('now + 42 days')
$expiration = $dependencies->call('expiration', 20); // return new DateTimeImmutable('now + 22 days')
```

Security contact information
----------------------------

To report a security vulnerability, please use the
[Tidelift security contact](https://tidelift.com/security).
Tidelift will coordinate the fix and disclosure.
