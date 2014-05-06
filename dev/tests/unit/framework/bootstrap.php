<?php

$includePaths = array(
    realpath(__DIR__ . '/../testsuite'),
    realpath(__DIR__ . '/../../../../lib'),
    realpath(__DIR__ . '/../../../../app/code/local'),
    realpath(__DIR__ . '/../../../../app/code/community'),
    realpath(__DIR__ . '/../../../../app/code/core')
);

set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $includePaths));
spl_autoload_register('MageAutoloaderUnitTests::load');

/**
 * Class MageAutoloaderUnitTests
 */
class MageAutoloaderUnitTests
{
    /**
     * Autoloader
     *
     * @param string $class
     * @throws Exception
     */
    static public function load($class)
    {
        $includes = get_include_path();
        $paths = explode(PATH_SEPARATOR, $includes);
        $file = str_replace('_', '/', $class) . '.php';
        foreach ($paths as $path) {
            $filename = realpath($path) . DIRECTORY_SEPARATOR . $file;
            if (is_file($filename)) {
                require_once $filename;
                return;
            }
        }
        throw new Exception("File $file not found. Includes:\n" . str_replace(PATH_SEPARATOR, "\n", $includes));
    }
}

