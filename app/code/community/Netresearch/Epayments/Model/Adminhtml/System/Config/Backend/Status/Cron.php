<?php

// @todo refactor
class Netresearch_Epayments_Model_Adminhtml_System_Config_Backend_Status_Cron extends Mage_Core_Model_Config_Data
{
    const INGENICO_CRON_ORDER_STATUS_UPDATE_STRING_PATH = 'crontab/jobs/ingenico_order_status_update/schedule/cron_expr';
    const INGENICO_CRON_INTERVAL_STATUS_UPDATE          = 'ingenico/orders_cron/interval_status_update';
    const INGENICO_CRON_FREQUENCY                       = 'ingenico/orders_cron/frequency';

    protected function _afterSave()
    {
        $interval = Mage::getStoreConfig(self::INGENICO_CRON_INTERVAL_STATUS_UPDATE);

        $cronExprArray = array(
            '0',                  # Minute
            intval($interval),    # Hour
            '*',                  # Day of the Month
            '*',                  # Month of the Year
            '*',                  # Day of the Week
        );
        $cronExprString = join(' ', $cronExprArray);

        try {
            Mage::getModel('core/config_data')
                ->load(self::INGENICO_CRON_ORDER_STATUS_UPDATE_STRING_PATH, 'path')
                ->setValue($cronExprString)
                ->setPath(self::INGENICO_CRON_ORDER_STATUS_UPDATE_STRING_PATH)
                ->save();
        } catch (Exception $e) {
            throw new Exception(Mage::helper('cron')->__('Unable to save the cron expression.'));
        }
    }
}
