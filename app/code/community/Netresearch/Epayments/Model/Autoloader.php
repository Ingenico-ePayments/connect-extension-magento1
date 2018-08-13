<?php
/**
 * Netresearch_Epayments
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @category  Epayments
 * @package   Netresearch_Epayments
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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