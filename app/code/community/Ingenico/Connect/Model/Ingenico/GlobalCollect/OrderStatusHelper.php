<?php

use Ingenico\Connect\Sdk\Domain\Capture\Definitions\Capture;
use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\Payment;
use Ingenico_Connect_Model_Ingenico_StatusInterface as StatusInterface;

/**
 * Class Ingenico_Connect_Model_Ingenico_GlobalCollect_OrderStatusHelper
 */
class Ingenico_Connect_Model_Ingenico_GlobalCollect_OrderStatusHelper
{
    /**
     * @var int[]
     */
    protected $statusesForSkipping = array(800, 900, 935, 975);

    /**
     * @var string[]
     */
    protected $applicableMethods = array('card');

    /**
     * @param Payment|Capture|AbstractOrderStatus $ingenicoStatus
     * @return bool
     */
    public function shouldOrderSkipPaymentReview(AbstractOrderStatus $ingenicoStatus)
    {
        $isCapturingMethod = $this->isCapturingCcMethod($ingenicoStatus);
        $isPendingApproval = $ingenicoStatus->status === StatusInterface::PENDING_APPROVAL;

        return ($ingenicoStatus instanceof Payment || $ingenicoStatus instanceof Capture) &&
               ($isCapturingMethod || $isPendingApproval);
    }

    /**
     * Check conditions for CAPTURE_REQUESTED meta status
     *
     * @param Payment|Capture|AbstractOrderStatus $ingenicoStatus
     * @return bool
     */
    protected function isCapturingCcMethod(AbstractOrderStatus $ingenicoStatus)
    {
        $isApplicableMethod = in_array($this->getMethod($ingenicoStatus), $this->applicableMethods, true);
        $isSkipStatus = in_array($this->getStatusCode($ingenicoStatus), $this->statusesForSkipping, true);

        return ($ingenicoStatus->status === StatusInterface::CAPTURE_REQUESTED && $isApplicableMethod && $isSkipStatus);
    }

    /**
     * Extract method string from status object
     *
     * @param Payment|Capture|AbstractOrderStatus $ingenicoStatus
     * @return string
     */
    protected function getMethod(AbstractOrderStatus $ingenicoStatus)
    {
        $method = '';
        if ($ingenicoStatus instanceof Payment) {
            $method = $ingenicoStatus->paymentOutput->paymentMethod;
        } elseif ($ingenicoStatus instanceof Capture) {
            $method = $ingenicoStatus->captureOutput->paymentMethod;
        }

        return $method;
    }

    /**
     * Fetch legacy payment status code
     *
     * @param Payment|Capture|AbstractOrderStatus $ingenicoStatus
     * @return mixed
     */
    protected function getStatusCode(AbstractOrderStatus $ingenicoStatus)
    {
        return $ingenicoStatus->statusOutput->statusCode;
    }
}
