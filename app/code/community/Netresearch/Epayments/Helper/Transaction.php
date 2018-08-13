<?php
/**
 * Netresearch_ePayments
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
 * @category  ePayments
 * @package   Netresearch_ePayments
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Payment as IngenicoPayment;
use Ingenico\Connect\Sdk\Domain\Refund\Definitions\RefundResult as IngenicoRefund;

class Netresearch_Epayments_Helper_Transaction extends Mage_Core_Helper_Abstract
{
    /**
     * @param AbstractOrderStatus|IngenicoPayment|IngenicoRefund $orderStatus
     * @return mixed[]
     */
    public static function getTransactionVisibleInfo(AbstractOrderStatus $orderStatus)
    {
        $visibleInfo = array();
        $visibleInfo['status'] = $orderStatus->status;

        $visibleInfo = array_merge(
            $visibleInfo,
            get_object_vars($orderStatus->statusOutput)
        );

        array_walk(
            $visibleInfo,
            function(&$value) {
                if (is_bool($value)) {
                    $value = $value ? 'Yes' : 'No';
                }
            }
        );

        $visibleInfo = array_filter($visibleInfo);

        return $visibleInfo;
    }
}
