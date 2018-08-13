<?php

define('MAGENTO_ROOT_DIR', getenv('MAGENTO_ROOT_DIR'));
spl_autoload_register('load', true, true);

/**
 * @param $class
 */
function load($class)
{
    $paths = array();
    $paths[] = MAGENTO_ROOT_DIR . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR . 'local';
    $paths[] = MAGENTO_ROOT_DIR . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR . 'community';
    $paths[] = MAGENTO_ROOT_DIR . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR . 'core';
    $paths[] = MAGENTO_ROOT_DIR . DIRECTORY_SEPARATOR . 'lib';

    $classFile = str_replace(' ', DIRECTORY_SEPARATOR, ucwords(str_replace('_', ' ', $class)));
    $classFile.= '.php';
    foreach ($paths as $path) {
        if (file_exists($path . DIRECTORY_SEPARATOR . $classFile)) {
            include_once $path . DIRECTORY_SEPARATOR . $classFile;
        }
    }

    /**
     * @TODO: must be done via composer
     */
    $prefix = 'Ingenico\\Connect\\Sdk';
    $ingenicoLibPaths[] = MAGENTO_ROOT_DIR . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR .'Ingenico' . DIRECTORY_SEPARATOR . 'ServerSDK' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Ingenico' . DIRECTORY_SEPARATOR . 'Connect' . DIRECTORY_SEPARATOR . 'Sdk';
    $ingenicoLibPaths[] = MAGENTO_ROOT_DIR . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR .'Ingenico' . DIRECTORY_SEPARATOR . 'ServerSDK' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'Ingenico' . DIRECTORY_SEPARATOR . 'Connect' . DIRECTORY_SEPARATOR . 'Sdk';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);

    foreach ($ingenicoLibPaths as $ingenicoPath) {
        $file = $ingenicoPath . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            include_once($file);
        }
    }
}
