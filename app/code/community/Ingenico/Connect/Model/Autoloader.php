<?php
/**
 * Ingenico_Connect
 *
 * See LICENSE.txt for license details.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @category  Epayments
 * @package   Ingenico_Connect
 */

class Ingenico_Connect_Model_Autoloader
{
    /**
     * Register this Ingenico_Connect_Model_Autoloader::load as autoload function
     */
    public static function register()
    {
        spl_autoload_register('Ingenico_Connect_Model_Autoloader::load', true, true);
    }
    /**
     * Autoloading Ingenico lib
     *
     * @param $class
     */
    public static function load($class)
    {
        $prefix = 'Ingenico\\Connect\\Sdk';
        $ingenicoLibPaths[] = BP . DS . 'lib' . DS .'Ingenico' . DS . 'ServerSDK' . DS . 'src' . DS . 'Ingenico' . DS . 'Connect' . DS . 'Sdk';
        $ingenicoLibPaths[] = BP . DS . 'lib' . DS .'Ingenico' . DS . 'ServerSDK' . DS . 'lib' . DS . 'Ingenico' . DS . 'Connect' . DS . 'Sdk';

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
}
