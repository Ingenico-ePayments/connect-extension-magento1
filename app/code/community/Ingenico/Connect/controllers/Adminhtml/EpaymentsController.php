<?php

class Ingenico_Connect_Adminhtml_EpaymentsController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Download Log file action
     */
    public function downloadLogFileAction()
    {
        /** @var Ingenico_Connect_Model_ConfigInterface $config */
        $config = Mage::getSingleton('ingenico_connect/config');

        $fileToDownload = Mage::getBaseDir('var') . DS . 'log' . DS . $config->getLogAllRequestsFile();
        if (is_file($fileToDownload)) {
            $this->_prepareDownloadResponse(
                basename($fileToDownload),
                array(
                    'value' => $fileToDownload,
                    'type' => 'filename'
                )
            );
        }

        $this->_redirectReferer();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/ingenico_epayments');
    }
}
