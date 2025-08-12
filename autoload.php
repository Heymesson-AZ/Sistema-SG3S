<?php
function autoloadModel($className)
{
    $filename = 'model/' . $className . '.class.php';
    if (is_readable($filename)) {
        require $filename;
    }
}

function autoloadController($className)
{
    $filename = 'controller/' . $className . '.class.php';
    if (is_readable($filename)) {
        require $filename;
    }
}
spl_autoload_register('autoloadModel');
spl_autoload_register('autoloadController');
?>