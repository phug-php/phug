
Phug Renderer
=============

What is Phug Renderer?
----------------------

The Phug renderer get pug string or a pug file then return a markup string.

Installation
------------

Install via Composer

```bash
composer require phug/renderer
```

Usage
-----

```php

$renderer = new Phug\Renderer($options);
$html = $renderer->render($pugInput);

//$html is now a string of HTML or any other markup according to the formatter you choose (XML, xHTML, etc.)
```

Security contact information
----------------------------

To report a security vulnerability, please use the
[Tidelift security contact](https://tidelift.com/security).
Tidelift will coordinate the fix and disclosure.
