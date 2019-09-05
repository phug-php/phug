
Phug Parser
===========

What is Phug Parser?
--------------------

The Phug parser utilizes the Phug lexer and parses the tokens it generates into an AST

Installation
------------

Install via Composer

```bash
composer require phug/parser
```

Usage
-----

```php

$parser = new Phug\Parser($options);
$root = $parser->parse($pugInput);

//$root is now a Phug\Parser\Node\DocumentNode element
```
