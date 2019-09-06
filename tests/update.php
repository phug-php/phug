<?php

function removeDirectory($directory)
{
    foreach (scandir($directory) as $entity) {
        if ($entity === '.' || $entity === '..') {
            continue;
        }

        if (is_dir($directory.'/'.$entity)) {
            removeDirectory($directory.'/'.$entity);
            continue;
        }

        @unlink($directory.'/'.$entity);
    }

    return @rmdir($directory);
}

$tempDirectories = ['pug'];
$directories = ['fixtures', 'cases'];

chdir(__DIR__);

foreach ($tempDirectories as $directory) {
    if (file_exists($directory)) {
        echo "Remove $directory directory\n";
        if (!removeDirectory($directory)) {
            echo "You should first remove $directory manually.\n";
            exit(1);
        }
    }
}

echo shell_exec('git clone https://github.com/pugjs/pug');

echo "Backup old cases\n";
foreach ($directories as $directory) {
    if (file_exists($directory)) {
        rename($directory, $directory.'-save');
    }
}

echo "Extract new cases\n";
foreach ($directories as $directory) {
    rename('pug/packages/pug/test/'.$directory, $directory);
}

clearstatcache();

foreach (array_merge($tempDirectories, array_map(function ($directory) {
    return $directory.'-save';
}, $directories)) as $directory) {
    if (file_exists($directory)) {
        echo "Remove $directory directory\n";
        if (!removeDirectory($directory)) {
            echo "You should remove $directory manually.\n";
        }
    }
}

echo "Done\n";
