<?php

namespace SNGBE;

/**
 * Class SNGBEcomm
 *
 * @package SNGBE
 */
class SNGBEcomm
{

    const VERSION = '0.1.3';


    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $testApiBase = 'https://ecm.sngb.ru/ECommerce';

    /**
     * @var string
     */
    private $productionApiBase = 'https://ecm.sngb.ru/Gateway';

    /**
     * @var bool
     */
    private $liveMode = false;

    /**
     * @var string
     */
    private $apiVersion = null;

    /**
     * @var bool
     */
    private $verifySslCerts = false;

    /**
     * @var string
     */
    private $merchant = '';

    /**
     * @var string
     */
    private $terminalAlias = '';

    /**
     * @param string  $apiKey
     * @param string  $merchant
     * @param string  $terminalAlias
     * @param boolean $liveMode
     * @param boolean $verifySslCerts
     * @param string  $apiVersion
     */
    function __construct($apiKey, $merchant, $terminalAlias, $liveMode = false, $verifySslCerts = false, $apiVersion = null)
    {
        $this->apiKey = $apiKey;
        $this->liveMode = $liveMode;
        $this->verifySslCerts = $verifySslCerts;
        $this->merchant = $merchant;
        $this->terminalAlias = $terminalAlias;
        $this->apiVersion = $apiVersion;
    }

    /**
     * @param string $trackId
     * @param float  $amount
     * @param int    $action
     *
     * @return Payment
     */
    public function createPayment($trackId, $amount, $action)
    {
        $payment = new Payment($this, $trackId, $amount, $action);

        return $payment;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->isLiveMode()?$this->getProductionApiBase():$this->getTestApiBase();
    }


    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     *
     * @return SNGBEcomm;
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getTestApiBase()
    {
        return $this->testApiBase;
    }

    /**
     * @param string $testApiBase
     *
     * @return SNGBEcomm;
     */
    public function setTestApiBase($testApiBase)
    {
        $this->testApiBase = $testApiBase;

        return $this;
    }

    /**
     * @return string
     */
    public function getProductionApiBase()
    {
        return $this->productionApiBase;
    }

    /**
     * @param string $productionApiBase
     *
     * @return SNGBEcomm;
     */
    public function setProductionApiBase($productionApiBase)
    {
        $this->productionApiBase = $productionApiBase;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isLiveMode()
    {
        return $this->liveMode;
    }

    /**
     * @param boolean $liveMode
     *
     * @return SNGBEcomm;
     */
    public function setLiveMode($liveMode)
    {
        $this->liveMode = $liveMode;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * @param string $apiVersion
     *
     * @return SNGBEcomm;
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isVerifySslCerts()
    {
        return $this->verifySslCerts;
    }

    /**
     * @param boolean $verifySslCerts
     *
     * @return SNGBEcomm;
     */
    public function setVerifySslCerts($verifySslCerts)
    {
        $this->verifySslCerts = $verifySslCerts;

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchant()
    {
        return $this->merchant;
    }

    /**
     * @param string $merchant
     *
     * @return SNGBEcomm;
     */
    public function setMerchant($merchant)
    {
        $this->merchant = $merchant;

        return $this;
    }

    /**
     * @return string
     */
    public function getTerminalAlias()
    {
        return $this->terminalAlias;
    }

    /**
     * @param string $terminalAlias
     *
     * @return SNGBEcomm;
     */
    public function setTerminalAlias($terminalAlias)
    {
        $this->terminalAlias = $terminalAlias;

        return $this;
    }
}
