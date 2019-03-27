<?php
/**
 * Netresearch_Epayments
 *
 * See LICENSE.txt for license details.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @category  Epayments
 * @package   Netresearch_Epayments
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @link      http://www.netresearch.de/
 */

class Netresearch_Epayments_Model_Autoloader
{
    /**
     * Register this Netresearch_Epayments_Model_Autoloader::load as autoload function
     */
    public static function register()
    {
        spl_autoload_register('Netresearch_Epayments_Model_Autoloader::load', true, true);
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
