<?php

interface Netresearch_Epayments_Model_WxTransfer_Sftp_ClientInterface
{
    /**
     * Connect to given remote host with given credentials
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @throws \Exception
     * @return Netresearch_Epayments_Model_WxTransfer_Sftp_ClientInterface
     */
    public function connect($host, $username, $password);

    /**
     * Close connection to remote host
     */
    public function disconnect();

    /**
     * Reads the file list of the remote directory and matches all regular files names against the given pattern
     *
     * @param string $pattern regex pattern to check the files against
     * @param string $remoteDir directory on remote host
     * @return string[][] list of files as ['fileName' => [metadata]]
     * @throws \Mage_Core_Exception
     */
    public function getFileCollection($pattern, $remoteDir);

    /**
     * Load remote file
     *
     * @param $fileName
     * @param $remoteDir
     * @return string
     */
    public function loadFile($fileName, $remoteDir = '');
}
