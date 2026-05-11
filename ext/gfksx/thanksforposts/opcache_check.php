<?php
header('Content-Type: text/plain; charset=UTF-8');

$keys = [
    'opcache.enable',
    'opcache.enable_cli',
    'opcache.validate_timestamps',
    'opcache.revalidate_freq',
    'opcache.memory_consumption',
    'opcache.max_accelerated_files',
    'PHP_VERSION',
    'SAPI',
];

foreach ($keys as $k)
{
    if ($k === 'PHP_VERSION')
    {
        echo $k . ' = ' . PHP_VERSION . PHP_EOL;
        continue;
    }

    if ($k === 'SAPI')
    {
        echo $k . ' = ' . php_sapi_name() . PHP_EOL;
        continue;
    }

    $v = ini_get($k);
    echo $k . ' = ' . (($v === false) ? 'N/A' : $v) . PHP_EOL;
}

