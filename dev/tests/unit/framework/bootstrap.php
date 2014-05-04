<?php
$includePaths = array(
    get_include_path(),
    './testsuite',
    '../../../lib',
    '../../../app/code/core',
    '../../../app/code/community'
);
set_include_path(implode(PATH_SEPARATOR, $includePaths));
spl_autoload_register('mageAutoloadUnitTests');

function mageAutoloadUnitTests($class)
{
    $file = str_replace('_', '/', $class) . '.php';
    require_once $file;
}
