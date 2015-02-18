<?php

error_reporting(-1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

date_default_timezone_set('UTC');

// simple psr-4 loader, example from
// https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
spl_autoload_register(function ($class) {
    $prefix = 'Geekwright\\Po\\';
    $base_dir = dirname(__DIR__) . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});
