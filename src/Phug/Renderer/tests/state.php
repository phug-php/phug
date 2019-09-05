<?php

chdir(__DIR__.'/..');
echo "Executing unit tests...\n";
$phpunit = shell_exec(realpath('vendor/bin/phpunit').' -c ./vendor/phug/dev-tool/config/phpunit.xml');

if (preg_match('/^OK /m', $phpunit)) {
    echo "100%\n";
    exit(0);
}

if (!preg_match('/^Tests: (\d+)((, [^,]+)*)\.$/m', $phpunit, $match)) {
    echo "Error:\n$phpunit";
    exit(1);
}

$text = $match[0];
$parts = explode(',', trim($match[2], ' ,'));
$data = [];
foreach ($parts as $part) {
    list($name, $number) = explode(':', trim($part));
    $data[strtolower($name)] = intval(trim($number));
}

echo $text."\n".round($data['assertions'] * 100 / array_sum($data), 1)."%\n";
exit(0);
