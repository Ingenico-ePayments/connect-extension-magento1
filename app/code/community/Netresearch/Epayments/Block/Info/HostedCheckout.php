<?php
use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;

class Netresearch_Epayments_Block_Info_HostedCheckout extends Mage_Payment_Block_Info
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('epayments/info/hostedCheckout.phtml');
    }

    /**
     * Get label of the selected payment product
     *
     * @return string|null
     */
    public function getPaymentProductLabel()
    {
        return $this->getInfo()->getAdditionalInformation(HostedCheckout::PRODUCT_LABEL_KEY);
    }

    /**
     * Get payment status definition from store config
     *
     * @return string|null
     */
    public function getPaymentStatusInfo()
    {
        $status = $this->getInfo()->getAdditionalInformation(HostedCheckout::PAYMENT_STATUS_KEY);
        if ($status) {
            $info = Mage::helper('netresearch_epayments')->getPaymentStatusInfo($status);
            if ($info) {
                return $info;
            }
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function toPdf()
    {
        $this->setTemplate('epayments/info/hostedCheckout.phtml');
        return $this->toHtml();
    }
}
