
Phug Invoker
==========

What is Phug Invoker?
---------------------

Register callbacks with argument class typing and call the invoker
to execute only the ones that match a given object.

Installation
------------

Install via Composer

```bash
composer require phug/invoker
```

Usage
-----

```php
class Foo {}
class Bar {}

$invoker = new \Phug\Invoker([
    function (Foo $foo) {
        return 'foo';
    },
    function (Bar $bar) {
        return 'BAR';
    },
]);

$invoker->invoke(new Foo); // ['foo']
$invoker->invoke(new Bar); // ['BAR']
```
