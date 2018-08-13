<?php

use Netresearch_Epayments_Model_WxTransfer_Sftp_ClientInterface as ClientInterface;

/**
 * Class Client
 * @package Netresearch\Epayments\WxTransfer\Sftp
 */
class Netresearch_Epayments_Model_WxTransfer_Sftp_Client implements ClientInterface
{
    /** @var \Varien_Io_Sftp */
    private $sftpClient = null;

    /**
     * Connect to given remote host with given credentials
     *
     * @param $host
     * @param $username
     * @param $password
     * @throws \Exception
     * @return $this
     */
    public function connect($host, $username, $password)
    {
        $this->sftpClient = new \Varien_Io_Sftp();
        $this->sftpClient->open(
            array(
                'host' => $host,
                'username' => $username,
                'password' => $password,
            )
        );
        return $this;
    }

    public function disconnect()
    {
        $this->sftpClient->close();
    }

    /**
     * Reads the file list of the remote directory and matches all regular files names against the given pattern
     *
     * @param string $pattern regex pattern to check the files against
     * @param string $remoteDir directory on remote host
     * @return string[][] list of files as ['fileName' => [metadata]]
     * @throws \Mage_Core_Exception
     */
    public function getFileCollection($pattern, $remoteDir)
    {
        if (preg_match($pattern, null) === false) {
            \Mage::throwException("Pattern {$pattern} is not a valid regular expression");
        }

        if (!$this->sftpClient) {
            \Mage::throwException('Please connect the client first.');
        }

        if (!$this->sftpClient->cd($remoteDir)) {
            \Mage::throwException("Could not read directory '{$remoteDir}' on remote host.");
        }

        $fileList = $this->sftpClient->rawls();
        $fileList = array_filter(
            $fileList,
            // in this version of the sftp client implementation the filename is being returned as key
            function ($element, $key) use ($pattern) {
                return preg_match($pattern, $key) > 0;
            },
            ARRAY_FILTER_USE_BOTH
        );

        return $fileList;
    }

    /**
     * @param string $fileName
     * @param string $remoteDir
     * @return string
     * @throws \Mage_Core_Exception
     */
    public function loadFile($fileName, $remoteDir = '')
    {
        if (!empty($remoteDir) && is_string($remoteDir)) {
            $this->sftpClient->cd($remoteDir);
        }

        $data = $this->sftpClient->read($fileName);
        $gzuncompress = gzdecode($data);
        if (!$gzuncompress) {
            \Mage::throwException(
                "Could not decompress compressed WX file {$fileName}"
            );
        }
        return $gzuncompress;
    }
}
