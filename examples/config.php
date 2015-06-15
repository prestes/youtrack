<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
 *  // in my .auth.php file, (needed for working examples):
 *  define('YOUTRACK_URL', 'https://...');
 *  if (isset($root)) {
 *  define('YOUTRACK_USERNAME', 'root');
 *  define('YOUTRACK_PASSWORD', '**secret**');
 *  } else {
 *  define('YOUTRACK_USERNAME', 'normal-user');
 *  define('YOUTRACK_PASSWORD', 'secret**');
 *  }
 */
if (file_exists('.auth.php')) {
    include_once '.auth.php';
}

if (!defined('YOUTRACK_URL')
    || !defined('YOUTRACK_USERNAME')
    || !defined('YOUTRACK_PASSWORD')
    || !defined('YOUTRACK_PROJECT')
) {
    throw new \Exception('Please define your credential constants in config.php to run these examples.');
}


define('YOUTRACK_AUTOLOADING', false);

include_once './../vendor/autoload.php';

if (YOUTRACK_AUTOLOADING) {
// We need autoloading for this library. If you have already PSR-0 autoloading in you project
// please remove the following lines
spl_autoload_register(function ($className)
    {
        if (class_exists($className)) {
            return true;
        }
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        require $fileName;
    });
}
