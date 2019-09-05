
Phug Reader
===========

What is Phug Reader?
--------------------

The Reader-class is a small utility that can parse and scan strings for specific entities.

It's mostly based on Regular Expressions, but also brings in tools to scan strings and expressions of any kind (e.g. string escaping, bracket counting etc.)

The string passed to the reader is swallowed byte by byte through `consume`-mechanisms.
When the string is empty, the parsing is done (usually).

This class is specifically made for lexical analysis and expression-validation.

Installation
------------

Install via Composer

```bash
composer require phug/reader
```

Usage
-----

### Basics

The process of reading with the `Phug\Reader` involves **peeking** and **consuming**.
You **peek**, check, if it's what you wanted and if it is, you **consume**.

**read**-methods on the Reader will peek and consume automatically until they found what you searched for.
**match**-methods work like **peek**, but work with regular expressions.

Lets create a small example code to parse:
```php
$code = 'someString = "some string"';
```

Now we create a reader for that code
```php
$reader = new Reader($code);
```

If you want a fixed encoding, use the second `$encoding` parameter.

Now we can do our reading process.
First we want to read our identifier. We can do that easily with `readIdentifier()` which returns `null` if no identifier has been encountered and the identifier found otherwise.
It will stop on anything that is _not_ an identifier-character (The space after the identifier, in this case)
```php
$identifier = $reader->readIdentifier();
if ($identifier === null) {
    throw new Exception("Failed to read: Identifier expected");
}
    
var_dump($identifier); //`someString`
```

To get to our `=`-character directly, we can just skip all spaces we encounter.
This also allows for any spacing you want (e.g. you can indent the above with tabs if you like)
```php
$reader->readSpaces();
```
If we need the spaces, we can always catch the returned result.
If no spaces are encountered, it just returns `null`.

Now we want to parse the assignment-operator (`=`) (or rather, validate that it's there)
```php
if (!$reader->peekChar('=')) {
    throw new Exception("Failed to read: Assignment expected");
}
    
//Consume the result, since we're `peek`ing, not `read`ing.
$reader->consume();
```

Skip spaces again
```php
$reader->readSpaces();
```

and read the string.
If no quote-character (`"` or `'`) is encountered, it will return null.
Otherwise, it will return the (already parsed) string, without quotes.
Notice that you have to check `null` explicitly, since we could also have an empty string (`""`) which evaluates to `true` in PHP
```php
$string = $reader->readString();

if ($string === null) {
    throw new Exception("Failed to read: Expected string");
}

var_dump($string); //`some string`
```
The quote-style encountered will be escaped by default, so you can scan `"some \" string"` correctly.
If you want to add other escaping, use the first parameter of `readString`.

Now you have all parts parsed to make up your actual action
```php
echo "Set `$identifier` to `$string`"; //Set `someString` to `some string`
```

and it was validated on that way.

This was just a small example, Phug Reader is made for loop-parsing.


### Build a small tokenizer

```php
use Phug\Reader;

//Some C-style example code
$code = 'someVar = {a, "this is a string (really, it \"is\")", func(b, c), d}';

$reader = new Reader($code);

$tokens = [];
$blockLevel = 0;
$expressionLevel = 0;
while ($reader->hasLength()) {
    
    //Skip spaces of any kind.
    $reader->readSpaces();
    
    //Scan for identifiers
    if ($identifier = $reader->readIdentifier()) {
        
        $tokens[] = ['type' => 'identifier', 'name' => $identifier];
        continue;
    }
    
    //Scan for Assignments
    if ($reader->peekChar('=')) {
        
        $reader->consume();
        $tokens[] = ['type' => 'assignment'];
        continue;
    }
    
    //Scan for strings
    if (($string = $reader->readString()) !== null) {
        
        $tokens[] = ['type' => 'string', 'value' => $string];
        continue;
    }
    
    //Scan block start
    if ($reader->peekChar('{')) {
        
        $reader->consume();
        $blockLevel++;
        $tokens[] = ['type' => 'blockStart'];
        continue;
    }
    
    //Scan block end
    if ($reader->peekChar('}')) {
    
        $reader->consume();
        $blockLevel--;
        $tokens[] = ['type' => 'blockEnd'];
        continue;
    }
    
    //Scan parenthesis start
    if ($reader->peekChar('(')) {
        
        $reader->consume();
        $expressionLevel++;
        $tokens[] = ['type' => 'listStart'];
        continue;
    }
    
    //Scan parenthesis end
    if ($reader->peekChar(')')) {
        
        $reader->consume();
        $expressionLevel--;
        $tokens[] = ['type' => 'listEnd'];
        continue;
    }
    
    //Scan comma
    if ($reader->peekChar(',')) {
        
        $reader->consume();
        $tokens[] = ['type' => 'next'];
        continue;
    }

    throw new \Exception(
        "Unexpected ".$reader->peek(10)
    );
}

if ($blockLevel || $expressionLevel)
    throw new \Exception("Unclosed bracket encountered");

var_dump($tokens);
/* Output:
[
    ['type' => 'identifier', 'name' => 'someVar'],
    ['type' => 'assignment'],
    ['type' => 'blockStart'],
    ['type' => 'identifier', 'name' => 'a'],
    ['type' => 'next'],
    ['type' => 'string', 'value' => 'this is a string (really, it "is")'],
    ['type' => 'next'],
    ['type' => 'identifier', 'name' => 'func'],
    ['type' => 'listStart'],
    ['type' => 'identifier', 'name' => 'b'],
    ['type' => 'next'],
    ['type' => 'identifier', 'name' => 'c'],
    ['type' => 'listEnd'],
    ['type' => 'next'],
    ['type' => 'identifier', 'name' => 'd'],
    ['type' => 'blockEnd']
]
*/
```


### Keep expressions intact

Sometimes you want to keep expressions intact, e.g. when you allow inclusion of third-party-code that needs to be parsed separately.

The Reader brings a bracket-counting-utility that can do just that exactly.
Let's take `Jade` as an example:
```jade
a(href=getUri('/abc', true), title=(title ? title : 'Sorry, no title.'))
```

To parse this, let's do the following:
```php
//Scan Identifier ("a")
$identifier = $reader->readIdentifier();

$attributes = [];
//Enter an attribute block if available
if ($reader->peekChar('(')) {

    $reader->consume();
    while ($reader->hasLength()) {
    
    
        //Ignore spaces
        $reader->readSpaces();
    
    
        //Scan the attribute name
        if (!($name = $this->readIdentifier())) {
            throw new \Exception("Attributes need a name!");
        }
        
        
        //Ignore spaces
        $reader->readSpaces();
        
        
        //Make sure there's a =-character
        if (!$reader->peekChar('=')) {
            throw new \Exception("Failed to read: Expected attribute value");
        }
            
        $reader->consume();
        
        
        //Ignore spaces
        $reader->readSpaces();
        
        
        //Read the expression until , or ) is encountered
        //It will ignore , and ) inside any kind of brackets and count brackets correctly until we actually
        //reached the end-bracket
        $value = $reader->readExpression([',', ')']);
        
        
        //Add the attribute to our attribute array
        $attributes[$name] = $value;
        
        
        //If we don't encounter a , to go on, we break the loop
        if (!$reader->peekChar(',')) {
            break;
        }
            
            
        //Else we consume the , and continue our attribute parsing
        $reader->consume();       
    }
    
    //Now make sure we actually closed our attribute block correctly.
    if (!$reader->peekChar(')')) {
        throw new \Exception("Failed to read: Expected closing bracket");
    }
}


$element = ['identifier' => $identifier, 'attributes' => $attributes];

var_dump($element);
/* Output:
[
    'identifier' => 'a',
    'attributes' => [
        'href' => 'getUri(\'/abc\', true)',
        'title' => '(title ? title : \'Sorry, no title.\')'
    ]
]
*/
```

You now got a parser for (really, really basic) Jade-elements!
It can handle as many attributes as you like with all possible values you could think of without ever breaking the listing, regardless of contained commas and brackets.


Digging deeper, the Phug Reader is actually able to lex source code and text of any kind.