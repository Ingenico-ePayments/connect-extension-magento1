<?php

/**
 * Interface Netresearch_Epayments_Model_WxTransfer_ClientInterface
 */
interface Netresearch_Epayments_Model_WxTransfer_ClientInterface
{
    const WX_FILE_PATTERN = "/^w[x,t][t0-9]*\.%010s%d\.(?<version>[\d]{6})\.?(?:%s)?\.xml.gz/";

    /**
     * Attempts to load WxFile for the given date with the configuration of the given scope
     *
     * @param string $date
     * @param int $scopeId
     * @return \DOMDocument|false - the file contents as DomDocument or false if no file was found
     */
    public function loadDailyWx($date, $scopeId);

}
