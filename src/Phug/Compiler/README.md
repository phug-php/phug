
Phug Compiler
===========

What is Phug Compiler?
--------------------

The Phug compiler get pug string or a pug file then return a PHP compiled template.

Installation
------------

Install via Composer

```bash
composer require phug/compiler
```

Usage
-----

```php

$compiler = new Phug\Compiler($options);
$phtml = $compiler->compile($pugInput);

//$phtml is the PHP compiled template
```
