<?php

echo 'PHP_MAJOR_VERSION: ';
echo PHP_MAJOR_VERSION."\n";
echo 'PHP_VERSION: ';
echo PHP_VERSION."\n";
echo 'phpversion(): ';
echo phpversion()."\n";
echo 'is_object(null): ';
var_dump(is_object(null));
echo 'method_exists(null, "foo"): ';
var_dump(method_exists(null, "foo"));
