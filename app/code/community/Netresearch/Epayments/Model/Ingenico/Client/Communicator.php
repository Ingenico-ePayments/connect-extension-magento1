<?php

class Netresearch_Epayments_Model_Ingenico_Client_Communicator extends \Ingenico\Connect\Sdk\Communicator
{
    /**
     * @var \Ingenico\Connect\Sdk\CommunicatorConfiguration
     */
    protected $originalCommunicatorConfiguration;

    /**
     * @var \Ingenico\Connect\Sdk\CommunicatorConfiguration
     */
    protected $secondaryCommunicatorConfiguration;

    /**
     * @var bool
     */
    protected $secondaryCommunicatorConfigurationEnabled = false;

    /**
     * @param \Ingenico\Connect\Sdk\CommunicatorConfiguration $communicatorConfiguration
     */
    public function setSecondaryCommunicatorConfiguration(\Ingenico\Connect\Sdk\CommunicatorConfiguration $communicatorConfiguration)
    {
        $this->secondaryCommunicatorConfiguration = $communicatorConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function get(
        \Ingenico\Connect\Sdk\ResponseClassMap $responseClassMap,
        $relativeUriPath,
        $clientMetaInfo = '',
        \Ingenico\Connect\Sdk\RequestObject $requestParameters = null,
        \Ingenico\Connect\Sdk\CallContext $callContext = null
    ) {
        try {
            $result = parent::get($responseClassMap, $relativeUriPath, $clientMetaInfo, $requestParameters, $callContext);
        } catch (\Ingenico\Connect\Sdk\InvalidResponseException $e) {
            $this->swapCommunicatorConfiguration();
            $result = parent::get($responseClassMap, $relativeUriPath, $clientMetaInfo, $requestParameters, $callContext);
        } catch (ErrorException $e) {
            $this->swapCommunicatorConfiguration();
            $result = parent::get($responseClassMap, $relativeUriPath, $clientMetaInfo, $requestParameters, $callContext);
        } catch (Exception $e) {
            throw $e;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Ingenico\Connect\Sdk\ResponseClassMap $responseClassMap,
        $relativeUriPath,
        $clientMetaInfo = '',
        \Ingenico\Connect\Sdk\RequestObject $requestParameters = null,
        \Ingenico\Connect\Sdk\CallContext $callContext = null
    ) {
        try {
            $result = parent::delete($responseClassMap, $relativeUriPath, $clientMetaInfo, $requestParameters, $callContext);
        } catch (\Ingenico\Connect\Sdk\InvalidResponseException $e) {
            $this->swapCommunicatorConfiguration();
            $result = parent::delete($responseClassMap, $relativeUriPath, $clientMetaInfo, $requestParameters, $callContext);
        } catch (ErrorException $e) {
            $this->swapCommunicatorConfiguration();
            $result = parent::delete($responseClassMap, $relativeUriPath, $clientMetaInfo, $requestParameters, $callContext);
        } catch (Exception $e) {
            throw $e;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function post(
        \Ingenico\Connect\Sdk\ResponseClassMap $responseClassMap,
        $relativeUriPath,
        $clientMetaInfo = '',
        \Ingenico\Connect\Sdk\DataObject $body = null,
        \Ingenico\Connect\Sdk\RequestObject $requestParameters = null,
        \Ingenico\Connect\Sdk\CallContext $callContext = null
    ) {
        try {
            $result = parent::post($responseClassMap, $relativeUriPath, $clientMetaInfo, $body, $requestParameters, $callContext);
        } catch (\Ingenico\Connect\Sdk\InvalidResponseException $e) {
            $this->swapCommunicatorConfiguration();
            $result = parent::post($responseClassMap, $relativeUriPath, $clientMetaInfo, $body, $requestParameters, $callContext);
        } catch (ErrorException $e) {
            $this->swapCommunicatorConfiguration();
            $result = parent::post($responseClassMap, $relativeUriPath, $clientMetaInfo, $body, $requestParameters, $callContext);
        } catch (Exception $e) {
            throw $e;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function put(
        \Ingenico\Connect\Sdk\ResponseClassMap $responseClassMap,
        $relativeUriPath,
        $clientMetaInfo = '',
        \Ingenico\Connect\Sdk\DataObject $body = null,
        \Ingenico\Connect\Sdk\RequestObject $requestParameters = null,
        \Ingenico\Connect\Sdk\CallContext $callContext = null
    ) {
        try {
            $result = parent::put($responseClassMap, $relativeUriPath, $clientMetaInfo, $body, $requestParameters, $callContext);
        } catch (\Ingenico\Connect\Sdk\InvalidResponseException $e) {
            $this->swapCommunicatorConfiguration();
            $result = parent::put($responseClassMap, $relativeUriPath, $clientMetaInfo, $body, $requestParameters, $callContext);
        } catch (ErrorException $e) {
            $this->swapCommunicatorConfiguration();
            $result = parent::put($responseClassMap, $relativeUriPath, $clientMetaInfo, $body, $requestParameters, $callContext);
        } catch (Exception $e) {
            throw $e;
        }

        return $result;
    }

    /**
     * Change original comunicator configuration to secondary configuration
     *
     * @return null
     */
    protected function swapCommunicatorConfiguration()
    {
        if (!$this->secondaryCommunicatorConfigurationEnabled && $this->secondaryCommunicatorConfiguration) {
            $this->secondaryCommunicatorConfigurationEnabled = true;
            $this->originalCommunicatorConfiguration = $this->getCommunicatorConfiguration();
            $this->setCommunicatorConfiguration($this->secondaryCommunicatorConfiguration);
        } elseif ($this->originalCommunicatorConfiguration) {
            $this->secondaryCommunicatorConfigurationEnabled = false;
            $this->setCommunicatorConfiguration($this->originalCommunicatorConfiguration);
        }
    }
}
